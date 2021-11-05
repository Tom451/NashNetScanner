<?php
function getKeyPassword($password, $salt){
    $key = base64_encode(hash_pbkdf2("sha256", $password, $salt, 10, 20, TRUE));

    return $key;
}
