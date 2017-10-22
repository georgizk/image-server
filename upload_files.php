<?php

$files = [];
if (!isset($argv[1]))
{
  exit('specify a folder');
}
$dir = $argv[1];
if (!is_dir($dir))
{
  exit('specify a folder');
}
$dh = opendir($dir);
while (($file = readdir($dh)) !== false) {
  $path = $dir . $file;
  if (filetype($path) == 'file') {
    $files[] = $path;
  }
}
closedir($dh);
sort($files);

// Set postdata array
$postData = [];

// Create array of files to post
foreach ($files as $index => $file) {
  $postData['images[' . $index . ']'] = curl_file_create(
    realpath($file),
    mime_content_type($file),
    basename($file)
  );
}

$request = curl_init('http://127.0.0.1:8080/upload.php');
curl_setopt($request, CURLOPT_POST, true);
curl_setopt($request, CURLOPT_POSTFIELDS, $postData);
curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($request);

if ($result === false) {
  error_log(curl_error($request));
}

curl_close($request);

$r = json_decode($result, true);
foreach ($r as $e)
{
  printf("%s - %s.%s\n", $e['name'], $e['checksum'], $e['extension']);
}
