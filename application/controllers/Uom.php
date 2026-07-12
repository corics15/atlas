<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Uom extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Uom_model');
    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'UOM (Unit of Measurement)',
      [
        'id'   => 'btnNewUom',
        'icon' => 'fas fa-plus',
        'text' => 'New UOM',
      ]
    );

    $this->pageScript = 'uom';
    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['uoms'] = $this->Uom_model->getAll($keyword);
    $this->data['recordCount'] = count($this->data['uoms']);

    $this->data['tableContent'] = $this->load->view(
        'uom/table',
        $this->data,
        TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditUom',
          'text' => 'Edit',
          'icon' => 'fas fa-edit'
      ],
      'activate' => [
          'id' => 'btnActivateUom',
          'text' => 'Activate',
          'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
          'id' => 'btnDeactivateUom',
          'text' => 'Deactivate',
          'icon' => 'fas fa-ban'
      ],
      'refresh' => [
          'id' => 'btnRefreshUom',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync'
      ]
    ];

    $this->render('uom/index');
  }

  public function get($id)
  {
      $uom = $this->Uom_model->get($id);

      if (!$uom) {
          return $this->jsonResponse(
            false,
            'UOM not found.'
          );
      }

      return $this->jsonResponse(
          true,
          '',
          $uom
      );
  }

  public function save()
  {
    $postData = $this->input->post();
    $id = (int) $postData['id'];
    $uom = trim($postData['uom']);

    if ($this->Uom_model->uomExists($uom, $id)) {
      return $this->validationResponse([
        'uom' => 'This UOM already exists.'
      ]);
    }

    $this->form_validation->set_rules(
      'uom',
      'UOM',
      'required|trim',
      [
        'required' => 'The %s field is mandatory.'
      ]
    );

    if (!$this->form_validation->run()) {
      return $this->validationResponse();
    }

    $data = [
      'uom' => strtoupper(trim($postData['uom'])),
    ];

    if (empty($id)) {
      $data['entered_by'] = $this->session->userdata('user_id');
      $data['entered_on'] = date('Y-m-d H:i:s');
    } else {
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    $this->Uom_model->save($data, $id);

    return $this->jsonResponse(
      true,
      empty($id)
          ? 'UOM saved successfully.'
          : 'UOM updated successfully.'
    );
  }

  public function activate($id)
  {
    if (!$this->Uom_model->get($id)) {
      return $this->jsonResponse(
        false,
        'UOM not found.'
      );
    }

    $this->Uom_model->activate($id);

    return $this->jsonResponse(
      true,
      'UOM activated successfully.'
    );
  }

  public function deactivate($id)
  {
    if (!$this->Uom_model->get($id)) {
      return $this->jsonResponse(
        false,
        'UOM not found.'
      );
    }

    $this->Uom_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'UOM deactivated successfully.'
    );
  }
}