<?php $isEdit = isset($salesOrder); $status = null; ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if (isset($salesOrder)) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Sales Order Header
          </h3>

          <?php
            $statusClass = NULL;
            switch ($salesOrder->status) {
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

          <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= $salesOrder->status ?>]</div>
        </div>

      <?php else : ?>
        <h3 class="card-title">
          Sales Order Header
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
                value="<?= isset($salesOrder) ? htmlspecialchars($salesOrder->so_no) : 'AUTO-GENERATED'; ?>"
                readonly>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label for="dtOrderDate">Order Date</label>
              <input
                type="date"
                id="dtOrderDate"
                class="form-control form-control-sm"
                value="<?= isset($salesOrder) ? $salesOrder->order_date : date('Y-m-d'); ?>">
            </div>
          </div>

        </div>

        <div class="row">

          <div class="col-md-4">
            <div class="form-group">

              <label for="selCustomer">Customer</label>
              <select
                id="selCustomer"
                class="form-control form-control-sm">
                <option value="">Select Customer</option>

                <?php foreach ($customers as $customer): ?>

                <option
                    value="<?= $customer->id ?>"
                    data-salesman-id="<?= $customer->salesman_id ?>"
                    data-terms="<?= htmlspecialchars($customer->terms_name) ?>"
                    data-terms-id="<?= $customer->terms_id ?>"
                    data-credit-limit="<?= $customer->credit_limit ?>"
                    <?= isset($salesOrder)
                        && $salesOrder->customer_id == $customer->id
                            ? 'selected'
                            : '' ?>>
                    <?= htmlspecialchars($customer->customer_name) ?>

                </option>

                <?php endforeach; ?>

              </select>

            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">

              <label for="selSalesman">Salesman</label>
              <select
                id="selSalesman"
                class="form-control form-control-sm">
                <option value="">Select Salesman</option>

                <?php foreach ($salesmen as $salesman): ?>

                <option
                    value="<?= $salesman->id ?>"
                    data-salesman-id="<?= $salesman->id ?>"
                    <?= isset($salesOrder)
                        && $salesOrder->salesman_id == $salesman->id
                            ? 'selected'
                            : '' ?>>
                    <?= htmlspecialchars($salesman->salesman_name) ?>
                </option>

                <?php endforeach; ?>

              </select>

            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">

              <label for="selTerms">Terms</label>
              <select
                id="selTerms"
                class="form-control form-control-sm">
                <option value="">Select Term</option>

                <?php foreach ($terms as $term): ?>

                <option
                    value="<?= $term->id ?>"
                    data-term-id="<?= $term->id ?>"
                    <?= isset($salesOrder)
                        && $salesOrder->terms_id == $term->id
                            ? 'selected'
                            : '' ?>>
                    <?= htmlspecialchars($term->terms_name) ?>
                </option>

                <?php endforeach; ?>

              </select>

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
                rows="3"><?= isset($salesOrder) ? htmlspecialchars($salesOrder->remarks) : ''; ?></textarea>
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