<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_finder_model extends CI_Model
{
  public function search($keyword)
  {
    $search = "%{$keyword}%";

    return $this->db
      ->select("
        p.id,
        p.barcode,
        s.supplier_name,
        p.description,
        u.uom,
        p.srp
      ")
      ->from('m_products p')
      ->join(
        'm_suppliers s',
        's.id = p.supplier_id'
      )
      ->join(
        'm_uom u',
        'u.id = p.uom_id'
      )
      ->group_start()
        ->where("p.barcode ILIKE", $search)
        ->or_where("p.description ILIKE", $search)
        ->or_where("s.supplier_name ILIKE", $search)
      ->group_end()
      ->order_by('p.description')
      ->limit(50)
      ->get()
      ->result();
  }
}