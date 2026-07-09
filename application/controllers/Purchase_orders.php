<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_orders extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Customer_model');
    $this->load->model('Salesman_model');

    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Purchase Orders',
      [
        'id'   => 'btnNewPurchaseOrder',
        'icon' => 'fas fa-cart-plus',
        'text' => 'New Purchase Order',
      ]
    );

    $this->pageScript = 'purchase_orders';

    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();

    $this->render('purchase_orders/index');
  }
}