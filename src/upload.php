<?php
ob_start();
require_once('config.php');
require_once('functions.php');

$dataToHandle = array();

if (!isset($_FILES['images'])) exit('No images');
foreach ($_FILES['images']['tmp_name'] as $path) {
  $dataToHandle[] = getDataFromPath($path);
}

$images = processUploadedData($dataToHandle);
if (!$images) return;
printf('<ul>');
foreach ($images as $imageName)
{
  printf('<li><a href="/%s">%s</a></li>', $imageName, $imageName);
}
printf('</ul>');
