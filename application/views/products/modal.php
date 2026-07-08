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
            <label>Supplier</label>
            <input type="text"
              id="selSupplier" name="supplier_id"
              class="form-control form-control-sm">
              <small id="errSupplier" class="text-danger d-block"></small>
          </div>

          <div class="form-group">
            <label>Barcode</label>
            <input type="text"
              id="txtBarcode" name="barcode"
              class="form-control form-control-sm">
              <small id="errBarcode" class="text-danger d-block"></small>
          </div>

          <div class="form-group">
            <label>Description</label>
            <input type="text"
              id="txtDescription" name="description"
              class="form-control form-control-sm">
              <small id="errDescription" class="text-danger d-block"></small>
          </div>

          <div class="form-group">
            <label>UOM</label>
            <input type="text"
              id="txtUOM" name="uom"
              class="form-control form-control-sm">
              <small id="errUOM" class="text-danger d-block"></small>
          </div>

          <div class="form-group">
            <label>Cost</label>
            <input type="text"
              id="txtCost" name="cost"
              class="form-control form-control-sm">
              <small id="errCost" class="text-danger d-block"></small>
          </div>

          <div class="form-group">
            <label>SRP</label>
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