<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_finder_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function search($keyword)
  {
    $search = "%{$keyword}%";
    $branchId = (int) $this->session->userdata('branch_id');

    return $this->db
      ->select("
        p.id,
        p.barcode,
        s.supplier_name,
        p.description,
        p.uom_id,
        u.uom,
        COALESCE(pu.selling_price, p.selling_price, p.srp, 0) AS srp,
        COALESCE(bi.qty_on_hand, 0) AS qty_on_hand
      ")
      ->from('m_products p')
      ->join(
          'm_product_uom pu',
          'pu.product_id = p.id AND pu.uom_id = p.uom_id AND pu.is_active = TRUE',
          'left',
          FALSE
      )
      ->join(
        't_branch_inventory bi',
        "bi.product_id = p.id AND bi.branch_id = {$branchId}",
        'left'
      )
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

  public function lookup($keyword)
  {
    $keyword = trim($keyword);
    $branchId = (int)$this->session->userdata('branch_id');

    return $this->db
        ->select("
            p.id,
            p.barcode,
            s.supplier_name,
            p.description,
            p.uom_id,
            u.uom,
            p.srp,
            COALESCE(bi.qty_on_hand, 0) qty_on_hand
        ")
        ->from('m_products p')
        ->join(
            't_branch_inventory bi',
            "bi.product_id = p.id AND bi.branch_id = {$branchId}",
            'left'
        )
        ->join(
            'm_suppliers s',
            's.id = p.supplier_id'
        )
        ->join(
            'm_uom u',
            'u.id = p.uom_id'
        )
        ->group_start()
            ->where('p.barcode', $keyword) /*** exact barcode */
            ->or_where('p.description ILIKE', "%{$keyword}%")
            ->or_where('s.supplier_name ILIKE', "%{$keyword}%")
        ->group_end()
        ->order_by("
            CASE
                WHEN p.barcode = ".$this->db->escape($keyword)." THEN 0
                ELSE 1
            END,
            p.description
        ", FALSE)
        ->limit(50)
        ->get()
        ->result();
  }
}