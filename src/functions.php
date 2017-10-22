<?php

require_once('config.php');

function mapExtension($extension) {
  $extension = strtolower($extension);
  if ($extension == 'jpeg') $extension = 'jpg';

  return $extension;
}

function readImageData($bindata)
{
  global $ALLOWED_TYPES;
  $data = @getimagesizefromstring($bindata);
  if (!$data || !isset($data['mime'])) return false;
  if (!array_key_exists($data['mime'], $ALLOWED_TYPES)) return false;

  $checksum = hash('sha256', $bindata);
  $mime = $data['mime'];
  $ext = $ALLOWED_TYPES[$mime];

  return array(
    'extension' => $ext,
    'mime'      => $mime,
    'checksum'  => $checksum,
    'width'     => $data[0],
    'height'    => $data[1],
    'size'      => strlen($bindata)
  );
}


function readMetadata($bindata) {
  if ($data = readImageData($bindata)) {
    return $data;
  }

  return false;
}

function getDataFromPath($path) {
  if (!$path) return false;

  if (!is_file($path)) return false;

  $contents = @file_get_contents($path, false);
  if ($contents) return $contents;

  return false;
}

function processUploadedData($items = array())
{
  $names = array();

  foreach ($items as $bindata) {
    if (!$bindata) continue;
    $metadata = readMetadata($bindata);

    // invalid data, or not allowed format
    if (!$metadata) {
      array_push($names, "error.jpg");
      continue;
    }

    global $IMAGE_ROOT;
    $savePath = getPathFromHash($metadata['checksum'], $metadata['extension'], $IMAGE_ROOT);
    saveData($bindata, $savePath);

    $filename = pathinfo($savePath, PATHINFO_FILENAME);
    array_push($names, $metadata);
  }

  return $names;
}

function getPathFromHash($hash, $extension, $root, $suffix = '', $depth = 10)
{
  if ($depth >= 64) return false;
  if (!preg_match('/[a-f0-9]{64}/', $hash)) return false;

  $dir = $root;

  for ($i = 0; $i < $depth; ++$i) {
    $dir .= '/' . $hash[$i];
  }

  $path = sprintf('%s/%s%s.%s',
    $dir, $hash, $suffix, $extension
  );

  return $path;
}

function readImage($bindata)
{
  $data = readImageData($bindata);
  if (!$data) return false;

  try {
    $image = new Imagick();
    $image->readImageBlob($bindata);
    $image->setImageFormat($data['extension']);
    return $image;
  }
  catch (\Exception $e) {
    return false;
  }
}

function saveData($bindata, $savePath)
{
  if (!$savePath || !$bindata || file_exists($savePath)) return false;
  $dir = dirname($savePath);
  if (!is_dir($dir)) mkdir($dir, 0750, true);
  file_put_contents ($savePath, $bindata);
  chmod($savePath, 0640);
  return true;
}

function resizeImage($image, $max_width, $max_height, $crop = false)
{
  if (!$image) return false;

  global $ALLOWED_RESIZE_RANGE;
  // check dimensions are valid
  if (!is_int($max_width) || !is_int($max_height) ||
      min(array($max_width, $max_height)) < $ALLOWED_RESIZE_RANGE['min'] ||
      max(array($max_width, $max_height)) > $ALLOWED_RESIZE_RANGE['max']) {
    return false;
  }

  try {
    if ($crop) {
      $image->cropThumbnailImage($max_width, $max_height);
    }
    else {
      $image->thumbnailImage($max_width, $max_height, true);
    }
    return $image;
  }
  catch (\Exception $e) {
    return false;
  }
}

function returnNotFound() {
  header("HTTP/1.0 404 Not Found");
  exit();
}

function serveData($bindata, $metadata) {
  header("Content-Type: {$metadata['mime']}");
  header("Content-Length: {$metadata['size']}");
  header('Last-Modified: ' . date('r', time()));
  header('Cache-Control: public');
  header('Expires: '. date('r', strtotime('+1 month')));
  echo $bindata;
  exit();
}

