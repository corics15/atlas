<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_return_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Inventory_model');
    $this->load->model('Goods_receipt_model');
  }

  public function getAll($filters = [])
  {
    /*** filters */
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("pr.pr_no ILIKE '%{$escaped}%'")
        ->or_where("gr.grn_no ILIKE '%{$escaped}%'")
        ->or_where("s.supplier_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'pr.return_date >=',
        $filters['date_from']
      );
    } else {
      $this->db->where(
        'pr.return_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'pr.return_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'pr.return_date <=',
        date('Y-m-d')
      );
    }

    if (!empty($filters['supplier_id'])) {
      $this->db->where(
        'pr.supplier_id =',
        $filters['supplier_id']
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'pr.status =',
        $filters['status']
      );
    }
    /*** end filters */

    return $this->db
        ->select("
          pr.*,
          gr.grn_no,
          gr.grn_date,
          gr.id AS goods_receipt_id,
          s.supplier_name,
          t.terms_name
        ")
        ->from('t_purchase_returns pr')
        ->join(
            't_goods_receipts gr',
            'gr.id = pr.goods_receipt_id',
            'left'
        )
        ->join(
            'm_suppliers s',
            's.id = pr.supplier_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = pr.terms_id',
            'left'
        )
        ->order_by('gr.grn_date','DESC')
        ->order_by('pr.id','DESC')
        ->get()
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->select("
            pr.*,
            gr.grn_no,
            c.supplier_name,
            t.terms_name
        ")
        ->from('t_purchase_returns pr')
        ->join(
            't_goods_receipts gr',
            'gr.id = pr.goods_receipt_id'
        )
        ->join(
            'm_suppliers c',
            'c.id = pr.supplier_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = pr.terms_id',
            'left'
        )
        ->where('pr.id', $id)
        ->get()
        ->row();
  }

  public function getDetails($id)
  {
    $branchId = (int) $this->session->userdata('branch_id');

    return $this->db
        ->select("
            sid.*,
            grd.qty_received,
            (
              SELECT COALESCE(SUM(prd.qty),0)
              FROM t_purchase_return_details prd
              INNER JOIN t_purchase_returns pr ON pr.id = prd.purchase_return_id
              WHERE pr.status <> 'CANCELLED'
              AND prd.goods_receipt_detail_id = sid.goods_receipt_detail_id
            ) AS qty_returned,
            p.barcode,
            p.description,
            COALESCE(bi.qty_on_hand, 0) AS qty_available,
            u.uom
        ")
        ->from('t_purchase_return_details sid')
        ->join(
            't_goods_receipt_details grd',
            'grd.id = sid.goods_receipt_detail_id',
            'left'
        )
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
            'u.id = p.uom_id',
            'left'
        )
        ->where(
            'sid.purchase_return_id',
            $id
        )
        ->order_by(
            'sid.id',
            'ASC'
        )
        ->get()
        ->result();
  }

  public function getGoodsReceipt($goodsReceiptId)
  {
    return $this->Goods_receipt_model->get($goodsReceiptId);
  }

  public function getGoodsReceiptDetails($goodsReceiptId)
  {
      $branchId = (int) $this->session->userdata('branch_id');

      return $this->db->query("SELECT
                                grd.id AS goods_receipt_detail_id,
                                grd.product_id,
                                grd.qty_received,
                                COALESCE(pr.qty_returned, 0) AS qty_returned,
                                -- 0 AS qty_returned,
                                grd.qty_received - COALESCE(pr.qty_returned,0) AS qty,
                                -- grd.qty_received AS qty,
                                p.barcode,
                                p.description,
                                COALESCE(bi.qty_on_hand,0) AS qty_available,
                                u.uom
                              FROM t_goods_receipt_details grd
                              INNER JOIN m_products p ON p.id = grd.product_id
                              LEFT JOIN t_branch_inventory bi ON bi.product_id = grd.product_id AND bi.branch_id = ?
                              LEFT JOIN m_uom u ON u.id = p.uom_id
                              LEFT JOIN
                              (
                                  SELECT
                                    prd.goods_receipt_detail_id,
                                    SUM(prd.qty) AS qty_returned
                                  FROM t_purchase_return_details prd
                                  INNER JOIN t_purchase_returns pr ON pr.id = prd.purchase_return_id
                                  WHERE pr.status <> 'CANCELLED'
                                  GROUP BY prd.goods_receipt_detail_id
                              ) pr
                                  ON pr.goods_receipt_detail_id = grd.id
                              WHERE grd.grn_id = ?
                                AND (grd.qty_received - COALESCE(pr.qty_returned,0)) > 0
                              ORDER BY grd.id
                          ", [
                              $branchId,
                              $goodsReceiptId
                          ])->result();
  }

  public function save($purchaseReturn)
  {
    try {
      $this->db->trans_begin();

      if (empty($purchaseReturn->id)) {
        $header = [
          'pr_no'            => $this->generateReturnNo(),
          'return_date'      => $purchaseReturn->return_date,
          'goods_receipt_id' => $purchaseReturn->goods_receipt_id,
          'supplier_id'    => $purchaseReturn->supplier_id,
          // 'terms_id'       => $purchaseReturn->terms_id,
          // 'credit_limit'   => $purchaseReturn->credit_limit,
          'remarks'        => trim($purchaseReturn->remarks) <> '' ? strtoupper(trim($purchaseReturn->remarks)) : NULL,
          'status'         => 'OPEN',
          'entered_by'     => $this->session->userdata('user_id'),
          'entered_on'     => date('Y-m-d H:i:s')
        ];

        $this->db->insert('t_purchase_returns', $header);

        $purchaseReturnId = $this->db->insert_id();
        $returnNo = $header['pr_no'];

        /*** synchronize details */
        $this->db
            ->where('purchase_return_id', $purchaseReturnId)
            ->delete('t_purchase_return_details');

        $hasQty = FALSE;

        foreach ($purchaseReturn->details as $detail)
        {
          if ((float)$detail->qty <= 0) {
            continue;
          }

          $hasQty = TRUE;

          $this->db->insert(
            't_purchase_return_details',
            [
              'purchase_return_id'      => $purchaseReturnId,
              'goods_receipt_detail_id' => $detail->goods_receipt_detail_id,
              'product_id'              => $detail->product_id,
              'qty'                     => $detail->qty,
              'unit_price'              => 0,
              'discount_percent'        => 0,
              'discount_amount'         => 0,
              'remarks'                 => NULL
            ]
          );
        }

        if (!$hasQty) {
          throw new Exception(
            'Please enter a quantity for at least one item.'
          );
        }
        /*** end synchronize details */
      }

      else {

        $return = $this->db
            ->where('id', $purchaseReturn->id)
            ->get('t_purchase_returns')
            ->row();

        if (!$return) {
          throw new Exception(
            'Purchase Return not found.'
          );
        }

        if ($return->status != 'OPEN') {
          throw new Exception(
            "Cannot modify a {$return->status} Purchase Return."
          );
        }

        $this->db
            ->where('id', $purchaseReturn->id)
            ->update(
                't_purchase_returns',
                [
                  'return_date' => $purchaseReturn->return_date,
                  'remarks' => trim($purchaseReturn->remarks) <> '' ? strtoupper(trim($purchaseReturn->remarks)) : NULL,
                  'updated_by' => $this->session->userdata('user_id'),
                  'updated_on' => date('Y-m-d H:i:s')
                ]
            );

        $purchaseReturnId = $purchaseReturn->id;
        $returnNo = $return->pr_no;
      }

      if ($this->db->trans_status() === FALSE)
      {
        throw new Exception('Unable to save Purchase Return.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Purchase Return saved.',
        'data' => [
          'purchase_return_id' => $purchaseReturnId,
          'pr_no' => $returnNo
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
          'Please select at least one Purchase Return.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
        $header = $this->db
            ->where('id', $id)
            ->get('t_purchase_returns')
            ->row();

        if (!$header) {
          throw new Exception(
            'Purchase Return not found.'
          );
        }

        /*** validate source Goods Receipt */
        /*** if a user tries to POST a Purchase Return whose source GR is still OPEN */
        $goodsReceipt = $this->Goods_receipt_model->get($header->goods_receipt_id);

        if (!$goodsReceipt) {
          throw new Exception(
            'Source Goods Receipt not found.'
          );
        }

        if ($goodsReceipt->status != 'POSTED') {
          throw new Exception(
            "Purchase Return {$header->pr_no} cannot be posted because Goods Receipt {$goodsReceipt->grn_no} is still {$goodsReceipt->status}."
          );
        }
        /*** end validate source Goods Receipt */

        if ($header->status != 'OPEN') {
          throw new Exception(
            "Purchase Return {$header->pr_no} is already {$header->status}."
          );
        }

        /*** inventory update */
        $result = $this->Inventory_model->postPurchaseReturn($id);
        if (!$result['success']) {
          throw new Exception($result['message']);
        }
        /*** end inventory update */

        /*** mark as posted sales return */
        $this->db
            ->where('id', $id)
            ->update(
                't_purchase_returns',
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
          'Unable to post Purchase Return.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Purchase Return(s) posted successfully.',
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
          'Please select at least one Purchase Return.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
          $invoice = $this->db
              ->where('id', $id)
              ->get('t_purchase_returns')
              ->row();

          if (!$invoice) {
            throw new Exception(
              'Purchase Return not found.'
            );
          }

          if ($invoice->status != 'OPEN') {
            throw new Exception(
              "Only OPEN Purchase Returns can be cancelled."
            );
          }

          $this->db
              ->where('id', $id)
              ->update(
                  't_purchase_returns',
                  [
                    'status'          => 'CANCELLED',
                    'cancel_reason'   => trim($cancelReason) <> '' ? strtoupper(trim($cancelReason)) : NULL,
                    'cancelled_by'    => $this->session->userdata('user_id'),
                    'cancelled_on'    => date('Y-m-d H:i:s'),
                  ]
              );
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to cancel Purchase Return.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Purchase Return(s) cancelled successfully.',
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

  private function generateReturnNo()
  {
    return 'PR-' . date('YmdHis');
  }

}