using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NND_Agent.Items
{
    internal class ComputerModel
    {
        public int ID { get; set; }
        public int RTT { get; set; }

        public string ipAddress { get; set; }

        public string macAddress { get; set; }

        public string name { get; set; }

        public string lastSeen { get; set; }
    }
}
