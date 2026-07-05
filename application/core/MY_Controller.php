<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
  protected $data = [];

  public function __construct()
  {
    parent::__construct();
    $this->config->load('atlas');
    $this->data['app'] = $this->config->item('atlas');
    if ($this->router->fetch_class() != 'auth') {
      $this->requireLogin();
    }
  }

  protected function render($view)
  {
    $this->data['content'] = $view;
    $this->load->view('layouts/master', $this->data);
  }

  protected function jsonResponse($success, $message = '', $data = [])
  {
    $response = [
      'success' => $success,
      'message' => $message,
      'data'    => $data
    ];

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
  }

  protected function requireLogin()
  {
    if (!$this->session->userdata('logged_in')) {
      redirect('auth');
      exit;
    }
  }

}