<?php

function getHelp($type): ?string
{
    $Help = null;
    if ($type == "misconfiguration"){
        $Help = "This vulnerbility is caused by a missconfiguration of the appication or program, Ensure that you check your configurations on the current device";

    }
    else if($type == "default installations"){

    }
    else if($type == "buffer overflow"){

        $Help = "This is a buffer overflow issue which ";

    }
    else if($type == "unpatched"){

    }
    else if($type == "flaw"){
        $Help = "This vulnerbility is caused by a flaw in the application or program mentioned, these can be fixed by ensuring that the 
        device/program is updated to the latest patches and also ensure that if this program is not used then it is removed off the system
        ";
    }
    else if($type == "default password"){

    }
    return $Help;
}


$CommonClassification = array(
    "misconfiguration", "default installations", "buffer overflow", "unpatched", "flaw",
    "application flaws", "default password");