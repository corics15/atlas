<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

        <h3 class="card-title">
          Adjustment Details
        </h3>

        <div class="card-tools">

          <?php if ($isEditable): ?>
            <button
              type="button"
              id="btnAddProductInventoryAdjustment"
              class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i>
            Add Product
            </button>
          <?php endif ?>

        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover">

            <thead class="thead-orange">
              <tr>
                <th width="120" class="text-center">Barcode</th>
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

                  <td class="ia-barcode">
                    <?= htmlspecialchars($detail->barcode) ?>
                  </td>

                  <td class="ia-description">
                    <?= htmlspecialchars($detail->description) ?>
                  </td>

                  <td class="ia-uom text-center">
                    <?= htmlspecialchars($detail->uom) ?>
                  </td>

                  <td class="ia-on-hand text-right">
                    <?= number_format($detail->on_hand) ?>
                  </td>

                  <?php if ($isEditable): ?>
                    <td>
                      <input
                          type="number"
                          step="any"
                          class="form-control form-control-sm text-right ia-adjustment"
                          value="<?= $detail->adjustment_qty ?>">
                    </td>
                  <?php else : ?>
                    <td class="text-right"><?= $detail->adjustment_qty ?></td>
                  <?php endif ?>

                  <td class="ia-new-balance text-right">
                    <?= number_format($detail->on_hand + $detail->adjustment_qty) ?>
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

      <?php if ($isEditable): ?>
        <div class="align-items-center card-footer d-flex justify-content-between">

          <div class="d-flex justify-content-between align-items-center">
            <button
              type="button"
              id="btnSaveInventoryAdjustmentx"
              class="btn btn-default btn-sm mr-2">
              Back
            </button>
            <button
              type="button"
              id="btnPostInventoryAdjustment"
              class="btn btn-default btn-sm mr-2">
              Post Adjustment
            </button>
            <button
              type="button"
              id="btnCancelInventoryAdjustment"
              class="btn btn-default btn-sm">
              Cancel Adjustment
            </button>
          </div>

          <div>
            <button
              type="button"
              id="btnSaveInventoryAdjustment"
              class="btn btn-outline-success">
              Save Adjustment
            </button>
          </div>

        </div>
      <?php endif ?>
    </div>
  </div>
</section>