<div class="card">
  <div class="card-header">

    <div class="d-flex justify-content-between align-items-center">
      <h3 class="card-title">
        Purchase Return Information
      </h3>

      <?php if (isset($purchaseReturn)) : ?>
        <?php
          $statusClass = NULL;
          switch ($purchaseReturn->status) {
            case 'POSTED':
              $statusClass = 'text-success';
              break;
            case 'OPEN':
              $statusClass = 'text-secondary';
              break;
            case 'PARTIAL':
              $statusClass = 'text-warning';
              break;
            case 'COMPLETED':
              $statusClass = 'text-primary';
              break;
            default: /*** CANCELLED */
              $statusClass = 'text-danger';
              break;
          }
        ?>
        <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= $purchaseReturn->status ?>]</div>
      <?php endif; ?>
    </div>

  </div>
  <div class="card-body">

    <input
        type="hidden"
        id="hidGoodsReceiptId"
        value="<?= isset($goodsReceipt) ? $goodsReceipt->id : '' ?>">

    <input
        type="hidden"
        id="hidPurchaseReturnId"
        value="<?= isset($purchaseReturn) ? $purchaseReturn->id : '' ?>">

    <input
        type="hidden"
        id="hidSupplierId"
        value="<?= isset($goodsReceipt) ? $goodsReceipt->supplier_id : '' ?>">

    <?php /*** header */ ?>
    <input type="hidden" id="txtPRNo" value="">
    <table class="table table-sm table-borderless">
      <tr>
        <th width="180">PR No.</th>
        <td class="font-weight-500 text-brown" id="tdRefNo">
          <?= isset($purchaseReturn) ? $purchaseReturn->pr_no : 'AUTO-GENERATED' ?>
        </td>
      </tr>
      <tr>
        <th>GRN No.</th>
        <td>
          <a href="<?= base_url('goods-receipts/view/').$goodsReceipt->id ?>" class="text-wrap text-olive" target="_blank">
            <i class="fa-external-link-alt fas fa-xs mr-1"></i>
            <?= htmlspecialchars($goodsReceipt->grn_no) ?>
          </a>
        </td>
      </tr>
      <tr>
        <th>GRN Remarks</th>
        <td>
          <?= htmlspecialchars($goodsReceipt->remarks)?>
        </td>
      </tr>
      <tr>
        <th>Supplier</th>
        <td>
          <?= htmlspecialchars($goodsReceipt->supplier_name) ?>
        </td>
      </tr>
      <tr>
        <th>Return Date</th>
        <td>
          <input
            type="date"
            id="dtReturnDate"
            class="form-control form-control-sm w-auto"
            value="<?= isset($purchaseReturn) ? $purchaseReturn->return_date : date('Y-m-d') ?>">
        </td>
      </tr>
      <tr>
        <th>Remarks</th>
        <td>
          <input id="txtPurchaseReturnRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter Remarks..." value="<?= isset($purchaseReturn) ? htmlspecialchars($purchaseReturn->remarks) : '' ?>">
        </td>
      </tr>
    </table>

  </div>
</div>