<div class="modal fade" id="mdlSalesman" tabindex="-1">
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

      <form id="frmSalesman">
        <input type="hidden" id="hidSalesmanId" name="id">

        <div class="modal-body">
          <div class="form-group">
            <label>Code</label>
            <input type="text"
              id="txtSalesmanCode" name="salesman_code"
              class="form-control form-control-sm">
              <small id="errSalesmanCode" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>First Name</label>
            <input type="text"
              id="txtFirstName" name="first_name"
              class="form-control form-control-sm">
              <small id="errFirstName" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text"
              id="txtLastName" name="last_name"
              class="form-control form-control-sm">
              <small id="errLastName" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>Contact #</label>
            <input type="text"
              id="txtContactNo" name="contact_no"
              class="form-control form-control-sm">
              <small id="errContactNo" class="text-danger d-block"></small>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="submit"
            id="btnSaveSalesman"
            class="btn btn-sm btn-primary">
          Save
          </button>
        </div>
      </form>

    </div>

  </div>
</div>