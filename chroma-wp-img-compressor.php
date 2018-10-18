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
add_filter( 'jpeg_quality', function () { return 77; } );

function chroma_wp_img_resize( $data ) {
  if ($data['type'] != 'image/jpeg' || $data['type'] != 'image/jpg' || $data['type'] != 'image/png')
    return $data;

    //set config
    $compression_level = 70;
    $min_size = 1920;

    //get file path
    $file_path = $data['file'];
    global $wpdb;
    $attachment_id = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $file_path ))[0];
    //resize file and compress on upload
    $image_editor = wp_get_image_editor($file_path);

    if (!is_wp_error( $modified_image ) ) {
      $sizes = $image_editor->get_size();

      if( isset($sizes['width']) || isset($sizes['height']) ) {
        if($sizes['width'] > $min_size || $sizes['height'] > $min_size) {
          $fraction_width = ( $min_size / $sizes['width'] ) * $sizes['width'];
          $fraction_height = ( $min_size / $sizes['height'] ) * $sizes['height'];
          $image_editor->resize($fraction_width, $fraction_height, false);
        }
      }
      //compress and save
      $image_editor->set_quality($compression_level);
      $saved_image = $image_editor->save($file_path);
      $meta_data = wp_generate_attachment_metadata( $attachment_id, $saved_image['path'] );
    }
    return $data;
}
add_action('wp_handle_upload', 'chroma_wp_img_resize', 100);

//We're gonna try to fit all necessary logic in this callback function which is essentially a wrapper for psliwa image Compressor
function chroma_wp_img_compressor($data) {
  if ($data['type'] != 'image/jpeg' || $data['type'] != 'image/jpg' || $data['type'] != 'image/png')
    return $data;

  //get file path
  $file_path = $data['file'];

  //initialize img Compressor
  $loader = require (plugin_dir_path( __FILE__ ) . '/vendor/autoload.php');
  $loader->addPsr4('ImageOptimizer\\', plugin_dir_path( __FILE__ ).'/ImageOptimizer');
  $factory = new \ImageOptimizer\OptimizerFactory(array('execute_only_first_jpeg_optimizer' => true, 'ignore_errors' => false));

  $image = null;
  switch ($data['type']) {
    case 'image/jpeg':
      $img_Compress0r = $factory->get('jpegoptim');
      $img_Compress0r->optimize($file_path);
      break;
    case 'image/jpg':
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
