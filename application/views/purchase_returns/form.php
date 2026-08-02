<div class="card">
  <div class="card-header">

    <div class="d-flex justify-content-between align-items-center">
      <h3 class="card-title">
        Purchase Return Header
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

    <div class="form-row">
      <div class="form-group col-md-3">
        <label for="txtPRNo">PR No.</label>
        <input
          type="text"
          id="txtPRNo"
          class="form-control form-control-sm"
          value="<?= isset($purchaseReturn) ? $purchaseReturn->pr_no : 'AUTO-GENERATED' ?>"
          readonly>
      </div>
      <div class="form-group col-md-3">
        <label for="dtReturnDate">Return Date</label>
        <input
          type="date"
          id="dtReturnDate"
          class="form-control form-control-sm"
          value="<?= isset($purchaseReturn) ? $purchaseReturn->return_date : date('Y-m-d') ?>">
      </div>
      <div class="form-group col-md-6">
        <label for="txtSupplier">Supplier</label>
        <input
          type="text"
          id="txtSupplier"
          class="form-control form-control-sm"
          value="<?= htmlspecialchars($goodsReceipt->supplier_name) ?>"
          readonly>
      </div>
    </div>

    <div class="row">

      <div class="col-md-12">
        <div class="form-group">
          <label for="txtGoodsReceiptRemarks">GRN Remarks</label>
          <input
            id="txtGoodsReceiptRemarks"
            class="form-control form-control-sm text-uppercase"
            value="<?= htmlspecialchars($goodsReceipt->remarks)?>"
            readonly>
        </div>
      </div>

    </div>

    <div class="row">

      <div class="col-md-12">
        <div class="form-group">
          <label for="txtPurchaseReturnRemarks">Remarks</label>
          <input
            id="txtPurchaseReturnRemarks"
            class="form-control form-control-sm text-uppercase"
            value="<?= isset($purchaseReturn) ? htmlspecialchars($purchaseReturn->remarks) : '' ?>">
        </div>
      </div>

    </div>

    <?php if (empty($error_message)) : ?>
    <hr>

    <div class="row justify-content-end">
      <div class="col-md-2">
        <button
          type="button"
          id="btnSavePurchaseReturn"
          class="btn btn-sm btn-default btn-block">
            Save Purchase Return
        </button>
      </div>
    </div>
    <?php endif ?>

  </div>
</div>