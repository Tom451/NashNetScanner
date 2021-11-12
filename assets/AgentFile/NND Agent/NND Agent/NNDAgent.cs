using NND_Agent.Data;
using NND_Agent.Items;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace NND_Agent
{
    public partial class NNDAgent : Form
    {

        public long userNONCE = 0;
        public NNDAgent()
        {
            InitializeComponent();

            //read the current user from nonce
            try
            {
                //try find the user file 
                string sCurrentDirectory = AppDomain.CurrentDomain.BaseDirectory;
                string sFile = System.IO.Path.Combine(sCurrentDirectory, @"UserNONCE.txt");
                string sFilePath = Path.GetFullPath(sFile);
                userNONCE = long.Parse(System.IO.File.ReadAllText(sFilePath));

                //if found then greet user
                NNDToolBarIcon.BalloonTipTitle = "Welcome";
                NNDToolBarIcon.BalloonTipText = "Please right click the icon to run a scan!";
                NNDToolBarIcon.Visible = true;
                NNDToolBarIcon.ShowBalloonTip(100);
            }
            catch
            {
                //show the user the error 
                
                NNDToolBarIcon.BalloonTipTitle = "File Error";
                NNDToolBarIcon.BalloonTipText = "Unable to find user ID file. Please try to re download agent";
                NNDToolBarIcon.Visible = true;
                NNDToolBarIcon.ShowBalloonTip(100);
            }
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

        private void RunScanToolStripMenuItem_Click(object sender, EventArgs e)
        {
            DataClass Scan = new DataClass();
            Scan.StartScan(userNONCE);
            
        }

 
    }
}
