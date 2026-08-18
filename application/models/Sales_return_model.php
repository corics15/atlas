<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_return_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Inventory_model');
    $this->load->model('Document_number_model');
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
          ->where("sr.sr_no ILIKE '%{$escaped}%'")
          ->or_where("inv.si_no ILIKE '%{$escaped}%'")
          ->or_where("c.customer_name ILIKE '%{$escaped}%'")
          ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'sr.return_date >=',
        $filters['date_from']
      );
    } else {
      $this->db->where(
        'sr.return_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'sr.return_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'sr.return_date <=',
        date('Y-m-d')
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'sr.status',
        $filters['status']
      );
    }
    return $this->db
        ->select("
            sr.*,
            inv.si_no,
            inv.id AS sales_invoice_id,
            c.customer_name,
            concat(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name
        ")
        ->from('t_sales_returns sr')
        ->join(
            't_sales_invoices inv',
            'inv.id = sr.sales_invoice_id',
            'left'
        )
        ->join(
            'm_customers c',
            'c.id = sr.customer_id',
            'left'
        )
        ->join(
            'm_salesmen s',
            's.id = sr.salesman_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = sr.terms_id',
            'left'
        )
        ->order_by(
            'inv.invoice_date',
            'DESC'
        )
        ->order_by(
            'sr.id',
            'DESC'
        )
        ->get()
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->select("
            sr.*,
            inv.si_no,
            c.customer_name,
            concat(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name,
            inv.remarks AS si_remarks,
            inv.status AS si_status
        ")
        ->from('t_sales_returns sr')
        ->join(
            't_sales_invoices inv',
            'inv.id = sr.sales_invoice_id'
        )
        ->join(
            'm_customers c',
            'c.id = sr.customer_id',
            'left'
        )
        ->join(
            'm_salesmen s',
            's.id = sr.salesman_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = sr.terms_id',
            'left'
        )
        ->where('sr.id', $id)
        ->get()
        ->row();
  }

  public function getDetails($id)
  {
    $branchId = (int) $this->session->userdata('branch_id');

    return $this->db
        ->select("
            sid.*,
            p.barcode,
            p.uom_id AS base_uom_id,
            p.description,
            COALESCE(bi.qty_on_hand, 0) AS qty_available,
            u.uom
        ")
        ->from('t_sales_return_details sid')
        ->join(
            'm_products p',
            'p.id = sid.product_id',
            'left'
        )
        ->join(
            't_branch_inventory bi',
            "bi.product_id = sid.product_id AND bi.branch_id = {$branchId}",
            'left'
        )
        ->join(
            'm_uom u',
            'u.id = sid.uom_id',
            'left'
        )
        ->where(
            'sid.sales_return_id',
            $id
        )
        ->order_by(
            'sid.id',
            'ASC'
        )
        ->get()
        ->result();
  }

  public function getSalesInvoice($salesInvoiceId)
  {
    return $this->db
        ->select("
            si.*,
            c.customer_name,
            concat(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name
        ")
        ->from('t_sales_invoices si')
        ->join(
            'm_customers c',
            'c.id = si.customer_id',
            'left'
        )
        ->join(
            'm_salesmen s',
            's.id = si.salesman_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = si.terms_id',
            'left'
        )
        ->where(
            'si.id',
            $salesInvoiceId
        )
        ->get()
        ->row();
  }

  public function getSalesInvoiceDetails($salesInvoiceId)
  {
      $branchId = (int)$this->session->userdata('branch_id');

      return $this->db
        ->query("SELECT
              sid.id AS sales_invoice_detail_id,
              sid.product_id,
              sid.uom_id,
              sid.unit_price,
              sid.discount_type,
              sid.discount_percent,
              sid.discount_amount AS si_discount_amount,
              sid.qty AS si_qty,
              sid.conversion_factor,
              p.uom_id AS base_uom_id,
              sid.qty - COALESCE(sr.qty_returned, 0) AS qty,
              p.barcode,
              p.description,
              COALESCE(bi.qty_on_hand, 0) AS qty_available,
              u.uom
          FROM t_sales_invoice_details sid
          INNER JOIN m_products p ON p.id = sid.product_id
          LEFT JOIN t_branch_inventory bi ON bi.product_id = sid.product_id AND bi.branch_id = ?
          LEFT JOIN m_uom u ON u.id = sid.uom_id
          LEFT JOIN (
              SELECT
                  srd.sales_invoice_detail_id,
                  SUM(srd.qty) qty_returned
              FROM t_sales_return_details srd
              INNER JOIN t_sales_returns sr ON sr.id = srd.sales_return_id
              WHERE sr.status <> 'CANCELLED'
              GROUP BY srd.sales_invoice_detail_id
          ) sr
          ON sr.sales_invoice_detail_id = sid.id
          WHERE sid.sales_invoice_id = ?
          AND (sid.qty - COALESCE(sr.qty_returned, 0)) > 0
          ORDER BY sid.id
      ",
      [
        $branchId,
        $salesInvoiceId
      ])
      ->result();
  }

  public function save($salesReturn)
  {
    try {
      $this->db->trans_begin();

      if (empty($salesReturn->id)) {
        $header = [
          'sr_no'            => $this->Document_number_model->generate('SR'),
          'return_date'      => $salesReturn->return_date,
          'sales_invoice_id' => $salesReturn->sales_invoice_id,
          'customer_id'    => $salesReturn->customer_id,
          'salesman_id'    => $salesReturn->salesman_id,
          'terms_id'       => $salesReturn->terms_id,
          'credit_limit'   => $salesReturn->credit_limit,
          'remarks'        => trim($salesReturn->remarks) <> '' ? strtoupper(trim($salesReturn->remarks)) : NULL,
          'status'         => 'OPEN',
          'entered_by'     => $this->session->userdata('user_id'),
          'entered_on'     => date('Y-m-d H:i:s')
        ];

        $this->db->insert('t_sales_returns', $header);

        $salesReturnId = $this->db->insert_id();
        $returnNo = $header['sr_no'];
      }

      else {

        $return = $this->db
            ->where('id', $salesReturn->id)
            ->get('t_sales_returns')
            ->row();

        if (!$return) {
          throw new Exception(
            'Sales Return not found.'
          );
        }

        if ($return->status != 'OPEN') {
          throw new Exception(
            "Cannot modify a {$return->status} Sales Return."
          );
        }

        $this->db
            ->where('id', $salesReturn->id)
            ->update(
                't_sales_returns',
                [
                  'return_date' => $salesReturn->return_date,
                  'remarks' => trim($salesReturn->remarks) <> '' ? strtoupper(trim($salesReturn->remarks)) : NULL,
                  'updated_by' => $this->session->userdata('user_id'),
                  'updated_on' => date('Y-m-d H:i:s')
                ]
            );

        $salesReturnId = $salesReturn->id;
        $returnNo = $return->sr_no;

        $this->db
            ->where(
              'sales_return_id',
              $salesReturnId
            )
            ->delete(
              't_sales_return_details'
            );
      }

      foreach ($salesReturn->details as $detail)
      {
        if ($detail->qty <= 0) {
          continue;
        }

        /*** validate source SI snapshot */
          $salesInvoiceDetail = $this->db
              ->select('
                product_id,
                uom_id,
                conversion_factor,
                qty,
                unit_price,
                discount_type,
                discount_percent,
                discount_amount
              ')
              ->where('id', $detail->sales_invoice_detail_id)
              ->get('t_sales_invoice_details')
              ->row();

          if (!$salesInvoiceDetail) {
            throw new Exception(
              'Sales Invoice detail not found.'
            );
          }

          if ((int)$detail->product_id !== (int)$salesInvoiceDetail->product_id) {
            throw new Exception(
              'Sales Return product does not match the Sales Invoice.'
            );
          }

          if ((int)$detail->uom_id !== (int)$salesInvoiceDetail->uom_id) {
            throw new Exception(
              'Sales Return UOM does not match the Sales Invoice.'
            );
          }

          if ((float)$detail->conversion_factor !== (float)$salesInvoiceDetail->conversion_factor) {
            throw new Exception(
              'Sales Return conversion does not match the Sales Invoice.'
            );
          }
        /*** end validdate */

        /*** calculate authoritative Sales Return commercial snapshot */
          $returnQty = (float)$detail->qty;
          $invoiceQty = (float)$salesInvoiceDetail->qty;
          $unitPrice = (float)$salesInvoiceDetail->unit_price;
          $discountType = strtoupper(trim($salesInvoiceDetail->discount_type ?? ''));
          $discountPercent = (float)$salesInvoiceDetail->discount_percent;
          $invoiceDiscountAmount = (float)$salesInvoiceDetail->discount_amount;
          $discountAmount = 0;

          /*** percentage discount */
          if ($discountType === 'PERCENT') {
            $grossAmount = $returnQty * $unitPrice;
            $discountAmount = round($grossAmount * ($discountPercent / 100), 2);
          }

          /*** fixed amount discount */
          elseif ($discountType === 'AMOUNT') {

            if ($invoiceQty <= 0) {
              throw new Exception(
                'Invalid Sales Invoice quantity for discount allocation.'
              );
            }

            /*** previous non-cancelled returns */
            $previous = $this->db
                ->select('
                  COALESCE(SUM(srd.qty), 0) AS qty_returned,
                  COALESCE(SUM(srd.discount_amount), 0) AS discount_returned
                ', FALSE)
                ->from('t_sales_return_details srd')
                ->join(
                  't_sales_returns sr',
                  'sr.id = srd.sales_return_id'
                )
                ->where(
                  'srd.sales_invoice_detail_id',
                  $detail->sales_invoice_detail_id
                )
                ->where('sr.status <>', 'CANCELLED')
                ->get()
                ->row();

            $previousQty = (float)$previous->qty_returned;

            $previousDiscount = (float)$previous->discount_returned;

            $remainingDiscount = max(0, $invoiceDiscountAmount - $previousDiscount);

            /*** final returned quantity receives exact remaining discount */
            if (($previousQty + $returnQty) >= $invoiceQty) {
              $discountAmount = round($remainingDiscount, 2);

            } else {
              $discountAmount = round($invoiceDiscountAmount * ($returnQty / $invoiceQty), 2);
              $discountAmount = min($discountAmount, $remainingDiscount);
            }
          }
        /*** end commercial snapshot */

        $this->db->insert(
          't_sales_return_details',
          [
            'sales_return_id'       => $salesReturnId,
            'sales_invoice_detail_id' => $detail->sales_invoice_detail_id,
            'product_id'            => $detail->product_id,
            'qty'                   => $detail->qty,
            'uom_id'                => $detail->uom_id,
            'conversion_factor'     => $detail->conversion_factor,
            'unit_price'            => $unitPrice,
            'discount_type'         => $discountType !== '' ? $discountType : NULL,
            'discount_percent'      => $discountType === 'PERCENT' ? $discountPercent : 0,
            'discount_amount'       => $discountAmount,
            'remarks'               => NULL,
          ]
        );
      }

      if ($this->db->trans_status() === FALSE)
      {
        throw new Exception('Unable to save Sales Return.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Return saved.',
        'data' => [
          'sales_return_id' => $salesReturnId,
          'sr_no' => $returnNo
        ]
      ];

    }
    catch (Exception $ex) {
      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data' => []
      ];
    }
  }

  public function post($ids)
  {
    try {
      if (empty($ids)) {
        throw new Exception(
          'Please select at least one Sales Return.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
        $return = $this->db
            ->where('id', $id)
            ->get('t_sales_returns')
            ->row();

        if (!$return) {
          throw new Exception(
            'Sales Return not found.'
          );
        }

        if ($return->status != 'OPEN') {
          throw new Exception(
            "Sales Return {$return->sr_no} is already {$return->status}."
          );
        }

        /*** validate source sales invoice */
        $salesInvoice = $this->db
            ->select('id, si_no, status')
            ->where('id', $return->sales_invoice_id)
            ->get('t_sales_invoices')
            ->row();

        if (!$salesInvoice) {
            throw new Exception(
              "Source Sales Invoice not found."
            );
        }

        if ($salesInvoice->status !== 'POSTED') {
            throw new Exception(
              "Sales Return {$return->sr_no} cannot be posted. " .
              "Sales Invoice {$salesInvoice->si_no} must be POSTED first."
            );
        }
        /*** end validate source sales invoice */

        /*** inventory update */
        $result = $this->Inventory_model->postSalesReturn($id);
        if (!$result['success']) {
          throw new Exception($result['message']);
        }
        /*** end inventory update */

        /*** mark as posted sales return */
        $this->db
            ->where('id', $id)
            ->update(
                't_sales_returns',
                [
                  'status'     => 'POSTED',
                  'posted_by'  => $this->session->userdata('user_id'),
                  'posted_on'  => date('Y-m-d H:i:s'),
                  'updated_by' => $this->session->userdata('user_id'),
                  'updated_on' => date('Y-m-d H:i:s')
                ]
            );
        /*** end  mark as posted sales return */
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to post Sales Return.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Return(s) posted successfully.',
        'data'    => []
      ];

    } catch (Exception $ex) {
        $this->db->trans_rollback();

        return [
          'success' => FALSE,
          'message' => $ex->getMessage(),
          'data'    => []
        ];
    }
  }

  public function cancel($ids, $cancelReason)
  {
    try {
      if (empty($ids)) {
        throw new Exception(
          'Please select at least one Sales Return.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
          $invoice = $this->db
              ->where('id', $id)
              ->get('t_sales_returns')
              ->row();

          if (!$invoice) {
            throw new Exception(
              'Sales Return not found.'
            );
          }

          if ($invoice->status != 'OPEN') {
            throw new Exception(
              "Only OPEN Sales Returns can be cancelled."
            );
          }

          $this->db
              ->where('id', $id)
              ->update(
                  't_sales_returns',
                  [
                      'status'          => 'CANCELLED',
                      'cancel_reason'   => trim($cancelReason),
                      'cancelled_by'    => $this->session->userdata('user_id'),
                      'cancelled_on'    => date('Y-m-d H:i:s'),
                      'updated_by'      => $this->session->userdata('user_id'),
                      'updated_on'      => date('Y-m-d H:i:s')
                  ]
              );
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to cancel Sales Return.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Return(s) cancelled successfully.',
        'data'    => []
      ];

    }
    catch (Exception $ex) {
        $this->db->trans_rollback();

        return [
          'success' => FALSE,
          'message' => $ex->getMessage(),
          'data'    => []
        ];
    }
  }

}