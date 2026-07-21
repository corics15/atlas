<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <?php //*** Purchase Order */ ?>
      <div class="card-header">
        <h3 class="card-title">Order Header</h3>
      </div>

      <div class="card-body">
        <?php //*** PO Info + Supplier */ ?>
        <div class="form-row">

          <div class="form-group col-md-3">
            <label for="txtPONo">PO No.</label>
            <input type="text" id="txtPONo" class="form-control form-control-sm" value="AUTO-GENERATED" readonly>
          </div>

          <div class="form-group col-md-3">
            <label for="txtPODate">Date</label>
            <input type="date" id="txtPODate" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>">
          </div>

          <div class="form-group col-md-6">
            <label for="selSupplier">Supplier</label>
            <select id="selSupplier" class="form-control form-control-sm">
              <option value="">Select Supplier</option>
              <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= $supplier->id; ?>" data-terms-id="<?= $supplier->terms_id; ?>">
                  <?= htmlspecialchars($supplier->supplier_name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>

        <?php //*** Terms + Credit Limit + Remarks */ ?>
        <div class="form-row">
          <div class="form-group col-md-3">
            <label for="selTerms">Terms</label>
            <select id="selTerms" class="form-control form-control-sm">
              <option value="">Select Terms</option>
              <?php foreach ($terms as $term): ?>
                <option value="<?= $term->id; ?>">
                  <?= htmlspecialchars($term->terms_name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group col-md-9">
            <label for="txtRemarks">Remarks</label>
            <input type="text" id="txtRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks">
          </div>
        </div>
      </div>
    </div>

    <?php /*** order details */ ?>
    <div class="card mt-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Order Details</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive table-scroll">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-orange">
              <tr>
                <th width="170">Scan/Input Barcode</th>
                <th width="150">Supplier</th>
                <th>Product Description</th>
                <th width="70" class="text-center">UOM</th>
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
                  <td class="po-supplier"></td>
                  <td class="po-description"></td>
                  <td class="po-uom text-center"></td>
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

    <?php /*** footer */ ?>
    <div class="card mt-3">
      <div class="card-body">
        <div class="form-row">
          <div class="col-md-9"></div>
          <div class="col-md-3">
            <table class="table table-sm mb-3">
              <tr>
                <td class="total-text">Total</td>
                <td id="lblTotal" class="text-right">0.00</td>
              </tr>
            </table>
            <button id="btnSavePurchaseOrder" class="btn btn-outline-success btn-block">Save Purchase Order</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  window.purchaseOrderId = <?= (int) ($purchaseOrderId ?? 0); ?>;
</script>