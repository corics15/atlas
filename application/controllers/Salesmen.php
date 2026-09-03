<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Salesmen extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Salesman_model');

    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Salesmen',
      [
        'id'   => 'btnNewSalesman',
        'icon' => 'fas fa-plus',
        'text' => 'New Salesman'
      ]
    );

    $this->pageScript = 'salesmen';
    $filter = $this->decodeFilter($this->input->get('filter'));
    $filters = [
      'keyword' => trim($filter['keyword'] ?? $this->input->get('keyword')),
    ];
    $this->data = array_merge(
      $this->data,
      $filters
    );
    $this->data['salesmen'] = $this->Salesman_model->getAll($filters['keyword']);
    $this->data['recordCount'] = count($this->data['salesmen']);
    $this->data['tableContent'] = $this->load->view(
      'salesmen/table',
      $this->data,
      TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditSalesman',
          'text' => 'Edit',
          'icon' => 'fas fa-edit'
      ],
      'activate' => [
          'id' => 'btnActivateSalesman',
          'text' => 'Activate',
          'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
          'id' => 'btnDeactivateSalesman',
          'text' => 'Deactivate',
          'icon' => 'fas fa-ban'
      ],
      'refresh' => [
          'id' => 'btnRefreshSalesmen',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync',
          'url'  => 'salesmen',
      ]
    ];
    $this->data['searchPlaceHolder'] = 'Search salesmen...';

    $this->render('salesmen/index');
  }

  public function save()
  {
    $postData = $this->input->post();

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
      return $this->validationResponse();
    }

    $id = $postData['id'];
    $code = trim($postData['salesman_code']);

    if ($this->Salesman_model->codeExists($code, $id)) {
      return $this->validationResponse([
        'salesman_code' => 'This code already exists.'
      ]);
    }

    $data = [
      'first_name' => strtoupper(trim($postData['first_name'])),
      'last_name' => strtoupper(trim($postData['last_name'])),
      'mobile_no' => trim($postData['contact_no']) <> '' ? trim($postData['contact_no']) : NULL,
    ];

    if (empty($id)) {
      /*** get next salesman code */
      $newCode = $this->Salesman_model->getNextSalesmanCode();
      $data['code'] = $newCode;
      $data['entered_by'] = $this->session->userdata('user_id');
      $data['entered_on'] = date('Y-m-d H:i:s');
    } else {
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    $this->Salesman_model->save($data, $id);

    return $this->jsonResponse(
      true,
      'Salesman saved successfully.'
    );
  }

  public function get($id)
  {
    $salesman = $this->Salesman_model->get($id);

    if (!$salesman) {
      return $this->jsonResponse(
        false,
        'Salesman not found.'
      );
    }

    return $this->jsonResponse(
      true,
      '',
      $salesman
    );
  }

  public function activate($id)
  {
    if (!$this->Salesman_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Salesman not found.'
      );
    }

    $this->Salesman_model->activate($id);

    return $this->jsonResponse(
      true,
      'Salesman activated successfully.'
    );
  }

  public function deactivate($id)
  {
    if (!$this->Salesman_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Salesman not found.'
      );
    }

    $this->Salesman_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'Salesman deactivated successfully.'
    );
  }

  public function getDropdown()
  {
    // $this->db->select('id, customer_name, salesman_id');
    $this->db->where('is_active', TRUE);
    $this->db->order_by('last_name');

    return $this->db->get('m_salesmen')->result();
  }
}