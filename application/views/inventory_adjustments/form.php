<div class="card">
  <div class="card-header">

    <h3 class="card-title">
      Inventory Adjustment Information
    </h3>

  </div>
  <div class="card-body">
    <div class="row">

      <div class="col-md-3">
        <div class="form-group">
          <label>Adjustment No.</label>
          <input
            type="text"
            id="txtAdjustmentNo"
            class="form-control"
            value="AUTO"
            readonly>
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          <label>Adjustment Date</label>
          <input
            type="date"
            id="dtAdjustmentDate"
            class="form-control"
            value="<?= date('Y-m-d'); ?>">
        </div>
      </div>

    </div>

    <div class="row">

      <div class="col-md-12">
        <div class="form-group">
          <label>Remarks</label>
          <textarea
            id="txtAdjustmentRemarks"
            class="form-control"
            rows="3"></textarea>
        </div>
      </div>

    </div>
  </div>
</div>