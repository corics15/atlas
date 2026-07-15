<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Company_model');
  }

  public function index()
  {
    $this->setPage('Settings');

    $this->pageScript = 'company';

    $this->data['company'] = $this->Company_model->get();

    $this->render('company/index');
  }
}