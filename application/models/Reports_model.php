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

}