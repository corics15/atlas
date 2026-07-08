<div class="modal fade" id="mdlUom" tabindex="-1">
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

      <form id="frmUom">
        <input type="hidden" id="hidUomId" name="id">

        <div class="modal-body">
          <div class="form-group">
            <label for="txtUom">UOM</label>
            <input type="text"
              id="txtUom" name="uom"
              class="form-control form-control-sm uppercase">
              <small id="errUom" class="text-danger d-block"></small>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="submit"
            id="btnSaveUom"
            class="btn btn-sm btn-primary">
          Save
          </button>
        </div>
      </form>

    </div>

  </div>
</div>