﻿using NND_Agent.Items;
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Diagnostics;
using System.Linq;
using System.Net.NetworkInformation;
using System.Text;
using System.Threading;
using System.Threading.Tasks;
using System.Xml;
using System.Text.RegularExpressions;
using Newtonsoft.Json;
using System.Net;

namespace NND_Agent.Data
{
    internal class DataClass
    {
        //current user 
        readonly userModel currentUser = new userModel();
        
        //current form
        NNDAgent form = NNDAgent.NNDForm;
        //data connection 
        DataUpload Connection = new DataUpload();
        //current user scanned devices 

        public bool checkForScan(long userNONCE)
        {
            if (Connection.SendGet("http://localhost/assets/php/DBUploadConn.php?USERID=" + userNONCE) == null)
            {
                return false;
            }
            else
            {
                return true;
            };
        }


        public async Task StartScan(long userNONCE)
        {

            //initalise the classes
            currentUser.userName = userNONCE.ToString();
            
 

            //get the scan
            currentUser.listScans = Connection.SendGet("http://localhost/assets/php/DBUploadConn.php?USERID=" + userNONCE);

            
            

            if (currentUser.listScans == null)
            {
                form.PopUp("Error with fetching scan", "No scan avalable please start a scan from the web interface", System.Windows.Forms.ToolTipIcon.Warning);
                return;

            }

            //start the scan
            foreach (var currentScan in currentUser.listScans)
            {
                //reset the current scan 
                currentUser.currentScan = currentScan;
                currentUser.scannedVulns = null;
                currentUser.scannedDevices = null;
                try
                {
                    if (NMapScan(currentScan, userNONCE))
                    {
                        //Convert the scan to JSON
                        currentScan.ScanStatus = "Finished";
                    }
                    else
                    {
                        //Convert the scan to JSON
                        currentScan.ScanStatus = "Error";
                    }
                }
                catch (System.IO.IOException ex)
                {
                    Thread.Sleep(10000);
                }
                

                //get ready for item upload
                //create a temp upload object 
                userModel tempUserModel = new userModel();
                tempUserModel.currentScan = currentUser.currentScan;
                tempUserModel.scannedVulns = currentUser.scannedVulns;
                tempUserModel.scannedDevices = currentUser.scannedDevices;
                tempUserModel.userName = currentUser.userName;

                string uploadJSON = await Task.Run(() => Connection.ToJSON(tempUserModel));

                //upload the devices
                try
                {
                    Connection.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("UploadWithVerification={0}", uploadJSON));

                }
                catch
                {
                    form.PopUp("No returned value", "Website may be down try again later", System.Windows.Forms.ToolTipIcon.Error);
                }
            }
            
            
            

           
        }

        //returns true on success and false on error 
        public bool NMapScan(ScanModel scan, long userNonce)
        {
            //Start the Scan
            Process process = new Process();
            ProcessStartInfo startInfo = new ProcessStartInfo
            {
                WindowStyle = ProcessWindowStyle.Hidden,
                FileName = "cmd.exe",
                UseShellExecute = false,
                CreateNoWindow = true

            };



                  
            // Pass the variables in 

            if (scan.scanType == "NetDisc")
            {
                return RunNetworkScan(scan, process, startInfo);
            }
            else if (scan.scanType == "VulnScan")
            {
                //upload the current device being scanned 
                ScanModel currentScan = new ScanModel();
                currentScan.userName = userNonce.ToString();
                currentScan.ScanStatus = "Scan Pending";

                //start a network scan to get device mac address 
                RunNetworkScan(scan, process, startInfo);

                //set to scanning 
                if (currentUser.scannedDevices.Count == 0)
                {
                    //if the host is down and there are no devices 
                    ComputerModel hostDown = new ComputerModel();
                    currentScan.ScanStatus = "Host Down";
                    currentScan.scanID = currentUser.currentScan.scanID;
                    currentScan.scanType = "Return";
                    currentScan.scanInfo = currentUser.currentScan.scanInfo;
                    
                    Connection.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("UploadWithVerification={0}", Connection.ToJSON(currentScan)));
                    return false;
                }
                else
                {
                    currentScan.scanInfo = currentUser.scannedDevices[0].macAddress;
                    currentScan.ScanStatus = "Scanning";
                    Connection.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("UploadWithVerification={0}", Connection.ToJSON(currentScan)));

                    if(!RunVulnScan(scan, process, startInfo))
                    {
                        currentScan.ScanStatus = "Error";
                        Connection.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("UploadWithVerification={0}", Connection.ToJSON(currentScan)));
                        return false;
                    };
                }
                

                //upload the current device being scanned
                 if (currentUser.scannedVulns == null || currentUser.scannedVulns.Count > 0)
                {
                    currentScan.ScanStatus = "Analysing";
                }
                else
                {
                    currentScan.ScanStatus = "Yes: Safe";
                }
                
                Connection.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("UploadWithVerification={0}", Connection.ToJSON(currentScan)));

                return true;

            }

            return true;


        }
        
        private Boolean ParseVulnerbilityData(ScanModel scan)
        {

            currentUser.scannedVulns = new List<VulnModel>();

            //read in data from the created XML File
            XmlDocument NMapXMLScan = new XmlDocument();

            //load the data after written
            NMapXMLScan.Load("C:\\Users\\Public\\Documents\\NMAPVulnScan.xml");

            //check if all the hosts are down
            XmlNode hosts = NMapXMLScan.SelectSingleNode("nmaprun/runstats/hosts");

            var numberDown = hosts.Attributes.GetNamedItem("down").InnerText;
            var numberTotal = hosts.Attributes.GetNamedItem("total").InnerText;

            if (numberDown == numberTotal)
            {
                form.PopUp("Host Down", "The host is currently down", System.Windows.Forms.ToolTipIcon.Warning);
                return false;
            }
            


            //select all the hosts in the document 
            XmlNodeList ports = NMapXMLScan.SelectNodes("nmaprun/host/ports/port");

            for (int i = 0; i < ports.Count - 1; i++)
            { 
                VulnModel tempModel = new VulnModel();

                var port = ports.Item(i);
                var service = port.SelectSingleNode("service");



                if (service.Attributes.GetNamedItem("name") != null)
                {
                    tempModel.VulnName = service.Attributes.GetNamedItem("name").InnerText;
                }
                else
                {
                    tempModel.VulnName = null;
                }

                if (service.Attributes.GetNamedItem("version") != null)
                {
                    tempModel.VulnVersion = service.Attributes.GetNamedItem("version").InnerText;
                }
                else
                {
                    tempModel.VulnVersion = "No Value Found";
                }


                if (service.Attributes.GetNamedItem("product") != null)
                {
                    tempModel.VulnProduct = service.Attributes.GetNamedItem("product").InnerText;
                }
                else
                {
                    tempModel.VulnProduct = "No Value Found";
                }

                if (service.Attributes.GetNamedItem("extrainfo") != null)
                {
                    tempModel.VulnProduct = service.Attributes.GetNamedItem("extrainfo").InnerText;
                }
                else
                {
                    tempModel.VulnExtraData = "No Value Found";
                }
                if (service.Attributes.GetNamedItem("cpe") != null)
                {
                    tempModel.VulnCPE = service.Attributes.GetNamedItem("cpe").InnerText;
                }
                //if cpe is nested 
                else
                {
                    if(service.ChildNodes.Count == 0)
                    {
                        tempModel.VulnCPE = "NO CPE";
                    }
                    else 
                    {
                        //if it is nested and services has chind nodes then try get a cpe 
                        try
                        {
                            tempModel.VulnCPE = service.SelectNodes("cpe").Item(0).InnerText;

                        }
                        catch (Exception)
                        {

                            tempModel.VulnCPE = "NO CPE";
                        }

                    }
                    
                }

                tempModel.VulnPortNumber = port.Attributes.GetNamedItem("portid").InnerText;

                tempModel.scanID = scan.scanID;

                currentUser.scannedVulns.Add(tempModel);


                

            }
            if (currentUser.scannedDevices == null)
            {
                //create the list
                currentUser.scannedDevices = new List<ComputerModel>();
                ComputerModel scannedDevice = new ComputerModel();

                //select all the hosts in the document with addresses
                XmlNodeList addresses = NMapXMLScan.SelectNodes("nmaprun/host/address");

                scannedDevice.ipAddress = addresses.Item(0).Attributes.GetNamedItem("addr").InnerText;
                scannedDevice.macAddress = addresses.Item(1).Attributes.GetNamedItem("addr").InnerText;

                //get the name, this is last as it may be false and error checking is needed 
                try
                {
                    XmlNode name = NMapXMLScan.SelectSingleNode("nmaprun/host/hostnames/hostname");

                    if (name != null)
                    {
                        scannedDevice.name = name.Attributes.GetNamedItem("name").InnerText;
                    }
                    else
                    {
                        scannedDevice.name = scannedDevice.macAddress;
                    }
                }
                catch (Exception ex)
                {
                    form.PopUp("Error", ex.Message, System.Windows.Forms.ToolTipIcon.Error);
                    return false;
                }


                scannedDevice.ScanID = scan.scanID;

                currentUser.scannedDevices.Add(scannedDevice);
            }
            
            

            return true;



        }
        public void ParseNetworkDiscoveryData(ScanModel scan)
        {
            currentUser.scannedDevices = new List<ComputerModel>();

            //read in data from the created XML File
            XmlDocument NMapXMLScan = new XmlDocument();

            //load the data after written
            NMapXMLScan.Load("C:\\Users\\Public\\Documents\\NMAPNetworkScan.xml");

            //select all the hosts in the document 
            XmlNodeList hosts = NMapXMLScan.SelectNodes("nmaprun/host");

            //Get the number of hosts and loop through them.
            for (int i = 0; i < hosts.Count; i++)
            {
                

                //create a temporay computer model
                ComputerModel tempDevice = new ComputerModel();

                //get the host currently at "i"
                var host = hosts.Item(i);

                //select the addresses nodeS this contains mac and ip and check if its an actual connection
                var status = host.SelectSingleNode("status").Attributes.GetNamedItem("reason").InnerText;

                if (status == "localhost-response")
                {
                    break;
                }

                //select the addresses nodeS this contains mac and ip
                var hostAddresses = host.SelectNodes("address");

                //sleect the host name node, this needs to be done twice just with how the way nmap laid out the nodes
                var hostName = host.SelectSingleNode("hostnames").SelectSingleNode("hostname");

                //select the addresses nodeS this contains mac and ip
                var hostTimes = host.SelectSingleNode("times");

                //creat string for host name, ip address and the mac address
                string hostActualName;

                //catch RTT issue 
                int hostRTT = 0;

                try
                {
                    if (hostTimes is null)
                    {
                        hostRTT = 0;
                    }
                    else
                    {
                        hostRTT = int.Parse(hostTimes.Attributes.GetNamedItem("rttvar").InnerText);
                    }


                    
                    
                }
                catch(Exception ex)
                {
                    hostRTT = 0;
                }
                
                string hostipaddress = hostAddresses.Item(0).Attributes.GetNamedItem("addr").InnerText;
                string mac = hostAddresses.Item(1).Attributes.GetNamedItem("addr").InnerText;

                // if the host name is null then set a default name 
                if (hostName == null)
                {
                    if(hostAddresses.Item(1).Attributes.GetNamedItem("vendor") == null)
                    {
                        hostActualName = mac;
                    }
                    else
                    {
                        hostActualName = hostAddresses.Item(1).Attributes.GetNamedItem("vendor").InnerText;
                    }

                    
                }
                //else if there os a name then set it to that
                else
                {

                    hostActualName = hostName.Attributes.GetNamedItem("name").InnerText;
                }

                //Set all the values 
                tempDevice.ID = i;
                tempDevice.RTT = hostRTT;
                tempDevice.ipAddress = hostipaddress;
                tempDevice.macAddress = mac;
                tempDevice.name = hostActualName;
                tempDevice.ScanID = scan.scanID;

                currentUser.scannedDevices.Add(tempDevice);

            }



        }

        public Boolean RunNetworkScan(ScanModel scan, Process process, ProcessStartInfo startInfo)
        {
            //get gateway IP and Mac Address for scan 
            string gateway = GetNetworkGateway();
            string[] gatewayArray = gateway.Split('.');
            IPAddress ip;

            if (scan.scanInfo == "N/A")
            {
                startInfo.Arguments = String.Format("/C nmap -sn -oX C:\\Users\\Public\\Documents\\NMAPNetworkScan.xml {0}.{1}.{2}.*/24 --no-stylesheet ", gatewayArray[0], gatewayArray[1], gatewayArray[2]);
                process.StartInfo = startInfo;


                process.Start();
            }
            else if(IPAddress.TryParse(scan.scanInfo, out ip))
            {
                startInfo.Arguments = String.Format("/C nmap -sn -oX C:\\Users\\Public\\Documents\\NMAPNetworkScan.xml {0} --no-stylesheet ", ip.ToString() );
                process.StartInfo = startInfo;


                process.Start();
            }
            

            //Read the output stream first and then wait.
            if (process.WaitForExit(180000))
            {
                //parse the scan data 
                ParseNetworkDiscoveryData(scan);
                return true;
            }
            else
            {
                form.PopUp("Scan took longer then 3 mins", "Scan canceled for exceeding length", System.Windows.Forms.ToolTipIcon.Error);

                return false;
            }
        }

        public Boolean RunVulnScan(ScanModel scan, Process process, ProcessStartInfo startInfo)
        {
            

            startInfo.Arguments = String.Format("/C nmap -sV -oX C:\\Users\\Public\\Documents\\NMAPVulnScan.xml {0} --no-stylesheet ", scan.scanInfo);
            process.StartInfo = startInfo;
            process.Start();

            //Read the output stream first and then wait.
            //Wait 3 mins 


            if (process.WaitForExit(180000))
            {
                return ParseVulnerbilityData(scan);
            }
            else
            {

                form.PopUp("Scan took longer then 3 mins", "Scan canceled for exceeding length", System.Windows.Forms.ToolTipIcon.Error);
                Thread.Sleep(10000);
                return false;
            }
        }
        public string GetNetworkGateway()
        {
            //start with a blank IP
            string ip = null;


            //for each "network interface" so get all interfaces
            foreach (NetworkInterface f in NetworkInterface.GetAllNetworkInterfaces())
            {
                //Find the network interface that is currently in use and UP, this will allow me to
                //obtain the information
                if (f.OperationalStatus == OperationalStatus.Up)
                {

                    foreach (GatewayIPAddressInformation d in f.GetIPProperties().GatewayAddresses)
                    {

                        ip = d.Address.ToString();

                    }
                }
            }

            return ip;
        }

        public int CheckProgress()
        {
            int scanCount = 0;

            foreach (var item in currentUser.listScans)
            {
                if(item.ScanStatus == "Pending")
                {
                    scanCount++;
                }
            }

            return scanCount;
        }


    }
}
