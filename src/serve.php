<?php
ob_start();

require_once('config.php');
require_once('functions.php');

$action = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$pathInfo = pathinfo($action);

if (!isset($pathInfo['extension']) || !isset($pathInfo['filename'])) {
  returnNotFound();
}

$hashInfo = explode('-', $pathInfo['filename']);

$info = array();
$info['extension'] = mapExtension($pathInfo['extension']);
$info['hash'] = $hashInfo[0];

if (isset($hashInfo[1])) {
  $dimensions = explode('x', $hashInfo[1]);
  if (count($dimensions) == 2) {
    $info['width']  = intval($dimensions[0]);
    $info['height'] = intval($dimensions[1]);
  }

  $info['crop'] = (isset($hashInfo[2]) && $hashInfo[2] == 'crop');
}

global $IMAGE_ROOT;
$filePath = getPathFromHash($info['hash'], $info['extension'], $IMAGE_ROOT);

if (!$filePath) {
  returnNotFound();
}

$bindata = getDataFromPath($filePath);
if (!$bindata) {
  returnNotFound();
}

// resize if necessary
if (isset($info['width'])) {
  $image   = readImage($bindata);
  $bindata = resizeImage($image, $info['width'], $info['height'], $info['crop']);
}

$meta = readImageData($bindata);

if (!$meta || $meta['extension'] !== $info['extension']) {
  returnNotFound();
}

// image exists if we got this far
serveData($bindata, $meta);

