<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('atlas_asset')) {
  function atlas_asset($path)
  {
    $app = atlas_app();
    $file = FCPATH . ltrim($path, '/');

    if (is_file($file)) {
      return base_url($path) . '?v=' . filemtime($file);
    }
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

if (!function_exists('amount_in_words')) {
  function amount_in_words($amount)
  {
    $amount = round((float)$amount, 2);

    $pesos = (int)floor($amount);
    $centavos = (int)round(($amount - $pesos) * 100);

    $formatter = new NumberFormatter(
      'en',
      NumberFormatter::SPELLOUT
    );

    $words = $formatter->format($pesos);

    return strtoupper(
      $words .
      ' PESOS AND ' .
      str_pad($centavos, 2, '0', STR_PAD_LEFT) .
      '/100 ONLY'
    );
  }
}