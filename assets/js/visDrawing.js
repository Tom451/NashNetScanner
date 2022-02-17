var DIR = "img/soft-scraps-icons/";

var nodes = null;
var edges = null;
var network = null;

// Called when the Visualization API is loaded.
function draw(items) {
    // create people.
    // value corresponds with the age of the person
    var DIR = "../img/indonesia/";


    //create the network
    nodes = [
        { id: 1, shape: "circularImage",
            image: "https://library.kissclipart.com/20180829/jxw/kissclipart-router-icon-clipart-wireless-router-computer-icons-7daa203c111e1196.jpg",
            label: "Router"
        }];

    edges = []
    console.log(items)
    for (index = 0; index < items.length; index++) {
        nodes.push(
            { id: items[index].ScanID,
                shape: "circularImage",
                image: "https://www.pinclipart.com/picdir/middle/560-5606930_file-wifiservice-svg-wikimedia-commons-wifi-symbol-black.png",
                label: items[index].deviceName
            })
        edges.push({ from: 1, to: items[index].ScanID })
    }



    // create a network
    var container = document.getElementById("mynetwork");
    var data = {
        nodes: nodes,
        edges: edges,
    };
    var options = {
        nodes: {
            borderWidth: 4,
            size: 30,
            color: {
                border: "#222222",
                background: "#666666",
            },
            font: { color: "#111111" },
            shadow: true,

        },
        edges: {
            color: "lightgray",
            shadow: true,
        },
    };
    network = new vis.Network(container, data, options);

    network.on("click", function (params) {

        console.log(params.nodes[0]);
        if (params.nodes[0]===1){

        }
        else{
            try {
                var pagebutton= document.getElementById(params.nodes[0]);
                pagebutton.click();
            } catch (error) {
                console.error(error);
                // expected output: ReferenceError: nonExistentFunction is not defined
                // Note - error messages will vary depending on browser
            }

        }


    });



}
