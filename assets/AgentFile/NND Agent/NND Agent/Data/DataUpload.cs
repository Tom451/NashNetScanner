using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using NND_Agent.Items;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using System.Web.Script.Serialization;

namespace NND_Agent
{
    internal class DataUpload
    {
        public string SendPost(string url, string postData)
        {
            string webpageContent = string.Empty;

            try
            {

                byte[] byteArray = Encoding.UTF8.GetBytes(postData);

                HttpWebRequest webRequest = (HttpWebRequest)WebRequest.Create(url);
                webRequest.Method = "POST";
                webRequest.ContentType = "application/x-www-form-urlencoded";
                webRequest.ContentLength = byteArray.Length;

                using (Stream webpageStream = webRequest.GetRequestStream())
                {
                    webpageStream.Write(byteArray, 0, byteArray.Length);
                }

                using (HttpWebResponse webResponse = (HttpWebResponse)webRequest.GetResponse())
                {
                    using (StreamReader reader = new StreamReader(webResponse.GetResponseStream()))
                    {
                        webpageContent = reader.ReadToEnd();
                    }
                }
            }
            catch (Exception ex)
            {
                //throw or return an appropriate response/exception
                
            }

            return webpageContent;
        }

        //method used to get the scan data using the NONCE 
        public ScanModel SendGet(string url)
        {
            string webpageContent = string.Empty;

            try
            {

                HttpWebRequest webRequest = (HttpWebRequest)WebRequest.Create(url);
                webRequest.Method = "GET";

                using (HttpWebResponse webResponse = (HttpWebResponse)webRequest.GetResponse())
                {
                    using (StreamReader reader = new StreamReader(webResponse.GetResponseStream()))
                    {
                        webpageContent = reader.ReadToEnd();                     
                    }
                }
            }
            catch (Exception ex)
            {
                //throw or return an appropriate response/exception
            }

            return FromJSON(webpageContent);
        }

        //json converter for 
        public string ToJSON(object obj)
        {
            string stringjson = JsonConvert.SerializeObject(obj);
            return stringjson;
        }
        public ScanModel FromJSON(string input)
        {
            //create the JSON object 
            JObject jObject = JObject.Parse(input);

            //create the scan object
            JToken jScan = jObject["scan"];

            //create a model 
            ScanModel tempModel = new ScanModel
            {
                scanID = (int)jScan["scanID"],
                scanInfo = (string)jScan["ScanInfo"],
                scanType = (string)jScan["ScanType"],
                userName = (string)jScan["userID"],
                ScanStatus = (string)jScan["ScanStatus"]
            };

            return tempModel;

        }

    }
}
