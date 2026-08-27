<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Order Details</h3>
    <div class="ml-auto">
      <a href="<?= base_url('purchase-orders/list') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>

      <button type="button" class="btn btn-sm btn-link" id="btnReceiveGoods" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-dolly mr-2"></i>Receive Goods</button>
      <button type="button" class="btn btn-sm btn-link" id="btnPrintPurchaseOrder"><i class="fa fa-print mr-2"></i>Print</button>
      <button type="button" class="btn btn-sm btn-link" id="btnCancelPurchaseOrder" <?= !$isEditable ? 'disabled' : '' ?>><i class="fas fa-ban mr-2"></i>Cancel</button>

    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="thead-orange">
          <tr>
            <th width="3%" class="text-center">#</th>
            <th width="12%">Scan/Input Barcode</th>
            <th>Supplier</th>
            <th>Product Description</th>
            <th width="7%" class="text-center">UOM</th>
            <th width="7%" class="text-right">Qty</th>
            <th width="8%" class="text-right">Price</th>
            <th width="7%" class="text-right">Discount</th>
            <th width="8%" class="text-right">Amount</th>
            <th width="40"></th>
          </tr>
        </thead>

          <tbody id="tblPurchaseOrderDetails">
            <tr>
              <td class="po-index text-center">1.</td>
              <td>
                <div class="input-group">
                  <input type="text" class="form-control form-control-sm po-barcode atlas-barcode text-center" placeholder="Barcode">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-sm btn-outline-warning btn-product-finder">
                      <i class="fas fa-search font-smr"></i>
                    </button>
                  </div>
                </div>
              </td>
              <td class="po-supplier"></td>
              <td class="po-description"></td>
              <td>
                <select class="form-control form-control-sm po-uom custom-select w-auto">
                  <option value="">Select...</option>
                  <?php foreach ($uoms as $uom): ?>
                    <option value="<?= $uom->id; ?>">
                      <?= htmlspecialchars($uom->uom); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm text-right po-qty" value="">
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm text-right po-price" value="0.00">
              </td>
              <td>
                <input type="number" step="any" class="form-control form-control-sm text-right po-discount" value="0.00">
              </td>
              <td class="po-total text-right">
                0.00
              </td>
              <td class="text-center">
                <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
              </td>
            </tr>
          </tbody>

      </table>
    </div>
  </div>
</div>