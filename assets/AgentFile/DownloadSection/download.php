<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: ../../../index.php');

    exit;
} else {

}

// Get real path for our folder
$rootPath = realpath('../NNDAgentDownloader');

// Initialize archive object
$zip = new ZipArchive();
$fileName = $_SESSION['user_id'] . "Download.zip";
$zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
//credit to "Niroj" - "https://www.edureka.co/community/82175/how-to-zip-a-whole-folder-using-php";
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);
//Credit End

foreach ($files as $name => $file)
{


    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // customerise user nonce
        if ($file->getFilename() == "UserNONCE.txt.deploy"){
            $myfile = fopen($file, "w");


            fwrite($myfile, $txt);
        }
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }





}



// Zip archive will be created only after closing object
$zip->close();

//header('Content-disposition: attachment; filename=files.zip');
//header('Content-type: application/zip');

//readfile($fileName);

//header('Location: ../../../homePage.php');

//delete the download once downloaded
unlink($fileName);
?>


