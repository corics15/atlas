<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Adjustment Details
        </h3>
        <div class="ml-auto">
          <a href="<?= base_url('inventory-adjustments') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>

          <?php if ($isEditable): ?>
            <button type="button" id="btnAddProductInventoryAdjustment" class="btn btn-sm btn-link"> <i class="fas fa-plus mr-2"></i> Add Product </button>
          <?php endif ?>

          <button type="button" class="btn btn-sm btn-link" id="btnPostInventoryAdjustment"><i class="fa fa-check mr-2"></i>Post</button>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintInventoryAdjustment"><i class="fa fa-print mr-2"></i>Print</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCancelInventoryAdjustment"><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
      </div>

      <?php /*** details */ ?>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover">

            <thead class="thead-orange">
              <tr>
                <th width="150" class="text-center">Barcode</th>
                <th>Description</th>
                <th width="80" class="text-center">UOM</th>
                <th width="120" class="text-right">On Hand</th>
                <th width="120" class="text-right">Adjustment</th>
                <th width="120" class="text-right">New Balance</th>
                <th width="50" class="text-center"></th>
              </tr>
            </thead>

            <tbody id="tblInventoryAdjustmentDetails">

              <?php if (!empty($inventoryAdjustmentDetails)) : ?>

                <?php foreach ($inventoryAdjustmentDetails as $detail) : ?>
                <tr data-product-id="<?= $detail->product_id ?>">

                  <td class="ia-barcode text-center">
                    <?= htmlspecialchars($detail->barcode) ?>
                  </td>

                  <td class="ia-description">
                    <?= htmlspecialchars($detail->description); ?>
                  </td>

                  <td class="ia-uom text-center">
                    <?= htmlspecialchars($detail->uom) ?>
                  </td>

                  <td class="ia-on-hand text-right">
                    <?= number_format($detail->on_hand) ?>
                  </td>

                  <?php if ($isEditable): ?>
                    <td>
                      <input type="number" step="any" class="form-control form-control-sm text-right ia-adjustment" value="<?= number_format($detail->adjustment_qty, 0) ?>">
                    </td>
                  <?php else : ?>
                    <td class="text-right ia-adjustment"><?= number_format($detail->adjustment_qty) ?></td>
                  <?php endif ?>

                  <td class="ia-new-balance text-right">
                    <?php if ($isEditable): ?>
                      <?= number_format($detail->on_hand + $detail->adjustment_qty) ?>
                    <?php else: ?>
                      <?= number_format($detail->on_hand) ?>
                    <?php endif; ?>
                  </td>

                  <?php if ($isEditable): ?>
                    <td class="text-center">
                      <i
                        class="fas fa-trash text-danger pointer btn-delete-row"
                        title="Remove Product" data-toggle="toolitp">
                      </i>
                    </td>
                  <?php else : ?>
                    <td></td>
                  <?php endif ?>

                </tr>
                <?php endforeach; ?>

              <?php else : ?>

                <tr id="iaPlaceholderRow">
                  <td colspan="7" class="text-center text-muted py-2">
                    No products added.
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
          <div class="align-items-end col-md-8 d-flex">

            <div class="alert alert-light font-sm mb-0" role="alert">
              <div class="font-weight-500 mb-1">
                <i class="fas fa-info-circle mr-1 text-info"></i>
                Inventory Adjustment Guide
              </div>

              <div>
                <span class="font-weight-500">1.</span>
                Click <span class="font-weight-500 text-olive">Add Product</span> or press <span class="font-weight-500 text-success">F2</span> to open the Product Finder.
              </div>

              <div>
                <span class="font-weight-500">2.</span>
                Search and select the product that needs an inventory adjustment.
              </div>

              <div>
                <span class="font-weight-500">3.</span>
                Review the current <span class="font-weight-500 text-danger">On Hand</span> quantity before entering the adjustment.
              </div>

              <div>
                <span class="font-weight-500">4.</span>
                Enter a positive quantity to <span class="font-weight-500 text-danger">increase</span> stock,
                or a negative quantity to <span class="font-weight-500 text-danger">decrease</span> stock.
                Example: <span class="font-weight-500 text-danger">5</span> adds five units, while <span class="font-weight-500 text-danger">-5</span> removes five units.
              </div>

              <div>
                <span class="font-weight-500">5.</span>
                Review the resulting <span class="font-weight-500 text-danger">New Balance</span> before saving.
              </div>

              <div>
                <span class="font-weight-500">6.</span>
                Click <span class="font-weight-500 text-brown">Save Adjustment</span> after verifying all products and quantities.
              </div>

              <div>
                <span class="font-weight-500">7.</span>
                Once verified, click <span class="font-weight-500 text-orange">Post</span> to update inventory quantities.
                Posting cannot be undone.
              </div>
            </div>

          </div>
          <div class="col-md-4">
            <button type="button" id="btnSaveInventoryAdjustment" class="btn btn-default btn-sm btn-block">Save Adjustment</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
  window.inventoryAdjustmentId = <?= isset($inventoryAdjustment) ? $inventoryAdjustment->id : 0; ?>;
</script>