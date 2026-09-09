<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Credit_memo_model extends CI_Model
{

  public function get($id)
  {
    return $this->db
      ->select("
        cm.*,
        c.customer_name,
        si.si_no,
        sr.sr_no
      ")
      ->from('t_credit_memos cm')
      ->join('m_customers c', 'c.id = cm.customer_id', 'left')
      ->join('t_sales_invoices si', 'si.id = cm.sales_invoice_id', 'left')
      ->join('t_sales_returns sr', 'sr.id = cm.sales_return_id', 'left')
      ->where(
        'cm.id',
        (int)$id
      )
      ->get()
      ->row();
  }

  public function getAllocations($creditMemoId)
  {
    return $this->db
        ->select('
          a.id,
          a.amount_applied,
          a.applied_on,
          si.id AS sales_invoice_id,
          si.si_no
        ')
        ->from('t_credit_memo_allocations a')
        ->join('t_sales_invoices si', 'si.id = a.sales_invoice_id', 'inner')
        ->where('a.credit_memo_id', (int)$creditMemoId)
        ->order_by('a.applied_on', 'ASC')
        ->get()
        ->result();
  }

}