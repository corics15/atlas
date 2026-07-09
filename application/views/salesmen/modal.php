<div class="modal fade" id="mdlSalesman" tabindex="-1" aria-labelledby="mdlSalesmanLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlSalesmanLabel">Salesman Information</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmSalesman">
        <input type="hidden" id="hidSalesmanId" name="id">

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtSalesmanCode">Code</label>
              <input type="text" id="txtSalesmanCode" name="salesman_code"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter code">
              <small id="errSalesmanCode" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtFirstName">First Name</label>
              <input type="text" id="txtFirstName" name="first_name"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter first name">
              <small id="errFirstName" class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtLastName">Last Name</label>
              <input type="text" id="txtLastName" name="last_name"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter last name">
              <small id="errLastName" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtContactNo">Contact #</label>
              <input type="text" id="txtContactNo" name="contact_no"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter contact number">
              <small id="errContactNo" class="text-danger"></small>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" id="btnSaveSalesman" class="btn btn-sm btn-primary">Save Salesman</button>
        </div>
      </form>

    </div>
  </div>
</div>