<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank_account_model extends CI_Model
{

  public function get($id)
  {
    return $this->db->select("
            ba.*,
            b.branch_code,
            b.branch_name,
            coa.account_code,
            coa.account_name
        ")
        ->from('m_bank_accounts ba')
        ->join('m_branches b', 'b.id = ba.branch_id', 'left')
        ->join('m_chart_of_accounts coa', 'coa.id = ba.coa_account_id')
        ->where('ba.id', $id)
        ->get()
        ->row();
  }

  public function getDropdown($branchId = null)
  {
    $this->db->select("
            ba.id,
            ba.branch_id,
            ba.bank_name,
            ba.account_name,
            ba.account_no,
            ba.bank_branch,
            ba.coa_account_id,
            ba.is_check_enabled,
            coa.account_code,
            coa.account_name AS coa_account_name
        ")
        ->from('m_bank_accounts ba')
        ->join('m_chart_of_accounts coa', 'coa.id = ba.coa_account_id')
        ->where('ba.is_active', true)
        ->where('coa.is_active', true)
        ->where('coa.is_posting', true);

    if (!empty($branchId)) {
        $this->db->group_start()
            ->where('ba.branch_id', (int) $branchId)
            ->or_where('ba.branch_id IS NULL', null, false)
            ->group_end();
    }

    return $this->db->order_by('ba.bank_name', 'ASC')
        ->order_by('ba.account_name', 'ASC')
        ->get()
        ->result();
  }

  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str(trim($keyword));

      $this->db->group_start()
          ->where("ba.bank_name ILIKE '%{$escaped}%'")
          ->or_where("ba.account_name ILIKE '%{$escaped}%'")
          ->or_where("ba.account_no ILIKE '%{$escaped}%'")
          ->or_where("ba.bank_branch ILIKE '%{$escaped}%'")
          ->or_where("b.branch_name ILIKE '%{$escaped}%'")
          ->or_where("coa.account_code ILIKE '%{$escaped}%'")
          ->or_where("coa.account_name ILIKE '%{$escaped}%'")
          ->group_end();
    }

    return $this->db->select("
            ba.*,
            b.branch_code,
            b.branch_name,
            coa.account_code,
            coa.account_name AS coa_account_name
        ")
        ->from('m_bank_accounts ba')
        ->join('m_branches b', 'b.id = ba.branch_id', 'left')
        ->join('m_chart_of_accounts coa', 'coa.id = ba.coa_account_id')
        ->order_by('ba.bank_name', 'ASC')
        ->order_by('ba.account_name', 'ASC')
        ->get()
        ->result();
  }

  public function getCoaDropdown()
  {
    return $this->db->select("id, account_code, account_name")
        ->where('account_type', 'ASSET')
        ->where('is_posting', true)
        ->where('is_active', true)
        ->order_by('account_code', 'ASC')
        ->get('m_chart_of_accounts')
        ->result();
  }

  public function save($data, $id = null)
  {
    if (empty($id)) {
      return $this->db->insert('m_bank_accounts', $data);
    }

    return $this->db->where('id', $id)->update('m_bank_accounts', $data);
  }

  public function activate($id)
  {
    return $this->db->where('id', $id)->update('m_bank_accounts', ['is_active' => true]);
  }

  public function deactivate($id)
  {
    return $this->db->where('id', $id)->update('m_bank_accounts', ['is_active' => false]);
  }

}
