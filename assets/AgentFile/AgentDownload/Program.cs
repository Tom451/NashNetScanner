using AgentDownload.DBFolder;
using AgentDownload.Items;
using MySql.Data.MySqlClient;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Net.NetworkInformation;
using System.Xml;

namespace AgentDownload
{
    class Program
    {

       

        public static void Main(string[] args)
        {
            //Welcome the user to the program
            Console.WriteLine("Welcome to NashNetworkDashboard Agent Program");
            Console.WriteLine("Please enter your ScanID provided by the webpage:");

            
            string scanID = Console.ReadLine();

            
            Program NMAP = new Program();
            NMAP.NMapScan();
            
        }

        List<ComputerModel> devices = new List<ComputerModel>();

        public bool NMapScan()
        {
            //Start the Scan
            System.Diagnostics.Process process = new System.Diagnostics.Process();
            System.Diagnostics.ProcessStartInfo startInfo = new System.Diagnostics.ProcessStartInfo();
            startInfo.WindowStyle = System.Diagnostics.ProcessWindowStyle.Hidden;
            startInfo.FileName = "cmd.exe";

            //get gateway IP and Mac Address for scan 
            string gateway = getNetworkGateway();
            string[] gatewayArray = gateway.Split('.');

            // Pass the variables in 

            startInfo.Arguments = String.Format("/C nmap -sn -oX C:\\Users\\Public\\Documents\\NMAPNetworkScan.xml {0}.{1}.{2}.*/24 --no-stylesheet ", gatewayArray[0], gatewayArray[1], gatewayArray[2], gatewayArray[3]);
            process.StartInfo = startInfo;
            process.Start();

            // Read the output stream first and then wait.
            process.WaitForExit();

            //parse the scan data 
            parseScanData();

            //connect to database 
            MyDBConnection DB = new MyDBConnection();
            DB.DBConnect();



            // for each device add it to the database 
            foreach (var device in devices)
            {
                //Insert the above query
                MySqlParameter[] deviceSQLParameters = new MySqlParameter[]
                {
                new MySqlParameter("indeviceIp", device.ipAddress),
                new MySqlParameter("inRTT", device.RTT),
                new MySqlParameter("inMacAddress",device.macAddress),
                new MySqlParameter("inName", device.name),
                //new MySqlParameter("networkMacAddress", gatewayMac)

                };

                DB.RunSP("addDevice", deviceSQLParameters);

            };
            return true;

        }

        public void parseScanData()
        {
            //read in data from the created XML File
            XmlDocument NMapXMLScan = new XmlDocument();

            //load the data after written
            NMapXMLScan.Load("NMap/data.xml");

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

                devices.Add(tempDevice);

            }
        }

        public string getNetworkGateway()
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

        public void getData()
        {
            MyDBConnection DB = new MyDBConnection();
            DB.DBConnect();
            devices.Clear();
            devices = DB.SelectAllDeviceData();
            

        }




    
}
}
