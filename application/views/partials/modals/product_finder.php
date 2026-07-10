<div class="modal fade" id="mdlProductFinder" tabindex="-1" role="dialog" aria-labelledby="productFinderLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
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
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Enter keyword...">
            <div class="input-group-append">
              <button class="btn btn-sm btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
            </div>
          </div>
        </div>

        <?php /*** table */ ?>
        <div class="table-responsive">
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
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
      </div>

    </div>
  </div>
</div>
