<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Document_numbering extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Document_number_model');
  }

  public function index()
  {
    $this->setPage('Document Numbering');

    $this->pageScript = 'document_numbering';

    $this->data['documentNumbering'] = $this->Document_number_model->getAll();

    $this->data['tableContent'] =
      $this->load->view(
        'document_numbering/table',
        $this->data,
        TRUE
      );

    $this->render('document_numbering/index');
  }

  public function update()
  {
    $request = $this->getJsonRequest();

    try {

      $this->Document_number_model->update(
        $request['id'] ?? 0,
        $request
      );

      return $this->jsonResponse(
        TRUE,
        'Document numbering updated.',
        []
      );

    }
    catch (Exception $ex) {

      return $this->jsonResponse(
        FALSE,
        $ex->getMessage(),
        []
      );
    }
  }
}