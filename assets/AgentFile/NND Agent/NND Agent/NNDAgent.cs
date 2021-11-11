using NND_Agent.Data;
using NND_Agent.Items;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace NND_Agent
{
    public partial class NNDAgent : Form
    {
        public string userNONCE = "14";
        public NNDAgent()
        {
            InitializeComponent();
            base.SetVisibleCore(false);
        }
        private void NNDAgent_Resize(object sender, EventArgs e)
        {
            //if the form is minimized  
            //hide it from the task bar  
            //and show the system tray icon (represented by the NotifyIcon control)  
            if (this.WindowState == FormWindowState.Minimized)
            {
                Hide();
                NNDToolBarIcon.Visible = true;
            }
        }


        private void NNDToolBarIcon_MouseDoubleClick_1(object sender, MouseEventArgs e)
        {
            Show();
            this.WindowState = FormWindowState.Normal;
            NNDToolBarIcon.Visible = false;

        }

        private void NNDAgent_Shown(object sender, EventArgs e)
        {
            //to minimize window
            this.WindowState = FormWindowState.Minimized;
            Hide();
            NNDToolBarIcon.Visible = true;
        }

        private void runScanToolStripMenuItem_Click(object sender, EventArgs e)
        {
            DataUpload Connection = new DataUpload();
            DataClass Data = new DataClass();
            ScanModel scan = Connection.SendGet("http://localhost/assets/php/DBUploadConn.php?USERID=" + userNONCE);

            List<ComputerModel> upload = Data.NMapScan(scan);

            
            string NewJson = Connection.ToJSON(upload);

            
            Connection.SendPost("http://localhost/assets/php/DBUploadConn.php", String.Format("JSON={0}", NewJson));
            
        }
    }
}
