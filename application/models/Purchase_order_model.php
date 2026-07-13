<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_order_model extends CI_Model
{
  public function save($po)
  {
    $this->validate($po);

    $this->db->trans_begin();

    try {
      $header = $this->insertHeader($po);
      $this->insertDetails(
        $header['id'],
        $po->details
      );

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => 'Purchase Order saved successfully.',
        'data' => [
          'purchase_order_id' => $header['id'],
          'po_no' => $header['po_no']
        ]
      ];


    } catch (Exception $e) {
        $this->db->trans_rollback();
        return [
          'success' => false,
          'message' => $e->getMessage(),
          'data' => null,
        ];
    }
  }

  public function update($po)
  {
    $this->validate($po);

    /*** verify before updating anything */
    $status = $this->db
                  ->select('status')
                  ->from('t_purchase_orders')
                  ->where('id', $po->id)
                  ->get()
                  ->row()
                  ->status;

    if ($status !== 'OPEN') {
      return [
        'success' => false,
        'message' => 'Only OPEN Purchase Orders can be modified.',
        'data' => null
      ];
    }

    $this->db->trans_begin();

    try {
      $this->updateHeader($po);
      $this->replaceDetails(
        $po->id,
        $po->details
      );
      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => 'Purchase Order updated successfully.',
        'data' => [
          'purchase_order_id' => $po->id,
          'po_no' => $po->po_no
        ]
      ];

    } catch (Exception $e) {
      $this->db->trans_rollback();

      return [
          'success' => false,
          'message' => $e->getMessage(),
          'data' => null
      ];
    }
  }

  public function generatePONumber()
  {
    // Temporary implementation
    return 'PO-' . date('YmdHis');
  }

  public function get($id)
  {
    $header = $this->db
        ->select("
          p.*,
          s.supplier_name,
          t.terms_name
        ")
        ->from('t_purchase_orders p')
        ->join('m_suppliers s', 's.id = p.supplier_id')
        ->join('m_terms t', 't.id = s.terms_id', 'left')
        ->where('p.id', $id)
        ->get()
        ->row();

    if (!$header) {
      throw new Exception('Purchase Order not found.');
    }

    $details = $this->db
        ->select("
            d.product_id,
            p.barcode,
            s.supplier_name,
            p.description,
            u.uom,
            d.qty,
            d.price,
            d.discount
        ")
        ->from('t_purchase_order_details d')
        ->join('m_products p', 'p.id = d.product_id')
        ->join('m_suppliers s', 's.id = p.supplier_id')
        ->join('m_uom u', 'u.id = p.uom_id')
        ->where('purchase_order_id', $id)
        ->order_by('d.id')
        ->get()
        ->result();

    return [
      'header' => $header,
      'details' => $details
    ];
  }

  public function getAll($filters = [])
  {
    $this->db
        ->select("p.id, p.po_no, p.po_date, s.supplier_name, p.status, COALESCE(SUM((d.qty * d.price) - d.discount), 0) AS total")
        ->from('t_purchase_orders p')
        ->join(
          'm_suppliers s',
          's.id = p.supplier_id'
        )
        ->join(
          't_purchase_order_details d',
          'd.purchase_order_id = p.id',
          'left'
        );

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'p.po_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'p.po_date <=',
        $filters['date_to']
      );
    }

    if (!empty($filters['supplier_id'])) {
      $this->db->where(
        'p.supplier_id',
        $filters['supplier_id']
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'p.status',
        $filters['status']
      );
    }

    return $this->db
        ->group_by("
            p.id,
            p.po_no,
            p.po_date,
            s.supplier_name,
            p.status
        ")
        ->order_by(
            'p.po_date DESC,
            p.id DESC'
        )
        ->get()
        ->result();
  }

  public function cancel($id)
  {
    $purchaseOrder = $this->db
        ->select('id, po_no, status')
        ->from('t_purchase_orders')
        ->where('id', $id)
        ->where('is_active', true)
        ->get()
        ->row();

    if (!$purchaseOrder) {
      return [
        'success' => false,
        'message' => 'Purchase Order not found.',
        'data' => null
      ];
    }

    if ($purchaseOrder->status === 'CANCELLED') {
      return [
        'success' => false,
        'message' => 'Purchase Order is already cancelled.',
        'data' => null
      ];
    }

    if ($purchaseOrder->status === 'CLOSED') {
      return [
        'success' => false,
        'message' => 'Closed Purchase Orders cannot be cancelled.',
        'data' => null
      ];
    }

    $this->db
        ->where('id', $id)
        ->update(
            't_purchase_orders',
            [
              'status'     => 'CANCELLED',
              'updated_by' => $this->session->userdata('user_id'),
              'updated_on' => date('Y-m-d H:i:s')
            ]
        );

    if ($this->db->affected_rows() === 0) {
      return [
        'success' => false,
        'message' => 'Unable to cancel Purchase Order.',
        'data' => null
      ];
    }

    return [
      'success' => true,
      'message' => 'Purchase Order cancelled successfully.',
      'data' => [
        'id' => $purchaseOrder->id,
        'po_no' => $purchaseOrder->po_no
      ]
    ];
  }

  public function cancelMany(array $ids)
  {
    $purchaseOrders = $this->db
        ->select('id, status')
        ->from('t_purchase_orders')
        ->where_in('id', $ids)
        ->where('is_active', true)
        ->get()
        ->result();

    $ids = array_unique(
      array_map('intval', $ids)
    );

    if (count($purchaseOrders) !== count($ids)) {
        return [
          'success' => false,
          'message' => 'One or more Purchase Orders could not be found.',
          'data' => null
        ];
    }

    foreach ($purchaseOrders as $po) {
      if ($po->status !== 'OPEN') {
        return [
          'success' => false,
          'message' => 'Please select only OPEN Purchase Orders to cancel.',
          'data' => null
        ];
      }
    }

    $this->db->trans_begin();

    $this->db
        ->where_in('id', $ids)
        ->update(
            't_purchase_orders',
            [
              'status'     => 'CANCELLED',
              'updated_by' => $this->session->userdata('user_id'),
              'updated_on' => date('Y-m-d H:i:s')
            ]
        );

    if ($this->db->trans_status() === FALSE) {

        $this->db->trans_rollback();

        return [
          'success' => false,
          'message' => 'Unable to cancel the selected Purchase Orders.',
          'data' => null
        ];

    }

    $this->db->trans_commit();

    return [
      'success' => true,
      'message' =>
          count($ids) === 1
            ? 'Purchase Order cancelled successfully.'
            : count($ids) . ' Purchase Orders cancelled successfully.',
      'data' => null
    ];
  }

  private function insertHeader($po)
  {
    $poNo = $this->generatePONumber();
    $remarks = trim($po->remarks) <> '' ? strtoupper(trim($po->remarks)) : NULL;

    $sql = "INSERT INTO t_purchase_orders
              (
                po_no,
                po_date,
                supplier_id,
                terms_id,
                remarks,
                entered_by,
                entered_on
              )
              VALUES
              (
                ?,?,?,?,?,?,
                CURRENT_TIMESTAMP
              )
              RETURNING id
    ";

    $query = $this->db->query(
      $sql,
      [
        $poNo,
        $po->po_date,
        $po->supplier_id,
        $po->terms_id,
        $remarks,
        $this->session->userdata('user_id')
      ]
    );

    if (!$query || $query->num_rows() === 0) {
      throw new Exception(
        'Unable to save Purchase Order header.'
      );
    }
    $row = $query->row();

    return [
      'id' => $row->id,
      'po_no' => $poNo,
    ];
  }

  private function insertDetails($purchaseOrderId, $details)
  {
    $sql = "INSERT INTO t_purchase_order_details
              (
                purchase_order_id,
                product_id,
                qty,
                price,
                discount,
                entered_by,
                entered_on
              )
              VALUES
              (
                ?,?,?,?,?,?,
                CURRENT_TIMESTAMP
              )
    ";

    foreach ($details as $detail) {
      $this->db->query(
        $sql,
        [
          $purchaseOrderId,
          $detail->product_id,
          $detail->qty,
          $detail->price,
          $detail->discount,
          $this->session->userdata('user_id'),
        ]
      );

      if ($this->db->affected_rows() == 0) {
        throw new Exception(
          'Unable to save Purchase Order detail.'
        );
      }
    }
  }

  private function validate($po)
  {
    if (empty($po->supplier_id)) {
      throw new Exception(
        'Please select a supplier.'
      );
    }

    if (count($po->details) === 0) {
      throw new Exception(
        'Please add at least one product.'
      );
    }

    if (empty($po->terms_id)) {
      throw new Exception(
        'Please select payment terms.'
      );
    }

    foreach ($po->details as $index => $detail) {
      if (empty($detail->product_id)) {
        throw new Exception(
          'Product is required on row '.($index + 1).'.'
        );
      }

      if ($detail->qty <= 0) {
        throw new Exception(
          'Invalid quantity on row '.($index + 1).'.'
        );
      }

      if ($detail->price < 0) {
        throw new Exception(
          'Invalid price on row '.($index + 1).'.'
        );
      }

      if ($detail->discount < 0) {
        throw new Exception(
          'Invalid discount on row '.($index + 1).'.'
        );
      }
    }
  }

  private function updateHeader($po)
  {
    $sql = "UPDATE t_purchase_orders
              SET
                po_date = ?,
                supplier_id = ?,
                terms = ?,
                remarks = ?,
                updated_by = ?,
                updated_on = CURRENT_TIMESTAMP
              WHERE id = ?
            ";

    $remarks = trim($po->remarks) <> '' ? strtoupper(trim($po->remarks)) : NULL;
    $this->db->query(
      $sql,
      [
        $po->po_date,
        $po->supplier_id,
        $po->terms_id,
        $remarks,
        $this->session->userdata('user_id'),
        $po->id
      ]
    );

    if ($this->db->error()['code']) {
      throw new Exception('Unable to update Purchase Order header.');
    }
  }

  private function replaceDetails($purchaseOrderId, $details)
  {
    if (empty($details)) {
      throw new Exception(
        'Purchase Order must contain at least one product.'
      );
    }

    $this->db->query("DELETE FROM t_purchase_order_details
                      WHERE purchase_order_id = ?",
                      [$purchaseOrderId]
                    );

    $this->insertDetails(
      $purchaseOrderId,
      $details
    );
  }
}