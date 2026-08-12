<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{

  public function getActiveCustomerCount()
  {
    return $this->db
      ->where('is_active', TRUE)
      ->count_all_results('m_customers');
  }

  public function getActiveProductCount()
  {
    return $this->db
      ->where('is_active', TRUE)
      ->count_all_results('m_products');
  }

  public function getInventorySummary()
  {
    $rows = $this->db
      ->get('v_inventory_inquiry')
      ->result();

    $totalQty = 0;
    $totalAmount = 0;

    foreach ($rows as $row) {
      $totalQty += (float) $row->qty_on_hand;
      $totalAmount += (float) $row->inventory_value;
    }

    return [
      'totalQty' => $totalQty,
      'totalAmount' => $totalAmount
    ];
  }

  public function getSalesToday()
  {
    $row = $this->db
      ->select("
        COALESCE(
          SUM(
            (d.qty * d.unit_price) - d.discount_amount
          ),
          0
        ) AS total_sales
      ", FALSE)
      ->from('t_sales_invoice_details d')
      ->join(
        't_sales_invoices h',
        'h.id = d.sales_invoice_id'
      )
      ->where('h.invoice_date', date('Y-m-d'))
      ->where('h.status', 'POSTED')
      ->get()
      ->row();

    return (float) $row->total_sales;
  }

  public function getSalesThisMonth()
  {
    $row = $this->db
      ->select("
        COALESCE(
          SUM(
            (d.qty * d.unit_price) - d.discount_amount
          ),
          0
        ) AS total_sales
      ", FALSE)
      ->from('t_sales_invoice_details d')
      ->join(
        't_sales_invoices h',
        'h.id = d.sales_invoice_id'
      )
      ->where(
        "h.invoice_date >= DATE_TRUNC('month', CURRENT_DATE)",
        NULL,
        FALSE
      )
      ->where(
        "h.invoice_date < DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'",
        NULL,
        FALSE
      )
      ->where('h.status', 'POSTED')
      ->get()
      ->row();

    return (float) $row->total_sales;
  }

  public function getOpenSalesOrderCount()
  {
    return $this->db
      ->where('status', 'OPEN')
      ->count_all_results('t_sales_orders');
  }

  public function getOpenPurchaseOrderCount()
  {
    return $this->db
      ->where('status', 'OPEN')
      ->count_all_results('t_purchase_orders');
  }

  public function getOutOfStockCount()
  {
    return $this->db
      ->where('qty_on_hand <=', 0)
      ->count_all_results('v_inventory_inquiry');
  }

  public function getRecentSales($limit = 5)
  {
    return $this->db
      ->select("
        si.id,
        si.si_no,
        si.invoice_date,
        c.customer_name,
        COALESCE(
          SUM(
            (sid.qty * sid.unit_price) - sid.discount_amount
          ),
          0
        ) AS total_amount
      ", FALSE)
      ->from('t_sales_invoices si')
      ->join(
        't_sales_invoice_details sid',
        'sid.sales_invoice_id = si.id',
        'left'
      )
      ->join(
        'm_customers c',
        'c.id = si.customer_id',
        'left'
      )
      ->where('si.status', 'POSTED')
      ->group_by([
        'si.id',
        'si.si_no',
        'si.invoice_date',
        'c.customer_name'
      ])
      ->order_by('si.invoice_date', 'DESC')
      ->order_by('si.id', 'DESC')
      ->limit($limit)
      ->get()
      ->result();
  }

  public function getSalesTrend($months = 6)
  {
    $startDate = date(
      'Y-m-01',
      strtotime("-" . ($months - 1) . " months")
    );

    $endDate = date('Y-m-t');

    return $this->db
      ->select("
        TO_CHAR(months.month_date, 'Mon YYYY') AS month,
        COALESCE(SUM(
          (sid.qty * sid.unit_price) - sid.discount_amount
        ), 0) AS total_amount
      ", FALSE)
      ->from("
        generate_series(
          DATE '{$startDate}',
          DATE '{$endDate}',
          INTERVAL '1 month'
        ) AS months(month_date)
      ", NULL, FALSE)
      ->join(
        't_sales_invoices si',
        "DATE_TRUNC('month', si.invoice_date) =
        months.month_date
        AND si.status = 'POSTED'",
        'left',
        FALSE
      )
      ->join(
        't_sales_invoice_details sid',
        'sid.sales_invoice_id = si.id',
        'left'
      )
      ->group_by('months.month_date')
      ->order_by('months.month_date', 'ASC')
      ->get()
      ->result();
  }

  public function getTopSellingProducts($limit = 5)
  {
    return $this->db
      ->select("
        sid.product_id,
        p.description,
        SUM(sid.qty) AS total_qty,
        SUM(
          (sid.qty * sid.unit_price) - sid.discount_amount
        ) AS total_amount
      ", FALSE)
      ->from('t_sales_invoice_details sid')
      ->join(
        't_sales_invoices si',
        'si.id = sid.sales_invoice_id',
        'inner'
      )
      ->join(
        'm_products p',
        'p.id = sid.product_id',
        'inner'
      )
      ->where('si.status', 'POSTED')
      ->group_by([
        'sid.product_id',
        'p.description'
      ])
      ->order_by('total_qty', 'DESC')
      ->limit($limit)
      ->get()
      ->result();
  }

}