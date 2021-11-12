using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NND_Agent.Items
{
    internal class ScanModel
    {
        public string sessionID { get; set; }
        public string userName { get; set; }
        public string scanInfo { get; set; }
        public string scanType { get; set; }

        public string ScanStatus { get; set; }
        public int scanID { get; set; }
    }
}
