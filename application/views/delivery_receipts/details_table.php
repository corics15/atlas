<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Items to Deliver</h3>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive table-scroll">
          <table class="table table-sm table-hover mb-0" id="tblDeliveryReceiptDetails">
            <thead class="thead-orange">
              <tr>
                <th width="120" class="text-center">Barcode</th>
                <th>Description</th>
                <th class="text-center">UOM</th>
                <th width="90" class="text-right">Ordered</th>
                <th width="90" class="text-right">Delivered</th>
                <th width="90" class="text-right">Remaining</th>
                <th width="90" class="text-right">Available</th>
                <th width="110" class="text-right">Deliver Qty</th>
              </tr>
            </thead>

            <tbody>

              <?php foreach($details as $row): ?>
                <tr
                    data-sales-order-detail-id="<?= $row->sales_order_detail_id; ?>"
                    data-product-id="<?= $row->product_id; ?>"
                    data-qty-remaining="<?= $row->qty_remaining; ?>"
                    data-qty-available="<?= $row->qty_available; ?>">
                  <td class="text-center"><?= htmlspecialchars($row->barcode); ?></td>
                  <td>
                      <?= htmlspecialchars($row->description); ?>
                  </td>

                  <td class="text-center">
                    <?= htmlspecialchars($row->uom); ?>
                  </td>

                  <td class="text-right">
                    <?= number_format($row->qty_ordered, 0); ?>
                  </td>

                  <td class="text-right">
                    <?= number_format($row->qty_delivered, 0); ?>
                  </td>

                  <td class="text-right">
                    <?= number_format(min($row->qty_remaining, $row->qty_available), 0); ?>
                  </td>

                  <td class="text-right">
                    <?php $stockClass = $row->qty_available >= $row->qty_remaining ? 'text-success fw-bold' : 'text-danger fw-bold'; ?>
                    <span class="<?= $stockClass; ?>">
                    <?= number_format($row->qty_available, 0); ?>
                    </span>
                  </td>

                  <td>
                    <input
                        type="number"
                        class="form-control text-right dr-deliver-qty"
                        min="0"
                        max="<?= $row->qty_remaining; ?>"
                        step="any"
                        value="0"
                        <?= isset($deliveryReceiptId) ? 'readonly' : '' ?>>
                  </td>
                  <?php /*** value="<?= number_format(min($row->qty_remaining, $row->qty_available), 0); ?>" */ ?>
                </tr>
              <?php endforeach; ?>

            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">

        <div class="d-flex justify-content-between">
          <div></div>
          <button type="button" class="btn btn-sm btn-default" id="btnSaveDeliveryReceipt">Save Delivery Receipt</button>
        </div>

      </div>
    </div>

  </div>
</section>

<input
    type="hidden"
    id="hidSalesOrderId"
    value="<?= $header->sales_order_id ?? $salesOrderId ?>">

<input
    type="hidden"
    id="hidCustomerId"
    value="<?= $header->customer_id; ?>">

<input
    type="hidden"
    id="hidDeliveryReceiptId"
    value="<?= isset($deliveryReceiptId)
        ? $deliveryReceiptId
        : 0; ?>">