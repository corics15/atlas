<div class="row mb-3">

  <div class="col-md-3">
    <div class="small-box bg-light mb-0">
      <div class="inner">
        <h4><?= number_format($recordCount); ?></h4>
        <p>Sales Order Detail Rows</p>
      </div>

      <div class="icon">
        <i class="fas fa-list"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light mb-0">
      <div class="inner">
        <h4><?= number_format($total_qty, 0); ?></h4>
        <p>Total Qty</p>
      </div>

      <div class="icon">
        <i class="fas fa-bars"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light mb-0">
      <div class="inner">
        <h4><?= number_format($total_item_count, 0); ?></h4>
        <p>Total Item Count</p>
      </div>

      <div class="icon">
        <i class="fas fa-cubes"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light mb-0">
      <div class="inner">
        <h4><?= number_format($total_amount, 2); ?></h4>
        <p>Total Amount</p>
      </div>

      <div class="icon">
        <i class="fas fa-coins"></i>
      </div>
    </div>
  </div>

</div>