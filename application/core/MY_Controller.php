<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
  protected $data = [];
  protected $requiresAuth = true;
  protected $pageScript = '';

  public function __construct()
  {
    parent::__construct();
    $this->config->load('atlas');
    $this->data['app'] = $this->config->item('atlas');

    if ($this->requiresAuth) {
      $this->requireLogin();
    }
  }

  protected function render($view)
  {
    $this->data['content'] = $view;
    $this->data['pageScript'] = $this->pageScript;

    $this->load->view('layouts/master', $this->data);
  }

  protected function jsonResponse($success, $message = '', $data = [])
  {
    $response = [
      'success' => $success,
      'message' => $message,
      'data'    => $data
    ];

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
  }

  protected function requireLogin()
  {
    if (!$this->session->userdata('logged_in')) {
      redirect('auth');
      exit;
    }
  }

  protected function setPage($title, $button = [])
  {
    $this->data['pageTitle'] = $title;
    $this->data['pageButton'] = $button;
  }

  protected function validationResponse($customErrors = [])
  {
    $errors = [];

    foreach ($_POST as $field => $value) {
      $error = form_error($field);
      if (!empty($error)) {
        $errors[$field] = strip_tags($error);
      }
    }

    $errors = array_merge($errors, $customErrors);
    return $this->jsonResponse(
      false,
      'Please correct the highlighted fields.',
      [
        'errors' => $errors
      ]
    );
  }

  protected function getJsonRequest($key = null, $default = null)
  {
    static $request = null;

    if ($request === null) {
      $request = json_decode(file_get_contents('php://input'), true);

      if (!is_array($request)) {
        $request = $_POST;
      }
    }

    if ($key === null) {
      return $request;
    }

    return $request[$key] ?? $default;
  }

  protected function decodeFilter($encoded)
  {
    if (empty($encoded)) {
      return [];
    }

    $decoded = base64_decode(
      strtr($encoded, '-_', '+/'),
      TRUE
    );

    if ($decoded === FALSE) {
      return [];
    }

    $json = json_decode($decoded, TRUE);

    return is_array($json) ? $json : [];
  }

  protected function encodeFilter($data)
  {
    return rtrim(
      strtr(
        base64_encode(json_encode($data)),
        '+/',
        '-_'
      ),
      '='
    );
  }

  protected function encodeId($id)
  {
    return rtrim(
      strtr(
        base64_encode((string) $id),
        '+/',
        '-_'
      ),
      '='
    );
  }

  protected function decodeId($encoded)
  {
    if (empty($encoded)) {
      return NULL;
    }

    $decoded = base64_decode(
      strtr($encoded, '-_', '+/'),
      TRUE
    );

    if ($decoded === FALSE || !ctype_digit($decoded)) {
      return NULL;
    }

    return (int) $decoded;
  }

}