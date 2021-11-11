using NND_Agent.Items;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Windows.Forms;

namespace NND_Agent
{
    internal static class Program
    {
        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {

            String deviceToken = HttpUtility.UrlEncode("YourDeviceToken");
            String passphrase = HttpUtility.UrlEncode("YourPassphrase");

            ComputerModel Computer = new ComputerModel();
            Computer.name = "Name";
            Computer.ID = 1;
            Computer.macAddress = "NewMacAddress";

            

            string NewJson = DataUpload.ToJSON(Computer);

            DataUpload upload = new DataUpload();
            upload.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("JSON={0}", NewJson));

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new NNDAgent());

            

        }


    }
}
