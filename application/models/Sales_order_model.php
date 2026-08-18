<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_order_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Document_number_model');
    $this->load->model('Company_model');
  }

  public function get($id)
  {
    return $this->db
        ->select("
            so.*,
            c.customer_name,
            concat(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name,
            (
                SELECT COUNT(*)
                FROM t_sales_order_details sod
                LEFT JOIN (
                    SELECT
                        drd.sales_order_detail_id,
                        SUM(drd.qty) AS qty_delivered
                    FROM t_delivery_receipt_details drd
                    INNER JOIN t_delivery_receipts dr ON dr.id = drd.delivery_receipt_id
                    WHERE dr.status = 'POSTED'
                    GROUP BY drd.sales_order_detail_id
                ) dr
                    ON dr.sales_order_detail_id = sod.id
                WHERE sod.sales_order_id = so.id
                AND (
                  sod.qty - COALESCE(dr.qty_delivered, 0)
                ) > 0
            ) AS remaining_items
        ")
        ->from('t_sales_orders so')
        ->join(
            'm_customers c',
            'c.id = so.customer_id',
            'left'
        )
        ->join(
            'm_salesmen s',
            's.id = so.salesman_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = so.terms_id',
            'left'
        )
        ->where('so.id', $id)
        ->get()
        ->row();
  }

  public function getDetails($salesOrderId)
  {
    $branchId = (int) $this->session->userdata('branch_id');

    return $this->db->query("SELECT
                              sod.id,
                              sod.product_id,
                              sod.uom_id,
                              sod.conversion_factor,
                              p.uom_id AS base_uom_id,
                              p.barcode,
                              p.description,
                              COALESCE(bi.qty_on_hand, 0) AS qty_available,
                              sod.qty,
                              sod.unit_price,
                              sod.discount_type,
                              sod.discount_percent,
                              sod.discount_amount,
                              COALESCE(inv.qty_invoiced, 0) AS qty_fulfilled,
                              sod.qty - COALESCE(inv.qty_invoiced, 0) AS qty_remaining,
                              u.uom
                            FROM t_sales_order_details sod
                            INNER JOIN m_products p ON p.id = sod.product_id
                            LEFT JOIN t_branch_inventory bi ON bi.product_id = sod.product_id AND bi.branch_id = ?
                            LEFT JOIN m_uom u ON u.id = sod.uom_id
                            LEFT JOIN (
                                SELECT
                                    sid.sales_order_detail_id,
                                    SUM(sid.qty) qty_invoiced
                                FROM t_sales_invoice_details sid
                                INNER JOIN t_sales_invoices si ON si.id = sid.sales_invoice_id
                                WHERE si.status <> 'CANCELLED'
                                GROUP BY sid.sales_order_detail_id
                            ) inv
                            ON inv.sales_order_detail_id = sod.id
                            WHERE sod.sales_order_id = ?
                            ORDER BY sod.id",
                            [
                              $branchId,
                              $salesOrderId
                            ]
                          )->result();
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
          ->where("so.so_no ILIKE '%{$escaped}%'")
          ->or_where("c.customer_name ILIKE '%{$escaped}%'")
          ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'so.order_date >=',
        $filters['date_from']
      );
    } else {
      $this->db->where(
        'so.order_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'so.order_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'so.order_date <=',
        date('Y-m-d')
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'so.status',
        $filters['status']
      );
    }

    return $this->db
      ->select("
            so.*,
            c.customer_name,
            concat(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name,
            (
                SELECT COUNT(*)
                FROM t_sales_order_details sod
                LEFT JOIN (
                    SELECT
                        drd.sales_order_detail_id,
                        SUM(drd.qty) AS qty_delivered
                    FROM t_delivery_receipt_details drd
                    INNER JOIN t_delivery_receipts dr ON dr.id = drd.delivery_receipt_id
                    WHERE dr.status = 'POSTED'
                    GROUP BY drd.sales_order_detail_id
                ) dr
                    ON dr.sales_order_detail_id = sod.id
                WHERE sod.sales_order_id = so.id
                AND (
                  sod.qty - COALESCE(dr.qty_delivered, 0)
                ) > 0
            ) AS remaining_items
        ")
      ->from('t_sales_orders so')
      ->join(
          'm_customers c',
          'c.id = so.customer_id',
          'left'
      )
      ->join(
          'm_salesmen s',
          's.id = so.salesman_id',
          'left'
      )
      ->join(
          'm_terms t',
          't.id = so.terms_id',
          'left'
      )
      ->order_by(
          'so.id',
          'DESC'
      )
      ->get()
      ->result();
  }

  public function save($postData)
  {
    try {
      $this->db->trans_begin();

      /*** resolve VAT snapshot */
        $vatMode = NULL;
        $vatRate = 0;

        if (empty($postData->id)) {

          /*** new SO uses current Company settings */
          $company = $this->Company_model->get();

          if (!$company) {
            throw new Exception(
              'Company settings not found.'
            );
          }

          $vatMode = strtoupper(trim($company->vat_mode ?? 'INCLUSIVE'));

          $vatRate = (float)($company->vat_rate ?? 0);

        } else {

          /*** existing SO keeps its original VAT snapshot */
          $existingSalesOrder = $this->db
              ->select('vat_mode, vat_rate')
              ->where('id', $postData->id)
              ->get('t_sales_orders')
              ->row();

          if (!$existingSalesOrder) {
            throw new Exception(
              'Sales Order not found.'
            );
          }

          $vatMode = strtoupper(trim($existingSalesOrder->vat_mode ?? ''));
          $vatRate = (float)$existingSalesOrder->vat_rate;
        }

        if (
          !in_array(
            $vatMode,
            ['INCLUSIVE', 'EXCLUSIVE'],
            TRUE
          )
        ) {
          throw new Exception(
            'Invalid Sales Order VAT pricing mode.'
          );
        }

        if ($vatRate < 0 || $vatRate > 100) {
          throw new Exception(
            'Invalid Sales Order VAT rate.'
          );
        }
      /*** end resolve VAT snapshot */

      if (empty($postData->id)) {

        /*** insert header */
        $header = [
          'so_no' => $this->Document_number_model->generate('SO'),
          'order_date' => $postData->order_date,
          'customer_id' => (int) $postData->customer_id,
          'salesman_id' => (int) $postData->salesman_id,
          'terms_id' => $postData->terms_id <> '' ? (int) $postData->terms_id : NULL,
          'credit_limit' => $postData->credit_limit,
          'vat_mode' => $vatMode,
          'vat_rate' => $vatRate,
          'remarks' => trim($postData->remarks) <> '' ? strtoupper(trim($postData->remarks)) : NULL,
          'status' => 'OPEN',
          'entered_by' => $this->session->userdata('user_id'),
          'entered_on' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('t_sales_orders', $header);

        $salesOrderId = $this->db->insert_id();
        $soNo = $header['so_no'];

      } else {

        /*** don't allow modification of POSTED/CANCELLED docs */
        $current = $this->db
            ->select('status')
            ->where('id', $postData->id)
            ->get('t_sales_orders')
            ->row();

        if (!$current) {
          throw new Exception('Sales Order not found.');
        }

        if ($current->status !== 'OPEN') {
          throw new Exception(
            "Cannot modify a {$current->status} Sales Order."
          );
        }

        /*** update header */
        $this->db
            ->where('id', $postData->id)
            ->update(
              't_sales_orders',
              [
                'order_date' => $postData->order_date,
                'customer_id' => (int) $postData->customer_id,
                'salesman_id' => (int) $postData->salesman_id,
                'terms_id' => (int) $postData->terms_id,
                'credit_limit' => $postData->credit_limit,
                'remarks' => trim($postData->remarks) <> '' ? strtoupper(trim($postData->remarks)) : NULL,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_on' => date('Y-m-d H:i:s')
              ]
            );

        $salesOrderId = $postData->id;

        $soNo = $this->db
            ->select('so_no')
            ->where('id', $salesOrderId)
            ->get('t_sales_orders')
            ->row()
            ->so_no;

        /*** remove old details */
        $this->db->where('sales_order_id', $salesOrderId)->delete('t_sales_order_details');
      }

      if (empty($postData->details)) {
        throw new Exception('Please add at least one product.');
      }

      /*** validate detail UOM snapshots */
        foreach ($postData->details as $detail) {

          if (empty($detail->product_id)) {
            throw new Exception(
              'Sales Order contains an invalid product.'
            );
          }

          if (empty($detail->uom_id)) {
            throw new Exception(
              'Please select a UOM for all Sales Order items.'
            );
          }

          $conversionFactor = (float)($detail->conversion_factor ?? 0);

          if ($conversionFactor <= 0) {
            throw new Exception(
              'Invalid UOM conversion on Sales Order.'
            );
          }

          /*** get product base UOM */
          $product = $this->db
              ->select('uom_id')
              ->where('id', $detail->product_id)
              ->get('m_products')
              ->row();

          if (!$product) {
            throw new Exception(
              'Sales Order contains an invalid product.'
            );
          }

          /*** base UOM must always use conversion 1 */
          if (
            (int)$detail->uom_id === (int)$product->uom_id &&
            $conversionFactor != 1
          ) {
            throw new Exception(
              'Invalid conversion for product base UOM.'
            );
          }

          /*** validate quantity */
            $qty = (float)($detail->qty ?? 0);

            if ($qty <= 0) {
              throw new Exception(
                'Sales Order quantity must be greater than zero.'
              );
            }

            /*** validate selling price */
            $unitPrice = (float)($detail->unit_price ?? 0);

            if ($unitPrice < 0) {
              throw new Exception(
                'Sales Order unit price cannot be negative.'
              );
            }

            /*** validate discount */
            $discountType = strtoupper(
              trim($detail->discount_type ?? '')
            );

            $discountValue = (float)(
              $detail->discount_value ?? 0
            );

            $grossAmount = $qty * $unitPrice;

            if (
              $discountType !== '' &&
              !in_array(
                $discountType,
                ['PERCENT', 'AMOUNT'],
                TRUE
              )
            ) {
              throw new Exception(
                'Invalid Sales Order discount type.'
              );
            }

            if ($discountValue < 0) {
              throw new Exception(
                'Sales Order discount cannot be negative.'
              );
            }

            if (
              $discountType === 'PERCENT' &&
              $discountValue > 100
            ) {
              throw new Exception(
                'Sales Order percentage discount cannot exceed 100%.'
              );
            }

            if (
              $discountType === 'AMOUNT' &&
              $discountValue > $grossAmount
            ) {
              throw new Exception(
                'Sales Order discount cannot exceed the product row amount.'
              );
            }
          /*** end validate quantity */

        }
      /*** end validate */

      /*** INSERT DETAILS */
      foreach ($postData->details as $detail) {

        /*** calculate authoritative discount amount */
        $qty = (float)$detail->qty;
        $unitPrice = (float)$detail->unit_price;
        $discountType = strtoupper(trim($detail->discount_type ?? ''));
        $discountValue = (float)($detail->discount_value ?? 0);
        $grossAmount = $qty * $unitPrice;
        $discountAmount = 0;
        if ($discountType === 'PERCENT') {
          $discountAmount = $grossAmount * ($discountValue / 100);

        } elseif ($discountType === 'AMOUNT') {
          $discountAmount = $discountValue;
        }
        $discountAmount = round($discountAmount, 2);
        /*** endd calculate */

        $this->db->insert(
          't_sales_order_details',
          [
            'sales_order_id' => $salesOrderId,
            'product_id' => $detail->product_id,
            'uom_id' => $detail->uom_id,
            'conversion_factor'=> $detail->conversion_factor,
            'qty' => $detail->qty,
            'unit_price' => $detail->unit_price,
            'discount_type' => $discountType !== '' ? $discountType : NULL,
            'discount_percent' =>  $discountType === 'PERCENT' ? $discountValue : 0,
            'discount_amount' =>  $discountAmount,
            'remarks' => NULL,
          ]
        );
      }

      /*** calculate authoritative Sales Order totals */
        $totals = $this->db
            ->select("
              COALESCE(
                SUM((qty * unit_price) - discount_amount),
                0
              ) AS discounted_amount
            ", FALSE)
            ->where('sales_order_id', $salesOrderId)
            ->get('t_sales_order_details')
            ->row();

        $discountedAmount =          round((float)$totals->discounted_amount, 2);
        $subtotal = 0;
        $vatAmount = 0;
        $totalAmount = 0;
        $vatDecimal = $vatRate / 100;

        /*** VAT Inclusive */
        if ($vatMode === 'INCLUSIVE') {
          $totalAmount = $discountedAmount;
          if ($vatDecimal > 0) {
            $subtotal = round($totalAmount / (1 + $vatDecimal), 2);
            $vatAmount = round($totalAmount - $subtotal, 2);
          } else {
            $subtotal = $totalAmount;
            $vatAmount = 0;
          }
        }

        /*** VAT Exclusive */
        elseif ($vatMode === 'EXCLUSIVE') {

          $subtotal = $discountedAmount;
          $vatAmount = round($subtotal * $vatDecimal, 2);
          $totalAmount = round($subtotal + $vatAmount, 2);
        }

        $this->db
            ->where('id', $salesOrderId)
            ->update(
              't_sales_orders',
              [
                'subtotal'     => $subtotal,
                'vat_amount'   => $vatAmount,
                'total_amount' => $totalAmount
              ]
            );
      /*** end calculate totals */

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to save Sales Order.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => empty($postData->id)
          ? 'Sales Order saved.'
          : 'Sales Order updated.',
        'data' => [
          'sales_order_id' => $salesOrderId,
          'so_no' => $soNo
        ]
      ];

    } catch (Exception $ex) {

      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data' => [],
      ];
    }
  }

  public function post($ids)
  {
    try {
      if (empty($ids)) {
        throw new Exception(
          'Please select at least one Sales Order.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {

        $salesOrder = $this->db
            ->where('id', $id)
            ->get('t_sales_orders')
            ->row();

        if (!$salesOrder) {
          throw new Exception(
            'Sales Order not found.'
          );
        }

        if ($salesOrder->status !== 'OPEN') {
          throw new Exception(
            "Sales Order {$salesOrder->so_no} is already {$salesOrder->status}."
          );
        }

        /*** validate Sales Order detail UOM conversions */
        $details = $this->db
          ->select("
            sod.product_id,
            sod.uom_id,
            sod.conversion_factor,
            p.uom_id AS base_uom_id
          ")
          ->from('t_sales_order_details sod')
          ->join('m_products p', 'p.id = sod.product_id')
          ->where('sod.sales_order_id', $id)
          ->get()
          ->result();

        if (count($details) === 0) {
          throw new Exception(
            "Sales Order {$salesOrder->so_no} has no items."
          );
        }

        foreach ($details as $detail) {
          if (!$detail->uom_id) {
            throw new Exception(
              "Sales Order {$salesOrder->so_no} contains an item without a UOM."
            );
          }

          if (!$detail->conversion_factor || (float) $detail->conversion_factor <= 0) {
            throw new Exception(
              "Sales Order {$salesOrder->so_no} contains an invalid UOM conversion."
            );
          }

          /*** product base UOM must always have conversion 1 */
          if ((int) $detail->uom_id === (int) $detail->base_uom_id &&(float) $detail->conversion_factor !== 1.0) {
            throw new Exception(
              "Sales Order {$salesOrder->so_no} contains an invalid base UOM conversion."
            );
          }
        }
        /*** end validate */

        /*** update status */
        $this->db
            ->where('id', $id)
            ->update(
                't_sales_orders',
                [
                  'status'     => 'POSTED',
                  'posted_by'  => $this->session->userdata('user_id'),
                  'posted_on'  => date('Y-m-d H:i:s'),
                ]
            );
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to post Sales Order.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Order(s) posted successfully.',
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

  public function cancel(array $ids, $cancelReason = null)
  {
    try {

      if (empty($ids)) {
        throw new Exception('Please select at least one Sales Order.');
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
          $row = $this->db
              ->where('id', $id)
              ->get('t_sales_orders')
              ->row();

          if (!$row) {
            throw new Exception("Sales Order #{$id} not found.");
          }

          if ($row->status !== 'OPEN') {
            throw new Exception(
              "Sales Order {$row->so_no} is already {$row->status}."
            );
          }

          $this->db
              ->where('id', $id)
              ->update(
                  't_sales_orders',
                  [
                    'status'         => 'CANCELLED',
                    'cancelled_by'   => $this->session->userdata('user_id'),
                    'cancelled_on'   => date('Y-m-d H:i:s'),
                    'cancel_reason'  => $cancelReason <> '' ? strtoupper(trim($cancelReason)) : NULL,
                  ]
              );

          if (!$this->db->affected_rows()) {
            throw new Exception(
              "Unable to cancel {$row->so_no}."
            );
          }
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => count($ids) . ' Sales Order(s) cancelled successfully.',
        'data'    => $ids
      ];

    } catch (Exception $e) {
      $this->db->trans_rollback();

      return [
        'success' => false,
        'message' => $e->getMessage(),
        'data'    => null
      ];
    }
  }

}