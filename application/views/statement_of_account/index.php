<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-body">

        <?php $this->load->view('statement_of_account/search_toolbar'); ?>

      </div>
    </div>

    <?php if ($customer_id > 0): ?>

      <div class="card">

        <div class="card-header">
          <h3 class="card-title">
            Transactions
          </h3>

          <div class="card-tools">
            <button type="button" id="btnPrintSOA" class="btn btn-sm btn-default">
              <i class="fas fa-print mr-2"></i>
              Print
            </button>
          </div>
        </div>

        <div class="card-body">
          <?php /*** header */ ?>
          <div class="row mb-4">
            <div class="col-md-6">
              <h4 class="font-weight-500 mb-3">Statement of Account</h4>
              <div class="font-weight-500 mb-1">Bill To:</div>
              <div class="font-weight-500"><?= htmlspecialchars($customer->customer_name ?? '') ?></div>

              <?php if (!empty($customer->address)): ?>
                <div>
                  <?= nl2br(htmlspecialchars($customer->address)) ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td width="180" class="font-weight-500">Statement Date</td>
                    <td><?= date('m/d/Y', strtotime($date_to)) ?></td>
                  </tr>

                  <tr>
                    <td class="font-weight-500">Statement Period</td>
                    <td><?= date('m/d/Y', strtotime($date_from)) ?> - <?= date('m/d/Y', strtotime($date_to)) ?></td>
                  </tr>

                  <tr>
                    <td class="font-weight-500">Customer ID</td>
                    <td><?= strtoupper($hashedCustomerId) ?></td>
                  </tr>

                  <?php if (!empty($customer->terms_name)): ?>
                    <tr>
                      <td class="font-weight-500">Terms</td>
                      <td><?= htmlspecialchars($customer->terms_name) ?></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <?php /*** account summary */ ?>
          <div class="row mb-4">
            <div class="col-md-6"></div>
            <div class="col-md-6">
              <div class="card mb-0">
                <div class="card-header font-weight-500 py-2">Account Summary</div>
                <div class="card-body p-0">

                  <table class="table table-sm mb-0">
                    <tbody>
                      <tr>
                        <td>Previous Balance</td>
                        <td class="text-right"><?= number_format((float)$openingBalance, 2) ?></td>
                      </tr>

                      <tr>
                        <td>New Charges</td>
                        <td class="text-right"><?= number_format((float)$periodInvoiced, 2) ?></td>
                      </tr>

                      <tr>
                        <td>Payments / Credits</td>
                        <td class="text-right"><?= number_format((float)$periodPaid, 2) ?></td>
                      </tr>

                      <tr class="font-weight-500">
                        <td>Total Balance Due</td>
                        <td class="text-right"><?= number_format((float)$amountDue, 2) ?></td>
                      </tr>
                    </tbody>
                  </table>

                </div>
              </div>
            </div>
          </div>

          <?php /*** details */ ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">

              <?php $this->load->view('partials/table'); ?>

            </table>
          </div>

          <?php /*** footer */ ?>
          <div class="row mt-4">
            <div class="col-md-8"></div>
            <div class="col-md-4">
              <div class="border-top pt-2">
                <div class="d-flex justify-content-between align-items-center">

                  <span class="font-weight-500">
                    ACCOUNT CURRENT BALANCE
                  </span>

                  <span class="font-weight-500">
                    <?= number_format((float)$amountDue, 2) ?>
                  </span>

                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    <?php endif; ?>
  </div>
</section>