<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_model extends MY_Model
{
  public function get()
  {
    return $this->db
      ->where('is_active', TRUE)
      ->order_by('id', 'ASC')
      ->get('m_company')
      ->row();
  }

  public function update($id, $data)
  {
    $id = (int)$id;

    if ($id <= 0) {
      throw new Exception(
        'Invalid company record.'
      );
    }

    $exists = $this->db
      ->where('id', $id)
      ->where('is_active', TRUE)
      ->count_all_results('m_company');

    if ($exists == 0) {
      throw new Exception(
        'Company record not found.'
      );
    }

    $result = $this->db
      ->where('id', $id)
      ->update(
        'm_company',
        [
          'company_name'  => trim($data['company_name'] ?? ''),
          'address'       => trim($data['address'] ?? ''),
          'telephone_no'  => trim($data['telephone_no'] ?? ''),
          'mobile_no'     => trim($data['mobile_no'] ?? ''),
          'email_address' => trim($data['email_address'] ?? ''),
          'tin_no'        => trim($data['tin_no'] ?? ''),
          'logo'          => trim($data['logo'] ?? ''),
          'updated_by'    => $this->session->userdata('user_id'),
          'updated_on'    => date('Y-m-d H:i:s')
        ]
      );

    if (!$result) {
      throw new Exception(
        'Unable to update company information.'
      );
    }

    return TRUE;
  }
}