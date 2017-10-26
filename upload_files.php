<?php
require 'vendor/autoload.php';

date_default_timezone_set('UTC');

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

if (count($argv) !== 3)
{
  printf("usage: %s <root url> <folder>\n", $argv[0]);
  exit();
}

$imgRoot  = $argv[1];
$dir      = $argv[2];

$files = [];
if (!is_dir($dir))
{
  exit("specify a valid folder\n");
}

$Directory = new RecursiveDirectoryIterator($dir);
$Iterator = new RecursiveIteratorIterator($Directory);
$Regex = new RegexIterator($Iterator, '/^.+\.(png|jpg|bmp)$/i', RecursiveRegexIterator::GET_MATCH);
foreach ($Regex as $file)
{
  $files[] = $file[0];
}

sort($files);
$images = [];
// make sure size in single request does not exceed 20MB
$sizeLimit = 20 * 1024 * 1024;
$currentBatchSize = 0;
$filesToUpload = [];
foreach ($files as $file)
{
  $size = filesize($file);
  if ($size >= $sizeLimit)
  {
    printf("unable to upload $file - size $size too big\n");
    continue;
  }
  if ($currentBatchSize + $size < $sizeLimit)
  {
    // can add more files to batch
    $currentBatchSize += $size;
    $filesToUpload[] = $file;
  } else {
    // need to upload this batch and start a new one
    $r = upload_files($imgRoot, $filesToUpload);
    $images = array_merge($images, $r);
    $currentBatchSize = $size;
    $filesToUpload = [$file];
  }
}

// process the final batch
$r = upload_files($imgRoot, $filesToUpload );
$images = array_merge($images, $r);
if (!$images)
{
  exit("no images uploaded\n");
}

$size = 0;
foreach ($images as $e)
{
  $size += $e['size'];
}

$manga = [
  'name'    => basename($dir),
  'size'    => $size,
  'imgRoot' => $imgRoot,
  'date'    => date('c'),
  'images'  => $images,
];

$sdk = new Aws\Sdk([
    'region'   => 'us-east-1',
    'version'  => 'latest'
]);

$dynamodb = $sdk->createDynamoDb();
$marshaler = new Marshaler();

$params = [
  'TableName' => 'Folders',
  'Item'      => $marshaler->marshalItem($manga),
];

try {
    $result = $dynamodb->putItem($params);
    echo "Added item\n";

} catch (DynamoDbException $e) {
    echo "Unable to add item:\n";
    echo $e->getMessage() . "\n";
}

function upload_files($imgRoot, $files)
{
  $postData = [];
  
  // Create array of files to post
  foreach ($files as $index => $file) {
    $postData["images[$index]"] = curl_file_create(
      realpath($file),
      mime_content_type($file),
      basename($file)
    );
  }
  $request = curl_init("$imgRoot/upload.php");
  curl_setopt($request, CURLOPT_POST, true);
  curl_setopt($request, CURLOPT_POSTFIELDS, $postData);
  curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($request);

  if ($result === false || curl_errno($request)) {
    error_log(curl_error($request));
  }

  $http_code = curl_getinfo($request, CURLINFO_HTTP_CODE);

  curl_close($request);

  if (200 !== $http_code)
  {
    echo 'Unexpected HTTP code: ', $http_code, "\n";
    return [];
  }

  $r = json_decode($result, true);
  if (!is_array($r))
  {
    echo "Result is not an array\n";
    return [];
  }
  return $r;
}
