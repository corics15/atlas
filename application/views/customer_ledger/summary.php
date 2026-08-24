<div class="row">

  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4><?= number_format((float)$openingBalance, 2) ?></h4>
        <p>Opening Balance</p>
      </div>

      <div class="icon">
        <i class="fas fa-address-card"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4><?= number_format((float)$periodInvoiced, 2) ?></h4>
        <p>Period Invoiced</p>
      </div>

      <div class="icon">
        <i class="fas fa-file-invoice"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4><?= number_format((float)$periodPaid, 2) ?></h4>
        <p>Period Paid</p>
      </div>

      <div class="icon">
        <i class="fas fa-handshake"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4><?= number_format((float)$currentBalance, 2) ?></h4>
        <p>Current Balance</p>
      </div>

      <div class="icon">
        <i class="fas fa-balance-scale"></i>
      </div>
    </div>
  </div>

</div>