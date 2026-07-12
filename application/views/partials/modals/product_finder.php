<div class="modal fade" id="mdlProductFinder" tabindex="-1" role="dialog" aria-labelledby="productFinderLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">

       <?php /*** header */ ?>
      <div class="modal-header">
        <h5 class="modal-title" id="productFinderLabel">Product Finder</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <?php /*** body */ ?>
      <div class="modal-body">

        <?php /*** search */ ?>
        <div class="form-group">
          <label for="searchInput">Search</label>
          <div class="input-group">
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Enter product name or barcode">
            <div class="input-group-append">
              <button class="btn btn-sm btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
            </div>
          </div>
        </div>

        <?php /*** table */ ?>
        <div id="pfRecordCount" class="small text-muted mb-2"></div>
        <div class="table-responsive table-scroll">
          <table class="table table-bordered table-sm">
            <thead class="thead-orange">
              <tr>
                <th class="text-center">Barcode</th>
                <th>Supplier</th>
                <th>Description</th>
                <th class="text-center">UOM</th>
                <th class="text-right">Price</th>
              </tr>
            </thead>
            <tbody id="tblProductFinder">
              <tr>
                <td colspan="5">No records found.</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <?php /*** footer */ ?>
      <div class="d-flex justify-content-between modal-footer">
        <div class="font-smr text-muted">
          Press <i class="fa fa-arrow-up"></i> or <i class="fa fa-arrow-down"></i> to navigate.
          Press "Enter" to confirm or "Esc" to cancel.
        </div>
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
      </div>

    </div>
  </div>
</div>
