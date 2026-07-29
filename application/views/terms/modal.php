<div class="modal fade" id="mdlTerm" tabindex="-1" aria-labelledby="mdlTermLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlTermLabel">Terms</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmTerm">
        <input type="hidden" id="hidTermId" name="id">

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtTerm">Payment Terms</label>
              <input type="text" id="txtTerm" name="terms_name" class="form-control form-control-sm text-uppercase" placeholder="Enter new term">
              <small id="errTerms" class="text-danger"></small>
            </div>

            <div class="form-group col-md-6">
              <div class="mb-2">
                <h6 class="mb-2">Common Payment Terms</h6>
                <ul class="pl-3 mb-2 small">
                  <li>COD (Cash on Delivery)</li>
                  <li>15 Days</li>
                  <li>30 Days</li>
                  <li>45 Days</li>
                  <li>60 Days</li>
                  <li>90 Days</li>
                  <li>EOM (End of Month)</li>
                  <li>Advance Payment</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" id="btnSaveTerm" class="btn btn-sm btn-default">Save</button>
        </div>
      </form>

    </div>
  </div>
</div>
