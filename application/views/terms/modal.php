<div class="modal fade" id="mdlTerm" tabindex="-1" aria-labelledby="mdlTermLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
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
              <label for="txtTerm">Terms</label>
              <input type="text" id="txtTerm" name="terms_name"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter new term">
              <small id="errTerms" class="text-danger"></small>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" id="btnSaveTerm" class="btn btn-sm btn-primary">Save</button>
        </div>
      </form>

    </div>
  </div>
</div>
