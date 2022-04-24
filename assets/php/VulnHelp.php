<?php

function getHelp($type, $cwe): ?array
{
    return inputCWE($cwe);

}

function inputCWE($CWE): ?array
{
    //initialise the array
    $Help[] = null;

    //this gets just the CWE number as all CWEs are witten as CWE-NUMBER
    $CWENumber = substr($CWE, 4);

    //get the file
    $MITRELookupFile = file_get_contents("../Lookup/MitreLookup.xml");

    //load the file to xml parser
    $MITRELookupFileXMLObject = simplexml_load_string($MITRELookupFile);

    //pass the xml object to json decoder/encoder to convert
    $MITRELookupJsonOBJECT = json_decode(json_encode($MITRELookupFileXMLObject), true);

    //get the list of weaknesses
    $weaknessesList = $MITRELookupJsonOBJECT['Weaknesses']['Weakness'];

    foreach ($weaknessesList as $weakness){

        $count = 0;

        // if the CWE is the selected one
        if ($weakness['@attributes']['ID'] == $CWENumber){

            // if the name is there set the name
            if(isset($weakness['@attributes']['Name'])){
                $Help['FriendlyName'][0] = $weakness['@attributes']['Name'];
            }

            // if there is an alternate term set that
            if(isset($weakness['Alternate_Terms'][0]['Term'])){
                $Help['Type'][0] = $weakness['Alternate_Terms'][0]['Term'];
            }

            //list of help
            $mitigationList[] = null;

            //if there is no help options
            if(!isset($weakness['Potential_Mitigations'])){
                $Help['Description'][] = "No Solutions please refer to links";
                return $Help;
            }

            //else fill the array
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