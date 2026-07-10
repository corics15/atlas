<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_finder extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Product_model');
  }

  public function list()
  {
    return $this->jsonResponse(
      true,
      '',
      $this->Product_model->getFinder()
    );
  }

  public function barcode($barcode = '')
  {
    $product = $this->Product_model->getByBarcode(urldecode($barcode));

    if (!$product) {
      return $this->jsonResponse(
          false,
          'Product not found.'
      );
    }

    return $this->jsonResponse(
      true,
      '',
      $product
    );
  }
}