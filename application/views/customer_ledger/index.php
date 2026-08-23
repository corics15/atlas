<?php $this->load->view('partials/page_header'); ?>

<?php /*** header */ ?>
<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          Customer Ledger
        </h3>
      </div>

      <div class="card-body">

        <div class="row">
          <div class="col-md-6">
            <div class="form-group mb-0">
              <label for="selCustomer">
                Customer
              </label>
              <select id="selCustomer" class="form-control form-control-sm">
                <option value="">Select Customer</option>
                <?php foreach ($customers as $customer): ?>
                  <option value="<?= $customer->id ?>">
                    <?= htmlspecialchars(
                      $customer->customer_name
                    ) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group mb-0">
              <label for="dtDateFrom">Date From</label>
              <input type="date" id="dtDateFrom" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group mb-0">
              <label for="dtDateTo">Date To</label>
              <input type="date" id="dtDateTo" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
          </div>

        </div>

      </div>
    </div>

  </div>
</section>

<?php /*** summary */ ?>
<section class="content">
  <div class="container-fluid">

    <div class="row">

      <div class="col-md-3">
        <div class="small-box bg-light">
          <div class="inner">
            <h4 id="clOpeningBalance">0.00</h4>
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
            <h4 id="clTotalInvoiced">0.00</h4>
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
            <h4 id="clTotalPaid">0.00</h4>
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
            <h4 id="clCurrentBalance">0.00</h4>
            <p>Current Balance</p>
          </div>

          <div class="icon">
            <i class="fas fa-balance-scale"></i>
          </div>
        </div>
      </div>

    </div>

  </div>
</section>

<?php /*** ledger transactions */ ?>
<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          Ledger Transactions
        </h3>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table
            class="table table-sm table-bordered table-hover mb-0"
            id="tblCustomerLedger">

            <thead class="thead-orange">
              <tr>
                <th width="120" class="text-center">Date</th>
                <th width="180" class="text-center">Reference</th>
                <th class="text-center">Transaction</th>
                <th width="140" class="text-right">Debit</th>
                <th width="140" class="text-right">Credit</th>
                <th width="140" class="text-right">Balance</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td
                  colspan="6"
                  class="text-center text-muted py-3">
                  Select a customer to view ledger transactions.
                </td>
              </tr>
            </tbody>

          </table>
        </div>
      </div>
    </div>

  </div>
</section>