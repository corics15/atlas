<div class="card">
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
        <input type="date" id="txtPODate" class="form-control form-control-sm">
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
        <input type="number" id="txtCreditLimit" class="form-control form-control-sm" placeholder="Credit limit" readonly>
      </div>
    </div>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Order Details</h3>
    <button id="btnAddProduct" class="btn btn-sm btn-primary">
      <i class="fas fa-plus"></i> Add Product
    </button>
  </div>
  <div class="card-body p-0">
    <table class="table table-sm table-hover mb-0">
      <thead class="thead-light">
        <tr>
          <th width="15%">Barcode</th>
          <th>Description</th>
          <th width="10%">UOM</th>
          <th width="10%" class="text-right">Qty</th>
          <th width="10%" class="text-right">Price</th>
          <th width="10%" class="text-right">Disc.</th>
          <th width="12%" class="text-right">Amount</th>
          <th width="40"></th>
        </tr>
      </thead>
      <tbody id="tblPurchaseOrderDetails">
        <tr>
          <td colspan="8" class="text-center text-muted py-4">No products added.</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card mt-3">
  <div class="card-body">
    <div class="form-row">
      <div class="col-md-9"></div>
      <div class="col-md-3">
        <table class="table table-sm mb-3">
          <tr>
            <th>Total</th>
            <td id="lblTotal" class="text-right">0.00</td>
          </tr>
        </table>
        <button id="btnSavePurchaseOrder" class="btn btn-success btn-block">
          <i class="fas fa-save"></i> Save Purchase Order
        </button>
      </div>
    </div>
  </div>
</div>
