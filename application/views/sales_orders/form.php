<?php $isEdit = isset($salesOrder); $status = null; ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if (isset($salesOrder)) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Sales Order Information
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
              case 'COMPLETED':
                $statusClass = 'text-primary';
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
          Sales Order Information
        </h3>
      <?php endif; ?>

      </div>

      <?php /*** header */ ?>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">

            <input type="hidden" id="txtSalesOrderNo" value="<?= isset($salesOrder) ? htmlspecialchars($salesOrder->so_no) : 'AUTO-GENERATED'; ?>">
            <table class="table table-sm table-borderless">
              <tr>
                <th width="180">SO No.</th>
                <td class="font-weight-500 text-brown" id="tdRefNo"><?= isset($salesOrder) ? htmlspecialchars($salesOrder->so_no) : 'AUTO-GENERATED'; ?></td>
              </tr>
              <tr>
                <th>Transfer Date</th>
                <td>
                  <input type="date" id="dtOrderDate" class="form-control form-control-sm w-auto" value="<?= isset($salesOrder) ? $salesOrder->order_date : date('Y-m-d'); ?>">
                </td>
              </tr>
              <tr>
                <th>Customer</th>
                <td>
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
                        data-discount-type="<?= htmlspecialchars($customer->discount_type ?? '') ?>"
                        data-discount-value="<?= (float)($customer->discount_value ?? 0) ?>"
                        <?= isset($salesOrder)
                            && $salesOrder->customer_id == $customer->id
                                ? 'selected'
                                : '' ?>>
                        <?= htmlspecialchars($customer->customer_name) ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <tr>
                <th>Salesman</th>
                <td>
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
                </td>
              </tr>
              <tr>
                <th>Terms</th>
                <td>
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
                </td>
              </tr>
              <tr>
                <th>Credit Limit</th>
                <td>
                  <input
                    type="text"
                    id="txtCreditLimit"
                    class="form-control form-control-sm"
                    value="<?= isset($salesOrder) ? number_format($salesOrder->credit_limit, 2) : '0.00'; ?>">
                    </td>
                  </tr>
                  <tr>
                <th>Remarks</th>
                <td>
                  <input type="text" id="txtSalesOrderRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks" value="<?= isset($salesOrder) ? htmlspecialchars($salesOrder->remarks) : ''; ?>">
                </td>
              </tr>
            </table>

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