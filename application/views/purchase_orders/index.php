<div class="card">

  <?php //* Purchase Order */ ?>
  <div class="card-header">
    <h3 class="card-title">Purchase Order</h3>
  </div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-group col-md-3">
        <label for="txtPONo">PO No.</label>
        <input type="text" id="txtPONo" class="form-control form-control-sm" readonly>
      </div>
      <div class="form-group col-md-3">
        <label for="txtPODate">Date</label>
        <input type="date" id="txtPODate" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="selCustomer">Customer</label>
        <select id="selCustomer" class="form-control form-control-sm">
          <option value="">Select Customer</option>
          <?php foreach ($customers as $customer): ?>
            <option value="<?= $customer->id; ?>" data-salesman-id="<?= $customer->salesman_id; ?>" data-terms="<?= $customer->terms; ?>" data-credit-limit="<?= $customer->credit_limit; ?>">
              <?= htmlspecialchars($customer->customer_name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-6">
        <label for="selSalesman">Salesman</label>
        <select id="selSalesman" class="form-control form-control-sm">
          <option value="">Select Salesman</option>
          <?php foreach ($salesmen as $salesman): ?>
            <option value="<?= $salesman->id; ?>">
              <?= htmlspecialchars($salesman->first_name.' '.$salesman->last_name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-3">
        <label for="txtTerms">Terms</label>
        <input type="text" id="txtTerms" class="form-control form-control-sm" placeholder="Select term">
      </div>
      <div class="form-group col-md-9">
        <label for="txtRemarks">Remarks</label>
        <input type="text" id="txtRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-3">
        <label for="txtCreditLimit">Credit Limit</label>
        <input type="number" step="any" id="txtCreditLimit" class="form-control form-control-sm" placeholder="Credit limit" readonly>
      </div>
    </div>
  </div>
</div>

<?php //* Order Details */ ?>
<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Order Details</h3>
  </div>
  <div class="card-body p-0">
    <table class="table table-sm table-hover mb-0">
      <thead class="thead-light">
        <tr>
          <th width="170">Scan/Input Barcode</th>
          <th width="150">Supplier</th>
          <th>Product Description</th>
          <th width="70">UOM</th>
          <th width="70" class="text-right">Qty</th>
          <th width="90" class="text-right">Price</th>
          <th width="90" class="text-right">Disc.</th>
          <th width="110" class="text-right">Amount</th>
          <th width="40"></th>
        </tr>
      </thead>

        <tbody id="tblPurchaseOrderDetails">
          <tr>
            <td>
              <div class="input-group">
                <input type="text" class="form-control form-control-sm po-barcode" placeholder="Barcode">
                <div class="input-group-append">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-info btn-product-finder">
                    <i class="fas fa-search font-smr"></i>
                  </button>
                </div>
              </div>
            </td>
            <td class="po-supplier align-middle"></td>
            <td class="po-description align-middle"></td>
            <td class="po-uom align-middle"></td>
            <td>
              <input
                type="number" step="any"
                class="form-control form-control-sm text-right po-qty"
                value="">
            </td>
            <td>
              <input
                type="number" step="any"
                class="form-control form-control-sm text-right po-price"
                value="0.00">
            </td>
            <td>
              <input
                type="number" step="any"
                class="form-control form-control-sm text-right po-discount"
                value="0.00">
            </td>
            <td class="po-total text-right align-middle">
              0.00
            </td>
            <td>
              <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
            </td>
          </tr>
        </tbody>

    </table>
  </div>
</div>

<?php //* Footer */ ?>
<div class="card mt-3">
  <div class="card-body">
    <div class="form-row">
      <div class="col-md-9"></div>
      <div class="col-md-3">
        <table class="table table-sm mb-3">
          <tr>
            <td class="po-total-text">Total</td>
            <td id="lblTotal" class="text-right">0.00</td>
          </tr>
        </table>
        <button id="btnSavePurchaseOrder" class="btn btn-outline-success btn-block">Save Purchase Order</button>
      </div>
    </div>
  </div>
</div>

<script>
  window.purchaseOrderId = <?= (int) ($purchaseOrderId ?? 0); ?>;
</script>