<div class="container-fluid mt-4">

  <!-- Purchase Order Header -->
  <div class="card">
    <div class="card-body">
      <form id="frmPurchaseOrder">

        <!-- Row 1: PO No + Date -->
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="txtPoNo">PO No.</label>
            <input type="text" id="txtPoNo" name="po_no" class="form-control form-control-sm">
          </div>
          <div class="form-group col-md-6">
            <label for="txtDate">Date</label>
            <input type="date" id="txtDate" name="date" class="form-control form-control-sm">
          </div>
        </div>

        <!-- Row 2: Customer + Salesman -->
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="selCustomer">Customer</label>
            <select id="selCustomer" name="customer_id" class="form-control form-control-sm">
              <option value="">Select Customer</option>
              <!-- Populate dynamically -->
            </select>
          </div>
          <div class="form-group col-md-6">
            <label for="selSalesman">Salesman</label>
            <select id="selSalesman" name="salesman_id" class="form-control form-control-sm">
              <option value="">Select Salesman</option>
              <!-- Populate dynamically -->
            </select>
          </div>
        </div>

        <!-- Row 3: Terms + Remarks -->
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="txtTerms">Terms</label>
            <input type="text" id="txtTerms" name="terms" class="form-control form-control-sm" placeholder="e.g. Net 30">
          </div>
          <div class="form-group col-md-6">
            <label for="txtRemarks">Remarks</label>
            <input type="text" id="txtRemarks" name="remarks" class="form-control form-control-sm" placeholder="Optional notes">
          </div>
        </div>

      </form>
    </div>
  </div>

  <!-- Products Section -->
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0">Products</h6>
      <button type="button" id="btnAddProduct" class="btn btn-sm btn-success">
        + Add Product
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="thead-light">
            <tr>
              <th>Barcode</th>
              <th>Description</th>
              <th>UOM</th>
              <th class="text-right">Qty</th>
              <th class="text-right">Price</th>
              <th class="text-right">Disc</th>
              <th class="text-right">Amount</th>
            </tr>
          </thead>
          <tbody id="tblProductsBody">
            <!-- Product rows injected dynamically -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Footer Section -->
  <div class="card mt-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Total Amount: <span id="lblTotalAmount">0.00</span></h5>
      <button type="submit" form="frmPurchaseOrder" id="btnSavePo" class="btn btn-primary btn-sm">
        Save
      </button>
    </div>
  </div>

</div>
