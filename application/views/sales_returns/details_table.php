<section class="content">
  <div class="container-fluid">

      <?php /*** sales invoice details */ ?>
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Sales Return Details
          </h3>
          <div class="ml-auto">
            <a href="<?= base_url('sales-returns') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
            <button type="button" class="btn btn-sm btn-link" id="btnPostSalesReturn" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-check mr-2"></i>Post</button>
            <button type="button" class="btn btn-sm btn-link" id="btnPrintSalesReturn"><i class="fa fa-print mr-2"></i>Print</button>
            <button type="button" class="btn btn-sm btn-link" id="btnCancelSalesReturn" <?= !$isEditable ? 'disabled' : '' ?>><i class="fas fa-ban mr-2"></i>Cancel</button>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive table-scroll">
            <table class="table table-sm table-hover mb-0">
              <thead class="thead-orange">
                <tr>
                  <th width="40" class="text-center">#</th>
                  <th width="170" class="text-center">Barcode</th>
                  <th>Description</th>
                  <th width="120" class="text-right">Available</th>
                  <th width="120" class="text-right">Qty</th>
                  <th width="80" class="text-center">UOM</th>
                  <th width="40"></th>
                </tr>
              </thead>

              <tbody id="tblSalesReturnDetails">
                <?php if (!empty($details)): ?>

                  <?php foreach ($details as $index => $detail): ?>
                    <tr
                      class="so-detail-row"
                      data-product-id="<?= $detail->product_id ?>"
                      data-sales-invoice-detail-id="<?= $detail->sales_invoice_detail_id ?>">
                      <td class="so-row-no text-center">
                        <?= ($index + 1) ?>.
                      </td>
                      <td>
                        <div class="input-group">
                          <label for="bc-<?= $index + 1 ?>"></label>
                          <input
                            id="bc-<?= $index + 1 ?>"
                            type="text"
                            class="form-control form-control-sm so-barcode"
                            placeholder="Barcode"
                            value="<?= htmlspecialchars($detail->barcode) ?>" readonly>
                          <div class="input-group-append">
                            <button
                              type="button"
                              class="btn btn-sm btn-outline-muted btn-product-finder no-event">
                            <i class="fas fa-search font-smr"></i>
                            </button>
                          </div>
                        </div>
                      </td>
                      <td class="so-description">
                        <?= htmlspecialchars($detail->description) ?>
                      </td>
                      <td class="so-available text-right">
                        <?= number_format($detail->qty_available, 0) ?>
                      </td>
                      <td class="text-right">
                        <input
                          type="number"
                          step="any"
                          class="form-control form-control-sm text-right so-qty"
                          value="<?= number_format($detail->qty, 0) ?>">
                      </td>
                      <td class="so-uom text-center">
                        <?= htmlspecialchars($detail->uom) ?>
                      </td>
                      <td class="text-center">
                        <i class="fas fa-trash text-muted pointer btn-delete-row no-event"></i>
                      </td>
                    </tr>
                  <?php endforeach; ?>

                <?php else: ?>

                  <tr class="so-detail-row">
                    <td class="so-row-no text-center">
                      1.
                    </td>
                    <td>
                      <div class="input-group">
                        <input
                          type="text"
                          class="form-control form-control-sm so-barcode"
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
                    <td class="so-description"></td>
                    <td class="so-available text-right">-</td>
                    <td class="text-right">
                      <input
                        type="number"
                        step="any"
                        class="form-control form-control-sm text-right so-qty"
                        value="">
                    </td>
                    <td class="so-uom text-center"></td>
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
              <button id="btnSaveSalesReturn" class="btn btn-default btn-sm btn-block" <?= !$isEditable ? 'disabled' : '' ?>>Save Sales Return</button>
            </div>
          </div>
        </div>
      </div>

  </div>
</section>

<input type="hidden" id="selCustomer" value="<?= $salesInvoice->customer_id ?>">
<input type="hidden" id="selSalesman" value="<?= $salesInvoice->salesman_id ?>">
<input type="hidden" id="selTerms" value="<?= $salesInvoice->terms_id ?>">
<input type="hidden" id="txtCreditLimit" value="<?= $salesInvoice->credit_limit ?>">
<script>
  window.salesReturnId = <?= $isEdit ? $header->id : 0 ?>;
  window.status = '<?= $isEdit ? $header->status : '' ?>';
</script>