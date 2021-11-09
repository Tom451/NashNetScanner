using AgentDownload.Items;
using MySql.Data.MySqlClient;
using System;
using System.Collections.Generic;
using System.Data;
using System.Text;

namespace AgentDownload.DBFolder
{
    class MyDBConnection
    {

        private MySqlConnection connection;
        private string server;
        private string database;
        private string uid;
        private string password;

        //Constructor
        public void DBConnect()
        {
            Initialize();
        }

        //Initialize values
        private void Initialize()
        {
            server = "localhost";
            database = "nashnetworkdashboard";
            uid = "Agent";
            password = "Password123";
            string connectionString;
            connectionString = "SERVER=" + server + ";" + "DATABASE=" +
            database + ";" + "UID=" + uid + ";" + "PASSWORD=" + password + ";";

            connection = new MySqlConnection(connectionString);
        }

        //open connection to database
        private bool OpenConnection()
        {
            try
            {
                connection.Open();
                return true;
            }
            catch (MySqlException ex)
            {

                switch (ex.Number)
                {
                    case 0:

                        break;

                    case 1045:

                        break;
                }
                return false;
            }
        }

        //Close connection
        private bool CloseConnection()
        {
            try
            {
                connection.Close();
                return true;
            }
            catch (MySqlException)
            {

                return false;
            }
        }

        //run statement
        public void RunSP(string storedProcedure, MySqlParameter[] mysqlparam)
        {
            //open connection
            if (this.OpenConnection() == true)
            {
                //create command and assign the query and connection from the constructor
                MySqlCommand cmd = new MySqlCommand(storedProcedure, connection)
                {
                    CommandType = System.Data.CommandType.StoredProcedure

                };

                foreach (var sqlParam in mysqlparam)
                {
                    cmd.Parameters.Add(sqlParam);

                }


                //Execute command

                try
                {
                    cmd.ExecuteNonQuery();
                }
                catch
                {

                }


                //close connection
                this.CloseConnection();
            }
        }



        //Delete statement
        public void Delete()
        {
            string query = "DELETE FROM tableinfo WHERE name='John Smith'";

            if (this.OpenConnection() == true)
            {
                MySqlCommand cmd = new MySqlCommand(query, connection);
                cmd.ExecuteNonQuery();
                this.CloseConnection();
            }
        }

        //Select All Devices statement
        public List<ComputerModel> SelectAllDeviceData()
        {
            //Create a Temp list.
            List<ComputerModel> temp = new List<ComputerModel>();

            //open connection
            if (this.OpenConnection() == true)
            {

                //Create a data tabel for the data that is coming in 
                DataTable tempDataTabe = new DataTable();

                //Set up the command that will select all the data
                MySqlCommand cmd = new MySqlCommand("selectAll", connection)
                {

                    //Set the type of command as a stored procedure 
                    CommandType = CommandType.StoredProcedure
                };

                //Execute command
                cmd.ExecuteNonQuery();

                //create a dataAdapter for data
                MySqlDataAdapter tempDataAdapter = new MySqlDataAdapter(cmd);

                //fill the table with the data
                tempDataAdapter.Fill(tempDataTabe);
                foreach (DataRow dr in tempDataTabe.Rows)
                {
                    //create a temp model object
                    ComputerModel obj = new ComputerModel
                    {

                        //input the data
                        ipAddress = dr["ipAddress"].ToString(),
                        macAddress = dr["macAddress"].ToString(),
                        name = dr["friendlyName"].ToString(),
                        lastSeen = dr["lastSeen"].ToString()
                    };

                    //add to the temp list
                    temp.Add(obj);
                }

                //close connection
                this.CloseConnection();
            }

            //return the temp data
            return temp;

        }

        public ComputerModel SelectIndividualDevice(string macAddress)
        {


            //Create a Temp list.
            ComputerModel temp = new ComputerModel();

            //open connection
            if (this.OpenConnection() == true)
            {

                //Create a data tabel for the data that is coming in 
                DataTable tempDataTabe = new DataTable();

                //Set up the command that will select all the data
                MySqlCommand cmd = new MySqlCommand("getDevice", connection)
                {

                    //Set the type of command as a stored procedure 
                    CommandType = CommandType.StoredProcedure
                };

                //Insert the above query

                MySqlParameter mac = new MySqlParameter("inMacAddress", macAddress);


                //Execute command
                cmd.Parameters.Add(mac);
                cmd.ExecuteNonQuery();

                //create a dataAdapter for data
                MySqlDataAdapter tempDataAdapter = new MySqlDataAdapter(cmd);

                //fill the table with the data
                tempDataAdapter.Fill(tempDataTabe);

                DataRow data = tempDataTabe.Rows[0];

                // input the data
                temp.ID = int.Parse(data["deviceID"].ToString());
                temp.ipAddress = data["ipAddress"].ToString();
                temp.macAddress = data["macAddress"].ToString();
                temp.name = data["friendlyName"].ToString();
                temp.lastSeen = data["lastSeen"].ToString();



                //close connection
                this.CloseConnection();
            }

            //return the temp data
            return temp;

        }

        public ScanModel getScanInfo(string SessionID)
        {
            ScanModel scanModel = new ScanModel();

            //open connection
            if (this.OpenConnection() == true)
            {


                //Create a data tabel for the data that is coming in 
                DataTable tempDataTabe = new DataTable();

                //Set up the command that will select all the data
                MySqlCommand cmd = new MySqlCommand("getScan", connection)
                {

                    //Set the type of command as a stored procedure 
                    CommandType = CommandType.StoredProcedure
                };

                //Insert the above query

                MySqlParameter sessionID = new MySqlParameter("inSessionID", SessionID);


                //Execute command
                cmd.Parameters.Add(sessionID);
                cmd.ExecuteNonQuery();

                //create a dataAdapter for data
                MySqlDataAdapter tempDataAdapter = new MySqlDataAdapter(cmd);

                //fill the table with the data
                tempDataAdapter.Fill(tempDataTabe);

                foreach (DataRow dr in tempDataTabe.Rows)
                {

                    scanModel.scanInfo = dr["ScanInfo"].ToString();
                    scanModel.userName = dr["userID"].ToString();
                    scanModel.sessionID = dr["sessionID"].ToString();
                    scanModel.scanType = dr["scanType"].ToString();
                    scanModel.scanID = int.Parse(dr["scanID"].ToString());

                }

            }

            return scanModel;

        }

        public string AddDeviceToScan(MySqlParameter[] inputParams, MySqlParameter outputParam)
        {
            //open connection
            if (this.OpenConnection() == true)
            {
                //create command and assign the query and connection from the constructor
                MySqlCommand cmd = new MySqlCommand("addDevice", connection)
                {
                    CommandType = CommandType.StoredProcedure
                };

                //add the input parameters
                foreach (var sqlParam in inputParams)
                {
                    cmd.Parameters.Add(sqlParam);

                }
                cmd.Parameters.Add(outputParam);


                try
                {
                    cmd.ExecuteNonQuery();
                    string value = cmd.Parameters["outDeviceID"].Value.ToString();
                    connection.Close();

                    return value;
                    //close connection

                }
                catch
                {

                    connection.Close();
                }


                return null;


            }
            return null;

        }

        public void addLink(int inDeviceID, int inScanID)
        {
            //open connection
            if (this.OpenConnection() == true)
            {
                //create command and assign the query and connection from the constructor
                MySqlCommand cmd = new MySqlCommand("addLink", connection)
                {
                    CommandType = CommandType.StoredProcedure
                };

                //Insert the above query
                MySqlParameter[] inSQLParameters = new MySqlParameter[]
                {
                new MySqlParameter("inDeviceID", inDeviceID),
                new MySqlParameter("inScanID", inScanID),

                //new MySqlParameter("networkMacAddress", gatewayMac)

                };

                foreach (var sqlParam in inSQLParameters)
                {
                    cmd.Parameters.Add(sqlParam);
                }
                



                //Execute command

                try
                {
                    cmd.ExecuteNonQuery();
                }
                catch
                {

                }


                //close connection
                this.CloseConnection();
            }




        }

        public void setScanStatus(string inStatus)
        {
            //open connection
            if (this.OpenConnection() == true)
            {
                //create command and assign the query and connection from the constructor
                MySqlCommand cmd = new MySqlCommand("addLink", connection)
                {
                    CommandType = CommandType.StoredProcedure
                };

                //Insert the above query
                MySqlParameter[] inSQLParameters = new MySqlParameter[]
                {
                new MySqlParameter("inStatus", inStatus)

                //new MySqlParameter("networkMacAddress", gatewayMac)

                };

                foreach (var sqlParam in inSQLParameters)
                {
                    cmd.Parameters.Add(sqlParam);
                }




                //Execute command

                try
                {
                    cmd.ExecuteNonQuery();
                }
                catch
                {

                }


                //close connection
                this.CloseConnection();
            }




        }
    }
}

