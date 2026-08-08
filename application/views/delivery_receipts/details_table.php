<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Items to Deliver
        </h3>
        <div class="ml-auto">
          <button type="button" class="btn btn-sm btn-link" onClick="Atlas.page.back('delivery-receipts');"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back</a>
            <button type="button" class="btn btn-sm btn-link" id="btnPostDeliveryReceipt"><i class="fa fa-check mr-2"></i>Post</button>
            <button type="button" class="btn btn-sm btn-link" id="btnCreateSalesInvoice"><i class="fa fa-file-contract mr-2"></i>Create Sales Invoice</button>
            <button type="button" class="btn btn-sm btn-link" id="btnPrintDeliveryReceipt"><i class="fa fa-print mr-2"></i>Print</button>
            <button type="button" class="btn btn-sm btn-link" id="btnCancelDeliveryReceipt"><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
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
                <th width="90" class="text-right">
                  <i class="fas fa-info-circle text-brown mr-1" data-toggle="tooltip" title="Available quantity to deliver"></i>AQTD
                </th>
                <th width="90" class="text-right">Stock</th>
                <th width="110" class="text-right">Deliver Qty</th>
              </tr>
            </thead>

            <tbody>

              <?php foreach($details as $row): ?>
                <tr
                    data-sales-order-detail-id="<?= $row->sales_order_detail_id; ?>"
                    data-product-id="<?= $row->product_id; ?>"
                    data-qty-remaining="<?= $row->qty_remaining; ?>"
                    data-qty-available="<?= $row->qty_available; ?>"
                    data-description="<?= $row->description ?>">

                  <td class="text-center"><?= htmlspecialchars($row->barcode); ?></td>
                  <td><?= htmlspecialchars($row->description); ?></td>
                  <td class="text-center"><?= htmlspecialchars($row->uom); ?></td>
                  <td class="text-right"><?= number_format($row->qty_ordered, 0); ?></td>
                  <td class="text-right"><?= number_format($row->qty_delivered, 0); ?></td>
                  <td class="text-right"><?= number_format($row->qty_remaining, 0); ?></td>
                  <td class="text-right"><?= number_format($row->qty_available_to_deliver, 0); ?></td>
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
                        max="<?= $row->qty_available_to_deliver; ?>"
                        step="any"
                        value="0"
                        <?= isset($deliveryReceiptId) ? 'readonly' : '' ?>>
                  </td>
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

<input type="hidden" id="hidSalesOrderId" value="<?= $header->sales_order_id ?? $salesOrderId ?>">
<input type="hidden" id="hidCustomerId" value="<?= $header->customer_id; ?>">
<input type="hidden" id="hidDeliveryReceiptId" value="<?= isset($deliveryReceiptId) ? $deliveryReceiptId : 0; ?>">
<script>
   window.deliveryReceiptId = <?= (int) ($deliveryReceiptId ?? 0); ?>;
   window.status = '<?= isset($deliveryReceiptId) ? $header->status : ''; ?>';
</script>