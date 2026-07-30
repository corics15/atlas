<?php
  $isEdit = isset($salesInvoice);
  $isFromSalesOrder = isset($salesOrder);
  $status = null;
?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if (isset($salesInvoice)) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Sales Invoice Header
          </h3>

          <?php
            $statusClass = NULL;
            switch ($salesInvoice->status) {
              case 'POSTED':
                $statusClass = 'text-success';
                break;
              case 'OPEN':
                $statusClass = 'text-secondary';
                break;
              default:
                $statusClass = 'text-danger';
                break;
            }
          ?>

          <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= $salesInvoice->status ?>]</div>
        </div>

      <?php else : ?>
        <h3 class="card-title">
          Sales Invoice Header
        </h3>
      <?php endif; ?>

      </div>

      <?php /*** header */ ?>
      <div class="card-body">
        <div class="row">

          <div class="col-md-3">
            <div class="form-group">
              <label for="txtSalesOrderNo">Sales Order No.</label>
              <input
                  type="text"
                  id="txtSalesOrderNo"
                  class="form-control form-control-sm"
                  value="<?= $isEdit ? htmlspecialchars($salesInvoice->so_no) : (!empty($salesOrder) ? htmlspecialchars($salesOrder->so_no) : '') ?>"
                  readonly>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label for="txtSalesInvoiceNo">Sales Invoice No.</label>
              <input
                  type="text"
                  id="txtSalesInvoiceNo"
                  class="form-control form-control-sm"
                  value="<?= $isEdit ? htmlspecialchars($salesInvoice->si_no) : 'AUTO-GENERATED'; ?>"
                  readonly>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label for="dtInvoiceDate">Invoice Date</label>
              <input
                  type="date"
                  id="dtInvoiceDate"
                  class="form-control form-control-sm"
                  value="<?= $isEdit ? $salesInvoice->invoice_date : date('Y-m-d'); ?>">
            </div>
          </div>

        </div>

        <div class="row">

          <div class="col-md-3">
            <div class="form-group">

              <label for="selCustomer">Customer</label>
              <select
                id="selCustomer"
                class="form-control form-control-sm no-event" readonly>
                <option value="">Select Customer</option>

                <?php foreach ($customers as $customer): ?>

                <option
                    value="<?= $customer->id ?>"
                    data-salesman-id="<?= $customer->salesman_id ?>"
                    data-terms="<?= htmlspecialchars($customer->terms_name) ?>"
                    data-terms-id="<?= $customer->terms_id ?>"
                    data-credit-limit="<?= $customer->credit_limit ?>"
                    <?=
                      $isEdit ? ($salesInvoice->customer_id == $customer->id ? 'selected' : '') :
                      ($isFromSalesOrder && $salesOrder->customer_id == $customer->id ? 'selected' : '')
                    ?>
                    >
                    <?= htmlspecialchars($customer->customer_name) ?>

                </option>

                <?php endforeach; ?>

              </select>

            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">

              <label for="selSalesman">Salesman</label>
              <select
                id="selSalesman"
                class="form-control form-control-sm no-event" readonly>
                <option value="">Select Salesman</option>

                <?php foreach ($salesmen as $salesman): ?>

                <option
                    value="<?= $salesman->id ?>"
                    data-salesman-id="<?= $salesman->id ?>"
                    <?=
                      $isEdit ? ($salesInvoice->salesman_id == $salesman->id ? 'selected' : '') :
                      ($isFromSalesOrder && $salesOrder->salesman_id == $salesman->id ? 'selected' : '' )
                    ?>
                    >
                    <?= htmlspecialchars($salesman->salesman_name) ?>
                </option>

                <?php endforeach; ?>

              </select>

            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">

              <label for="selTerms">Terms</label>
              <select
                id="selTerms"
                class="form-control form-control-sm no-event" readonly>
                <option value="">Select Term</option>

                <?php foreach ($terms as $term): ?>

                <option
                    value="<?= $term->id ?>"
                    data-term-id="<?= $term->id ?>"
                    <?=
                      $isEdit ? ($salesInvoice->terms_id == $term->id ? 'selected' : '') :
                      ($isFromSalesOrder && $salesOrder->terms_id == $term->id ? 'selected' : '')
                    ?>
                    >
                    <?= htmlspecialchars($term->terms_name) ?>
                </option>

                <?php endforeach; ?>

              </select>

            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">

              <label for="txtCreditLimit">Credit Limit</label>
              <input
                type="text"
                id="txtCreditLimit"
                class="form-control form-control-sm no-event"
                value="<?=
                          $isEdit ? number_format($salesInvoice->credit_limit, 2) :
                          ($isFromSalesOrder ? number_format($salesOrder->credit_limit, 2) : '0.00')
                        ?>"
                readonly>

            </div>
          </div>

        </div>

        <div class="row">

          <div class="col-md-12">
            <div class="form-group">
              <label for="txtSalesOrderRemarks">Remarks</label>
              <textarea
                id="txtSalesOrderRemarks"
                class="form-control form-control-sm text-uppercase"
                rows="3"><?=
                            $isEdit ? htmlspecialchars($salesInvoice->remarks) :
                            ($isFromSalesOrder? htmlspecialchars($salesOrder->remarks): '')
                          ?></textarea>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<input
    type="hidden"
    id="hidSalesOrderId"
    value="<?= isset($salesOrderId) ? $salesOrderId : ''; ?>">
<input
    type="hidden"
    id="hidSalesInvoiceId"
    value="<?= isset($salesInvoice) ? $salesInvoice->id : '' ?>">