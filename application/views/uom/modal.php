<div class="modal fade" id="mdlUom" tabindex="-1" aria-labelledby="mdlUomLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlUomLabel">Unit of Measure</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmUom">
        <input type="hidden" id="hidUomId" name="id">

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtUom">UOM</label>
              <input type="text" id="txtUom" name="uom" class="form-control form-control-sm text-uppercase" placeholder="Enter unit of measure">
              <small id="errUom" class="text-danger"></small>
            </div>

            <div class="form-group col-md-6">
              <div class="mb-2">
                <h6 class="mb-2">Common Units of Measurement</h6>
                <ul class="pl-3 mb-2 small">
                  <li>PCS (Pieces)</li>
                  <li>PACK</li>
                  <li>BOX</li>
                  <li>DOZ (Dozen)</li>
                  <li>KG (Kilogram)</li>
                  <li>G (Gram)</li>
                  <li>LB (Pound)</li>
                  <li>OZ (Ounce)</li>
                  <li>L (Liter)</li>
                  <li>ML (Milliliter)</li>
                  <li>M (Meter)</li>
                  <li>CM (Centimeter)</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" id="btnSaveUom" class="btn btn-sm btn-default">Save UOM</button>
        </div>
      </form>

    </div>
  </div>
</div>
