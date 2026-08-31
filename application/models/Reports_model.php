<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports_model extends MY_Model
{

  public function getSalesPerSupplier($filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where(
        'si.invoice_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'si.invoice_date <=',
        $filters['date_to']
      );
    }

    if (!empty($filters['branch_id'])) {
      $this->db->where(
        'dr.branch_id',
        (int)$filters['branch_id']
      );
    }

    if (!empty($filters['supplier_id'])) {
      $this->db->where(
        's.id',
        (int)$filters['supplier_id']
      );
    }

    return $this->db
        ->select("
          s.id AS supplier_id,
          s.supplier_name,
          COUNT(DISTINCT si.id) AS invoice_count,
          COALESCE(SUM(sid.qty * sid.unit_price), 0) AS gross_sales,
          COALESCE(SUM(COALESCE(sid.discount_amount, 0)), 0) AS discount_amount,
          COALESCE(SUM((sid.qty * sid.unit_price) - COALESCE(sid.discount_amount, 0)), 0) AS net_sales
        ", FALSE)
        ->from('t_sales_invoice_details sid')
        ->join(
          't_sales_invoices si',
          'si.id = sid.sales_invoice_id'
        )
        ->join(
          't_delivery_receipts dr',
          'dr.id = si.delivery_receipt_id'
        )
        ->join(
          'm_products p',
          'p.id = sid.product_id'
        )
        ->join(
          'm_suppliers s',
          's.id = p.supplier_id'
        )
        ->where(
          'si.status',
          'POSTED'
        )
        ->group_by([
          's.id',
          's.supplier_name'
        ])
        ->order_by(
          'net_sales',
          'DESC'
        )
        ->order_by(
          's.supplier_name',
          'ASC'
        )
        ->get()
        ->result();
  }

  public function getSalesPerSupplierProducts($filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where(
        'si.invoice_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'si.invoice_date <=',
        $filters['date_to']
      );
    }

    if (!empty($filters['branch_id'])) {
      $this->db->where(
        'dr.branch_id',
        (int)$filters['branch_id']
      );
    }

    if (!empty($filters['supplier_id'])) {
      $this->db->where(
        's.id',
        (int)$filters['supplier_id']
      );
    }

    return $this->db
        ->select("
          s.id AS supplier_id,
          s.supplier_name,
          p.id AS product_id,
          p.description AS product_description,
          u.id AS uom_id,
          u.uom,
          COUNT(DISTINCT si.id) AS invoice_count,
          COALESCE(SUM(sid.qty), 0) AS qty_sold,
          COALESCE(SUM(sid.qty * sid.unit_price), 0) AS gross_sales,
          COALESCE(SUM(COALESCE(sid.discount_amount, 0)), 0) AS discount_amount,
          COALESCE(SUM((sid.qty * sid.unit_price) - COALESCE(sid.discount_amount, 0)), 0) AS net_sales
        ", FALSE)
        ->from('t_sales_invoice_details sid')
        ->join(
          't_sales_invoices si',
          'si.id = sid.sales_invoice_id'
        )
        ->join(
          't_delivery_receipts dr',
          'dr.id = si.delivery_receipt_id'
        )
        ->join(
          'm_products p',
          'p.id = sid.product_id'
        )
        ->join(
          'm_suppliers s',
          's.id = p.supplier_id'
        )
        ->join(
          'm_uom u',
          'u.id = sid.uom_id'
        )
        ->where(
          'si.status',
          'POSTED'
        )
        ->group_by([
          's.id',
          's.supplier_name',
          'p.id',
          'p.description',
          'u.id',
          'u.uom'
        ])
        ->order_by(
          'p.description',
          'ASC'
        )
        ->order_by(
          'u.uom',
          'ASC'
        )
        ->get()
        ->result();
  }

  public function getSalesPerSupplierSalesman($filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where(
        'si.invoice_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'si.invoice_date <=',
        $filters['date_to']
      );
    }

    if (!empty($filters['branch_id'])) {
      $this->db->where(
        'dr.branch_id',
        (int)$filters['branch_id']
      );
    }

    if (!empty($filters['salesman_id'])) {
      $this->db->where(
        'm.id',
        (int)$filters['salesman_id']
      );
    }

    return $this->db
        ->select("
          s.id AS supplier_id,
          s.supplier_name,
          m.id AS salesman_id,
          CONCAT(m.first_name, ' ', m.last_name) AS salesman_name,
          COUNT(DISTINCT si.id) AS invoice_count,
          COALESCE(
            SUM(sid.qty * sid.unit_price),
            0
          ) AS gross_sales,
          COALESCE(
            SUM(
              COALESCE(sid.discount_amount, 0)
            ),
            0
          ) AS discount_amount,
          COALESCE(
            SUM(
              (sid.qty * sid.unit_price)
              - COALESCE(sid.discount_amount, 0)
            ),
            0
          ) AS net_sales
        ", FALSE)
        ->from('t_sales_invoice_details sid')
        ->join(
          't_sales_invoices si',
          'si.id = sid.sales_invoice_id'
        )
        ->join(
          't_delivery_receipts dr',
          'dr.id = si.delivery_receipt_id'
        )
        ->join(
          'm_products p',
          'p.id = sid.product_id'
        )
        ->join(
          'm_suppliers s',
          's.id = p.supplier_id'
        )
        ->join(
          'm_salesmen m',
          'm.id = si.salesman_id'
        )
        ->where(
          'si.status',
          'POSTED'
        )
        ->group_by([
          's.id',
          's.supplier_name',
          'm.id',
          'm.first_name',
          'm.last_name'
        ])
        ->order_by(
          'net_sales',
          'DESC'
        )
        ->order_by(
          's.supplier_name',
          'ASC'
        )
        ->order_by(
          'salesman_name',
          'ASC'
        )
        ->get()
        ->result();
  }

  public function getSalesPerCustomer($filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where(
        'si.invoice_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'si.invoice_date <=',
        $filters['date_to']
      );
    }

    if (!empty($filters['branch_id'])) {
      $this->db->where(
        'dr.branch_id',
        (int)$filters['branch_id']
      );
    }

    if (!empty($filters['customer_id'])) {
      $this->db->where(
        'c.id',
        (int)$filters['customer_id']
      );
    }

    return $this->db
        ->select("
          c.id AS customer_id,
          c.customer_name,
          COUNT(DISTINCT si.id) AS invoice_count,
          COALESCE(SUM(sid.qty * sid.unit_price), 0) AS gross_sales,
          COALESCE(SUM(COALESCE(sid.discount_amount, 0)), 0) AS discount_amount,
          COALESCE(SUM((sid.qty * sid.unit_price) - COALESCE(sid.discount_amount, 0)), 0) AS net_sales
        ", FALSE)
        ->from('t_sales_invoice_details sid')
        ->join(
          't_sales_invoices si',
          'si.id = sid.sales_invoice_id'
        )
        ->join(
          't_delivery_receipts dr',
          'dr.id = si.delivery_receipt_id'
        )
        ->join(
          'm_customers c',
          'c.id = si.customer_id'
        )
        ->where(
          'si.status',
          'POSTED'
        )
        ->group_by([
          'c.id',
          'c.customer_name'
        ])
        ->order_by(
          'net_sales',
          'DESC'
        )
        ->order_by(
          'c.customer_name',
          'ASC'
        )
        ->get()
        ->result();
  }

  public function getSalesPerCustomerProducts($filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where(
        'si.invoice_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'si.invoice_date <=',
        $filters['date_to']
      );
    }

    if (!empty($filters['branch_id'])) {
      $this->db->where(
        'dr.branch_id',
        (int)$filters['branch_id']
      );
    }

    if (!empty($filters['customer_id'])) {
      $this->db->where(
        'c.id',
        (int)$filters['customer_id']
      );
    }

    return $this->db
        ->select("
          c.id AS customer_id,
          c.customer_name,
          p.id AS product_id,
          p.description AS product_description,
          u.id AS uom_id,
          u.uom,
          COUNT(DISTINCT si.id) AS invoice_count,
          COALESCE(SUM(sid.qty), 0) AS qty_sold,
          COALESCE(SUM(sid.qty * sid.unit_price), 0) AS gross_sales,
          COALESCE(SUM(COALESCE(sid.discount_amount, 0)), 0) AS discount_amount,
          COALESCE(SUM((sid.qty * sid.unit_price) - COALESCE(sid.discount_amount, 0)), 0) AS net_sales
        ", FALSE)
        ->from('t_sales_invoice_details sid')
        ->join(
          't_sales_invoices si',
          'si.id = sid.sales_invoice_id'
        )
        ->join(
          't_delivery_receipts dr',
          'dr.id = si.delivery_receipt_id'
        )
        ->join(
          'm_customers c',
          'c.id = si.customer_id'
        )
        ->join(
          'm_products p',
          'p.id = sid.product_id'
        )
        ->join(
          'm_uom u',
          'u.id = sid.uom_id'
        )
        ->where(
          'si.status',
          'POSTED'
        )
        ->group_by([
          'c.id',
          'c.customer_name',
          'p.id',
          'p.description',
          'u.id',
          'u.uom'
        ])
        ->order_by(
          'p.description',
          'ASC'
        )
        ->order_by(
          'u.uom',
          'ASC'
        )
        ->get()
        ->result();
  }

  public function getSalesPerCustomerSalesman($filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where(
        'si.invoice_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'si.invoice_date <=',
        $filters['date_to']
      );
    }

    if (!empty($filters['branch_id'])) {
      $this->db->where(
        'dr.branch_id',
        (int)$filters['branch_id']
      );
    }

    if (!empty($filters['salesman_id'])) {
      $this->db->where(
        'm.id',
        (int)$filters['salesman_id']
      );
    }

    return $this->db
        ->select("
          c.id AS customer_id,
          c.customer_name,
          m.id AS salesman_id,
          CONCAT(m.first_name, ' ', m.last_name) AS salesman_name,
          COUNT(DISTINCT si.id) AS invoice_count,
          COALESCE(SUM(sid.qty * sid.unit_price), 0) AS gross_sales,
          COALESCE(SUM(COALESCE(sid.discount_amount, 0)), 0) AS discount_amount,
          COALESCE(SUM((sid.qty * sid.unit_price) - COALESCE(sid.discount_amount, 0)), 0) AS net_sales
        ", FALSE)
        ->from('t_sales_invoice_details sid')
        ->join(
          't_sales_invoices si',
          'si.id = sid.sales_invoice_id'
        )
        ->join(
          't_delivery_receipts dr',
          'dr.id = si.delivery_receipt_id'
        )
        ->join(
          'm_customers c',
          'c.id = si.customer_id'
        )
        ->join(
          'm_salesmen m',
          'm.id = si.salesman_id'
        )
        ->where(
          'si.status',
          'POSTED'
        )
        ->group_by([
          'c.id',
          'c.customer_name',
          'm.id',
          'm.first_name',
          'm.last_name'
        ])
        ->order_by(
          'net_sales',
          'DESC'
        )
        ->order_by(
          'c.customer_name',
          'ASC'
        )
        ->order_by(
          'salesman_name',
          'ASC'
        )
        ->get()
        ->result();
  }

  public function getSalesDetail($filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where('dr.delivery_date >=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $this->db->where('dr.delivery_date <=', $filters['date_to']);
    }

    if (!empty($filters['supplier_id'])) {
      $this->db->where('sup.id', (int)$filters['supplier_id']);
    }

    if (!empty($filters['salesman_id'])) {
      $this->db->where('s.id', (int)$filters['salesman_id']);
    }

    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("dr.dr_no ILIKE '%{$escaped}%'")
        ->or_where("c.customer_name ILIKE '%{$escaped}%'")
        ->or_where("pu.barcode ILIKE '%{$escaped}%'")
        ->or_where("p.barcode ILIKE '%{$escaped}%'")
        ->or_where("p.description ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->select("
          dr.delivery_date AS dr_date,
          dr.dr_no,
          sup.supplier_name,
          COALESCE(pu.barcode, p.barcode) AS barcode,
          p.description,
          u.uom,
          drd.qty,
          sod.unit_price,
          (drd.qty * sod.unit_price) AS gross_amount,
          sod.discount_percent,
          CASE WHEN sod.qty > 0 THEN drd.qty * (COALESCE(sod.discount_amount, 0) / sod.qty) ELSE 0 END AS discount_amount,
          CASE WHEN sod.qty > 0 THEN drd.qty * (((sod.qty * sod.unit_price) - COALESCE(sod.discount_amount, 0)) / sod.qty) ELSE 0 END AS net_amount,
          ot.outlet_type_name,
          s.code AS salesman_code,
          CONCAT(s.first_name, ' ', s.last_name) AS salesman_name,
          c.customer_name,
          c.address
        ", FALSE)
        ->from('t_delivery_receipt_details drd')
        ->join('t_delivery_receipts dr', 'dr.id = drd.delivery_receipt_id')
        ->join('t_sales_order_details sod', 'sod.id = drd.sales_order_detail_id')
        ->join('t_sales_orders so', 'so.id = sod.sales_order_id')
        ->join('m_customers c', 'c.id = dr.customer_id', 'left')
        ->join('m_salesmen s', 's.id = so.salesman_id', 'left')
        ->join('m_outlet_types ot', 'ot.id = c.outlet_type_id', 'left')
        ->join('m_products p', 'p.id = drd.product_id')
        ->join('m_suppliers sup', 'sup.id = p.supplier_id', 'left')
        ->join('m_uom u', 'u.id = drd.uom_id', 'left')
        ->join('m_product_uom pu', 'pu.product_id = drd.product_id AND pu.uom_id = drd.uom_id', 'left')
        ->where('dr.status', 'POSTED')
        ->order_by('dr.delivery_date', 'ASC')
        ->order_by('dr.dr_no', 'ASC')
        ->order_by('drd.id', 'ASC')
        ->get()
        ->result();
  }

  public function getSalesOrderDetail($filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where('so.order_date >=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $this->db->where('so.order_date <=', $filters['date_to']);
    }

    if (!empty($filters['customer_id'])) {
      $this->db->where('c.id', (int)$filters['customer_id']);
    }

    if (!empty($filters['salesman_id'])) {
      $this->db->where('so.salesman_id', (int)$filters['salesman_id']);
    }

    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("so.so_no ILIKE '%{$escaped}%'")
        ->or_where("c.customer_name ILIKE '%{$escaped}%'")
        ->or_where("sup.supplier_name ILIKE '%{$escaped}%'")
        ->or_where("p.description ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->select("
          so.order_date,
          so.so_no,
          sup.supplier_name,
          p.description,
          p.pkg AS packing,
          u.uom,
          sod.qty,
          c.customer_name,
          CONCAT(s.first_name, ' ', s.last_name) AS salesman_name,
          t.terms_name,
          so.remarks,
          (SELECT COUNT(*) FROM t_sales_order_details sod_count WHERE sod_count.sales_order_id = so.id) AS item_count,
          so.total_amount,
          (
            SELECT COUNT(*)
            FROM t_sales_order_details sod_remaining
            LEFT JOIN (
              SELECT
                drd.sales_order_detail_id,
                SUM(drd.qty) AS qty_delivered
              FROM t_delivery_receipt_details drd
              INNER JOIN t_delivery_receipts dr
                ON dr.id = drd.delivery_receipt_id
              WHERE dr.status = 'POSTED'
              GROUP BY drd.sales_order_detail_id
            ) dr ON dr.sales_order_detail_id = sod_remaining.id
            WHERE sod_remaining.sales_order_id = so.id
              AND (sod_remaining.qty - COALESCE(dr.qty_delivered, 0)) > 0
          ) AS remaining,
          so.status
        ", FALSE)
        ->from('t_sales_order_details sod')
        ->join('t_sales_orders so', 'so.id = sod.sales_order_id')
        ->join('m_products p', 'p.id = sod.product_id')
        ->join('m_suppliers sup', 'sup.id = p.supplier_id', 'left')
        ->join('m_uom u', 'u.id = sod.uom_id', 'left')
        ->join('m_customers c', 'c.id = so.customer_id', 'left')
        ->join('m_salesmen s', 's.id = so.salesman_id', 'left')
        ->join('m_terms t', 't.id = so.terms_id', 'left')
        ->where('so.status <>', 'CANCELLED')
        ->order_by('so.order_date', 'DESC')
        ->order_by('so.so_no',)
        // ->order_by('sod.id', 'ASC')
        ->get()
        ->result();
  }

}