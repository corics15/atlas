<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller
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
      'Customers',
      [
        'id'   => 'btnNewCustomer',
        'icon' => 'fas fa-plus',
        'text' => 'New Customer',
      ]
    );

    $this->pageScript = 'customers';
    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['customers'] = $this->Customer_model->getAll($keyword);
    $this->data['recordCount'] = count($this->data['customers']);

    $this->data['tableContent'] = $this->load->view(
        'customers/table',
        $this->data,
        TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditCustomer',
          'text' => 'Edit',
          'icon' => 'fas fa-edit'
      ],
      'activate' => [
          'id' => 'btnActivateCustomer',
          'text' => 'Activate',
          'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
          'id' => 'btnDeactivateCustomer',
          'text' => 'Deactivate',
          'icon' => 'fas fa-ban'
      ],
      'refresh' => [
          'id' => 'btnRefreshCustomer',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync'
      ]
    ];

    $this->data['salesmen'] = $this->Salesman_model->getDropdown();

    $this->render('customers/index');
  }

  public function get($id)
  {
    $customer = $this->Customer_model->get($id);

    if (!$customer) {
      return $this->jsonResponse(
        false,
        'Customer not found.'
      );
    }

    return $this->jsonResponse(
      true,
      '',
      $customer
    );
  }

  public function save()
  {
    $postData = $this->input->post();
    $id = (int) $postData['id'];

    $this->form_validation->set_rules(
      'customer_name',
      'Customer Name',
      'required|trim'
    );

    $this->form_validation->set_rules(
      'salesman_id',
      'Salesman',
      'required'
    );

    $this->form_validation->set_rules(
      'terms',
      'Terms',
      'required|trim'
    );

    if (!$this->form_validation->run()) {
      return $this->validationResponse();
    }

    $data = [
      'customer_name'  => strtoupper(trim($postData['customer_name'])),
      'address'        => trim($postData['address']) <> '' ? strtoupper(trim($postData['address'])) : NULL,
      'contact_person' => trim($postData['contact_person']) <> '' ? strtoupper(trim($postData['contact_person'])) : NULL,
      'mobile_no'      => trim($postData['mobile_no']) <> '' ? strtoupper(trim($postData['mobile_no'])) : NULL,
      'telephone_no'   => trim($postData['telephone_no']) <> '' ? strtoupper(trim($postData['telephone_no'])) : NULL,
      'email_address'  => trim($postData['email_address']) <> '' ? trim($postData['email_address']) : NULL,
      'salesman_id'    => (int) $postData['salesman_id'],
      'terms'          => trim($postData['terms']),
      'credit_limit'   => (float) $postData['credit_limit'],
    ];

    if (empty($id)) {
      $data['entered_by'] = $this->session->userdata('user_id');
      $data['entered_on'] = date('Y-m-d H:i:s');
    } else {
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    $this->Customer_model->save($data, $id);

    return $this->jsonResponse(
      true,
      empty($id)
          ? 'Customer saved successfully.'
          : 'Customer updated successfully.'
    );
  }

  public function activate($id)
  {
    if (!$this->Customer_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Customer not found.'
      );
    }

    $this->Customer_model->activate($id);

    return $this->jsonResponse(
      true,
      'Customer activated successfully.'
    );
  }

  public function deactivate($id)
  {
    if (!$this->Customer_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Customer not found.'
      );
    }

    $this->Customer_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'Customer deactivated successfully.'
    );
  }

}