using NND_Agent.Items;
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
    internal class DataProcessing
    {
        //current user 
        readonly userModel currentUser = new userModel();

        //current form
        readonly NNDAgent form = NNDAgent.NNDForm;

        //data connection 
        readonly DataUpload Connection = new DataUpload();

        //public methods accessible from outside this class
        //start scan method 
        public async Task StartScan(long userNONCE)
        {

            //initialise the classes
            currentUser.userName = userNONCE.ToString();



            //get the scan
            currentUser.listScans = Connection.SendGet("http://" + NNDAgent.WebpageAddress + "/assets/php/database/DBUploadConn.php?USERID=" + userNONCE);




            if (currentUser.listScans == null)
            {
                form.PopUp("Error with fetching scan", "No scan available please start a scan from the web interface", System.Windows.Forms.ToolTipIcon.Warning);
                return;

            }

            //start the scan
            foreach (var currentScan in currentUser.listScans)
            {
                //reset the current scan 
                currentUser.currentScan = currentScan;
                currentUser.scannedVulns = null;
                currentUser.scannedDevices = null;

                bool tryAgain = true;
                while (tryAgain)
                {
                    try
                    {
                        if (NMapScan(currentScan, userNONCE))
                        {
                            //Convert the scan to JSON
                            currentScan.ScanStatus = "Finished";
                            tryAgain = false;
                        }
                        else
                        {
                            //Convert the scan to JSON
                            currentScan.ScanStatus = "Error";
                            tryAgain = false;
                        }
                    }
                    catch (System.IO.IOException)
                    {

                        try
                        {
                            foreach (Process process in Process.GetProcessesByName("Nmap"))
                            {
                                process.Kill();
                            }
                            Thread.Sleep(5000);
                        }
                        catch
                        {
                            form.PopUp("NMAP is using the file. Attempting to close NMAP Failed.", "System will wait for 30 seconds, please try close NMAP from Task Manager ", System.Windows.Forms.ToolTipIcon.Error);
                            Thread.Sleep(30000);
                        }



                    }
                }




                //get ready for item upload
                //create a temp upload object 
                userModel tempUserModel = new userModel
                {
                    currentScan = currentUser.currentScan,
                    scannedVulns = currentUser.scannedVulns,
                    scannedDevices = currentUser.scannedDevices,
                    userName = currentUser.userName
                };

                string uploadJSON = await Task.Run(() => Connection.ToJSON(tempUserModel));

                //upload the devices
                try
                {
                    Connection.SendPost("http://" + NNDAgent.WebpageAddress + "/assets/php/database/DBUploadConn.php", String.Format("UploadWithVerification={0}", uploadJSON));

                }
                catch
                {
                    form.PopUp("No returned value", "Website may be down try again later", System.Windows.Forms.ToolTipIcon.Error);
                }
            }





        }

        //check for scan method
        public bool CheckForScan(long userNONCE)
        {
            if (Connection.SendGet("http://" + NNDAgent.WebpageAddress + "/assets/php/database/DBUploadConn.php?USERID=" + userNONCE) == null )
            {
                return false;
            }
            else
            {
                return true;
            };
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
                ScanModel currentScan = new ScanModel
                {
                    userName = userNonce.ToString(),
                    ScanStatus = "Scan Pending"
                };

                //start a network scan to get device mac address 
                RunNetworkScan(scan, process, startInfo);

                //set to scanning 
                if (currentUser.scannedDevices.Count == 0)
                {
                    //if the host is down and there are no devices 
                    currentScan.ScanStatus = "Host Down";
                    currentScan.scanID = currentUser.currentScan.scanID;
                    currentScan.scanType = "Return";
                    currentScan.scanInfo = currentUser.currentScan.scanInfo;
                    
                    Connection.SendPost("http://" + NNDAgent.WebpageAddress + "/assets/php/database/DBUploadConn.php", String.Format("UploadWithVerification={0}", Connection.ToJSON(currentScan)));
                    return false;
                }
                else
                {
                    currentScan.scanInfo = currentUser.scannedDevices[0].macAddress;
                    currentScan.ScanStatus = "Scanning";
                    Connection.SendPost("http://" + NNDAgent.WebpageAddress + "/assets/php/database/DBUploadConn.php", String.Format("UploadWithVerification={0}", Connection.ToJSON(currentScan)));

                    if(!RunVulnScan(scan, process, startInfo))
                    {
                        currentScan.ScanStatus = "Error";
                        Connection.SendPost("http://" + NNDAgent.WebpageAddress + "/assets/php/database/DBUploadConn.php", String.Format("UploadWithVerification={0}", Connection.ToJSON(currentScan)));
                        //wait 10 seconds for the next scan, this gives the program chnace to finish (NMAP)
                        Thread.Sleep(10000);
                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }

            }

            return true;


        }

        //progress check method 
        public int CheckProgress()
        {
            // set the scan count initial value of 0 
            int scanCount = 0;
            try
            {
                // if the current user doesnt have a lsit of scans then that means no scans 
                if (currentUser.listScans is null)
                {
                    scanCount = 0;
                }
                else
                {
                    // go through the items and if it is set as pending then incremet the count
                    foreach (var item in currentUser.listScans)
                    {
                        if (item.ScanStatus == "Pending")
                        {
                            scanCount++;
                        }
                    }
                }

            }
            catch (NullReferenceException)
            {
                // if the null refernece is thrown then there will be 0 scans 
                scanCount = 0;
            }

            // return the count 
            return scanCount;
        }


        //private methods 
        #region Parse Data Area
        private Boolean ParseVulnerbilityData(ScanModel scan)
        {
            //create a new list for the currently scanned vulnerbilities
            currentUser.scannedVulns = new List<VulnModel>();

            //read in data from the created XML File
            XmlDocument NMapXMLScan = new XmlDocument();

            //load the data after written
            NMapXMLScan.Load("C:\\Users\\Public\\Documents\\NMAPVulnScan.xml");

            //check if all the hosts are down
            XmlNode hosts = NMapXMLScan.SelectSingleNode("nmaprun/runstats/hosts");

            //get the number of down devices 
            var numberDown = hosts.Attributes.GetNamedItem("down").InnerText;

            //get the number of total devices 
            var numberTotal = hosts.Attributes.GetNamedItem("total").InnerText;

            //the the number of devices down is the same as number of devices total e.g 1 down 1 total then the device is down.
            if (numberDown == numberTotal)
            {
                form.PopUp("Host Down", "The host is currently down", System.Windows.Forms.ToolTipIcon.Warning);
                return false;
            }
            


            //select all the hosts in the document 
            XmlNodeList ports = NMapXMLScan.SelectNodes("nmaprun/host/ports/port");

            for (int i = 0; i < ports.Count - 1; i++)
            { 
                // start by creating a new vulnerbility model 
                VulnModel tempModel = new VulnModel();

                //port that is effected 
                var port = ports.Item(i);
                var service = port.SelectSingleNode("service");


                //if there is a name then set 
                if (service.Attributes.GetNamedItem("name") != null)
                {
                    tempModel.VulnName = service.Attributes.GetNamedItem("name").InnerText;
                }
                else
                {
                    //else set it as null
                    tempModel.VulnName = null;
                }

                //same as number 1 but for verison e.g 4.5
                if (service.Attributes.GetNamedItem("version") != null)
                {
                    tempModel.VulnVersion = service.Attributes.GetNamedItem("version").InnerText;
                }
                else
                {
                    tempModel.VulnVersion = "No Value Found";
                }

                //same as number 1 but for the product e.g MySQL 
                if (service.Attributes.GetNamedItem("product") != null)
                {
                    tempModel.VulnProduct = service.Attributes.GetNamedItem("product").InnerText;
                }
                else
                {
                    tempModel.VulnProduct = "No Value Found";
                }

                //same as number 1 but for extra info, this can contain extra info e.g patch 4
                if (service.Attributes.GetNamedItem("extrainfo") != null)
                {
                    tempModel.VulnProduct = service.Attributes.GetNamedItem("extrainfo").InnerText;
                }
                else
                {
                    tempModel.VulnExtraData = "No Value Found";
                }
                //same as number 1 but for cpe 
                if (service.Attributes.GetNamedItem("cpe") != null)
                {
                    tempModel.VulnCPE = service.Attributes.GetNamedItem("cpe").InnerText;
                }

                //if cpe is nested inside a cpe section 
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

                //get the port ID from the port specified at the start
                tempModel.VulnPortNumber = port.Attributes.GetNamedItem("portid").InnerText;
                //get the current scanID
                tempModel.scanID = scan.scanID;
                //add this vulnerbility to the list of vulnerbilites 
                currentUser.scannedVulns.Add(tempModel);

            }
            //one device is done at a time due to then the ability to update on the fly is possible 
            if (currentUser.scannedDevices == null)
            {
                //create the list
                currentUser.scannedDevices = new List<ComputerModel>();

                //create a temp devices
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
        private Boolean ParseNetworkDiscoveryData(ScanModel scan)
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
                int hostRTT;

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
                catch(Exception)
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

            return true;


        }

        #endregion

        #region Run Scan Area

        private Boolean RunNetworkScan(ScanModel scan, Process process, ProcessStartInfo startInfo)
        {
            //get gateway IP and Mac Address for scan 
            string gateway = GetNetworkGateway();
            string[] gatewayArray = gateway.Split('.');

            //if scan info is set as N/A then network scan is needed 
            if (scan.scanInfo == "N/A")
            {
                startInfo.Arguments = String.Format("/C nmap -sn -oX C:\\Users\\Public\\Documents\\NMAPNetworkScan.xml {0}.{1}.{2}.*/24 --no-stylesheet ", gatewayArray[0], gatewayArray[1], gatewayArray[2]);
                process.StartInfo = startInfo;


                process.Start();
            }
            // else if an ip is provided then scan that specific ip 
            else if (IPAddress.TryParse(scan.scanInfo, out IPAddress ip))
            {
                startInfo.Arguments = String.Format("/C nmap -sn -oX C:\\Users\\Public\\Documents\\NMAPNetworkScan.xml {0} --no-stylesheet ", ip.ToString());
                process.StartInfo = startInfo;


                process.Start();
            }

            //start the proccess and wait for the output
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

        private Boolean RunVulnScan(ScanModel scan, Process process, ProcessStartInfo startInfo)
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
                //if the scan takes longer then 3 minutes inform the user and then go to the next scan 
                form.PopUp("Scan took longer then 3 mins", "Scan canceled for exceeding length", System.Windows.Forms.ToolTipIcon.Error);
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

        #endregion

        


    }
}
