using AgentDownload.DBFolder;
using AgentDownload.Items;
using MySql.Data.MySqlClient;
using System;
using System.Collections.Generic;
using System.Data;
using System.Diagnostics;
using System.Net.NetworkInformation;
using System.Xml;
using BCrypt;
using System.Security.Cryptography;
using System.IO;
using System.Text;
using System.Security;

namespace AgentDownload
{
    class Program
    {
        // Encryption Variables and sign in Information
        readonly byte[] iv = new byte[16] { 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0 };
        byte[] encryptionKey = new byte[16];

        //Login Info
        string passwordKey, scanID, userName;


        List<ComputerModel> devices = new List<ComputerModel>();
        public static void Main(string[] args)
        {
            //start the program 
            Program NMAP = new Program();

            //Welcome the user to the program
            Console.WriteLine("Welcome to NashNetworkDashboard Agent Program");

            //get the users username
            Console.WriteLine("Please enter your UserName:");
            NMAP.userName = Console.ReadLine();

            //get the users password
            Console.WriteLine("Please enter your password:");
            string tempPassword = Console.ReadLine();

            //get the derived key from the user's password
            //temp salt, will be used in database 
            byte[] salt = Encoding.UTF8.GetBytes("testSalt");
            var pbkdf2 = new Rfc2898DeriveBytes(tempPassword, salt, 10, HashAlgorithmName.SHA256);

            //get the 20 charater random key from the password used for encryption 
            byte[] bytesKey = pbkdf2.GetBytes(20);

            //set that as the password held by the program 
            NMAP.passwordKey = Convert.ToBase64String(bytesKey);
            tempPassword = null; //clear the password 

            //get the users scanID
            Console.WriteLine("Please enter your ScanID provided by the webpage:");
            NMAP.scanID = Console.ReadLine();

            // Create sha256 hash and key prom the users password
            SHA256 mySHA256 = SHA256Managed.Create();
            NMAP.encryptionKey = mySHA256.ComputeHash(Encoding.ASCII.GetBytes(NMAP.passwordKey));

            //start the scan 
            NMAP.NMapScan(NMAP.scanID);
            
        }


        public bool NMapScan(string scanID)
        {
            //Start the Scan
            Process process = new Process();
            ProcessStartInfo startInfo = new ProcessStartInfo
            {
                WindowStyle = ProcessWindowStyle.Hidden,
                FileName = "cmd.exe"
            };

            //get gateway IP and Mac Address for scan 
            string gateway = getNetworkGateway();
            string[] gatewayArray = gateway.Split('.');


            //connect to database 
            MyDBConnection DB = new MyDBConnection();
            DB.DBConnect();

            //get the encrypted scan info
            ScanModel ScanInfo = DB.getScanInfo(scanID);

            //decrypt scan data 
            string DecScanInfo = DecryptString(ScanInfo.scanInfo);
            string DecScanType = DecryptString(ScanInfo.scanType);

            // Pass the variables in 
            startInfo.Arguments = String.Format("/C {0} {1}.{2}.{3}.*/24 --no-stylesheet ", DecScanInfo, gatewayArray[0], gatewayArray[1], gatewayArray[2], gatewayArray[3]);
            process.StartInfo = startInfo;
            process.Start();

            // Read the output stream first and then wait.
            process.WaitForExit();

            if (DecScanType == "NetDisc")
            {
                //parse the scan data 
                parseNetworkDiscoveryData(ScanInfo);

            }



            return true;
            

        }

        public void parseNetworkDiscoveryData(ScanModel ScanInfo)
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
                tempDevice.ipAddress = EncryptString(hostipaddress);
                tempDevice.macAddress = EncryptString(mac);
                tempDevice.name = EncryptString(hostActualName);

                devices.Add(tempDevice);

            }


            //connect to database 
            MyDBConnection DB = new MyDBConnection();
            DB.DBConnect();

            // for each device add it to the database 
            foreach (var device in devices)
            {
                //Insert the above query
                MySqlParameter[] inSQLParameters = new MySqlParameter[]
                {
                new MySqlParameter("indeviceIp", device.ipAddress),
                new MySqlParameter("inRTT", device.RTT),
                new MySqlParameter("inMacAddress",device.macAddress),
                new MySqlParameter("inName", device.name),

                };

                MySqlParameter outDeviceID = new MySqlParameter("outDeviceID", MySqlDbType.VarChar)
                {
                    Direction = ParameterDirection.Output
                };

                string deviceID = DB.AddDeviceToScan(inSQLParameters, outDeviceID);

                //add the link between the device and the scans
                DB.addLink(int.Parse(deviceID), ScanInfo.scanID);

            };

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

        public void populateDevices()
        {
            MyDBConnection DB = new MyDBConnection();
            DB.DBConnect();
            devices.Clear();
            devices = DB.SelectAllDeviceData();
            

        }


        //Encryption Aspects 
        public string EncryptString(string plainText)
        {
            // Instantiate a new Aes object to perform string symmetric encryption
            Aes encryptor = Aes.Create();

            encryptor.Mode = CipherMode.CBC;
            //encryptor.KeySize = 256;
            //encryptor.BlockSize = 128;
            //encryptor.Padding = PaddingMode.Zeros;

            // Set key and IV
            encryptor.Key = encryptionKey;
            encryptor.IV = iv;

            // Instantiate a new MemoryStream object to contain the encrypted bytes
            MemoryStream memoryStream = new MemoryStream();

            // Instantiate a new encryptor from our Aes object
            ICryptoTransform aesEncryptor = encryptor.CreateEncryptor();

            // Instantiate a new CryptoStream object to process the data and write it to the 
            // memory stream
            CryptoStream cryptoStream = new CryptoStream(memoryStream, aesEncryptor, CryptoStreamMode.Write);

            // Convert the plainText string into a byte array
            byte[] plainBytes = Encoding.ASCII.GetBytes(plainText);

            // Encrypt the input plaintext string
            cryptoStream.Write(plainBytes, 0, plainBytes.Length);

            // Complete the encryption process
            cryptoStream.FlushFinalBlock();

            // Convert the encrypted data from a MemoryStream to a byte array
            byte[] cipherBytes = memoryStream.ToArray();

            // Close both the MemoryStream and the CryptoStream
            memoryStream.Close();
            cryptoStream.Close();

            // Convert the encrypted byte array to a base64 encoded string
            string cipherText = Convert.ToBase64String(cipherBytes, 0, cipherBytes.Length);

            // Return the encrypted data as a string
            return cipherText;
        }

        public string DecryptString(string cipherText)
        {
            // Instantiate a new Aes object to perform string symmetric encryption
            Aes encryptor = Aes.Create();

            encryptor.Mode = CipherMode.CBC;
            //encryptor.KeySize = 256;
            //encryptor.BlockSize = 128;
            //encryptor.Padding = PaddingMode.Zeros;

            // Set key and IV
            encryptor.Key = encryptionKey;
            encryptor.IV = iv;

            // Instantiate a new MemoryStream object to contain the encrypted bytes
            MemoryStream memoryStream = new MemoryStream();

            // Instantiate a new encryptor from our Aes object
            ICryptoTransform aesDecryptor = encryptor.CreateDecryptor();

            // Instantiate a new CryptoStream object to process the data and write it to the 
            // memory stream
            CryptoStream cryptoStream = new CryptoStream(memoryStream, aesDecryptor, CryptoStreamMode.Write);

            // Will contain decrypted plaintext
            string plainText = String.Empty;

            try
            {
                // Convert the ciphertext string into a byte array
                byte[] cipherBytes = Convert.FromBase64String(cipherText);

                // Decrypt the input ciphertext string
                cryptoStream.Write(cipherBytes, 0, cipherBytes.Length);

                // Complete the decryption process
                cryptoStream.FlushFinalBlock();

                // Convert the decrypted data from a MemoryStream to a byte array
                byte[] plainBytes = memoryStream.ToArray();

                // Convert the decrypted byte array to string
                plainText = Encoding.ASCII.GetString(plainBytes, 0, plainBytes.Length);
            }
            finally
            {
                // Close both the MemoryStream and the CryptoStream
                memoryStream.Close();
                cryptoStream.Close();
            }
            

            // Return the decrypted data as a string
            return plainText;
        }



    }
}
