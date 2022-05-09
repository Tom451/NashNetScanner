let nodes = null;
let edges = null;
let network = null;

// Called when the Visualization API is loaded.
function draw(items) {
    // create people.
    // value corresponds with the age of the person


    //create the network
    nodes = [];

    edges = []

    for (let index = 0; index < items.length; index++) {

        var colour;
        var image;
        var router;

        //if the device is vulnerable then set the red desktop photo
        if (items[index].deviceScanned === "Yes: Vulnerable"){
            colour = "red";
            image = "/assets/images/DesktopRed.svg";

        }
        //if the device is safe then set the green desktop photo
        else if (items[index].deviceScanned === "Yes: Safe"){
            colour = "green";
            image = "/assets/images/DesktopGreen.svg";
        }
        //if the device is being scanned then set the orange desktop photo
        else if (items[index].deviceScanned === "Scanning"){
            colour = "orange";
            image = "/assets/images/DesktopOrange.svg";
        }
        //if the device is not scanned yet then set the un-scanned desktop photo
        else if (items[index].deviceScanned === "No"){
            colour = "orange";
            image = "/assets/images/DesktopUnscanned.svg";
        }
        //else set other
        else{
            colour = "blue"
            image = "/assets/images/DesktopGrey.svg";
        }

        if (items[index].deviceIP.endsWith(".1")){
            nodes.push(
                {
                    id: 1,
                    shape: "image",
                    image: image,
                    label: items[index].deviceName,

                })
            router = 1;
        }
        else{
            nodes.push(
                {
                    id: items[index].deviceID,
                    shape: "image",
                    image: image,
                    label: items[index].deviceName,

                })

            if (items[index].deviceScanned.includes("Yes") || items[index].deviceScanned.includes("Host Down") || items[index].deviceScanned.includes("Scanning")){
                edges.push({ from: 1, to: items[index].deviceID })
            }

        }

    }
    if (router !== 1){
        nodes.push(
            {
                id: 1,
                shape: "image",
                image: "https://www.pinclipart.com/picdir/middle/560-5606930_file-wifiservice-svg-wikimedia-commons-wifi-symbol-black.png",
                label: "Unknown Router",
                color:  "grey",


            })
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
            size: 50,
            color: {
                border: "white",
                background: "white",
            },
            font: {  color: "#111111", size: 30,  },
            shadow: true,

        },
        edges: {
            color: "lightgray",
            shadow: true,
        },
        physics: {
            // Even though it's disabled the options still apply to network.stabilize().
            enabled: true,
            solver: "repulsion",
            repulsion: {
                nodeDistance: 400 // Put more distance between the nodes.
            }
        },
        interaction: {
            navigationButtons: true,
            keyboard: true,
            colour: "blue"
        }
    };

    network = new vis.Network(container, data, options);




    network.on("click", function (params) {

        var newestscan = null;

        $.ajax({
            url: 'devices.php',
            type: 'post',
            data: { "GetNewestScanForVIS": params.nodes[0]},
            success: function(response) {

                newestscan = response;

                console.log(newestscan)
                console.log(params.nodes[0]);

                if (params.nodes[0]===1){
                    console.error(error);

                }
                else{
                    try {
                        var pagebutton = document.getElementById(newestscan);
                        pagebutton.click();
                    } catch (error) {
                        console.error(error);
                        // expected output: ReferenceError: nonExistentFunction is not defined
                        // Note - error messages will vary depending on browser
                    }

                }
            }
        });




    });



}
