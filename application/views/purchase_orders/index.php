<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">

      <?php //*** Purchase Order */ ?>
      <div class="card-header">

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Order Information
          </h3>

          <?php /*** status class set in purchase_orders.js */ ?>
          <div class="ls-wider" style="font-weight:500"></div>
        </div>
      </div>

      <?php /*** header */ ?>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">

            <input type="hidden" id="txtPONo" value="">
            <table class="table table-sm table-borderless">
              <tr>
                <th width="180">PO No.</th>
                <td class="font-weight-500 text-brown" id="tdRefNo">AUTO-GENERATED</td>
              </tr>
              <tr>
                <th>PO Date</th>
                <td>
                  <input type="date" id="txtPODate" class="form-control form-control-sm w-auto" value="<?= date('Y-m-d'); ?>">
                </td>
              </tr>
              <tr>
                <th>Supplier</th>
                <td>
                  <select id="selSupplier" class="form-control form-control-sm w-auto">
                    <option value="">Select Supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                      <option value="<?= $supplier->id; ?>" data-terms-id="<?= $supplier->terms_id; ?>">
                        <?= htmlspecialchars($supplier->supplier_name); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <tr>
                <th>Terms</th>
                <td>
                  <select id="selTerms" class="form-control form-control-sm">
                    <option value="">Select Terms</option>
                    <?php foreach ($terms as $term): ?>
                      <option value="<?= $term->id; ?>">
                        <?= htmlspecialchars($term->terms_name); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <tr>
                <th>Remarks</th>
                <td>
                  <input type="text" id="txtRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks">
                </td>
              </tr>
            </table>

          </div>
        </div>
      </div>

    </div>

    <?php /*** order details */ ?>
    <div class="card mt-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Order Details</h3>
        <div class="ml-auto">
          <a href="<?= base_url('purchase-orders/list') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
          <button type="button" class="btn btn-sm btn-link" id="btnReceiveGoods"><i class="fa fa-dolly mr-2"></i>Receive Goods</button>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintPurchaseOrder"><i class="fa fa-print mr-2"></i>Print</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCancelPurchaseOrder"><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
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
                      <input type="text" class="form-control form-control-sm po-barcode atlas-barcode" placeholder="Barcode">
                      <div class="input-group-append">
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-warning btn-product-finder">
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
          </div>
        </div>

        <div class="form-row">
          <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
              <div class="col-md-9"></div>
              <div class="col-md-3">
                <button id="btnSavePurchaseOrder" class="btn btn-default btn-sm btn-block">Save Purchase Order</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  window.purchaseOrderId = <?= (int) ($purchaseOrderId ?? 0); ?>;
</script>