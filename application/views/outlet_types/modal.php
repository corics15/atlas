<div class="modal fade" id="mdlOutletType" tabindex="-1" aria-labelledby="mdlOutletTypeLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlOutletTypeLabel">Customer Information</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmOutletType">
        <input type="hidden" id="hidOutletTypeId" name="id">

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtOutletTypeName">Description</label>
              <input type="text" id="txtOutletTypeName" name="outlet_type_name"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter description">
              <small id="errOutletTypeName" class="text-danger"></small>
            </div>

            <div class="form-group col-md-6">
              <div class="mb-2">
                <h6 class="mb-2">Common Outlet Types</h6>
                <ul class="pl-3 mb-2 small">
                  <li>Retail Store</li>
                  <li>Supermarket</li>
                  <li>Convenience Store</li>
                  <li>Pharmacy</li>
                  <li>Restaurant</li>
                  <li>Café</li>
                  <li>Wholesale Distributor</li>
                  <li>Online Shop</li>
                  <li>Department Store</li>
                  <li>Specialty Shop</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" id="btnSaveOutletType" class="btn btn-sm btn-default">Save Outlet</button>
        </div>
      </form>

    </div>
  </div>
</div>