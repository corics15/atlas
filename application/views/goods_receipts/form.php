<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      Receiving Header
    </h3>
  </div>
  <div class="card-body">

    <input
      type="hidden"
      id="hidPurchaseOrderId"
      name="po_id"
      value="<?= $purchaseOrder['header']->id ?>">

    <input
      type="hidden"
      id="hidSupplierId"
      name="supplier_id"
      value="<?= $purchaseOrder['header']->supplier_id ?>">

    <div class="form-row">
      <div class="form-group col-md-3">
        <label for="txtGRNNo">GRN No.</label>
        <input
          type="text"
          id="txtGRNNo"
          class="form-control form-control-sm"
          value="(Auto Generated)"
          readonly>
      </div>
      <div class="form-group col-md-3">
        <label for="dtGRNDate">GRN Date</label>
        <input
          type="date"
          id="dtGRNDate"
          class="form-control form-control-sm"
          value="<?= date('Y-m-d') ?>">
      </div>
      <div class="form-group col-md-6">
        <label for="txtSupplier">Supplier</label>
        <input
          type="text"
          id="txtSupplier"
          class="form-control form-control-sm"
          value="<?= htmlspecialchars($purchaseOrder['header']->supplier_name) ?>"
          readonly>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col-md-3">
        <label for="txtPONo">PO No.</label>
        <input
          type="text"
          id="txtPONo"
          class="form-control form-control-sm"
          value="<?= htmlspecialchars($purchaseOrder['header']->po_no) ?>"
          readonly>
      </div>
      <div class="form-group col-md-9">
        <label for="txtRemarks">Remarks</label>
        <input
          type="text"
          id="txtRemarks"
          class="form-control form-control-sm text-uppercase"
          placeholder="Enter remarks">
      </div>
    </div>

    <hr>

    <div class="text-right">
      <button
        type="button"
        id="btnSaveGoodsReceipt"
        class="btn btn-outline-success">
        Save Goods Receipt
      </button>
    </div>

  </div>
</div>