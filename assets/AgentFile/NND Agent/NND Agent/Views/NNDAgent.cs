using Microsoft.Win32;
using Newtonsoft.Json;
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
using Timer = System.Windows.Forms.Timer;

namespace NND_Agent
{
    public partial class NNDAgent : Form
    {
        public static string WebpageAddress = "10.0.1.205";
        public static NNDAgent NNDForm = null;

        public long userNONCE = 0;

        //create a timer
        private Timer timer1;
        DataProcessing Scan;
        bool ScanStatus;

        //tell the user scan has started
        public NNDAgent()
        {

            //Start up code 
            InitializeComponent();

            //check nmap is installed 
            try
            {
                string output = "Empty";
                string errorOutput = "Empty";


                //start cmd proccess
                try
                {
                    
                    Process process = new Process();
                    
                    ProcessStartInfo startInfo = new ProcessStartInfo
                    {
                        WindowStyle = ProcessWindowStyle.Maximized,
                        FileName = "cmd.exe",
                        Arguments = "/C nmap -V",
                        UseShellExecute = false,
                        RedirectStandardOutput = true,
                        RedirectStandardError = true,
                        CreateNoWindow = false
                    };
                    

                    process.StartInfo = startInfo;

                   
                    process.Start();

                    //get the out put and the errors for running 2
                    output = process.StandardOutput.ReadLine();
                    errorOutput = process.StandardError.ReadToEnd();

                    

                    

                }
                catch (Exception e){
                    PopUp("1", e.Message, ToolTipIcon.Warning);
                }



                if(output != null)
                {

                    if (output.Contains("Nmap version 7.92"))
                    {
                        //read the current user from nonce
                        try
                        {
                            //try find the user file 
                            var outPutDirectory = Path.GetDirectoryName(Assembly.GetExecutingAssembly().CodeBase);
                            var sFilePath = Path.Combine(outPutDirectory, @"Data\UserNONCE.txt");
                            userNONCE = long.Parse(System.IO.File.ReadAllText(@"Data\UserNONCE.txt"));
                            //userNONCE = long.Parse(System.IO.File.ReadAllText(@"Data\UserNONCE.txt"));

                            //if found then greet user
                            PopUp("Welcome", "Please right click the icon to run a scan!", ToolTipIcon.Info);




                        }
                        catch
                        {
                            //show the user the error 
                            PopUp("File Error", "Unable to find user ID file. Please try to re download agent", ToolTipIcon.Error);

                        }

                        NNDForm = this;

                    }
                   

                }
                else
                {

                    //show the user the error that NMAp is not installed 
                    PopUp("NMAP Not Installed", "NMAP installer will open in 10 seconds, please follow the instructions to install", ToolTipIcon.Error);

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
            catch(Exception e)
            {
                //if scan errors out
                PopUp("Unable to start", e.InnerException.Message, ToolTipIcon.Error);
                //Application exit if error occurs, error shouldnt occur often
                Application.Exit();

            }

            Scan = new DataProcessing();

            //set teh agent as on 
            ScanChecker();
            SetAgent(1);

            lblCurrentIP.Text = WebpageAddress;
                     
            SystemEvents.PowerModeChanged += OnPowerChange;





        }

        private void ScanChecker()
        {
            timer1 = new Timer();
            timer1.Tick += new EventHandler(ScanCheck_Tick);
            timer1.Interval = 2000; // in miliseconds
            timer1.Start();
        }
        private void SetAgent(int value)
        {
            if(value != 0 && value != 1)
            {
                return;
            }
            
            //check for scan on load 
            DataUpload setAgentOnline = new DataUpload();
            var agent = new
            {
                userNONCE,
                agentStatus = value
            };
            //Tranform it to Json object
            string jsonData = JsonConvert.SerializeObject(agent);

            setAgentOnline.SendPost("http://"+ WebpageAddress + "/assets/php/DBUploadConn.php", String.Format("AgentStatus={0}", jsonData));
        }

        private async void ScanCheck_Tick(object sender, EventArgs e)
        {
            if (Scan.CheckForScan(userNONCE))
            {
                PopUp("AutoScan Found", "Starting your scan now", ToolTipIcon.Info);
                timer1.Stop();
                ScanStatus = true;

                await Task.Run(() => Scan.StartScan(userNONCE));

                PopUp("Scan Finished", "Finished", ToolTipIcon.Info);
                //start the timer and reset it
                timer1.Start();
                timer1.Interval = 2000;
                ScanStatus = false;
            }
            else
            {
                
                if (timer1.Interval > 60000)
                {
                    timer1.Interval = 60000;
                }
                else
                {
                    timer1.Interval = (int)(timer1.Interval * 1.5);
                }
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

        private async void RunScanToolStripMenuItem_Click(object sender, EventArgs e)
        {

            if (ScanStatus)
            {
                //if there is already a scan started then tell the user 
                PopUp("Scan Already Started","No need to start", ToolTipIcon.Warning);
            }
            else
            {
                //pause the auto scan timer and start the scan 
                timer1.Stop();
                if (!Scan.CheckForScan(userNONCE))
                {
                    // if there was an error getting scan details then no scan is available 
                    PopUp("Error with fetching scan", "No scan avalable please start a scan from the web interface", System.Windows.Forms.ToolTipIcon.Warning);
                }
                else
                {
                    //else start the scan 
                    PopUp("Scan Found", "Starting your scan now", ToolTipIcon.Info);
                    await Task.Run(() => Scan.StartScan(userNONCE));
                    ScanStatus = true;

                    //when the scan is finished then tell user 
                    PopUp("Scan Finished", "Finished", ToolTipIcon.Info);
                    ScanStatus = false;
                    timer1.Start();
                }
                
            }
            

        }

        //pop up method, made public so it can be called anywhere throught the app
        public void PopUp(string title, string info, ToolTipIcon type)
        {
            NNDToolBarIcon.BalloonTipTitle = title;
            NNDToolBarIcon.BalloonTipText = info;
            NNDToolBarIcon.BalloonTipIcon = type;
            NNDToolBarIcon.Visible = true;
            NNDToolBarIcon.ShowBalloonTip(100);
           
            
        }
        //cancel scan method
        private void CancelScanToolStripMenuItem_Click(object sender, EventArgs e)
        {
            //show user scan canceled 
            PopUp("Scan Cancled", "Ended", ToolTipIcon.Error);
            // WinForms app
            System.Windows.Forms.Application.Exit();
            Application.Run(new NNDAgent());

        }

        private void ScanStatusToolStripMenuItem_Click(object sender, EventArgs e)
        {
            
            PopUp("There are", Scan.CheckProgress() + " Left to Go", ToolTipIcon.Info);
        }

        private void NNDAgent_Load(object sender, EventArgs e)
        {

        }

        private void NNDAgent_FormClosing(object sender, FormClosingEventArgs e)
        {
            SetAgent(0);
        }


        private void CloseApplicationToolStripMenuItem_Click(object sender, EventArgs e)
        {
            SetAgent(0);
            System.Windows.Forms.Application.Exit();

        }

        void OnPowerChange(Object sender, PowerModeChangedEventArgs e)
        {
            if (e.Mode == PowerModes.Suspend)
            {
                SetAgent(0);
            }
            else if (e.Mode == PowerModes.Resume)
            {
                SetAgent(1);
            }


        }

        private void btnSubmitChange_Click(object sender, EventArgs e)
        {
            WebpageAddress = txtIp.Text;
            lblCurrentIP.Text = WebpageAddress;
        }

        private void NNDAgent_FormClosed(object sender, FormClosedEventArgs e)
        {
            SetAgent(0);
        }
    }
}
