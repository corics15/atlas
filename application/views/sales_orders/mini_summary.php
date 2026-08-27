<div class="row mb-3">
  <div class="col-md-4">
    <div class="small-box bg-light mb-0">
      <div class="inner">
        <h4><?= number_format($recordCount); ?></h4>
        <p>Total Items</p>
      </div>

      <div class="icon">
        <i class="fas fa-boxes"></i>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="small-box bg-light mb-0">
      <div class="inner">
        <h4><?= number_format($item_count, 0); ?></h4>
        <p>Total Item Count</p>
      </div>

      <div class="icon">
        <i class="fas fa-cubes"></i>
      </div>
    </div>
  </div>

  <div class="col-md-4">
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