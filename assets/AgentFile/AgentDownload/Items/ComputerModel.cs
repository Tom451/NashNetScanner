using System;
using System.Collections.Generic;
using System.Text;

namespace AgentDownload.Items
{
    class ComputerModel
    {

        public int ID { get; set; }
        public int RTT { get; set; }

        public string ipAddress { get; set; }

        public string macAddress { get; set; }

        public string name { get; set; }

        public string lastSeen { get; set; }
    }
}
