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
                  <th class="text-center" width="3%">#</th>
                  <th class="text-center"  width="13%">Barcode</th>
                  <th width="30%">Description</th>
                  <th class="text-right" width="6%">Available</th>
                  <th class="text-right" width="6%">Qty</th>
                  <th class="text-center" width="6%">UOM</th>
                  <th class="text-right" width="8%">Unit Price</th>
                  <th class="text-center" width="8%">Discount Type</th>
                  <th class="text-right" width="6%">Discount</th>
                  <th width="130" class="text-right" width="12%">Net Amount</th>
                  <th width="2%"></th>
                </tr>
              </thead>

              <tbody id="tblSalesReturnDetails">
                <?php if (!empty($details)): ?>

                  <?php foreach ($details as $index => $detail): ?>
                    <tr
                      class="so-detail-row"
                      data-product-id="<?= $detail->product_id ?>"
                      data-uom-id="<?= $detail->uom_id ?>"
                      data-base-uom-id="<?= $detail->base_uom_id ?>"
                      data-conversion-factor="<?= $detail->conversion_factor ?>"
                      data-sales-invoice-detail-id="<?= $detail->sales_invoice_detail_id ?>"
                      data-unit-price="<?= (float)($detail->unit_price ?? 0) ?>"
                      data-discount-amount="<?= (float)(
                        $detail->discount_amount
                        ?? (
                          ($detail->discount_type ?? '') === 'PERCENT'
                            ? round(
                                (float)$detail->qty *
                                (float)$detail->unit_price *
                                ((float)$detail->discount_percent / 100),
                                2
                              )
                            : 0
                        )
                      ) ?>">
                      <?php /*** numbering */ ?>
                      <td class="so-row-no text-center">
                        <?= ($index + 1) ?>.
                      </td>

                      <?php /*** barcode */ ?>
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

                      <?php /*** description */ ?>
                      <td class="so-description">
                        <?php
                          $description = htmlspecialchars($detail->description);
                          echo (mb_strlen($description) > 30)
                            ? mb_strimwidth($description, 0, 30, '...')
                            : $description;
                        ?>
                      </td>

                      <?php /*** available */ ?>
                      <td class="so-available text-right">
                        <?= number_format($detail->qty_available, 0) ?>
                      </td>

                      <?php /*** qty */ ?>
                      <td class="text-right">
                        <input type="number" step="any" class="form-control form-control-sm text-right so-qty" value="<?= number_format($detail->qty, 0) ?>">
                      </td>

                      <?php /*** uom */ ?>
                      <td class="so-uom text-center">
                        <?= htmlspecialchars($detail->uom) ?>
                      </td>

                      <?php
                        /*** unit price */
                        $unitPrice = (float)($detail->unit_price ?? 0);
                        $discountType = $detail->discount_type ?? '';

                        /*** saved SR already has authoritative discount amount */
                        if (isset($detail->discount_amount)) {
                          $discountAmount = (float)$detail->discount_amount;
                          $hasSavedDiscount = TRUE;
                        } else {
                          /**
                           * New SR:
                           * percentage can be previewed exactly.
                           * fixed amount is allocated authoritatively during save.
                           */
                          $hasSavedDiscount = FALSE;

                          if ($discountType === 'PERCENT') {
                            $discountAmount = round((float)$detail->qty * $unitPrice * ((float)$detail->discount_percent / 100), 2);
                          } else {
                            $discountAmount = 0;
                          }
                        }
                        $grossAmount = (float)$detail->qty * $unitPrice;
                        $netAmount = $grossAmount - $discountAmount;
                      ?>

                      <td class="text-right">
                        <?= number_format($unitPrice, 2) ?>
                      </td>

                      <?php /*** discount type */ ?>
                      <td class="text-center">
                        <?php if ($discountType === 'PERCENT'): ?>
                          Percent (%)
                        <?php elseif ($discountType === 'AMOUNT'): ?>
                          Amount
                        <?php else: ?>
                          -
                        <?php endif; ?>
                      </td>

                      <?php /*** discount */ ?>
                      <td class="text-right">
                        <?php if ($discountType === 'PERCENT'): ?>
                          <?= number_format((float)$detail->discount_percent, 2) ?>%
                        <?php elseif ($discountType === 'AMOUNT'): ?>
                          <?php if ($hasSavedDiscount): ?>
                            <?= number_format($discountAmount, 2) ?>
                          <?php else: ?>
                            <span class="text-muted">
                              Calculated on Save
                            </span>
                          <?php endif; ?>
                        <?php else: ?>
                          0.00
                        <?php endif; ?>
                      </td>

                      <?php /*** net amt */ ?>
                      <td class="text-right sr-net-amount">
                        <?php if ($discountType === 'AMOUNT' && !$hasSavedDiscount): ?>
                          -
                        <?php else: ?>
                          <?= number_format($netAmount, 2) ?>
                        <?php endif; ?>
                      </td>

                      <?php /*** delete */ ?>
                      <td class="text-center">
                        <i class="fas fa-trash text-muted pointer btn-delete-row no-event"></i>
                      </td>
                    </tr>
                  <?php endforeach; ?>

                <?php else: ?>

                  <tr class="so-detail-row">
                    <?php /*** numbering */ ?>
                    <td class="so-row-no text-center">1.</td>

                    <?php /*** barcode */ ?>
                    <td>
                      <div class="input-group">
                        <input type="text" class="form-control form-control-sm so-barcode" placeholder="Barcode">
                        <div class="input-group-append">
                          <button type="button" class="btn btn-sm btn-outline-warning btn-product-finder">
                          <i class="fas fa-search font-smr"></i>
                          </button>
                        </div>
                      </div>
                    </td>

                    <?php /*** description */ ?>
                    <td class="so-description"></td>

                    <?php /*** available */ ?>
                    <td class="so-available text-right">-</td>

                    <?php /*** numbering */ ?>
                    <td class="text-right">
                      <input type="number" step="any" class="form-control form-control-sm text-right so-qty" value="">
                    </td>

                    <?php /*** uom */ ?>
                    <td class="so-uom text-center"></td>

                    <?php /*** unit price */ ?>
                    <td class="text-right">0.00</td>

                    <?php /*** discount type */ ?>
                    <td class="text-center">-</td>

                    <?php /*** discount */ ?>
                    <td class="text-right">0.00</td>

                    <?php /*** net amt */ ?>
                    <td class="text-right">0.00</td>

                    <?php /*** delete */ ?>
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

      <?php
        /***
         * CREATE = source SI VAT snapshot
         * EDIT   = saved SR VAT snapshot
         */
        $vatMode = $isEdit ? ($header->vat_mode ?? '') : ($salesInvoice->vat_mode ?? '');
        $vatRate = $isEdit ? (float)($header->vat_rate ?? 0) : (float)($salesInvoice->vat_rate ?? 0);
      ?>

      <?php /*** footer */ ?>
      <div class="card mt-3 mb-3">
        <div class="card-body">
          <div class="form-row">
            <div class="col-md-8"></div>
            <div class="col-md-4">

              <table class="table table-sm mb-3">
                <tbody>
                  <tr>
                    <td>Gross Amount</td>
                    <td id="srGrossAmount" class="text-right">0.00</td>
                  </tr>

                  <tr>
                    <td>Less Discount</td>
                    <td id="srDiscountAmount" class="text-right">0.00</td>
                  </tr>

                  <tr>
                    <td>Subtotal</td>
                    <td id="srSubtotal" class="text-right">0.00</td>
                  </tr>

                  <tr>
                    <td>
                      VAT
                      <span id="srVatRateLabel">
                        <?= number_format($vatRate, 2) ?>%
                      </span>
                    </td>

                    <td id="srVatAmount" class="text-right">0.00</td>
                  </tr>

                  <tr class="font-weight-500">
                    <td>TOTAL</td>
                    <td id="srTotalAmount" class="text-right">0.00</td>
                  </tr>

                </tbody>
              </table>

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
  window.salesReturnVatMode = '<?= htmlspecialchars($vatMode) ?>';
  window.salesReturnVatRate = <?= (float)$vatRate ?>;
</script>