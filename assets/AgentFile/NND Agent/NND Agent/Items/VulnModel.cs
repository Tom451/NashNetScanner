using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NND_Agent.Items
{
    internal class VulnModel
    {
        public string VulnName { get; set; }

        public string VulnProduct { get; set; }

        public string VulnVersion { get; set; }

        public string VulnExtraData { get; set; }

        public string VulnPortNumber { get; set; }

        public int scanID { get; set; }
    }
}
