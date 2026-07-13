<div class="modal fade" id="mdlSupplier" tabindex="-1" aria-labelledby="mdlSupplierLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlSupplierLabel">Supplier Information</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmSupplier">
        <input type="hidden" id="hidSupplierId" name="id">

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtSupplierName">Supplier</label>
              <input type="text" id="txtSupplierName" name="supplier_name"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter supplier name">
              <small id="errSupplierName" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtContactPerson">Contact Person</label>
              <input type="text" id="txtContactPerson" name="contact_person"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter contact person">
              <small id="errContactPerson" class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtMobileNo">Mobile #</label>
              <input type="text" id="txtMobileNo" name="mobile_no"
                     class="form-control form-control-sm" placeholder="09XX-XXX-XXXX">
              <small id="errMobileNo" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtTelephoneNo">Tel #</label>
              <input type="text" id="txtTelephoneNo" name="telephone_no"
                     class="form-control form-control-sm" placeholder="(XXX) XXX-XXXX">
              <small id="errTelephoneNo" class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtEmailAddress">Email</label>
              <input type="text" id="txtEmailAddress" name="email_address"
                     class="form-control form-control-sm" placeholder="example@domain.com">
              <small id="errEmailAddress" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtAddress">Address</label>
              <input type="text" id="txtAddress" name="address"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter address">
              <small id="errAddress" class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtTinNo">TIN</label>
              <input type="text" id="txtTinNo" name="tin_no"
                     class="form-control form-control-sm" placeholder="Enter TIN">
              <small id="errTinNo" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="selTerms">Terms</label>
              <select
                id="selTerms"
                name="terms_id"
                class="form-control form-control-sm custom-select">

                <option value="">Select Terms</option>

                <?php foreach ($terms as $term): ?>
                  <option value="<?= $term->id; ?>">
                    <?= htmlspecialchars($term->terms_name); ?>
                  </option>
                <?php endforeach; ?>

              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" id="btnSaveSalesman" class="btn btn-sm btn-primary">Save Supplier</button>
        </div>
      </form>

    </div>
  </div>
</div>
