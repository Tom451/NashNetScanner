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

namespace NND_Agent.Data
{
    internal class DataClass
    {
        readonly userModel currentUser = new userModel();
        readonly NNDAgent form = NNDAgent.NNDForm;
        
        public void StartScan(long userNONCE)
        {
            //initalise the classes
            currentUser.UserName = userNONCE.ToString();
            DataUpload Connection = new DataUpload();
 

            //get the scan
            currentUser.currentScan = Connection.SendGet("http://localhost/assets/php/DBUploadConn.php?USERID=" + userNONCE);

            if (currentUser.currentScan == null)
            {
                form.PopUp("Error with fetching scan", "No scan avalable please start a scan from the web interface", System.Windows.Forms.ToolTipIcon.Warning);
                return;

            }

            //start the scan
            if (NMapScan(currentUser.currentScan))
            {
                //Convert the scan to JSON
                currentUser.currentScan.ScanStatus = "Finished";
            }
            else
            {
                //Convert the scan to JSON
                currentUser.currentScan.ScanStatus = "Error";
            }
            

            

            //get ready for item upload
            string uploadJSON = Connection.ToJSON(currentUser);

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

        //returns true on success and false on error 
        public bool NMapScan(ScanModel scan)
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

            //get gateway IP and Mac Address for scan 
            string gateway = GetNetworkGateway();
            string[] gatewayArray = gateway.Split('.');

            

            // Pass the variables in 

            if (scan.scanType == "NetDisc")
            {
                

                startInfo.Arguments = String.Format("/C {0} {1}.{2}.{3}.*/24 --no-stylesheet ", scan.scanInfo, gatewayArray[0], gatewayArray[1], gatewayArray[2]);
                process.StartInfo = startInfo;


                process.Start();

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
            else if (scan.scanType == "VulnScan")
            {


                startInfo.Arguments = String.Format("/C {0} --no-stylesheet ",  scan.scanInfo);
                process.StartInfo = startInfo;
                process.Start();

                //Read the output stream first and then wait.
                //Wait 3 mins 

                
                if (process.WaitForExit(180000))
                {
                    ParseVulnerbilityData(scan);
                    return true;
                }
                else
                {
                    form.PopUp("Scan took longer then 3 mins", "Scan canceled for exceeding length", System.Windows.Forms.ToolTipIcon.Error);
                    return false;
                }
                
                

                
            }
            return false;


        }
        

        private void ParseVulnerbilityData(ScanModel scan)
        {

            currentUser.scannedVulns = new List<VulnModel>();

            //read in data from the created XML File
            XmlDocument NMapXMLScan = new XmlDocument();

            //load the data after written
            NMapXMLScan.Load("C:\\Users\\Public\\Documents\\NMAPVulnScan.xml");

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
                return;
            }


            scannedDevice.ScanID = scan.scanID;

            currentUser.scannedDevices.Add(scannedDevice);



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
            for (int i = 0; i < hosts.Count - 1; i++)
            {
                

                //create a temporay computer model
                ComputerModel tempDevice = new ComputerModel();

                //get the host currently at "i"
                var host = hosts.Item(i);


                //select the addresses nodeS this contains mac and ip
                var hostAddresses = host.SelectNodes("address");

                //sleect the host name node, this needs to be done twice just with how the way nmap laid out the nodes
                var hostName = host.SelectSingleNode("hostnames").SelectSingleNode("hostname");

                //select the addresses nodeS this contains mac and ip
                var hostTimes = host.SelectSingleNode("times");

                //creat string for host name, ip address and the mac address
                string hostActualName;
                int hostRTT = int.Parse(hostTimes.Attributes.GetNamedItem("rttvar").InnerText);
                string hostipaddress = hostAddresses.Item(0).Attributes.GetNamedItem("addr").InnerText;
                string mac = hostAddresses.Item(1).Attributes.GetNamedItem("addr").InnerText;

                // if the host name is null then set a default name 
                if (hostName == null)
                {
                    hostActualName = mac;
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


    }
}
