<div class="modal fade" id="mdlSupplier" tabindex="-1">
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

      <form id="frmSupplier">
        <input type="hidden" id="hidSupplierId" name="id">

        <div class="modal-body">
          <div class="form-group">
            <label>Supplier</label>
            <input type="text"
              id="txtSupplierName" name="supplier_name"
              class="form-control form-control-sm">
              <small id="errSupplierName" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>Contact Person</label>
            <input type="text"
              id="txtContactPerson" name="contact_person"
              class="form-control form-control-sm">
              <small id="errContactPerson" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>Mobile #</label>
            <input type="text"
              id="txtMobileNo" name="mobile_no"
              class="form-control form-control-sm">
              <small id="errMobileNo" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>Contact #</label>
            <input type="text"
              id="txtTelephoneNo" name="telephone_no"
              class="form-control form-control-sm">
              <small id="errTelephoneNo" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="text"
              id="txtEmailAddress" name="email_address"
              class="form-control form-control-sm">
              <small id="errContactNo" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>Address</label>
            <input type="text"
              id="txtAddress" name="address"
              class="form-control form-control-sm">
              <small id="errAddress" class="text-danger d-block"></small>
          </div>
          <div class="form-group">
            <label>TIN</label>
            <input type="text"
              id="txtTinNo" name="tin_no"
              class="form-control form-control-sm">
              <small id="errTinNo" class="text-danger d-block"></small>
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