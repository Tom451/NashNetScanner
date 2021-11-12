using NND_Agent.Items;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Net.NetworkInformation;
using System.Text;
using System.Threading.Tasks;
using System.Xml;

namespace NND_Agent.Data
{
    internal class DataClass
    {
        List<ComputerModel> devices = new List<ComputerModel>();
        public void StartScan(long userNONCE)
        {
            //initalise the classes
            DataUpload Connection = new DataUpload();
 

            //get the scan
            ScanModel scan = Connection.SendGet("http://localhost/assets/php/DBUploadConn.php?USERID=" + userNONCE);

            List<ComputerModel> upload = NMapScan(scan);

            //convert the device objects to JSON
            try
            {
                //get ready for item upload
                string uploadDeviceJSON = Connection.ToJSON(upload);

                //Convert the scan to JSON
                scan.ScanStatus = "Finished";
                string uploadScanJSON = Connection.ToJSON(scan);

                //upload the devices
                Connection.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("JSON={0}", uploadDeviceJSON));

                //upload the scan
                Connection.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("SCANUPDATE={0}", uploadScanJSON));
            }
            catch (Exception)
            {
                scan.ScanStatus = "Failed";

            }
        }

        public List<ComputerModel> NMapScan(ScanModel scan)
        {
            //Start the Scan
            Process process = new Process();
            ProcessStartInfo startInfo = new ProcessStartInfo
            {
                WindowStyle = ProcessWindowStyle.Hidden,
                FileName = "cmd.exe"
            };

            //get gateway IP and Mac Address for scan 
            string gateway = GetNetworkGateway();
            string[] gatewayArray = gateway.Split('.');

            string ScanSaveLocation = "C:\\Users\\Public\\Documents\\NMAPNetworkScan.xml";

            // Pass the variables in 
            startInfo.Arguments = String.Format("/C {0} {1} {2}.{3}.{4}.*/24 --no-stylesheet ", scan.scanInfo, ScanSaveLocation, gatewayArray[0], gatewayArray[1], gatewayArray[2], gatewayArray[3]);
            process.StartInfo = startInfo;
            process.Start();

            // Read the output stream first and then wait.
            process.WaitForExit();

            if (scan.scanType == "NetDisc")
            {
                //parse the scan data 
                ParseNetworkDiscoveryData(scan);

            }



            return devices;


        }

        public void ParseNetworkDiscoveryData(ScanModel scan)
        {
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

                devices.Add(tempDevice);

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
