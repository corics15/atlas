<section class="content">
  <div class="container-fluid">

    <?php /*** stock details */ ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Stock Details
        </h3>
        <div class="ml-auto">
          <a href="<?= base_url('stock-transfers') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
          <button type="button" class="btn btn-sm btn-link" id="btnPostStockTransfer"><i class="fa fa-check mr-2"></i>Post</button>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintStockTransfer"><i class="fa fa-print mr-2"></i>Print</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCancelStockTransfer"><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-orange">
              <tr>
                <th width="40" class="text-center">#</th>
                <th width="170" class="text-center">Scan/Input Barcode</th>
                <th>Description</th>
                <th width="120" class="text-right">Available</th>
                <th width="120" class="text-right">Transfer Qty</th>
                <th width="80" class="text-center">UOM</th>
                <th width="40"></th>
              </tr>
            </thead>

            <tbody id="tblStockTransferDetails">
              <?php if (!empty($details)): ?>

                <?php foreach ($details as $index => $detail): ?>
                  <tr
                    class="st-detail-row"
                    data-product-id="<?= $detail->product_id ?>">
                    <td class="st-row-no text-center">
                      <?= ($index + 1) ?>.
                    </td>
                    <td>
                      <div class="input-group">
                        <input type="text" class="form-control form-control-sm st-barcode atlas-barcode text-center" placeholder="Barcode" value="<?= htmlspecialchars($detail->barcode) ?>">
                        <div class="input-group-append">
                          <button type="button" class="btn btn-sm btn-outline-warning btn-product-finder">
                          <i class="fas fa-search font-smr"></i>
                          </button>
                        </div>
                      </div>
                    </td>
                    <td class="st-description">
                      <?= htmlspecialchars($detail->description) ?>
                    </td>
                    <td class="st-available text-right">
                      <?= number_format($detail->qty_on_hand, 0) ?>
                    </td>
                    <td class="text-right">
                      <input type="number" step="any" class="form-control form-control-sm text-right st-qty" value="<?= number_format($detail->qty) ?>">
                    </td>
                    <td class="st-uom text-center">
                      <?= htmlspecialchars($detail->uom) ?>
                    </td>
                    <td class="text-center">
                      <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
                    </td>
                  </tr>
                <?php endforeach; ?>

                <?php else: ?>

                <tr class="st-detail-row">
                  <td class="st-row-no text-center">
                    1.
                  </td>
                  <td>
                    <div class="input-group">
                      <input type="text" class="form-control form-control-sm st-barcode atlas-barcode text-center" placeholder="Barcode">
                      <div class="input-group-append">
                        <button type="button" class="btn btn-sm btn-outline-warning btn-product-finder">
                        <i class="fas fa-search font-smr"></i>
                        </button>
                      </div>
                    </div>
                  </td>
                  <td class="st-description"></td>
                  <td class="st-available text-right">0</td>
                  <td class="text-right">
                    <input type="number" step="any" class="form-control form-control-sm text-right st-qty" value="">
                  </td>
                  <td class="st-uom text-center"></td>
                  <td class="text-center">
                    <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
                  </td>
                </tr>

              <?php endif; ?>
            </tbody>

          </table>
        </div>
      </div>
    </div>

    <?php /*** footer */ ?>
    <div class="card mt-3 mb-3">
      <div class="card-body">
        <div class="form-row">
          <div class="align-items-end col-md-9 d-flex">

            <div class="alert alert-light font-sm mb-0" role="alert">
              <div class="font-weight-500 mb-1">
                <i class="fas fa-info-circle mr-1 text-info"></i>
                Stock Transfer Guide
              </div>

              <div>
                <span class="font-weight-500">1.</span>
                Select the <span class="font-weight-500 text-danger">Source Branch</span> and <span class="font-weight-500 text-danger">Destination Branch</span>.
                The source and destination must be different.
              </div>

              <div>
                <span class="font-weight-500">2.</span>
                Scan or enter the product <span class="font-weight-500">Barcode</span> and press <span class="font-weight-500 text-success">Enter</span>,
                press <span class="font-weight-500 text-success">F2</span>, or click the <i class="fas fa-search text-brown"></i> button to open the Product Finder.
              </div>

              <div>
                <span class="font-weight-500">3.</span>
                Check the product's <span class="font-weight-500 text-danger">Available</span> quantity at the Source Branch.
              </div>

              <div>
                <span class="font-weight-500">4.</span>
                Enter the <span class="font-weight-500 text-danger">Transfer Qty</span>.
                The quantity must be greater than zero and must not exceed the available stock.
              </div>

              <div>
                <span class="font-weight-500">5.</span>
                Click <span class="font-weight-500 text-brown">Save Stock Transfer</span> after verifying the branches, products, and quantities.
              </div>

              <div>
                <span class="font-weight-500">6.</span>
                Once verified, click <span class="font-weight-500 text-orange">Post</span> to update the inventory quantities.
                Posting cannot be undone.
              </div>
            </div>

          </div>
          <div class="col-md-3">
            <button id="btnSaveStockTransfer" class="btn btn-default btn-sm btn-block">Save Stock Transfer</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
  window.stockTransferId = <?= (int) ($stockTransferId ?? 0); ?>;
</script>