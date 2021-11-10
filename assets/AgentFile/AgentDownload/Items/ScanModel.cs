using System;
using System.Collections.Generic;
using System.Text;

namespace AgentDownload.Items
{
    internal class ScanModel
    {
        public string sessionID { get; set; }
        public string userName { get; set; }
        public string scanInfo { get; set; }
        public string scanType { get; set; }

        public int scanID { get; set; }

    }
}
