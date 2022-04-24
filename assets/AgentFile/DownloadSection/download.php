<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: ../../../index.php');
    exit;
}

// Get real path for our folder
$rootPath = realpath('../NNDAgentDownloader');

// Initialize archive object
$zip = new ZipArchive();
$fileName = $_SESSION['user_id'] . "Download.zip";
$zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
//credit to "Niroj" - "https://www.edureka.co/community/82175/how-to-zip-a-whole-folder-using-php";
/** @var SplFileInfo[] $NNDEasyDownload */
$NNDEasyDownload = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);
//Credit End

//Create a new unique nonce
$myfile = fopen($_SESSION['user_id']."NonceFile.txt", "w");

//Get the user nonce
$nonce = $_SESSION['nonce'];

//write to the file
fwrite($myfile, $nonce);

//get the real path of the NEW nonce
$noncePath = realpath($_SESSION['user_id']."NonceFile.txt");

//close the file
fclose($myfile);

// Add current file to archive where the ORIGINAL user nonce would be
$zip->addFile($noncePath, "AppFiles/Data/UserNonce.txt");

foreach ($NNDEasyDownload as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {

            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);

            // Add current file to archive
            $zip->addFile($filePath, $relativePath);



    }





}



// Zip archive will be created only after closing object
$zip->close();

header('Content-disposition: attachment; filename=NNDEasyDownload.zip');
header('Content-type: application/zip');

readfile($fileName);

header('Location: ../../../homePage.php');

//delete the download and the new nonce file once downloaded
unlink($fileName);
unlink($_SESSION['user_id']."NonceFile.txt");


