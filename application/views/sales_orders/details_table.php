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
        <div class="table-responsive table-scroll">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-orange">
              <tr>
                <th width="40" class="text-center">#</th>
                <th width="170" class="text-center">Scan/Input Barcode</th>
                <th>Description</th>
                <th width="80" class="text-center">UOM</th>
                <th width="120" class="text-right">Available</th>
                <th width="120" class="text-right">Fulfilled</th>
                <th width="120" class="text-right">Remaining</th>
                <th width="120" class="text-right">Qty</th>
                <th width="40"></th>
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
                    <td class="so-row-no text-center">
                      <?= ($index + 1) ?>.
                    </td>
                    <td>
                      <div class="input-group">
                        <input
                          type="text"
                          class="form-control form-control-sm so-barcode atlas-barcode"
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
                    <td class="so-description"><?= htmlspecialchars($detail->description) ?></td>
                    <td>
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
                      $conversionFactor = (float) $detail->conversion_factor;
                      $availableQty = $conversionFactor > 0 ? ((float) $detail->qty_available / $conversionFactor) : 0;
                    ?>
                    <td class="so-available text-right"><?= number_format($availableQty, 2) ?></td>

                    <td class="text-right"><?= number_format($detail->qty_fulfilled, 0) ?></td>
                    <td class="text-right <?= ($detail->qty_remaining == 0) ? 'text-success font-weight-500' : '' ?>" <?= ($detail->qty_remaining == 0) ? 'title="Fully Invoiced"' : '' ?>>
                      <?= number_format($detail->qty_remaining, 0) ?>
                    </td>
                    <td class="text-right">
                      <input
                        type="number"
                        step="any"
                        class="form-control form-control-sm text-right so-qty"
                        value="<?= number_format($detail->qty) ?>">
                    </td>
                    <td class="text-center">
                      <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
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
                  <td class="so-description"></td>
                  <td>
                    <select class="form-control form-control-sm so-uom custom-select w-auto">
                      <option value="">Select...</option>

                      <?php foreach ($uoms as $uom): ?>
                        <option value="<?= $uom->id; ?>">
                          <?= htmlspecialchars($uom->uom); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td class="so-available text-right">-</td>
                  <td></td>
                  <td></td>
                  <td class="text-right">
                    <input
                      type="number"
                      step="any"
                      class="form-control form-control-sm text-right so-qty"
                      value="">
                  </td>
                  <td class="text-center"><i class="fas fa-trash text-danger pointer btn-delete-row"></i></td>
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
</script>