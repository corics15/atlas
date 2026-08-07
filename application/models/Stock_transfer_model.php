<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_transfer_model extends CI_Model
{
  protected $table = 't_stock_transfers';

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Inventory_model');
  }

  public function save($stockTransfer)
  {
    try {
      $this->db->trans_begin();

      if (empty($stockTransfer->id)) {

        /*** insert header */
        $header = [
          'transfer_no'    => $this->generateTransferNo(),
          'transfer_date'  => $stockTransfer->transfer_date,
          'from_branch_id' => $stockTransfer->from_branch_id,
          'to_branch_id'   => $stockTransfer->to_branch_id,
          'remarks'        => trim($stockTransfer->remarks) <> '' ? strtoupper(trim($stockTransfer->remarks)) : NULL,
          'status'         => 'OPEN',
          'entered_by'     => $this->session->userdata('user_id'),
          'entered_on'     => date('Y-m-d H:i:s')
        ];

        $this->db->insert('t_stock_transfers', $header);

        $stockTransferId = $this->db->insert_id();
        $transferNo = $header['transfer_no'];

      } else {

        /*** don't allow modification of POSTED/CANCELLED docs */
        $current = $this->db
            ->select('status')
            ->where('id', $stockTransfer->id)
            ->get('t_stock_transfers')
            ->row();

        if (!$current) {
          throw new Exception('Stock Transfer not found.');
        }

        if ($current->status !== 'OPEN') {
          throw new Exception(
            "Cannot modify a {$current->status} Stock Transfer."
          );
        }

        /*** update header */
        $this->db
            ->where('id', $stockTransfer->id)
            ->update(
              't_stock_transfers',
              [
                'transfer_date'  => $stockTransfer->transfer_date,
                'from_branch_id' => $stockTransfer->from_branch_id,
                'to_branch_id'   => $stockTransfer->to_branch_id,
                'remarks'        => trim($stockTransfer->remarks) <> '' ? strtoupper(trim($stockTransfer->remarks)) : NULL,
                'updated_by'     => $this->session->userdata('user_id'),
                'updated_on'     => date('Y-m-d H:i:s')
              ]
            );

        $stockTransferId = $stockTransfer->id;

        $transferNo = $this->db
            ->select('transfer_no')
            ->where('id', $stockTransferId)
            ->get('t_stock_transfers')
            ->row()
            ->transfer_no;

        /*** remove old details */
        $this->db->where('stock_transfer_id', $stockTransferId)->delete('t_stock_transfer_details');
      }

      /*** INSERT DETAILS */
      foreach ($stockTransfer->details as $detail) {
        $this->db->insert(
          't_stock_transfer_details',
          [
            'stock_transfer_id' => $stockTransferId,
            'product_id'        => $detail->product_id,
            'qty'               => $detail->qty,
            'unit_cost'         => 0,
            'remarks'           => NULL
          ]
        );
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to save Stock Transfer.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => empty($stockTransfer->id)
          ? 'Stock Transfer saved.'
          : 'Stock Transfer updated.',
        'data' => [
          'stock_transfer_id' => $stockTransferId,
          'transfer_no'       => $transferNo
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
          'Please select at least one Stock Transfer.'
        );
      }

      $result = $this->Inventory_model->postStockTransfer($ids[0]);

      if (!$result['success']) {
        throw new Exception($result['message']);
      }

      return [
        'success' => TRUE,
        'message' => 'Stock Transfer posted successfully.',
        'data'    => []
      ];

    } catch (Exception $ex) {

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
        throw new Exception('Please select at least one Stock Transfer.');
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
          $stockTransfer = $this->db
              ->where('id', $id)
              ->get('t_stock_transfers')
              ->row();

          if (!$stockTransfer) {
            throw new Exception("Stock Transfer #{$id} not found.");
          }

          if ($stockTransfer->status !== 'OPEN') {
            throw new Exception(
              "{$stockTransfer->transfer_no} is already {$stockTransfer->status}."
            );
          }

          $this->db
              ->where('id', $id)
              ->update(
                  't_stock_transfers',
                  [
                    'status'         => 'CANCELLED',
                    'cancelled_by'   => $this->session->userdata('user_id'),
                    'cancelled_on'   => date('Y-m-d H:i:s'),
                    'cancel_reason'  => $cancelReason,
                    'updated_by'     => $this->session->userdata('user_id'),
                    'updated_on'     => date('Y-m-d H:i:s')
                  ]
              );

          if (!$this->db->affected_rows()) {
            throw new Exception(
              "Unable to cancel {$stockTransfer->transfer_no}."
            );
          }
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => count($ids) . ' Stock Transfer(s) cancelled successfully.',
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

  public function get($id)
  {
    return $this->db
        ->select("
          st.*,
          fb.branch_name AS from_branch,
          tb.branch_name AS to_branch
        ")
        ->from('t_stock_transfers st')
        ->where('st.id', $id)
        ->where('st.is_active', TRUE)
        ->join(
          'm_branches fb',
          'fb.id = st.from_branch_id',
          'left'
        )
        ->join(
          'm_branches tb',
          'tb.id = st.to_branch_id',
          'left'
        )
        ->get()
        ->row();
  }

  public function getDetails($id)
  {
      return $this->db
          ->select("
              d.*,
              p.barcode,
              p.description,
              u.uom,
              COALESCE(inv.qty_on_hand, 0) AS qty_on_hand
          ")
          ->from('t_stock_transfer_details d')
          ->join(
              't_stock_transfers st',
              'st.id = d.stock_transfer_id'
          )
          ->join(
              'm_products p',
              'p.id = d.product_id'
          )
          ->join(
              'm_uom u',
              'u.id = p.uom_id'
          )
          ->join(
              't_branch_inventory inv',
              'inv.product_id = d.product_id
              AND inv.branch_id = st.from_branch_id',
              'left'
          )
          ->where(
              'd.stock_transfer_id',
              $id
          )
          ->order_by('d.id')
          ->get()
          ->result();
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("st.transfer_no ILIKE '%{$escaped}%'")
        ->or_where("fb.branch_name ILIKE '%{$escaped}%'")
        ->or_where("tb.branch_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'transfer_date >=',
        $filters['date_from']
      );
    } else {
      $this->db->where(
        'transfer_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'transfer_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'transfer_date <=',
        date('Y-m-d')
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'status',
        $filters['status']
      );
    }

    return $this->db
        ->select("
          st.*,
          fb.branch_name AS from_branch,
          tb.branch_name AS to_branch
        ")
        ->from('t_stock_transfers st')
        ->join(
          'm_branches fb',
          'fb.id = st.from_branch_id',
          'left'
        )
        ->join(
          'm_branches tb',
          'tb.id = st.to_branch_id',
          'left'
        )
        ->where('st.is_active', TRUE)
        ->order_by(
          'st.transfer_date DESC,
          st.id DESC'
        )
        ->get()
        ->result();
  }

  public function generateTransferNo()
  {
    // Temporary implementation, temporary generate
    return 'ST-' . date('YmdHis');
  }
}