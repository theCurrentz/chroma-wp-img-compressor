<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
Plugin Name: Chroma WP IMG Compressor
Author: Parker Westfall
Description: Leverages the psliwa/image-optimizer library to compress images on upload to the wordpress media folder.
Version: 1.0
NOT LICENSED
*/
//Image Compression Override
add_filter( 'jpeg_quality', create_function( '', 'return 72;' ) );

//We're gonna try to fit all necessary logic in this callback function which is essentially a wrapper for psliwa image Compressor
function chroma_wp_img_compressor($data) {
  //initialize img Compressor
  $loader = require (plugin_dir_path( __FILE__ ) . '/vendor/autoload.php');
  $loader->addPsr4('ImageOptimizer\\', plugin_dir_path( __FILE__ ).'/ImageOptimizer');
  $factory = new \ImageOptimizer\OptimizerFactory(array('execute_only_first_jpeg_optimizer' => true, 'ignore_errors' => false));

  $file_path = $data['file'];
  $image = null;
  switch ($data['type']) {
    case 'image/jpeg':
      $img_Compress0r = $factory->get('jpegoptim');
      $img_Compress0r->optimize($file_path);
      break;
    case 'image/png':
      $img_Compress0r = $factory->get('png');
      $img_Compress0r->optimize($file_path);
      break;
    default:
      break;
  }
  return $data;
}
add_filter( 'wp_handle_upload', 'chroma_wp_img_compressor' );
