<div class="card-body">
  <div class="row">
    <div class="col-md-6">

      <input type="hidden" id="txtPONo" value="">
      <table class="table table-sm table-borderless">
        <tr>
          <th width="180">PO No.</th>
          <td class="font-weight-500 text-brown" id="tdRefNo">AUTO-GENERATED</td>
        </tr>
        <tr>
          <th>PO Date</th>
          <td>
            <input type="date" id="txtPODate" class="form-control form-control-sm w-auto" value="<?= date('Y-m-d'); ?>">
          </td>
        </tr>
        <tr>
          <th>Supplier</th>
          <td>
            <select id="selSupplier" class="form-control form-control-sm w-auto">
              <option value="">Select Supplier</option>
              <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= $supplier->id; ?>" data-terms-id="<?= $supplier->terms_id; ?>">
                  <?= htmlspecialchars($supplier->supplier_name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <th>Terms</th>
          <td>
            <select id="selTerms" class="form-control form-control-sm">
              <option value="">Select Terms</option>
              <?php foreach ($terms as $term): ?>
                <option value="<?= $term->id; ?>">
                  <?= htmlspecialchars($term->terms_name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <th>Remarks</th>
          <td>
            <input type="text" id="txtRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks">
          </td>
        </tr>
      </table>

    </div>
  </div>
</div>