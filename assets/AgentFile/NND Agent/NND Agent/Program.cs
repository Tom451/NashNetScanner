using NND_Agent.Items;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
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
           

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            try
            {
                Application.Run(new NNDAgent());
            }
            catch (System.Reflection.TargetInvocationException ex)
            {
                File.Delete(@"C:\Users\Public\Documents\NMAPNetworkScan.xml");
                File.Delete(@"C:\Users\Public\Documents\NMAPVulnScan.xml");
                Application.Run(new NNDAgent());

            }
            

            

        }
        


    }
}
