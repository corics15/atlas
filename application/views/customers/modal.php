<div class="modal fade" id="mdlCustomer" tabindex="-1" aria-labelledby="mdlCustomerLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlCustomerLabel">Customer Information</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmCustomer">
        <input type="hidden" id="hidCustomerId" name="id">

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtCustomerName">Customer Name</label>
              <input type="text" id="txtCustomerName" name="customer_name"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter full name">
              <small id="errCustomerName" class="text-danger"></small>
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
              <label for="txtContactPerson">Contact Person</label>
              <input type="text" id="txtContactPerson" name="contact_person"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter contact person">
              <small id="errContactPerson" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtMobileNo">Mobile No.</label>
              <input type="text" id="txtMobileNo" name="mobile_no"
                     class="form-control form-control-sm text-uppercase" placeholder="09XX-XXX-XXXX">
              <small id="errMobileNo" class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtTelephoneNo">Tel. No.</label>
              <input type="text" id="txtTelephoneNo" name="telephone_no"
                     class="form-control form-control-sm text-uppercase" placeholder="(XXX) XXX-XXXX">
              <small id="errTelephoneNo" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtEmailAddress">Email</label>
              <input type="email" id="txtEmailAddress" name="email_address"
                     class="form-control form-control-sm" placeholder="example@domain.com">
              <small id="errEmailAddress" class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="selSalesman">Salesman</label>
              <select id="selSalesman" name="salesman_id" class="form-control form-control-sm">
                <option value="">Select Salesman</option>
                <?php foreach ($salesmen as $salesman): ?>
                  <option value="<?= $salesman->id; ?>">
                    <?= htmlspecialchars($salesman->code . ' - ' . $salesman->first_name . ' ' . $salesman->last_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small id="errSalesmanId" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="selTerms">Terms</label>
              <select
                id="selTerms"
                name="terms_id"
                class="form-control form-control-sm">
                <option value="">Select Terms</option>
                <?php foreach ($terms as $term): ?>
                <option value="<?= $term->id; ?>">
                  <?= htmlspecialchars($term->terms_name); ?>
                </option>
                <?php endforeach; ?>
              </select>
              <small id="errTerms"
                class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtCreditLimit">Credit Limit</label>
              <input type="number" id="txtCreditLimit" name="credit_limit"
                     class="form-control form-control-sm" placeholder="Enter amount">
              <small id="errCreditLimit" class="text-danger"></small>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" id="btnSaveCustomer" class="btn btn-sm btn-primary">Save Customer</button>
        </div>
      </form>

    </div>
  </div>
</div>