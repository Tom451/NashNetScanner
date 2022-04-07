<?php

?>
<table class="table table-hover table table-striped table-bordered">
    <thead>
    <tr>
        <th>Weakness Name</th>
        <th>Type Of Vulnerbility</th>
        <th>Port Affected</th>
        <th>Serverty</th>
    </tr>
    </thead>
    <tbody>
    <?php

    //define the flaws
    $CommonClassification = array(
        "misconfiguration", "installations", "buffer overflow", "unpatched", "flaw",
        "application flaws", "password", "user", "program", "integer overflow", "memory", "SQL injection");


    if ($CVEList != null){
        //new layout
        foreach ($CVEList as $CVE) {
            //if the new style cvss exsits show that

            $ID = $CVE -> cve -> CVE_data_meta -> ID;
            //get the appropriate CWE's
            $CWE = $CVE -> cve -> problemtype -> problemtype_data[0] -> description[0] -> value;
            $Description = null;
            $Links = array();

            foreach ($CVE -> cve -> description -> description_data as $descriptionList) {
                $Description = $descriptionList -> value;
            }

            foreach ($CVE -> cve -> references -> reference_data as $URLItem) {
                $Links[] = $URLItem -> url;
            }

            $Type = "Unknown";

            foreach($CommonClassification as $a) {
                if (stripos(strtolower($Description),$a) !== false)
                    $Type = ucwords($a . " Exploit");
            }

            echo '<tr data-toggle="collapse" data-target="#'.$ID.'" >';

            if (isset($Help['FriendlyName'])){
                echo '<td style="max-width: 2vw; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" >' . $Help['FriendlyName'][0] . '</td>';
            }else{
                echo '<td style="max-width: 2vw; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">Name N/A: ' . $CVE -> cve -> CVE_data_meta -> ID . '</td>';
            }

            echo '<td>' . $Type . '</td>';
            echo '<td>' . $item['VulnPortNumber'] . '</td>';
            if (empty($CVE->impact->baseMetricV3->cvssV3)){
                echo '<td>' . $CVE ->impact->baseMetricV2->severity . '</td>';
            }
            else{
                echo '<td>' . $CVE ->impact->baseMetricV3->cvssV3->baseSeverity . '</td>';
            }




            echo '<tr>';

            $Help = getHelp($Type, $CWE);

            //Data area
            echo '<tr><td id="'.$ID.'" class="collapse" colspan="4"><div ">';
            echo '<h3>Description</h3>';
            echo $Description . '<br>';

            if (isset($Help['Description'])){
                echo '<h3>You have '. count($Help['Description']).
                    ' options: </h3>';
            }
            else{
                echo '<h3> Please refer to links provided. </h3>';
            }

            $i = 1;



            echo'<section class="features-boxed"><div class="row justify-content-center features">';

            if (!isset($Help['Description'])){
                $Help['Description'][0] = "CWE Contained No Description, please view links bellow";
            }

            foreach ($Help['Description'] as $HelpSolution){


                echo'<div class="col-sm-3 col-md-3 col-lg-3 item">';
                echo'<div class="box" >';
                echo'<h3 class="name">Option '.$i.'</h3>';
                echo'<p class="description">'.$HelpSolution.'</p><a class="learn-more" href="#">Learn more »</a>';
                echo'</div>';
                echo'</div>';

                $i++;


            }
            echo'</div></section>';


            echo '<h3>Relevant Links</h3>';
            foreach ($Links as $link){
                echo'<a style="overflow-wrap: break-word; max-width:50%"  href="'.$link.'">'. $link .'</a><br>';

            }

            echo '</div></td></tr>';
        }
    }

    else{
        echo '<tr>';
        echo '<td colspan="4"> No Vulnerabilities! </td>';
        echo '<tr>';
    }



    ?>

</table>
