<div class="row">
  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4><?= number_format($summary['cv_count']) ?></h4>
        <p>Check Vouchers</p>
      </div>

      <div class="icon">
        <i class="fas fa-book"></i>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4><?= number_format($summary['line_count']) ?></h4>
        <p>Transaction Lines</p>
      </div>

      <div class="icon">
        <i class="fas fa-align-justify"></i>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4><?= number_format($summary['total_debit'], 2) ?></h4>
        <p>Total Debit</p>
      </div>

      <div class="icon">
        <i class="fas fa-balance-scale-left"></i>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4><?= number_format($summary['total_credit'], 2) ?></h4>
        <p>Total Credit</p>
      </div>

      <div class="icon">
        <i class="fas fa-balance-scale-right"></i>
      </div>
    </div>
  </div>
</div>