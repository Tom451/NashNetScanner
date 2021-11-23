using NND_Agent.Data;
using NND_Agent.Items;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Reflection;
using System.Text;
using System.Threading;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace NND_Agent
{
    public partial class NNDAgent : Form
    {
        public static NNDAgent NNDForm = null;

        public long userNONCE = 0;
        public NNDAgent()
        {

            //Start up code 
            InitializeComponent();

            //check nmap is installed 
            try
            {
                //start cmd proccess
                Process process = new Process();
                ProcessStartInfo startInfo = new ProcessStartInfo
                {
                    WindowStyle = ProcessWindowStyle.Hidden,
                    FileName = "cmd.exe",
                    Arguments = "/C nmap -V",
                    UseShellExecute = false,
                    RedirectStandardOutput = true,
                    CreateNoWindow = true
                };

                process.StartInfo = startInfo;
                process.Start();

                string output = process.StandardOutput.ReadLine();

                if (output.Contains("Nmap version 7.92"))
                {
                    //read the current user from nonce
                    try
                    {
                        //try find the user file 
                        var outPutDirectory = Path.GetDirectoryName(Assembly.GetExecutingAssembly().CodeBase);
                        var sFilePath = Path.Combine(outPutDirectory, @"Data\UserNONCE.txt");

                        userNONCE = long.Parse(System.IO.File.ReadAllText(@"Data\UserNONCE.txt"));

                        //if found then greet user
                        popUp("Welcome", "Please right click the icon to run a scan!", ToolTipIcon.Info);

                    }
                    catch
                    {
                        //show the user the error 
                        popUp("File Error", "Unable to find user ID file. Please try to re download agent", ToolTipIcon.Error);

                    }

                    NNDForm = this;

                }
                else
                {

                    //show the user the error that NMAp is not installed 
                    popUp("NMAP Not Installed", "NMAP installer will open in 10 seconds, please follow the instructions to install", ToolTipIcon.Error);

                    //wait for ten seconds
                    int milliseconds = 10000;
                    Thread.Sleep(milliseconds);
                    string sFilePath = Path.GetFullPath(@"Data\Prerequisites\nmap-7.92-setup.exe");

                    //start cmd proccess
                    Process install = new Process();
                    ProcessStartInfo installInfo = new ProcessStartInfo
                    {
                        FileName = sFilePath,
                        UseShellExecute = true,
                    };

                    //start the install
                    install.StartInfo = installInfo;
                    install.Start();

                    install.WaitForExit();

                    

                }


            }
            catch
            {

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

        public void popUp(string title, string info, ToolTipIcon type)
        {
            NNDToolBarIcon.BalloonTipTitle = title;
            NNDToolBarIcon.BalloonTipText = info;
            NNDToolBarIcon.BalloonTipIcon = type;
            NNDToolBarIcon.Visible = true;
            NNDToolBarIcon.ShowBalloonTip(100);
           
            // Create buttons and set text property.  
            
        }

 
    }
}
