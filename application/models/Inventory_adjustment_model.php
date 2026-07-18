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

  public function getAll()
  {
    return $this->db
        ->order_by('adjustment_date', 'DESC')
        ->order_by('id', 'DESC')
        ->get('v_inventory_adjustments')
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
      // Step 1: Save Header
      // -------------------------------------------------

      $headerData = [
        'adjustment_no'   => $this->generateAdjustmentNo(),
        'adjustment_date' => $data['adjustment_date'],
        'remarks'         => $data['remarks'],
        'entered_by'      => $data['entered_by']
      ];

      $this->db->insert($this->table, $headerData);
      $adjustmentId = $this->db->insert_id();

      if (!$adjustmentId) {
        throw new Exception('Unable to save inventory adjustment header.');
      }

      // -------------------------------------------------
      // Step 2: Save Details
      // -------------------------------------------------

      $this->saveDetails(
        $adjustmentId,
        $data['details']
      );

      // -------------------------------------------------
      // Step 3 & 4: Update Product Stock and Record Ledger
      // -------------------------------------------------

      foreach ($data['details'] as $detail) {
        $this->updateProductStock(
          $detail['product_id'],
          $detail['new_balance']
        );

        $this->recordStockMovement(
          $adjustmentId,
          $headerData['adjustment_no'],
          $detail,
          $data['entered_by']
        );
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Database transaction failed.');
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => 'Inventory Adjustment saved successfully.',
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
   * @param array  $detail
   * @param int    $enteredBy
   * @return int
   */
  private function recordStockMovement($adjustmentId, $adjustmentNo, array $detail, $enteredBy)
  {
    $adjustment = (float) $detail['adjustment'];

    $ledgerData = [
      'transaction_date' => date('Y-m-d H:i:s'),
      'transaction_type' => 'ADJUSTMENT',
      'reference_id'     => $adjustmentId,
      'reference_no'     => $adjustmentNo,
      'product_id'       => $detail['product_id'],
      'qty_in'           => $adjustment > 0 ? $adjustment : 0,
      'qty_out'          => $adjustment < 0 ? abs($adjustment) : 0,
      'balance_after'    => $detail['new_balance'],
      'unit_cost'        => 0,
      'remarks'          => $detail['remarks'] ?? null,
      'entered_by'       => $enteredBy
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