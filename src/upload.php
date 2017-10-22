<?php
ob_start();
require_once('config.php');
require_once('functions.php');

$images = array();

if (!isset($_FILES['images'])) exit('No images');

foreach ($_FILES['images']['tmp_name'] as $key => $path) {
  $dataToHandle = [getDataFromPath($path)];
  $imageData = processUploadedData($dataToHandle);
  if (!$imageData) continue;
  $imageData[0]['name'] = $_FILES['images']['name'][$key];
  $images[] = $imageData[0];
}

if (!$images) return;
print json_encode($images);
