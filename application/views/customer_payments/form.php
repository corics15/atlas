<?php $isEdit = isset($customerPayment); $status = null; ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

        <?php if (isset($customerPayment)) : ?>

          <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">
              Payment Information
            </h3>

            <?php
              $statusClass = NULL;

              switch ($customerPayment->status) {
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

            <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= $customerPayment->status ?>]</div>
          </div>

        <?php else : ?>

          <h3 class="card-title">
            Payment Information
          </h3>

        <?php endif; ?>

      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-md-7">

            <table class="table table-sm table-borderless">
              <tr>
                <th width="180">Payment No.</th>
                <td class="font-weight-500 text-brown" id="tdRefNo"><?= isset($customerPayment) ? htmlspecialchars($customerPayment->payment_no) : 'AUTO-GENERATED'; ?></td>
              </tr>

              <tr>
                <th>Payment Date</th>
                <td>
                  <input type="date" id="dtPaymentDate" class="form-control form-control-sm w-auto" value="<?= isset($customerPayment)
                        ? $customerPayment->payment_date
                        : date('Y-m-d'); ?>">
                </td>
              </tr>

              <tr>
                <th>Customer</th>
                <td>
                  <select id="selCustomer" class="form-control form-control-sm">
                    <option value="">Select Customer</option>

                    <?php foreach ($customers as $customer): ?>
                      <option
                        value="<?= $customer->id ?>"
                        <?= isset($customerPayment) &&
                            $customerPayment->customer_id == $customer->id
                              ? 'selected'
                              : '' ?>>
                        <?= htmlspecialchars($customer->customer_name) ?>
                      </option>
                    <?php endforeach; ?>

                  </select>
                </td>
              </tr>

              <tr>
                <th>Branch</th>
                <td>
                  <select id="selBranch" class="form-control form-control-sm">
                    <option value="">Select Branch</option>

                    <?php foreach ($branches as $branch): ?>
                      <option
                        value="<?= $branch->id ?>"
                        <?= isset($customerPayment) &&
                            $customerPayment->branch_id == $branch->id
                              ? 'selected'
                              : '' ?>>
                        <?= htmlspecialchars($branch->branch_name) ?>
                      </option>
                    <?php endforeach; ?>

                  </select>
                </td>
              </tr>

              <tr>
                <th>Amount Received</th>
                <td>
                  <input type="number" step="0.01" min="0" id="txtAmountReceived" class="form-control form-control-sm"
                    value="<?= isset($customerPayment) ? number_format((float)$customerPayment->amount_received, 2, '.', '') : ''; ?>"
                    placeholder="0.00">
                </td>
              </tr>

              <?php $paymentMethod = isset($customerPayment) ? $customerPayment->payment_method : '' ?>
              <tr>
                <th>Payment Method</th>
                <td>
                  <select id="selPaymentMethod" class="form-control form-control-sm">

                    <option value="">Select Payment Method</option>
                    <option value="CASH" <?= $paymentMethod === 'CASH' ? 'selected' : '' ?>>Cash</option>
                    <option value="CHECK" <?= $paymentMethod === 'CHECK' ? 'selected' : '' ?>>Check</option>
                    <option value="BANK_TRANSFER" <?= $paymentMethod === 'BANK_TRANSFER' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="OTHER" <?= $paymentMethod === 'OTHER' ? 'selected' : '' ?>>Other</option>

                  </select>
                </td>
              </tr>

              <tr>
                <th>Reference No.</th>
                <td>
                  <input type="text" id="txtReferenceNo" class="form-control form-control-sm text-uppercase" placeholder="Enter reference no."
                    value="<?= isset($customerPayment) ? htmlspecialchars($customerPayment->reference_no ?? '') : ''; ?>">
                </td>
              </tr>

              <tr>
                <th>Collected By</th>
                <td>
                  <select id="selCollectedBy" class="form-control form-control-sm">
                    <option value="">None / Office Collection</option>

                    <?php foreach ($salesmen as $salesman): ?>
                      <option value="<?= $salesman->id ?>" <?= isset($customerPayment) && $customerPayment->collected_by_salesman_id == $salesman->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars(trim($salesman->first_name . ' ' . $salesman->last_name)) ?>
                      </option>
                    <?php endforeach; ?>

                  </select>
                </td>
              </tr>

              <tr>
                <th>Remarks</th>
                <td>
                  <input type="text" id="txtCustomerPaymentRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks"
                    value="<?= isset($customerPayment) ? htmlspecialchars($customerPayment->remarks ?? '') : ''; ?>">
                </td>
              </tr>

            </table>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<input type="hidden" id="hidCustomerPaymentId" value="<?= isset($customerPayment) ? $customerPayment->id : ''; ?>">