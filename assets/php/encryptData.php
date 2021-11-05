<?php
function encryptData($unencryptedText, $password) {
    //method of encryption
    $method = 'aes-256-cbc';

    // IV must be exact 16 chars (128 bit)
    $iv = chr(0x0) . chr(0x0) .
        chr(0x0) . chr(0x0) .
        chr(0x0) . chr(0x0) .
        chr(0x0) . chr(0x0) .
        chr(0x0) . chr(0x0) .
        chr(0x0) . chr(0x0) .
        chr(0x0) . chr(0x0) .
        chr(0x0) . chr(0x0);

    // Must be exact 32 chars (256 bit)
    $password = substr(hash('sha256', $password, true), 0, 32);

    //encrypt the txt and then return it.
    $encryptedText = base64_encode(openssl_encrypt($unencryptedText, $method, $password, OPENSSL_RAW_DATA, $iv));

    return $encryptedText;
}