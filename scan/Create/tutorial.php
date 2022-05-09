<?php
require '..\..\assets\php\sessionChecker.php';
require_once '..\..\assets\php\database\DBFunctions.php';
require_once '..\..\assets\php\database\DBConfig.php';

if(isset($_POST['checkScan'])){
    if (count(getDiscoveredDevicesFromDB(getConnection(),  $_SESSION['user_id'])) === 0){
        echo 'false';
        return;
    }
    else{
        echo 'true';
        return;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Get Started</title>
    <?php require "../../assets/php/navBar/headerData.php" ?>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/css/Features-Boxed.css">
    <link rel="stylesheet" href="../../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="../../assets/css/Highlight-Section.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.3.1/css/swiper.min.css">
    <link rel="stylesheet" href="../../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">


</head>

<body>
<?php require '../../assets/php/navBar/navBarLoggedIn.php' ?>

<section class="features-clean">
    <div class="container">
        <div class="intro">
            <h2 class="text-center">You have no Devices!</h2>
            <p class="text-center">Not a problem, we are here to help. Follow this simple easy to read guide to get you started!&nbsp;&nbsp;</p>
        </div>
    </div>
    <div role="tablist" id="accordion-1">
        <div class="card">
            <div class="card-header" role="tab">
                <h5 class="mb-0"><a data-toggle="collapse" aria-expanded="true" aria-controls="accordion-1 .item-1" href="#accordion-1 .item-1">Step One - The Download</a></h5>
            </div>
            <div class="collapse show item-1" id="1" role="tabpanel" data-parent="#accordion-1">
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Firstly, we are going to begin by downloading the simple little agent to you're computer, this will be a simple zip file which can be placed anywhere on your computer.&nbsp;</p>
                                <p>Feel free to place this on a memory stick as the files are portable they won't take up much room at all</p><button class="btn btn-secondary" type="button" id="download">Download</button>
                            </div>
                            <div class="col-md-6"><img alt="download" src="../../assets/images/Help/download.png"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" role="tab">
                <h5 class="mb-0"><a data-toggle="collapse" aria-expanded="false" aria-controls="accordion-1 .item-2" href="#accordion-1 .item-2">Step Two - The Scan</a></h5>
            </div>
            <div class="collapse item-2" id="2" role="tabpanel" data-parent="#accordion-1">
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Please start by saving the files, and running the agent. Right now that is out of the way, the second part is to create a scan. This is important as it will show where the vulnerabilities are located on the network, please begin by following the steps and then starting a scan below:&nbsp; &nbsp;</p>
                                <ul>
                                    <li>Open Download</li>
                                    <li>Extract Zip</li>
                                    <li>Run NNDEasyStart from desktop</li>
                                    <li>Run a discovery using the button below</li>
                                </ul>
                                <button class="btn btn-primary" type="submit" name="createScan" value="NetDisc" id="discovery">Run Discovery</button>
                            </div>
                            <div class="col-md-6">
                                <video width="720" height="480" controls>
                                    <source src="../../assets/videos/DownloadHelp.mkv" type="video/mkv">
                                    <source src="../../assets/videos/DownloadHelp.mkv" type="video/ogg">
                                    Your browser does not support the video tag.
                                </video>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" role="tab">
                <h5 class="mb-0"><a data-toggle="collapse" aria-expanded="false" aria-controls="accordion-1 .item-3" href="#accordion-1 .item-3">Step Three - The Relax&nbsp;</a></h5>
            </div>
            <div class="collapse item-3" id="3" role="tabpanel" data-parent="#accordion-1">
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Congratulations you are now one step closer to being safer! The program will keep you updated in the scan section so you can see how long you scan has currently got. Once the scan is complete the program will give you instructions on how to move forward depending on what it finds.&nbsp;</p>
                                <ul>
                                    <li>Visit the <a href="../View/Devices.php">Devices Page</a> soon to view all devices</li>
                                    <li>Follow the help guides on any devices necessary</li>
                                    <li>Sit back and enjoy a more secure network</li>
                                </ul>
                                <div style="display: flex" id="ScanSent"><p id="ScanSentText" style="color: red; padding-right: 5px">Scan Not Started </p> <i id="ScanSentIcon" class="fa fa-circle text-center" style="color: red"></i></div>
                                <p><i>This page will refresh once the scan is completed</i></p>
                            </div>
                            <div class="col-md-6">
                                <img src="https://media1.giphy.com/media/BPJmthQ3YRwD6QqcVD/giphy.gif?cid=790b7611d1b7c51dd32b38ff15158d44dd15cfc2905e94c0&rid=giphy.gif&ct=g" alt="this slowpoke moves"  width="720" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    document.getElementById("download").onclick = function () {
        location.href = "../../assets/AgentFile/DownloadSection/download.php";
        document.getElementById(1).className = "collapse item-1";
        document.getElementById(2).className = "collapse show item-2"

    };

    // Button DOM
    let btn = document.getElementById("discovery");

    // Adding event listener to button
    btn.addEventListener("click", () => {
        // Fetching Button value
        let btnValue = btn.value;
        // jQuery Ajax Post Request
        $.post('/scan/Create/CreateScan.php', {
            createScan : btnValue
        }, (response) => {

            // response from PHP back-end
            document.getElementById(2).className = "collapse item-2";
            document.getElementById(3).className = "collapse show item-3";

            //inform the user the scan has been sent
            document.getElementById("ScanSentText").innerText = "Scan Underway";
            document.getElementById("ScanSentText").style.color = "forestGreen"
            document.getElementById("ScanSentIcon").style.color = "forestGreen"
            document.getElementById("ScanSentIcon").className = "fa fa-circle blink_me text-center"

            setTimeout(checkReload, 1000);
        });
    });

    function checkReload() {

        $.post('/scan/Create/tutorial.php', {
            checkScan : true
        }, (response) => {

            console.log(response)

            if (response === "true"){
                location.href = "/scan/View/Devices.php"
            }
            else{
                setTimeout(checkReload, 5000);
            }

        });
    }


</script>
<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.3.1/js/swiper.jquery.min.js"></script>
</body>

</html>
