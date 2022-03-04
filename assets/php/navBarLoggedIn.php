<?php ?>


<nav class="navbar navbar-light navbar-expand-md navigation-clean-button" style="margin-bottom: 21px;box-shadow: 0px 2px 7px var(--secondary);padding: 10px;background: white;">

    <div class="container">
        <img style="width: 50px;height: 50px;margin-right: 15px;" src="/assets/images/31431a2b-b9f3-4e62-8545-c5ce5a898951_200x200.png" />
        <a class="navbar-brand" href="/homePage.php">NashNetworkScanner</a><button data-toggle="collapse" class="navbar-toggler" data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navcol-1">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="/homePage.php">Home Page</a></li>
                <li class="nav-item"><a class="nav-link" href="/assets/AgentFile/DownloadSection/download.php">Agent Download</a></li>
                <li class="nav-item dropdown"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">Scans</a>
                    <div class="dropdown-menu"><a class="dropdown-item" href="/scan/createScan.php">Create Scan</a><a class="dropdown-item" href="/scan/previousScans.php">Previous Scans</a><a class="dropdown-item" href="/scan/devices.php">Devices</a></div>
                </li>
            </ul><span class="navbar-text actions"> <strong style="padding-right: 10px;"><?php echo $_SESSION['userName']?></strong><a class="btn btn-light action-button" role="button" href="/user/logOut.php">Log Out</a></span>
        </div>
    </div>
</nav>
