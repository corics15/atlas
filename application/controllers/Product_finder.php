<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_finder extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Product_model');
    $this->load->model('Product_finder_model');
  }

  public function list()
  {
    return $this->jsonResponse(
      true,
      '',
      $this->Product_model->getFinder()
    );
  }

  /*** replaced by lookup function */
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

  public function search()
  {
    $keyword = trim($this->input->get('q'));
    $result = $this->Product_finder_model->search($keyword);

    return $this->jsonResponse(
      true,
      '',
      $result
    );
  }

  public function lookup()
  {
    $keyword = trim($this->input->get('q'));
    $result = $this->Product_finder_model->lookup($keyword);

    if (!$result) {
      return $this->jsonResponse(
        false,
        'Product not found.'
      );
    }

    return $this->jsonResponse(
      true,
      '',
      $result
    );
  }

}