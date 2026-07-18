<div class="card">
  <div class="card-header">

    <h3 class="card-title">
      Adjustment Details
    </h3>

    <div class="card-tools">
      <button
        type="button"
        id="btnAddProductInventoryAdjustment"
        class="btn btn-sm btn-primary">
      <i class="fas fa-plus mr-1"></i>
      Add Product
      </button>

    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-bordered table-hover">

        <thead class="thead-orange">
          <tr>
            <th width="120" class="text-center">Barcode</th>
            <th>Description</th>
            <th width="80" class="text-center">UOM</th>
            <th width="120" class="text-right">On Hand</th>
            <th width="120" class="text-right">Adjustment</th>
            <th width="120" class="text-right">New Balance</th>
          </tr>
        </thead>

        <tbody id="tblInventoryAdjustmentDetails">
          <tr id="iaPlaceholderRow">
            <td colspan="6" class="text-center text-muted py-3">
              No products added.
            </td>
          </tr>
        </tbody>

      </table>
    </div>
  </div>

  <div class="card-footer d-flex justify-content-end">

    <button
      type="button"
      id="btnCancelInventoryAdjustment"
      class="btn btn-default mr-2">
    Cancel
    </button>

    <div class="col-md-2">
      <button
        type="button"
        id="btnSaveInventoryAdjustment"
        class="btn btn-outline-success btn-block">
        Save Adjustment
      </button>
    </div>

  </div>
</div>