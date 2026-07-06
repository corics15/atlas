<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
  public function __construct()
  {
      parent::__construct();

      $this->load->model('User_model');
  }

  public function index()
  {
    $this->setPage(
      'Users',
      [
        'id'   => 'btnNew',
        'icon' => 'fas fa-plus',
        'text' => 'New User',
      ]
    );
    $this->pageScript = 'users';

    $this->data['users'] = $this->User_model->getAll();
    $this->render('users/index');
  }

  public function save()
  {
      $data = [
        'username'   => trim($this->input->post('username')),
        'first_name' => trim($this->input->post('first_name')),
        'last_name'  => trim($this->input->post('last_name')),
        'password'   => password_hash('password123', PASSWORD_DEFAULT)
      ];

      $this->User_model->insert($data);

      return $this->jsonResponse(
          true,
          'User saved successfully.'
      );
  }
}