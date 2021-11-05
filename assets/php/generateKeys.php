<?php

$plaintext = 'My secret message 1234';
$password = '3sc3RLrpd17';
$salt = "12ht12ht";
$newPassword = base64_encode(hash_pbkdf2("sha256", "TomIsMyName", $salt, 10, 20, TRUE));
$method = 'aes-256-cbc';
echo $newPassword;

// Must be exact 32 chars (256 bit)
$password = substr(hash('sha256', $password, true), 0, 32);
//echo "Password:" . $password . "\n";

// IV must be exact 16 chars (128 bit)
$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

// av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
$encrypted = base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
$encryptedNew = "yv3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=";
// My secret message 1234
$decrypted = openssl_decrypt(base64_decode($encryptedNew), $method, $password, OPENSSL_RAW_DATA, $iv);

//echo 'plaintext=' . $plaintext . "\n";
//echo 'cipher=' . $method . "\n";
//echo 'encrypted to: ' . $encrypted . "\n";
//echo 'decrypted to: ' . $decrypted . "\n\n";