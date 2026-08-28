<section class="content">
  <div class="container-fluid">

    <?php /*** sales invoice details */ ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Sales Invoice Details
        </h3>
        <div class="ml-auto">
          <a href="<?= base_url('sales-invoices') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
          <button type="button" class="btn btn-sm btn-link" id="btnPostSalesInvoice" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-check mr-2"></i>Post</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCreateSalesReturn" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-exchange-alt mr-2"></i>Create Sales Return</button>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintSalesInvoice"><i class="fa fa-print mr-2"></i>Print</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCancelSalesInvoice" <?= !$isEditable ? 'disabled' : '' ?>><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-orange">
              <tr>
                <th width="40" class="text-center">#</th>
                <th width="170" class="text-center">Barcode</th>
                <th>Description</th>
                <th width="120" class="text-right">Available</th>
                <th width="120" class="text-right">Qty</th>
                <th width="80" class="text-center">UOM</th>
                <th width="120" class="text-right">Unit Price</th>
                <th width="120" class="text-center">Discount Type</th>
                <th width="120" class="text-right">Discount</th>
                <th width="130" class="text-right">Net Amt</th>
              </tr>
            </thead>

            <tbody id="tblSalesOrderDetails">
              <?php if (!empty($details)): ?>

                <?php foreach ($details as $index => $detail): ?>
                  <tr
                    class="so-detail-row"
                    data-product-id="<?= $detail->product_id ?>"
                    data-unit-price="<?= (float)($detail->unit_price ?? 0) ?>"
                    data-discount-amount="<?= (float)($detail->discount_amount ?? $detail->so_discount_amount ?? 0) ?>"
                    data-uom-id="<?= $detail->uom_id ?>"
                    data-base-uom-id="<?= $detail->base_uom_id ?>"
                    data-conversion-factor="<?= $detail->conversion_factor ?>"
                    data-sales-order-detail-id="<?= $detail->sales_order_detail_id ?>">
                    <td class="so-row-no text-center"><?= ($index + 1) ?>.</td>
                    <td>
                      <div class="input-group">
                        <label for="bc-<?= $index + 1 ?>"></label>
                        <input id="bc-<?= $index + 1 ?>" type="text" class="form-control form-control-sm so-barcode text-center" placeholder="Barcode" value="<?= htmlspecialchars($detail->barcode) ?>" readonly>
                      </div>
                    </td>
                    <td class="so-description"><?= htmlspecialchars($detail->description) ?></td>
                    <td class="so-available text-right"><?= number_format($detail->qty_available, 0) ?></td>
                    <td class="text-right">
                      <input type="number" step="any" class="form-control form-control-sm text-right so-qty" value="<?= number_format($detail->qty, 0) ?>" readonly>
                    </td>
                    <td class="so-uom text-center"><?= htmlspecialchars($detail->uom) ?></td>

                    <?php
                      $grossAmount = (float)$detail->qty * (float)$detail->unit_price;
                      $discountAmount = (float)($detail->discount_amount ?? $detail->so_discount_amount ?? 0);
                      $netAmount = $grossAmount - $discountAmount;
                      $discountType = $detail->discount_type ?? '';
                    ?>

                    <td class="text-right">
                      <?= number_format((float)$detail->unit_price, 2) ?>
                    </td>

                    <td class="text-center">
                      <?php if ($discountType === 'PERCENT'): ?>
                        Percent (%)
                      <?php elseif ($discountType === 'AMOUNT'): ?>
                        Amount
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>

                    <td class="text-right">
                      <?php if ($discountType === 'PERCENT'): ?>
                        <?= number_format(      (float)$detail->discount_percent, 2) ?>%
                      <?php elseif ($discountType === 'AMOUNT'): ?>
                        <?php if (isset($detail->discount_amount)): ?>
                          <?= number_format($discountAmount, 2) ?>
                        <?php else: ?>
                          <span class="text-muted">Calculated on Save</span>
                        <?php endif; ?>
                      <?php else: ?>
                        0.00
                      <?php endif; ?>
                    </td>

                    <td class="text-right so-net-amount">
                      <?= number_format($netAmount, 2) ?>
                    </td>

                  </tr>
                <?php endforeach; ?>

              <?php else: ?>

                <tr>
                  <td colspan="10" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    No items are available for invoicing from this Delivery Receipt.
                  </td>
                </tr>

              <?php endif; ?>

            </tbody>

          </table>
        </div>
      </div>
    </div>

    <?php /*** footer */ ?>
    <?php
      /**
       * VAT source:
       * EDIT  = saved SI snapshot
       * CREATE = source SO/DR header snapshot
       */
      $vatMode = $isEdit ? ($header->vat_mode ?? '') : ($header->vat_mode ?? '');
      $vatRate = $isEdit ? (float)($header->vat_rate ?? 0) : (float)($header->vat_rate ?? 0);
    ?>

    <div class="card mt-3 mb-3">
      <div class="card-body">
        <div class="form-row">
          <div class="col-md-8 d-flex align-items-end">

            <div class="alert alert-light font-sm mb-0" role="alert">
              <div class="font-weight-500 mb-1">
                <i class="fas fa-info-circle mr-1 text-info"></i>
                Sales Invoice Guide
              </div>

              <div>
                <span class="font-weight-500">1.</span>
                Review the products and quantities from the selected <span class="font-weight-500 text-success">Delivery Receipt</span>.
              </div>

              <div>
                <span class="font-weight-500">2.</span>
                Check the <span class="font-weight-500 text-danger">Available</span> quantity for each item before invoicing.
              </div>

              <div>
                <span class="font-weight-500">3.</span>
                Review the <span class="font-weight-500 text-danger">Qty</span>, <span class="font-weight-500 text-danger">UOM</span>, <span class="font-weight-500 text-danger">Unit Price</span>,
                and <span class="font-weight-500 text-danger">Discount</span> for each item.
              </div>

              <div>
                <span class="font-weight-500">4.</span>
                Verify the calculated <span class="font-weight-500 text-danger">Gross Amount</span>, <span class="font-weight-500 text-danger">Discount</span>,
                <span class="font-weight-500 text-danger">Subtotal</span>, <span class="font-weight-500 text-danger">VAT</span>, and <span class="font-weight-500 text-danger">Total</span>.
              </div>

              <div>
                <span class="font-weight-500">5.</span>
                Click <span class="font-weight-500 text-brown">Save Sales Invoice</span> after verifying all invoice details.
              </div>

              <div>
                <span class="font-weight-500">6.</span>
                Once verified, click <span class="font-weight-500 text-orange">Post</span> to finalize the Sales Invoice.
              </div>

              <div>
                <span class="font-weight-500">7.</span>
                After posting, click <span class="font-weight-500 text-olive">Create Sales Return</span> if invoiced items
                need to be returned by the customer.
              </div>
            </div>

          </div>

          <div class="col-md-4">

            <table class="table table-sm mb-3">
              <tbody>
                <tr>
                  <td>Gross Amount</td>
                  <td id="siGrossAmount" class="text-right">0.00</td>
                </tr>

                <tr>
                  <td>Less Discount</td>
                  <td id="siDiscountAmount" class="text-right">0.00</td>
                </tr>

                <tr>
                  <td>Subtotal</td>
                  <td id="siSubtotal" class="text-right">
                    <?= $isEdit ? number_format((float)($header->subtotal ?? 0), 2) : '0.00' ?>
                  </td>
                </tr>

                <tr>
                  <td>
                    VAT
                    <span id="siVatRateLabel">
                      <?= number_format($vatRate, 2) ?>%
                    </span>
                  </td>

                  <td id="siVatAmount" class="text-right">
                    <?= $isEdit ? number_format((float)($header->vat_amount ?? 0), 2) : '0.00' ?>
                  </td>
                </tr>

                <tr class="font-weight-500">
                  <td>TOTAL</td>
                  <td id="siTotalAmount" class="text-right">
                    <?= $isEdit ? number_format((float)($header->total_amount ?? 0), 2) : '0.00' ?>
                  </td>
                </tr>
              </tbody>
            </table>

            <button id="btnSaveSalesInvoice" class="btn btn-default btn-sm btn-block" <?= !$isEditable ? 'disabled' : '' ?>>Save Sales Invoice</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
  window.salesInvoiceId = <?= (int) ($salesInvoiceId ?? 0); ?>;
  window.status = '<?= isset($salesInvoiceId) ? $header->status : ''; ?>';
  window.salesInvoiceVatMode = '<?= htmlspecialchars($vatMode) ?>';
  window.salesInvoiceVatRate = <?= (float)$vatRate ?>;
</script>