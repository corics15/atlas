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

}