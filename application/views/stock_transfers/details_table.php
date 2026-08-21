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
                        <input
                          type="text"
                          class="form-control form-control-sm st-barcode atlas-barcode"
                          placeholder="Barcode"
                          value="<?= htmlspecialchars($detail->barcode) ?>">
                        <div class="input-group-append">
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-warning btn-product-finder">
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
                      <input
                        type="number"
                        step="any"
                        class="form-control form-control-sm text-right st-qty"
                        value="<?= number_format($detail->qty) ?>">
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
                      <input
                        type="text"
                        class="form-control form-control-sm st-barcode atlas-barcode"
                        placeholder="Barcode">
                      <div class="input-group-append">
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-warning btn-product-finder">
                        <i class="fas fa-search font-smr"></i>
                        </button>
                      </div>
                    </div>
                  </td>
                  <td class="st-description"></td>
                  <td class="st-available text-right">0</td>
                  <td class="text-right">
                    <input
                      type="number"
                      step="any"
                      class="form-control form-control-sm text-right st-qty"
                      value="">
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
          <div class="col-md-9"></div>
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