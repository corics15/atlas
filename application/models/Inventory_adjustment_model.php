<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_adjustment_model extends CI_Model
{

  protected $table = 't_inventory_adjustments';
  protected $detailTable = 't_inventory_adjustment_details';
  protected $productTable = 'm_products';

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Stock_ledger_model');
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['status'])) {
      $this->db->where(
        'status',
        $filters['status']
      );
    }

    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);
      $this->db->where("adjustment_no ILIKE '%{$escaped}%'");
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'adjustment_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'adjustment_date <=',
        $filters['date_to']
      );
    }

    return $this->db
        ->order_by('adjustment_date', 'DESC')
        ->order_by('id', 'DESC')
        ->get('v_inventory_adjustments')
        ->result();
  }

  public function getDetails($adjustmentId)
  {
    return $this->db
        ->select('
            d.id,
            d.product_id,
            p.barcode,
            p.description,
            u.uom,
            p.qty_on_hand AS on_hand,
            d.adjustment_qty,
            d.remarks
        ')
        ->from('t_inventory_adjustment_details d')
        ->join('m_products p', 'p.id = d.product_id')
        ->join('m_uom u', 'u.id = p.uom_id')
        ->where('d.adjustment_id', $adjustmentId)
        ->order_by('d.id')
        ->get()
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('v_inventory_adjustments')
        ->row();
  }

  public function getProductStock($productId)
  {
    $row = $this->db
        ->select('qty_on_hand')
        ->from('m_products')
        ->where('id', $productId)
        ->where('is_active', 't')
        ->get()
        ->row();

    return $row ? (int) $row->qty_on_hand : 0;
  }

  public function save($data)
  {
    $this->db->trans_begin();

    try {

      // -------------------------------------------------
      // save header
      // -------------------------------------------------

      $headerData = [
        'adjustment_date' => $data['adjustment_date'],
        'remarks'         => $data['remarks'] <> '' ? strtoupper(trim($data['remarks'])) : NULL,
      ];

      if (empty($data['adjustment_id'])) {

          /*** new document */
          $headerData['adjustment_no'] = $this->generateAdjustmentNo();
          $headerData['status']        = 'DRAFT';
          $headerData['entered_by']    = $data['entered_by'];

          $this->db->insert($this->table, $headerData);

          $adjustmentId = $this->db->insert_id();

          if (!$adjustmentId) {
            throw new Exception('Unable to save inventory adjustment header.');
          }

      } else {

          /*** existing document */
          $adjustmentId = (int) $data['adjustment_id'];

          $headerData['updated_by'] = $data['updated_by'];
          $headerData['updated_on'] = date('Y-m-d H:i:s');

          $adjustment = $this->db
              ->where('id', $adjustmentId)
              ->get($this->table)
              ->row();

          if (!$adjustment) {
            throw new Exception('Inventory Adjustment not found.');
          }

          if ($adjustment->status !== 'DRAFT') {
            throw new Exception('Only draft inventory adjustments can be modified.');
          }

          $this->db
              ->where('id', $adjustmentId)
              ->where('status', 'DRAFT')
              ->update($this->table, $headerData);

          if ($this->db->affected_rows() < 0) {
            throw new Exception('Unable to update inventory adjustment header.');
          }

          /*** delete detail rows */
          $this->db
              ->where('adjustment_id', $adjustmentId)
              ->delete($this->detailTable);
      }

      // -------------------------------------------------
      // save details
      // -------------------------------------------------

      $this->saveDetails(
        $adjustmentId,
        $data['details']
      );

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Database transaction failed.');
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => 'Inventory adjustment saved as DRAFT.',
        'data'    => []
      ];

    } catch (Exception $e) {
        $this->db->trans_rollback();

        log_message(
          'error',
          __METHOD__ . ': ' . $e->getMessage()
        );

        return [
          'success' => false,
          'message' => $e->getMessage(),
          'data'    => []
        ];
    }
  }

  public function post($adjustmentId, $postedBy)
  {
    $this->db->trans_begin();

    try {

      /*** validate draft document */
      $adjustment = $this->db
          ->where('id', $adjustmentId)
          ->get($this->table)
          ->row();

      if (!$adjustment) {
        throw new Exception('Inventory Adjustment not found.');
      }

      if ($adjustment->status !== 'DRAFT') {
        throw new Exception('Only draft inventory adjustments can be posted.');
      }

      $details = $this->db
          ->select('
              d.*,
              p.qty_on_hand
          ')
          ->from($this->detailTable . ' d')
          ->join($this->productTable . ' p', 'p.id = d.product_id')
          ->where('d.adjustment_id', $adjustmentId)
          ->get()
          ->result();

      if (empty($details)) {
          throw new Exception('Inventory Adjustment has no detail items.');
      }
      /*** end validation */

      /*** load details */
      foreach ($details as $detail) {
        $newBalance = $detail->qty_on_hand + $detail->adjustment_qty;

        /*** update product stock */
        $this->updateProductStock(
          $detail->product_id,
          $newBalance
        );

        /*** record stock ledger */
        $this->recordStockMovement(
          $adjustment->id,
          $adjustment->adjustment_no,
          $detail,
          $newBalance,
          $postedBy
        );
      }

      /*** mark posted */
      $this->db
          ->where('id', $adjustmentId)
          ->update($this->table, [
              'status'    => 'POSTED',
              'posted_by' => $postedBy,
              'posted_on' => date('Y-m-d H:i:s')
          ]);

      if ($this->db->affected_rows() !== 1) {
        throw new Exception('Unable to update Inventory Adjustment status.');
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Database transaction failed.');
      }

      /*** commit changes */
      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => sprintf(
          'Inventory Adjustment %s posted successfully.',
          $adjustment->adjustment_no
        ),
        'data'    => []
      ];

    } catch (Exception $e) {
      $this->db->trans_rollback();

      log_message(
        'error',
        __METHOD__ . ': ' . $e->getMessage()
      );

      return [
        'success' => false,
        'message' => $e->getMessage(),
        'data'    => []
      ];
    }
  }

  public function cancel($adjustmentId, $cancelledBy, $cancelReason)
  {
    $this->db->trans_begin();

    try {

      /*** validate draft document */
      $adjustment = $this->db
          ->where('id', $adjustmentId)
          ->get($this->table)
          ->row();

      if (!$adjustment) {
        throw new Exception('Inventory Adjustment not found.');
      }

      if ($adjustment->status !== 'DRAFT') {
        throw new Exception('Only draft inventory adjustments can be cancelled.');
      }

      /*** mark cancelled */
      $this->db
          ->where('id', $adjustmentId)
          ->update($this->table, [
              'status'          => 'CANCELLED',
              'cancelled_by'    => $cancelledBy,
              'cancelled_on'    => date('Y-m-d H:i:s'),
              'cancel_reason'   => trim($cancelReason) <> '' ? strtoupper(trim($cancelReason)) : NULL,
          ]);

      if ($this->db->affected_rows() !== 1) {
        throw new Exception('Unable to cancel Inventory Adjustment.');
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Database transaction failed.');
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => sprintf(
            'Inventory Adjustment %s cancelled successfully.',
            $adjustment->adjustment_no
        ),
        'data' => []
      ];

    } catch (Exception $e) {
      $this->db->trans_rollback();

      log_message(
          'error',
          __METHOD__ . ': ' . $e->getMessage()
      );

      return [
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
      ];
    }
  }

  /*** private functions */

  /**
   * Updates the current stock of a product.
   *
   * @param int   $productId
   * @param float $newBalance
   * @return void
   * @throws Exception
   */
  private function updateProductStock($productId, $newBalance)
  {
    $this->db
        ->where('id', $productId)
        ->update($this->productTable, [
            'qty_on_hand' => $newBalance
        ]);

    if ($this->db->error()['code']) {
      throw new Exception(
        "Unable to update stock for product ID {$productId}."
      );
    }
  }

  /**
   * Records the stock movement for an inventory adjustment.
   *
   * @param int    $adjustmentId
   * @param string $adjustmentNo
   * @param object $detail
   * @param float  $newBalance
   * @param int    $postedBy
   * @return int
   */
  private function recordStockMovement($adjustmentId, $adjustmentNo, $detail, $newBalance, $postedBy)
  {
    $adjustment = (float) $detail->adjustment_qty;

    $ledgerData = [
      'transaction_date' => date('Y-m-d H:i:s'),
      'transaction_type' => 'ADJUSTMENT',
      'reference_id'     => $adjustmentId,
      'reference_no'     => $adjustmentNo,
      'product_id'       => $detail->product_id,
      'qty_in'           => $adjustment > 0 ? $adjustment : 0,
      'qty_out'          => $adjustment < 0 ? abs($adjustment) : 0,
      'balance_after'    => $newBalance,
      'unit_cost'        => 0,
      'remarks'          => $detail->remarks,
      'entered_by'       => $postedBy
    ];

    return $this->Stock_ledger_model->record($ledgerData);
  }

  /**
   * Saves the inventory adjustment detail rows.
   *
   * @param int   $adjustmentId
   * @param array $details
   * @return void
   * @throws Exception
   */
  private function saveDetails($adjustmentId, array $details)
  {
    foreach ($details as $detail) {
      $detailData = [
        'adjustment_id' => $adjustmentId,
        'product_id'    => $detail['product_id'],
        'adjustment_qty'=> $detail['adjustment'],
        'remarks'       => $detail['remarks'] ?? null
      ];

      $this->db->insert($this->detailTable, $detailData);
      $detailId = $this->db->insert_id();

      if (!$detailId) {
        throw new Exception(
          'Unable to save inventory adjustment detail.'
        );
      }
    }
  }

  private function generateAdjustmentNo()
  {
    return 'IA-' . date('YmdHis'); /*** temporary generate */
  }
}