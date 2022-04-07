<?php


?>
<style>
    .blink_me {
        animation: blinker 2s linear infinite;
    }

    @keyframes blinker {
        50% {
            opacity: 0;
        }
    }
</style>



<nav class="navbar navbar-light navbar-expand-md navigation-clean-button" style="margin-bottom: 21px;box-shadow: 0 2px 7px var(--secondary);padding: 10px;background: white;">

    <div class="container">
        <img style="width: 50px;height: 50px;margin-right: 15px;" src="/assets/images/31431a2b-b9f3-4e62-8545-c5ce5a898951_200x200.png"  alt="Logo"/>
        <a class="navbar-brand" href="/homePage.php">NashNetworkScanner</a><button data-toggle="collapse" class="navbar-toggler" data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navcol-1">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="/homePage.php">Home Page</a></li>

                <li class="nav-item dropdown"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">Scans</a>
                    <div class="dropdown-menu"><a class="dropdown-item" href="/scan/Create/CreateScan.php">Create Scan</a><a class="dropdown-item" href="/scan/View/previousScans.php">Previous Scans</a><a class="dropdown-item" href="/scan/View/Devices.php">Devices</a></div>
                </li>

                <li class="nav-item dropdown"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">Agent:
                        <?php
                        if($_SESSION['agentStatus'] == 1){
                            echo '<span style="color: lawngreen">Online <i class="fa fa-circle blink_me"></i></span>';
                        }
                        else{
                            echo '<span style="color: red">Offline <i class="fa fa-circle"></i></span>';
                        }
                        ?>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="/assets/AgentFile/DownloadSection/download.php">Download</a>
                    </div>
                </li>
            </ul>
            <span class="navbar-text actions">
                <strong style="padding-right: 10px;"><?php echo ucfirst($_SESSION['userName'])?></strong>
                <a class="btn btn-light action-button" role="button" href="/user/logOut.php">Log Out</a>
            </span>
        </div>
    </div>
</nav>



