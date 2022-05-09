<?php
    function createTableArea($CVEList, $item){

        //define the flaws
        $CommonClassification = array(
            "misconfiguration", "installations", "buffer overflow", "unpatched", "flaw",
            "application flaws", "password", "user", "program", "integer overflow", "memory", "SQL injection");


        if ($CVEList != null){
            //start by assigning variables
            $Low = [];
            $Medium = [];
            $High = [];
            $Critical = [];

            //new layout
            foreach ($CVEList as $CVE) {

                if (empty($CVE->impact->baseMetricV3->cvssV3)){
                    if($CVE ->impact->baseMetricV2->severity == "LOW"){
                        $Low[] = $CVE;
                    }
                    else if($CVE ->impact->baseMetricV2->severity == "MEDIUM"){
                        $Medium[] = $CVE;
                    }
                    else if($CVE ->impact->baseMetricV2->severity == "HIGH"){
                        $High[] = $CVE;
                    }
                }
                else{
                    if($CVE ->impact->baseMetricV3->cvssV3->baseSeverity == "LOW"){
                        $Low[] = $CVE;
                    }
                    else if($CVE ->impact->baseMetricV3->cvssV3->baseSeverity == "MEDIUM"){
                        $Medium[] = $CVE;
                    }
                    else if($CVE ->impact->baseMetricV3->cvssV3->baseSeverity == "HIGH"){
                        $High[] = $CVE;
                    }
                    else if($CVE ->impact->baseMetricV3->cvssV3->baseSeverity == "CRITICAL"){
                        $Critical[] = $CVE;
                    }
                }

            }

            addToTable($Critical, $item, $CommonClassification, "Critical");

            addToTable($High, $item, $CommonClassification, "High");

            addToTable($Medium, $item, $CommonClassification, "Medium");

            addToTable($Low, $item, $CommonClassification, "Low");

        }

        else{
            echo '<tr>';
            echo '<td colspan="4"> No Vulnerabilities! </td>';
            echo '<tr>';
        }
    };

function addToTable($CVEs, $item, $CommonClassification, $type){

    foreach ($CVEs as $CVE){
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

        echo '<td style="max-width: 2vw; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;"> '. $CVE -> cve -> CVE_data_meta -> ID.'</td>';

        //if (isset($Help['FriendlyName'])){
        //    echo '<td style="max-width: 2vw; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" >' . $Help['FriendlyName'][0] . '</td>';
        //}else{
        //    echo '<td style="max-width: 2vw; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">Name N/A: ' . $CVE -> cve -> CVE_data_meta -> ID . '</td>';
        //}

        echo '<td>' . $Type . '</td>';
        echo '<td>' . $item['VulnPortNumber'] . '</td>';

        if ($type === "Critical"){
            echo '<td style="background: rgba(139, 0, 0, 0.4)">';

        }
        else if($type === "High" ){
            echo '<td style="background: rgba(255, 0, 0, 0.2)">';
        }
        else if ($type === "Medium"){
            echo '<td style="background: rgba(255, 165, 0, 0.2)">';
        }
        else if ($type === "Low"){
            echo '<td style="background: rgba(76, 175, 80, 0.2)">';
        }

        if (empty($CVE->impact->baseMetricV3->cvssV3)){
            echo $CVE ->impact->baseMetricV2->severity;
        }
        else{

            echo $CVE ->impact->baseMetricV3->cvssV3->baseSeverity;

        }
        echo '</td>';



        echo '<tr>';

        $Help = getHelp($Type, $CWE);

        //Data area
        echo '<tr><td id="'.$ID.'" class="collapse" colspan="4"><div ">';
        echo '<h3>Description</h3>';
        echo $Description . '<br>';
        echo '<br>';
        echo'<a class="learn-more" href="'. "https://nvd.nist.gov/vuln/detail/". $ID .'" target="_blank">Learn More »</a>';
        echo '<br>';

        if (isset($Help['Description'])){
            echo '<h3>You have '. count($Help['Description']).
                ' options: </h3>';
        }
        else{
            echo '<h3> Please refer to links provided. </h3>';
        }

        $i = 1;



        echo'<section class="features-boxed"><div class="row justify-content-center features">';

        //check that help was set if not set it as null
        //This will only be needed if "Help Description" is set but it is a blank array
        if (!isset($Help['Description'][0])){
            $Help['Description'][0] = "No Solutions please refer to links";
        }

        foreach ($Help['Description'] as $HelpSolution){


            echo'<div class="col-sm-3 col-md-3 col-lg-3 item">';
            echo'<div class="box" >';
            echo'<h3 class="name">Option '.$i.'</h3>';
            echo'<p class="description">'.$HelpSolution.'</p>';


            echo'</div>';
            echo'</div>';

            $i++;


        }
        echo'</div></section>';


        echo '<h3>Relevant Links</h3>';
        echo'<p class="description"><b> Please Note all links to external sites have not all been checked and are visited at users risk</p></b>';
        foreach ($Links as $link){
            echo'<a style="overflow-wrap: break-word; max-width:50%"  href="'.$link.'" target="_blank">'. $link .'</a><br>';

        }

        echo '</div></td></tr>';
    }
}

    ?>


