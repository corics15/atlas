<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Outstanding Invoices
        </h3>
        <div class="ml-auto">
          <a href="<?= base_url('customer-payments') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
          <button type="button" class="btn btn-sm btn-link" id="btnPostCustomerPayment" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-check mr-2"></i>Post</button>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintCustomerPayment"><i class="fa fa-print mr-2"></i>Print</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCancelCustomerPayment"><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover mb-0" id="tblOutstandingInvoices">

            <thead class="thead-orange">
              <tr>
                <th width="160" class="text-center">SI No.</th>
                <th width="120" class="text-center">Invoice Date</th>
                <th width="140" class="text-right">SI Amount</th>
                <th width="140" class="text-right">Paid</th>
                <th width="140" class="text-right">Balance</th>
                <th width="160" class="text-right">Apply Amount</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td colspan="6" class="text-center text-muted py-3">Select a customer to view outstanding invoices.</td>
              </tr>
            </tbody>

          </table>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-8"></div>
          <div class="col-md-4">
            <table class="table table-sm mb-3">
              <tbody>
                <tr>
                  <td>Amount Received</td>
                  <td id="cpAmountReceived" class="text-right">0.00</td>
                </tr>

                <tr>
                  <td>Amount Applied</td>
                  <td id="cpAmountApplied" class="text-right">0.00</td>
                </tr>

                <tr class="font-weight-500">
                  <td>Unapplied Amount</td>
                  <td id="cpAmountUnapplied" class="text-right">0.00</td>
                </tr>
              </tbody>
            </table>

            <button id="btnSaveCustomerPayment" class="btn btn-default btn-sm btn-block" <?= !$isEditable ? 'disabled' : '' ?>>Save Customer Payment</button>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>