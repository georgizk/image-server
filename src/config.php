<?php

// Set up Composer's autoloader
require_once dirname(__FILE__) . '/../vendor/autoload.php';

$ALLOWED_TYPES = array (
  'image/jpeg' => 'jpg',
  'image/gif'  => 'gif',
  'image/png'  => 'png',
  'image/tiff' => 'tiff'
);

$ALLOWED_URL_SCHEMES = array('http', 'https');

$ALLOWED_RESIZE_RANGE = array('min' => 1, 'max' => 1920);

$IMAGE_ROOT = dirname(__FILE__) . '/images';
