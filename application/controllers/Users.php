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
        'id'   => 'btnNewUser',
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
    $id = (int) $this->input->post('id');

    $username = trim($this->input->post('username'));
    if ($this->User_model->usernameExists($username, $id)) {
      return $this->jsonResponse(
        false,
        'Username already exists.'
      );
    }

    $this->form_validation->set_rules(
      'username',
      'Username',
      'required|min_length[5]|trim',
      array(
        'required'   => 'The %s field is mandatory.',
        'min_length' => 'The %s must be at least 5 characters long.',
        'is_unique'  => 'This %s already exists.'
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
      return $this->validationResponse([
          'username',
          'first_name',
          'last_name'
      ]);
    }
    $data = [
      'username'   => trim($this->input->post('username')),
      'first_name' => trim($this->input->post('first_name')),
      'last_name'  => trim($this->input->post('last_name')),
      'password'   => password_hash('password123', PASSWORD_DEFAULT)
    ];

    if ($id > 0) {
      $this->User_model->update($id, $data);
      return $this->jsonResponse(
        true,
        'User updated successfully.'
      );
    }

    $this->User_model->insert($data);

    return $this->jsonResponse(
        true,
        'User saved successfully.'
    );
  }

  public function get($id)
  {
    $user = $this->User_model->get($id);

    if (!$user) {
        return $this->jsonResponse(
            false,
            'User not found.'
        );
    }

    return $this->jsonResponse(
        true,
        '',
        $user
    );
  }

  public function deactivate($id)
  {
    if (!$this->User_model->get($id)) {
      return $this->jsonResponse(
        false,
        'User not found.'
      );
    }

    $this->User_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'User deactivated successfully.'
    );
  }
}