<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('atlas_asset')) {
  function atlas_asset($path)
  {
    $CI =& get_instance();
    $app = $CI->config->item('atlas');
    return base_url($path) . '?v=' . $app['app_version'];
  }
}