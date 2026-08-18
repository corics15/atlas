<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_order_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Document_number_model');
  }

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
            d.id,
            d.product_id,
            d.uom_id,
            d.conversion_factor,
            p.uom_id AS base_uom_id,
            p.barcode,
            s.supplier_name,
            p.description,
            u.uom,
            d.qty,
            d.qty_received,
            (d.qty - d.qty_received) AS qty_remaining,
            d.price,
            d.discount
        ")
        ->from('t_purchase_order_details d')
        ->join(
          'm_products p',
          'p.id = d.product_id'
        )
        ->join(
          'm_suppliers s',
          's.id = p.supplier_id'
        )
        ->join(
          'm_uom u',
          'u.id = d.uom_id',
          'left'
        )
        ->where(
          'd.purchase_order_id',
          $id
        )
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
        ->select("p.id, p.po_no, p.po_date, s.supplier_name, p.status, COALESCE(SUM((d.qty * d.price) - d.discount), 0) AS total, p.remarks")
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

    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("p.po_no ILIKE '%{$escaped}%'")
        ->or_where("s.supplier_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'p.po_date >=',
        $filters['date_from']
      );
    } else {
      $this->db->where(
        'p.po_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'p.po_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'p.po_date <=',
        date('Y-m-d')
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

  public function cancel($id, $cancelReason = NULL)
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
            'status'          => 'CANCELLED',
            'cancelled_by'    => $this->session->userdata('user_id'),
            'cancelled_on'    => date('Y-m-d H:i:s'),
            'cancel_reason'   => $cancelReason <> '' ? strtoupper(trim($cancelReason)) : NULL,
            'updated_by'      => $this->session->userdata('user_id'),
            'updated_on'      => date('Y-m-d H:i:s')
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

  public function cancelMany(array $ids, $cancelReason = null)
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
              'cancelled_by'    => $this->session->userdata('user_id'),
              'cancelled_on'    => date('Y-m-d H:i:s'),
              'cancel_reason'   => $cancelReason <> '' ? strtoupper(trim($cancelReason)) : NULL,
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

  public function getDocument($ids)
  {
    if (!is_array($ids)) {
      $ids = [$ids];
    }

    $documents = [];

    foreach ($ids as $id) {
      $header = $this->db
          ->select("
              p.*,
              s.supplier_name,
              s.address,
              s.contact_person,
              s.telephone_no,
              t.terms_name
          ")
          ->from('t_purchase_orders p')
          ->join('m_suppliers s', 's.id = p.supplier_id')
          ->join('m_terms t', 't.id = p.terms_id', 'left')
          ->where('p.id', $id)
          ->get()
          ->row();

      $details = $this->db
          ->select("
              d.*,
              p.barcode,
              p.description,
              u.uom
          ")
          ->from('t_purchase_order_details d')
          ->join('m_products p', 'p.id = d.product_id')
          ->join('m_uom u', 'u.id = p.uom_id')
          ->where('purchase_order_id', $id)
          ->order_by('d.id')
          ->get()
          ->result();

      $documents[] = (object)[
        'header' => $header,
        'details' => $details
      ];
    }

    return $documents;
  }

  public function getForReceiving($id)
  {
    /*** header */
    $header = $this->db
        ->where('id', $id)
        ->get('v_purchase_orders')
        ->row();

    if (!$header) {
      return null;
    }

    /*** details */
    $details = $this->db
        ->where('purchase_order_id', $id)
        ->order_by('id', 'ASC')
        ->get('v_purchase_order_details')
        ->result();

    return [
      'header'  => $header,
      'details' => $details
    ];
  }

  /*** private functions */
  private function insertHeader($po)
  {
    $poNo = $this->Document_number_model->generate('PO');
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
                uom_id,
                conversion_factor,
                entered_by,
                entered_on
              )
              VALUES
              (
                ?,?,?,?,?,?,?,?,
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
          $detail->uom_id,
          $detail->conversion_factor,
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

    /*** validate detail UOM snapshots */
      foreach ($po->details as $detail) {

        if (empty($detail->uom_id)) {
          throw new Exception(
            'Please select a UOM for all Purchase Order items.'
          );
        }
      }
    /*** end validate */
  }

  private function updateHeader($po)
  {
    $sql = "UPDATE t_purchase_orders
              SET
                po_date = ?,
                supplier_id = ?,
                terms_id = ?,
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