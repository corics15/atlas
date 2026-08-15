<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->requireAccess('ADMIN');

    $this->load->model('Company_model');
  }

  public function index()
  {
    $this->setPage('Company');
    $this->pageScript = 'company';
    $this->data['company'] = $this->Company_model->get();
    $this->render('company/index');
  }

  public function update()
  {
    $request = $this->input->post();

    try {

      $companyId = (int)($request['id'] ?? 0);

      if ($companyId <= 0) {
        throw new Exception(
          'Invalid company record.'
        );
      }

      $logoPath = trim($request['current_logo'] ?? '' );

      if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['logo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
          throw new Exception(
            'Unable to upload company logo.'
          );
        }

        /*** Maximum 2 MB */
        if ($file['size'] > (2 * 1024 * 1024)) {
          throw new Exception(
            'Company logo must not exceed 2 MB.'
          );
        }

        /*
        * Validate MIME type using PHP's
        * file information, not the filename.
        */
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedTypes = [
          'image/png'  => 'png',
          'image/jpeg' => 'jpg'
        ];

        if (!isset($allowedTypes[$mimeType])) {
          throw new Exception(
            'Company logo must be a PNG or JPG image.'
          );
        }

        $uploadPath = FCPATH . 'assets/images/company/';

        if (!is_dir($uploadPath)) {
          if (!mkdir($uploadPath, 0755, TRUE)) {
            throw new Exception(
              'Unable to create company logo directory.'
            );
          }
        }

        $extension = $allowedTypes[$mimeType];
        $fileName = 'company_logo.' . $extension;
        $destination = $uploadPath . $fileName;

        /*** Move uploaded file. */
        if (!move_uploaded_file(
          $file['tmp_name'],
          $destination
        )) {
          throw new Exception(
            'Unable to save company logo.'
          );
        }

        $logoPath = 'assets/images/company/' . $fileName;
      }

      $request['logo'] = $logoPath;

      $this->Company_model->update(
        $companyId,
        $request
      );

      return $this->jsonResponse(
        TRUE,
        'Company information updated.',
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