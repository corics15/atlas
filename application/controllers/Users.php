<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('User_model');
    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Users', [
        'id'   => 'btnNew',
        'icon' => 'fas fa-plus',
        'text' => 'New User',
      ]
    );
    $this->pageScript = 'users';

    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['users'] = $this->User_model->getAll($keyword);

    $this->data['tableContent'] = $this->load->view(
      'users/table',
      $this->data,
      TRUE
    );

    $this->render('users/index');
  }

  public function save()
  {
    $this->form_validation->set_rules(
      'username',
      'Username',
      'required|min_length[5]|trim|is_unique[m_users.username]',
      array(
        'required'   => 'The %s field is mandatory.',
        'min_length' => 'The %s must be at least 5 characters long.',
        'is_unique'  => 'This %s has already been registered or already exists.'
      )
    );

    $this->form_validation->set_rules(
      'first_name',
      'First Name',
      'required|trim'
    );

    $this->form_validation->set_rules(
      'last_name',
      'Last Name',
      'required|trim'
    );

    if (!$this->form_validation->run()) {
      return $this->jsonResponse(
        false,
        validation_errors()
      );
    }
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