<?php
require 'vendor/autoload.php';
use Archive7z\Archive7z;
if (count($argv) !== 2)
{
  printf("usage: %s <archive path>\n");
  exit();
}
$archive = $argv[1];
$outputPath = pathinfo($archive, PATHINFO_FILENAME);
if (file_exists($outputPath))
{
  exit("Path $outputPath already exists\n");
}

mkdir($outputPath);

$obj = new Archive7z($archive);

if (!$obj->isValid()) {
    throw new Exception('Incorrect archive');
}

$obj->setOutputDirectory($outputPath);
$obj->extract(); // extract archive
