<?php
require '..\assets\php\sessionChecker.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NashNetworkScanner</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="../assets/css/Article-List.css">
    <link rel="stylesheet" href="../assets/css/Data-Table-1.css">
    <link rel="stylesheet" href="../assets/css/Data-Table.css">
    <link rel="stylesheet" href="../assets/css/Features-Boxed-1.css">
    <link rel="stylesheet" href="../assets/css/Features-Boxed.css">
    <link rel="stylesheet" href="../assets/css/Features-Clean.css">
    <link rel="stylesheet" href="../assets/css/Highlight-Blue.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.3.1/css/swiper.min.css">
    <link rel="stylesheet" href="../assets/css/Navigation-with-Button.css">
    <link rel="stylesheet" href="../assets/css/styles.css">

</head>

<body>
<?php require '../assets/php/navBarLoggedIn.php' ?>

<section class="features-clean">
    <div class="container">
        <div class="intro">
            <h2 class="text-center">You have no Scans!</h2>
            <p class="text-center">Not a problem, we are here to help. Follow this simple easy to read guide to get you started!&nbsp;&nbsp;</p>
        </div>
    </div>
    <div role="tablist" id="accordion-1">
        <div class="card">
            <div class="card-header" role="tab">
                <h5 class="mb-0"><a data-toggle="collapse" aria-expanded="true" aria-controls="accordion-1 .item-1" href="#accordion-1 .item-1">Step One - The Download</a></h5>
            </div>
            <div class="collapse show item-1" role="tabpanel" data-parent="#accordion-1">
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Firstly, we are going to begin by downloading the simple little agent to you're computer, this will be a simple zip file which can be placed anywhere on your computer.&nbsp;</p>
                                <p>Please do note however, due to windows limitations, this has to be on an internal hard drive, so for the time being no memory sticks please!</p><button class="btn btn-secondary" type="button">Download</button>
                            </div>
                            <div class="col-md-6"><iframe allowfullscreen="" frameborder="0" width="90%" height="100%"></iframe></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" role="tab">
                <h5 class="mb-0"><a data-toggle="collapse" aria-expanded="true" aria-controls="accordion-1 .item-2" href="#accordion-1 .item-2">Step Two - The Scan</a></h5>
            </div>
            <div class="collapse show item-2" role="tabpanel" data-parent="#accordion-1">
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Right now that is out of the way, the second part is to create a scan. This is important as it will show where the vulnerabilities are located on the network, please begin by starting a scan below:&nbsp; &nbsp;</p><form action="/scan/createScan.php" method="post"><button class="btn btn-primary" type="submit" name="createScan" value="NetDisc">Run Discovery</button></form>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" role="tab">
                <h5 class="mb-0"><a data-toggle="collapse" aria-expanded="true" aria-controls="accordion-1 .item-3" href="#accordion-1 .item-3">Step Three - The Relax&nbsp;</a></h5>
            </div>
            <div class="collapse show item-3" role="tabpanel" data-parent="#accordion-1">
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6">
                                <p>Congratulations you are now one step closer to being safer! The program will keep you updated in the scan section so you can see how long you scan has currently got. Once the scan is complete the program will give you instructions on how to move forward depending on what it finds.&nbsp;</p>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.3.1/js/swiper.jquery.min.js"></script>
<script src="assets/js/Simple-Slider.js"></script>
</body>

</html>
