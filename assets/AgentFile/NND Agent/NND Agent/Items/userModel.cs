using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NND_Agent.Items
{
    internal class userModel
    {

        public string userName { get; set;}

        //users scaned vulnerbilities 
        public List<VulnModel> scannedVulns { get; set; }

        // their scanned devices 
        public List<ComputerModel> scannedDevices { get; set; }

        //list of the all teh scans the user has to do 
        public List<ScanModel> listScans { get; set; }

        //the scan currently being completed 
        public ScanModel currentScan { get; set; }

        
    }
}
