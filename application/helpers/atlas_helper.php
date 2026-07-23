<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('atlas_asset')) {
  function atlas_asset($path)
  {
    $app = atlas_app();
    return base_url($path) . '?v=' . $app['app_version'];
  }
}

if (!function_exists('atlas_url')) {
  function atlas_url($path = '')
  {
    return base_url($path);
  }
}

if (!function_exists('atlas_app')) {
  function atlas_app()
  {
    $CI =& get_instance();
    return $CI->config->item('atlas');
  }
}

function atlas_company()
{
  $CI =& get_instance();
  $CI->load->model('Company_model');
  return $CI->Company_model->get();
}