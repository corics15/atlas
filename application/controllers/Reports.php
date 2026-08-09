<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Reports_model');
    $this->load->model('Branch_model');
  }

  public function sales_per_supplier()
  {
    $this->setPage('Reports');
    $this->pageScript = 'reports';

    $this->data['branches'] = $this->Branch_model->getDropdown();

    $this->data['selectedBranchId'] =
      (int)$this->session->userdata('branch_id');

    $this->render('reports/sales_per_supplier');
  }
}