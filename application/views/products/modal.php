<div class="modal fade" id="mdlProduct" tabindex="-1" aria-labelledby="mdlProductLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlProductLabel">Product Information</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmProduct">
        <input type="hidden" id="hidProductId" name="id">

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="selSupplier">Supplier</label>
              <select id="selSupplier" name="supplier_id" class="form-control form-control-sm">
                <option value="">Select Supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                  <option value="<?= $supplier->id; ?>">
                    <?= htmlspecialchars($supplier->supplier_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="txtBarcode">Barcode</label>
              <input type="text" id="txtBarcode" name="barcode"
                     class="form-control form-control-sm" placeholder="Enter barcode">
              <small id="errBarcode" class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtDescription">Description</label>
              <input type="text" id="txtDescription" name="description"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter product name">
              <small id="errDescription" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="selUom">UOM</label>
              <select id="selUom" name="uom_id" class="form-control form-control-sm">
                <option value="">Select UOM</option>
                <?php foreach ($uoms as $uom): ?>
                  <option value="<?= $uom->id; ?>">
                    <?= htmlspecialchars($uom->uom); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtCost">Cost</label>
              <input type="number" id="txtCost" name="cost" step="any"
                     class="form-control form-control-sm" placeholder="Enter cost">
              <small id="errCost" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtSRP">SRP</label>
              <input type="number" id="txtSRP" name="srp" step="any"
                     class="form-control form-control-sm" placeholder="Enter SRP">
              <small id="errSRP" class="text-danger"></small>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" id="btnSaveProduct" class="btn btn-sm btn-primary">Save Product</button>
        </div>
      </form>

    </div>
  </div>
</div>