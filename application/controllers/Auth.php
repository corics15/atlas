<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('User_model');
  }

  public function index()
  {
    $this->load->view('auth/login');
  }

  public function login()
  {
    $username = trim($this->input->post('username'));
    $password = $this->input->post('password');

    $user = $this->User_model->getByUsername($username);

    if (!$user) {
      return $this->jsonResponse(false, 'Invalid username or password.');
    }

    if (!password_verify($password, $user->password)) {
      return $this->jsonResponse(false, 'Invalid username or password.');
    }

    $this->session->set_userdata([
      'user_id'    => $user->id,
      'username'   => $user->username,
      'first_name' => $user->first_name,
      'last_name'  => $user->last_name,
      'logged_in'  => TRUE
    ]);

    return $this->jsonResponse(true, 'Login successful.');
  }
}