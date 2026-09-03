<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Outlet_types extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Outlet_type_model');

    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Outlet Types',
      [
        'id'   => 'btnNewOutletType',
        'icon' => 'fas fa-plus',
        'text' => 'New Outlet',
      ]
    );

    $this->pageScript = 'outlet_types';
    $filter = $this->decodeFilter($this->input->get('filter'));
    $filters = [
      'keyword' => trim($filter['keyword'] ?? $this->input->get('keyword')),
    ];
    $this->data = array_merge(
      $this->data,
      $filters
    );
    $this->data['outlet_types'] = $this->Outlet_type_model->getAll($filters['keyword']);
    $this->data['recordCount'] = count($this->data['outlet_types']);

    $this->data['tableContent'] = $this->load->view(
        'outlet_types/table',
        $this->data,
        TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditOutletType',
          'text' => 'Edit',
          'icon' => 'fas fa-edit'
      ],
      'activate' => [
          'id' => 'btnActivateOutletType',
          'text' => 'Activate',
          'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
          'id' => 'btnDeactivateOutletType',
          'text' => 'Deactivate',
          'icon' => 'fas fa-ban'
      ],
      'refresh' => [
          'id' => 'btnRefreshOutletType',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync',
          'url'  => 'outlet-types',
      ]
    ];
    $this->data['searchPlaceHolder'] = 'Search outlet types...';

    $this->render('outlet_types/index');
  }

  public function get($id)
  {
    $outletType = $this->Outlet_type_model->get($id);

    if (!$outletType) {
      return $this->jsonResponse(
        false,
        'Outlet Type not found.'
      );
    }

    return $this->jsonResponse(
      true,
      '',
      $outletType
    );
  }

  public function save()
  {
    $postData = $this->input->post();
    $id = (int) $postData['id'];

    $this->form_validation->set_rules(
      'outlet_type_name',
      'Description',
      'required|trim'
    );

    if (!$this->form_validation->run()) {
      return $this->validationResponse();
    }

    $data = [
      'outlet_type_name'  => strtoupper(trim($postData['outlet_type_name'])),
    ];

    if (empty($id)) {
      $data['entered_by'] = $this->session->userdata('user_id');
      $data['entered_on'] = date('Y-m-d H:i:s');
    } else {
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    $this->Outlet_type_model->save($data, $id);

    return $this->jsonResponse(
      true,
      empty($id)
          ? 'Outlet Type saved successfully.'
          : 'Outlet Type updated successfully.'
    );
  }

  public function activate($id)
  {
    if (!$this->Outlet_type_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Outlet Type not found.'
      );
    }

    $this->Outlet_type_model->activate($id);

    return $this->jsonResponse(
      true,
      'Outlet Type activated successfully.'
    );
  }

  public function deactivate($id)
  {
    if (!$this->Outlet_type_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Outlet Type not found.'
      );
    }

    $this->Outlet_type_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'Outlet Type deactivated successfully.'
    );
  }

}