<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Terms extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Term_model');
    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Terms',
      [
        'id'   => 'btnNewTerm',
        'icon' => 'fas fa-plus',
        'text' => 'New Terms',
      ]
    );

    $this->pageScript = 'terms';
    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['terms'] = $this->Term_model->getAll($keyword);
    $this->data['recordCount'] = count($this->data['terms']);

    $this->data['tableContent'] = $this->load->view(
        'terms/table',
        $this->data,
        TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditTerm',
          'text' => 'Edit',
          'icon' => 'fas fa-edit'
      ],
      'activate' => [
          'id' => 'btnActivateTerm',
          'text' => 'Activate',
          'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
          'id' => 'btnDeactivateTerm',
          'text' => 'Deactivate',
          'icon' => 'fas fa-ban'
      ],
      'refresh' => [
          'id' => 'btnRefreshTerm',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync'
      ]
    ];

    $this->render('terms/index');
  }

  public function get($id)
  {
      $terms = $this->Term_model->get($id);

      if (!$terms) {
          return $this->jsonResponse(
            false,
            'Item not found.'
          );
      }

      return $this->jsonResponse(
          true,
          '',
          $terms
      );
  }

  public function save()
  {
    $postData = $this->input->post();
    $id = (int) $postData['id'];
    $terms = trim($postData['terms_name']);

    if ($this->Term_model->termsExists($terms, $id)) {
      return $this->validationResponse([
        'terms' => 'This item already exists.'
      ]);
    }

    $this->form_validation->set_rules(
      'terms_name',
      'Terms',
      'required|trim',
      [
        'required' => 'The %s field is mandatory.'
      ]
    );

    if (!$this->form_validation->run()) {
      return $this->validationResponse();
    }

    $data = [
      'terms_name' => strtoupper(trim($postData['terms_name'])),
    ];

    if (empty($id)) {
      $data['entered_by'] = $this->session->userdata('user_id');
      $data['entered_on'] = date('Y-m-d H:i:s');
    } else {
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    $this->Term_model->save($data, $id);

    return $this->jsonResponse(
      true,
      empty($id)
          ? 'Term saved successfully.'
          : 'Term updated successfully.'
    );
  }

  public function activate($id)
  {
    if (!$this->Term_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Item not found.'
      );
    }

    $this->Term_model->activate($id);

    return $this->jsonResponse(
      true,
      'Item activated successfully.'
    );
  }

  public function deactivate($id)
  {
    if (!$this->Term_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Item not found.'
      );
    }

    $this->Term_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'Item deactivated successfully.'
    );
  }
}