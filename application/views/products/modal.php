<div class="modal fade" id="mdlProduct" tabindex="-1">
  <div class="modal-dialog modal-lg">

    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>

        <button type="button"
          class="close"
          data-dismiss="modal">
        <span>&times;</span>
        </button>
      </div>

      <form id="frmProduct">
        <input type="hidden" id="hidProductId" name="id">

        <div class="modal-body">
          <div class="form-group">
            <label for="selSupplier">Supplier</label>
            <select
              id="selSupplier"
              name="supplier_id"
              class="">
              <option value="">Select Supplier</option>
              <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= $supplier->id; ?>">
                  <?= htmlspecialchars($supplier->supplier_name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="txtBarcode">Barcode</label>
            <input type="text"
              id="txtBarcode" name="barcode"
              class="form-control form-control-sm">
              <small id="errBarcode" class="text-danger d-block"></small>
          </div>

          <div class="form-group">
            <label for="txtDescription">Description</label>
            <input type="text"
              id="txtDescription" name="description"
              class="form-control form-control-sm uppercase">
              <small id="errDescription" class="text-danger d-block"></small>
          </div>

          <div class="form-group">
            <label for="selUom">UOM</label>
              <select
                id="selUom"
                name="uom_id"
                class="">
                <option value="">Select UOM</option>
                <?php foreach ($uoms as $uom): ?>
                  <option value="<?= $uom->id; ?>">
                    <?= htmlspecialchars($uom->uom); ?>
                  </option>
                <?php endforeach; ?>
              </select>
          </div>

          <div class="form-group">
            <label for="txtCost">Cost</label>
            <input type="text"
              id="txtCost" name="cost"
              class="form-control form-control-sm">
              <small id="errCost" class="text-danger d-block"></small>
          </div>

          <div class="form-group">
            <label for="txtSRP">SRP</label>
            <input type="text"
              id="txtSRP" name="srp"
              class="form-control form-control-sm">
              <small id="errSRP" class="text-danger d-block"></small>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="submit"
            id="btnSaveProduct"
            class="btn btn-sm btn-primary">
          Save
          </button>
        </div>
      </form>

    </div>

  </div>
</div>