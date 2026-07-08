<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Product_model');
    $this->load->library('form_validation');
  }
  public function index()
  {
    $this->setPage(
      'Products',
      [
        'id'   => 'btnNewProduct',
        'icon' => 'fas fa-plus',
        'text' => 'New Product',
      ]
    );

    $this->pageScript = 'products';
    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['products'] = $this->Product_model->getAll($keyword);

    $this->data['tableContent'] = $this->load->view(
        'products/table',
        $this->data,
        TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditProduct',
          'text' => 'Edit',
          'icon' => 'fas fa-edit'
      ],
      'activate' => [
          'id' => 'btnActivateProduct',
          'text' => 'Activate',
          'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
          'id' => 'btnDeactivateProduct',
          'text' => 'Deactivate',
          'icon' => 'fas fa-ban'
      ],
      'refresh' => [
          'id' => 'btnRefreshProduct',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync'
      ]
    ];

    $this->render('products/index');
  }

  public function get($id)
  {
      $product = $this->Product_model->get($id);

      if (!$product) {
          return $this->jsonResponse(
            false,
            'Product not found.'
          );
      }

      return $this->jsonResponse(
          true,
          '',
          $product
      );
  }

  public function save()
  {
    $postData = $this->input->post();
    $id = (int) $postData['id'];
    // $supplierName = trim($postData['supplier_name']);

    // if ($this->Product_model->productCodeExists($productCode, $id)) {
    //   return $this->jsonResponse(
    //     false,
    //     'Product already exists.'
    //   );
    // }

    $this->form_validation->set_rules(
      'description',
      'Description',
      'required|trim',
      [
        'required' => 'The %s field is mandatory.'
      ]
    );

    if (!$this->form_validation->run()) {
      return $this->validationResponse();
    }

    $data = [
      // 'product_code' => trim($postData['product_code']),
      'supplier_id' => trim($postData['supplier_id']),
      'barcode' => trim($postData['barcode']),
      'description' => trim($postData['description']) <> '' ? strtoupper(trim($postData['description'])) : NULL,
      'uom' => trim($postData['uom']) <> '' ? trim($postData['uom']) : NULL,
      'cost' => $postData['cost'],
      'srp' => $postData['srp'],
    ];

    if (empty($id)) {
      $data['entered_by'] = $this->session->userdata('user_id');
      $data['entered_on'] = date('Y-m-d H:i:s');
    } else {
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    $this->Product_model->save($data, $id);

    return $this->jsonResponse(
      true,
      empty($id)
          ? 'Product saved successfully.'
          : 'Product updated successfully.'
    );
  }

  public function activate($id)
  {
    if (!$this->Product_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Product not found.'
      );
    }

    $this->Product_model->activate($id);

    return $this->jsonResponse(
      true,
      'Supplier activated successfully.'
    );
  }

  public function deactivate($id)
  {
    if (!$this->Product_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Product not found.'
      );
    }

    $this->Product_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'Product deactivated successfully.'
    );
  }

}