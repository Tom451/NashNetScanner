<?php

function getHelp($type, $cwe): ?array
{


    return inputCWE($cwe);

}

function inputCWE($CWE): ?array
{

    $Help[] = null;
    $descriptionList[] = null;

    $str2 = substr($CWE, 4);

    $xml = file_get_contents("../Lookup/MitreLookup.xml");

    $xmlObject = simplexml_load_string($xml);

    $finishedJson = json_decode(json_encode($xmlObject), true);

    $weaknessesList = $finishedJson['Weaknesses']['Weakness'];

    foreach ($weaknessesList as $weakness){

        $count = 0;

        if ($weakness['@attributes']['ID'] == $str2){

            if(isset($weakness['@attributes']['Name'])){
                $Help['FriendlyName'][0] = $weakness['@attributes']['Name'];
            }

            if(isset($weakness['Alternate_Terms'][0]['Term'])){
                $Help['Type'][0] = $weakness['Alternate_Terms'][0]['Term'];
            }

            $mitigationList[] = null;

            if(!isset($weakness['Potential_Mitigations'])){
                $Help['Description'][] = "No Solutions please refer to links";
                return $Help;
            }

            foreach ($weakness['Potential_Mitigations'] as $mitigation){
                $mitigationList =  $mitigation;
            }


            foreach ($mitigationList as $description){
                if (isset($description['Description'])){

                    if ($description['Description'] != null){
                        $Help['Description'][] = $description['Description'];
                    }

                }
                else{
                    $Help['Description'][] = "No Solutions please refer to links";
                    return $Help;
                }

            }
            $count++;






        }


    }

    return $Help;

}

$CommonClassification = array(
    "misconfiguration", "default installations", "buffer overflow", "unpatched", "flaw",
    "application flaws", "default password");