<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Supplier_model');
    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Suppliers',
      [
        'id'   => 'btnNewSupplier',
        'icon' => 'fas fa-plus',
        'text' => 'New Supplier',
      ]
    );

    $this->pageScript = 'suppliers';
    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['suppliers'] = $this->Supplier_model->getAll($keyword);
    $this->data['recordCount'] = count($this->data['suppliers']);

    $this->data['tableContent'] = $this->load->view(
        'suppliers/table',
        $this->data,
        TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditSupplier',
          'text' => 'Edit',
          'icon' => 'fas fa-edit'
      ],
      'activate' => [
          'id' => 'btnActivateSupplier',
          'text' => 'Activate',
          'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
          'id' => 'btnDeactivateSupplier',
          'text' => 'Deactivate',
          'icon' => 'fas fa-ban'
      ],
      'refresh' => [
          'id' => 'btnRefreshSupplier',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync'
      ]
    ];

    $this->render('suppliers/index');
  }

  public function get($id)
  {
      $supplier = $this->Supplier_model->get($id);

      if (!$supplier) {
          return $this->jsonResponse(
            false,
            'Supplier not found.'
          );
      }

      return $this->jsonResponse(
          true,
          '',
          $supplier
      );
  }

  public function save()
  {
    $postData = $this->input->post();
    $id = (int) $postData['id'];
    $supplierName = trim($postData['supplier_name']);

    if (false) :
    if ($this->Supplier_model->supplierNameExists($supplierName, $id)) {
      return $this->jsonResponse(
        false,
        'Supplier already exists.'
      );
    }
    endif;

    $this->form_validation->set_rules(
      'supplier_name',
      'Supplier Name',
      'required|trim',
      [
        'required' => 'The %s field is mandatory.'
      ]
    );

    if (!$this->form_validation->run()) {
      return $this->validationResponse();
    }

    $data = [
      'supplier_name' => strtoupper(trim($postData['supplier_name'])),
      'contact_person' => trim($postData['contact_person']) <> '' ? strtoupper(trim($postData['contact_person'])) : NULL,
      'mobile_no' => trim($postData['mobile_no']) <> '' ? trim($postData['mobile_no']) : NULL,
      'telephone_no' => trim($postData['telephone_no']) <> '' ? trim($postData['telephone_no']) : NULL,
      'email_address' => trim($postData['email_address']) <> '' ? trim($postData['email_address']) : NULL,
      'address' => trim($postData['address']) <> '' ? strtoupper(trim($postData['address'])) : NULL,
      'tin_no' => trim($postData['tin_no']) <> '' ? trim($postData['tin_no']) : NULL,
    ];

    if (empty($id)) {
      $data['entered_by'] = $this->session->userdata('user_id');
      $data['entered_on'] = date('Y-m-d H:i:s');
    } else {
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    $this->Supplier_model->save($data, $id);

    return $this->jsonResponse(
      true,
      empty($id)
          ? 'Supplier saved successfully.'
          : 'Supplier updated successfully.'
    );
  }

  public function activate($id)
  {
    if (!$this->Supplier_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Supplier not found.'
      );
    }

    $this->Supplier_model->activate($id);

    return $this->jsonResponse(
      true,
      'Supplier activated successfully.'
    );
  }

  public function deactivate($id)
  {
    if (!$this->Supplier_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Supplier not found.'
      );
    }

    $this->Supplier_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'Supplier deactivated successfully.'
    );
  }
}