<?php
/*
         M""""""""`M            dP                     
         Mmmmmm   .M            88                     
         MMMMP  .MMM  dP    dP  88  .dP   .d8888b.     
         MMP  .MMMMM  88    88  88888"    88'  `88     
         M' .MMMMMMM  88.  .88  88  `8b.  88.  .88     
         M         M  `88888P'  dP   `YP  `88888P'     
         MMMMMMMMMMM  [    -*- Magebay.com -*-   ]      
                                                       
         * * * * * * * * * * * * * * * * * * * * *     
         * -    - -    M.A.G.E.B.A.Y    - -    - *     
         * -  Copyright © 2010 - 2016 Magebay  - *     
         *    -  -  All Rights Reserved  -  -    *     
         * * * * * * * * * * * * * * * * * * * * *     
                                                     
*//**
 * --*--*--*--*--*--*--*--*--*--*--*--*--*--*--*--*-- *
 * @PROJECT    : Super Scan DIRECTORY
 * @AUTHOR     : Zuko
 * @COPYRIGHT  : © 2016 Magebay - Magento Ext Provider
 * @LINK       : https://www.magebay.com/
 * @FILE       : index.php
 * @CREATED    : 8:38 AM , 08/Nov/2016
 * @DETAIL     :
 * --*--*--*--*--*--*--*--*--*--*--*--*--*--*--*--*-- *
**/
use Magebay\Misc\PathHelper;
use Magebay\Misc\Scanner;

include "src/Scanner.php";
include "src/PathHelper.php";
echo "
<html>
<head>
<title>Load-all</title>
</head>
<body>
<form method='post'>
<label>Folder Path : <input type='text' name='path' /></label>
<p>Relative path with index.php</p>
<p><strong>OR</strong></p>
<label>Full Path : <input type='text' name='fullpath' /></label>
<p>Full path to folder . Eg : E:\Desktop\Images</p>
<button type='submit'>Chơi</button>
</form>
</body>
</html>
";
if($_POST)
{
    $zip = new \ZipArchive();
    $zipFile = \Magebay\Misc\PathHelper::generateUniqueName().'.zip';
    if(!file_exists($zipFile)) touch($zipFile);
    $zip->open($zipFile);
    $pathHelper = new PathHelper();
    $imgExt = ['png','svg','jpg','jpeg'];
    $fontExt = ['ttf','otf'];
    $jsonFile = 'data.json';
    $path = './';
    if(strlen($_POST['fullpath']))
    {
        $path = $_POST['fullpath'];
    }
    else
        $path .= $_POST['path'];
    try
    {
        $imgDest = './dest/img';
        $pathHelper->isDirWriteAble($imgDest);
        $fontDest = './dest/font';
        $pathHelper->isDirWriteAble($fontDest);
    }
    catch (\Exception $e)
    {
        echo $e->getMessage();
    }
    $dir = new Scanner($path);
    if(file_exists('data.json')) unlink('data.json');
    $jsonHandle = fopen('data.json','w+');
    $dir->setFileEntryCallback('imageCallback');
    $dir->getDirEntries();
    $zip->addFile(realpath('data.json'),'data.json');
    $zip->close();
    $imgDir = new Scanner($imgDest);
    $fontDir = new Scanner($fontDest);
    $imgDir->setFileEntryCallback('unlink');
    $fontDir->setFileEntryCallback('unlink');
    $imgDir->getDirEntries();
    $fontDir->getDirEntries();
    echo "Okie";
}

function imageCallback ($filepath)
{
    global $imgDest,$imgExt,$jsonFile,$fontDest,$fontExt,$zip;
    $json = getJsonData();
    $fileExt = pathinfo($filepath,PATHINFO_EXTENSION);
    $fileName = pathinfo($filepath,PATHINFO_FILENAME);
    if(in_array($fileExt,$imgExt))
    {
        while (file_exists($imgDest.DIRECTORY_SEPARATOR.$fileName.'.'.$fileExt))
        {
            $fileName .= '_2';
        }
        if(copy($filepath,$imgDest.DIRECTORY_SEPARATOR.$fileName.'.'.$fileExt))
        {
            $json[] = [
                'file' => $fileName.'.'.$fileExt,
                'type' => 2,
                'title' => $fileName
            ];
            $zip->addFile(realpath($imgDest.DIRECTORY_SEPARATOR.$fileName.'.'.$fileExt),$fileName.'.'.$fileExt);
//            unlink(realpath($imgDest.DIRECTORY_SEPARATOR.$fileName.'.'.$fileExt));
        }
    }elseif(in_array($fileExt,$fontExt))
    {
        while (file_exists($fontDest.DIRECTORY_SEPARATOR.$fileName.'.'.$fileExt))
        {
            $fileName .= '_2';
        }
        if(copy($filepath,$fontDest.DIRECTORY_SEPARATOR.$fileName.'.'.$fileExt))
        {
            $json[] = [
                'file' => $fileName.'.'.$fileExt,
                'type' => 1,
                'title' => $fileName
            ];
            $zip->addFile(realpath($fontDest.DIRECTORY_SEPARATOR.$fileName.'.'.$fileExt),$fileName.'.'.$fileExt);
//            unlink(realpath($imgDest.DIRECTORY_SEPARATOR.$fileName.'.'.$fileExt));
        }
    }
    file_put_contents($jsonFile,json_encode($json,JSON_PRETTY_PRINT));
}
function getJsonData()
{
    global $jsonFile,$jsonHandle;
    if(!$size = filesize($jsonFile))
    {
        $jsonStr = [];
        fwrite($jsonHandle,json_encode($jsonStr,JSON_PRETTY_PRINT));
    }

    return json_decode(file_get_contents($jsonFile),true);
}