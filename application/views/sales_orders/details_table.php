<section class="content">
  <div class="container-fluid">

    <?php /*** sales order details */ ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Sales Order Details
        </h3>
        <div class="ml-auto">
          <a href="<?= base_url('sales-orders') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
          <button type="button" class="btn btn-sm btn-link" id="btnPostSalesOrder" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-check mr-2"></i>Post</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCreateDeliveryReceipt" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-truck mr-2"></i>Create Delivery Receipt</button>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintSalesOrder"><i class="fa fa-print mr-2"></i>Print</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCancelSalesOrder" <?= !$isEditable ? 'disabled' : '' ?>><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-orange">
              <tr>
                <!-- <th class="text-center">#</th>
                <th class="text-center" width="13%">Scan/Input Barcode</th>
                <th width="15%">Description</th>
                <th class="text-center">UOM</th>
                <th class="text-right">Available</th>
                <th class="text-right">Fulfilled</th>
                <th class="text-right">Remaining</th>
                <th class="text-right" width="6%">Qty</th>

                <th class="text-right" width="8%">Unit Price</th>
                <th class="text-center" width="8%">Discount Type</th>
                <th class="text-right" width="8%">Discount</th>
                <th class="text-right" width="8%">Net Amt</th>

                <th></th> -->
                <th class="text-center">#</th>
                <th class="text-center">Scan/Input Barcode</th>
                <th>Description</th>
                <th class="text-center">UOM</th>
                <th class="text-right">Available</th>
                <th class="text-right">Fulfilled</th>
                <th class="text-right">Remaining</th>
                <th class="text-right">Qty</th>

                <th class="text-right">Unit Price</th>
                <th class="text-center">Discount Type</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Net Amt</th>

                <th></th>
              </tr>
            </thead>

            <tbody id="tblSalesOrderDetails">
              <?php if (!empty($details)): ?>

                <?php foreach ($details as $index => $detail): ?>
                  <tr
                    class="so-detail-row"
                    data-product-id="<?= $detail->product_id ?>"
                    data-base-uom-id="<?= $detail->base_uom_id ?>"
                    data-conversion-factor="<?= $detail->conversion_factor ?>"
                    data-base-qty-available="<?= $detail->qty_available ?>">

                    <?php /*** numbering */ ?>
                    <td class="so-row-no text-center"><?= ($index + 1) ?>.</td>

                    <?php /*** barcode */ ?>
                    <td>
                      <div class="input-group">
                        <input type="text" class="form-control form-control-sm so-barcode atlas-barcode text-center" placeholder="Barcode"
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

                    <?php /*** description */ ?>
                    <td class="so-description" <?= mb_strlen(htmlspecialchars($detail->description)) > 30 ? 'data-toggle="tooltip" title="'.htmlspecialchars($detail->description).'"' : '' ?>>
                      <?php
                        $description = htmlspecialchars($detail->description);
                        echo (mb_strlen($description) > 30)
                          ? mb_strimwidth($description, 0, 30, '...')
                          : $description;
                      ?>
                    </td>

                    <?php /*** UOM */ ?>
                    <td class="text-right">
                      <select class="form-control form-control-sm so-uom custom-select w-auto">
                        <option value="">Select...</option>
                        <?php foreach ($uoms as $uom): ?>
                          <option
                            value="<?= $uom->id; ?>"
                            <?= ((int)$detail->uom_id === (int)$uom->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($uom->uom); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </td>

                    <?php
                      /*** available qty */
                      $conversionFactor = (float) $detail->conversion_factor;
                      $availableQty = $conversionFactor > 0 ? ((float) $detail->qty_available / $conversionFactor) : 0;
                    ?>
                    <td class="so-available text-right"><?= number_format($availableQty, 2) ?></td>

                    <?php /*** fullfilled */ ?>
                    <td class="text-right"><?= number_format($detail->qty_fulfilled, 0) ?></td>

                    <?php /*** remaining */ ?>
                    <td class="text-right <?= ($detail->qty_remaining == 0) ? 'text-success font-weight-500' : '' ?>" <?= ($detail->qty_remaining == 0) ? 'title="Fully Invoiced"' : '' ?>>
                      <?= number_format($detail->qty_remaining, 0) ?>
                    </td>

                    <?php /*** qty */ ?>
                    <td class="text-right">
                      <input type="number" step="any" class="form-control form-control-sm text-right so-qty"
                        value="<?= number_format($detail->qty) ?>">
                    </td>

                    <?php /*** unit price */ ?>
                    <td>
                      <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right so-unit-price"
                        value="<?= number_format((float)($detail->unit_price ?? 0), 2, '.', '') ?>">
                    </td>

                    <?php /*** discount type selection */ ?>
                    <td>
                      <select class="form-control form-control-sm so-discount-type custom-select">
                        <option value="">No Discount</option>
                        <option value="PERCENT"
                          <?= (($detail->discount_type ?? '') === 'PERCENT') ? 'selected' : '' ?>>
                          Percent (%)
                        </option>
                        <option value="AMOUNT"
                          <?= (($detail->discount_type ?? '') === 'AMOUNT') ? 'selected' : '' ?>>
                          Amount
                        </option>
                      </select>
                    </td>

                    <?php /*** discount amount or percentage */ ?>
                    <td>
                      <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right so-discount-value"
                        value="<?=
                          (($detail->discount_type ?? '') === 'PERCENT')
                            ? number_format((float)($detail->discount_percent ?? 0), 2, '.', '')
                            : number_format((float)($detail->discount_amount ?? 0), 2, '.', '')
                        ?>">
                    </td>

                    <?php /*** net amount */ ?>
                    <td class="so-net-amount text-right">0.00</td>

                    <?php /*** delete row button */ ?>
                    <td class="text-center">
                      <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
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
                      <input type="text" class="form-control form-control-sm so-barcode atlas-barcode" placeholder="Barcode">
                      <div class="input-group-append">
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-warning btn-product-finder">
                        <i class="fas fa-search font-smr"></i>
                        </button>
                      </div>
                    </div>
                  </td>

                  <?php /*** description */ ?>
                  <td class="so-description" data-toggle="tooltip"></td>

                  <?php /*** UOM */ ?>
                  <td class="text-right">
                    <select class="form-control form-control-sm so-uom custom-select w-auto">
                      <option value="">Select...</option>
                      <?php foreach ($uoms as $uom): ?>
                        <option value="<?= $uom->id; ?>">
                          <?= htmlspecialchars($uom->uom); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>

                  <?php /*** available */ ?>
                  <td class="so-available text-right">-</td>

                  <?php /*** fulfilled */ ?>
                  <td></td>

                  <?php /*** remaining */ ?>
                  <td></td>

                  <?php /*** qty */ ?>
                  <td class="text-right">
                    <input type="number" step="any" class="form-control form-control-sm text-right so-qty" value="">
                  </td>

                  <?php /*** unit price */ ?>
                  <td>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right so-unit-price" value="0.00">
                  </td>

                  <?php /*** discount type selection */ ?>
                  <td>
                    <select class="form-control form-control-sm so-discount-type custom-select">
                      <option value="">No Discount</option>
                      <option value="PERCENT">Percent (%)</option>
                      <option value="AMOUNT">Amount</option>
                    </select>
                  </td>

                  <?php /*** discount value */ ?>
                  <td>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right so-discount-value" value="0.00" disabled>
                  </td>

                  <?php /*** net amount */ ?>
                  <td class="so-net-amount text-right">0.00</td>

                  <td class="text-center"><i class="fas fa-trash text-danger pointer btn-delete-row"></i></td>
                </tr>

              <?php endif; ?>
            </tbody>

          </table>
        </div>
      </div>
    </div>

    <?php /*** footer, total computation */ ?>
    <div class="card mt-3 mb-3">
      <div class="card-body">

        <div class="form-row">
          <div class="col-md-8"></div>
          <div class="col-md-4">
            <table class="table table-sm mb-3">
              <tbody>
                <tr>
                  <td>Gross Amount</td>
                  <td id="soGrossAmount" class="text-right">0.00</td>
                </tr>

                <tr>
                  <td>Less Discount</td>
                  <td id="soDiscountAmount"class="text-right">0.00</td>
                </tr>

                <tr>
                  <td>Subtotal</td>
                  <td id="soSubtotal" class="text-right"><?= isset($salesOrder) ? number_format((float)$salesOrder->subtotal, 2) : '0.00' ?></td>
                </tr>

                <tr>
                  <td>
                    VAT
                    <span id="soVatRateLabel">
                      <?= isset($salesOrder)
                        ? number_format((float)$salesOrder->vat_rate, 2)
                        : '0.00' ?>%
                    </span>
                  </td>
                  <td
                    id="soVatAmount"
                    class="text-right">
                    <?= isset($salesOrder)
                      ? number_format((float)$salesOrder->vat_amount, 2)
                      : '0.00' ?>
                  </td>
                </tr>

                <tr class="font-weight-500">
                  <td>TOTAL</td>
                  <td
                    id="soTotalAmount"
                    class="text-right">
                    <?= isset($salesOrder)
                      ? number_format((float)$salesOrder->total_amount, 2)
                      : '0.00' ?>
                  </td>
                </tr>

              </tbody>
            </table>

            <button id="btnSaveSalesOrder" class="btn btn-default btn-sm btn-block" <?= !$isEditable ? 'disabled' : '' ?>>Save Sales Order</button>
          </div>
        </div>

      </div>
    </div>

  </div>
</section>

<script>
  window.salesOrderId = <?= (int) ($salesOrderId ?? 0); ?>;
  window.status = '<?= isset($salesOrder) ? $salesOrder->status : ''; ?>';
  window.remainingItems = <?= isset($salesOrder) ? (int) $salesOrder->remaining_items : 0; ?>;
  window.atlasUoms = <?= json_encode(
    array_map(function ($uom) {
      return [
        'id' => (int) $uom->id,
        'uom' => $uom->uom
      ];
    }, $uoms),
  ); ?>;

  window.salesOrderVatMode =  '<?= isset($salesOrder) ? $salesOrder->vat_mode : ($vatMode ?? 'INCLUSIVE'); ?>';
  window.salesOrderVatRate =  <?= isset($salesOrder) ? (float)$salesOrder->vat_rate : (float)($vatRate ?? 12); ?>;
</script>