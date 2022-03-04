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

        public List<VulnModel> scannedVulns { get; set; }

        public List<ComputerModel> scannedDevices { get; set; }

        public List<ScanModel> listScans { get; set; }

        public ScanModel currentScan { get; set; }

        
    }
}
