<div class="row">

  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4>
          <?= number_format((float)$openingBalance, 2) ?>
        </h4>
        <p>Opening Balance</p>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4>
          <?= number_format((float)$periodInvoiced, 2) ?>
        </h4>
        <p>Period Invoiced</p>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4>
          <?= number_format((float)$periodPaid, 2) ?>
        </h4>
        <p>Period Paid</p>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="small-box bg-light">
      <div class="inner">
        <h4>
          <?= number_format((float)$amountDue, 2) ?>
        </h4>
        <p>Amount Due</p>
      </div>
    </div>
  </div>

</div>