<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllCustomerPayments">
        <label class="custom-control-label" for="chkSelectAllCustomerPayments"></label>
      </div>
    </th>

    <th class="text-center">Payment Date</th>
    <th class="text-center">Payment No.</th>
    <th>Customer</th>
    <th class="text-center">Payment Method</th>
    <th class="text-center">Reference No.</th>
    <th>Collector</th>
    <th class="text-right">Amount Received</th>
    <th class="text-right">Applied</th>
    <th class="text-right">Unapplied</th>
    <th class="text-center">Status</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($customerPayments)): ?>

    <tr>
      <td colspan="11" class="text-center text-muted py-3">
        No customer payments found.
      </td>
    </tr>

  <?php else: ?>

    <?php foreach ($customerPayments as $payment): ?>

      <?php
        switch ($payment->status) {
          case 'OPEN':
            $status = '<span class="badge badge-secondary">OPEN</span>';
          break;
          case 'POSTED':
            $status = '<span class="badge badge-success">POSTED</span>';
          break;
          default:
            $status = '<span class="badge badge-danger">CANCELLED</span>';
          break;
        }
      ?>

      <tr data-id="<?= $payment->id; ?>">

        <td class="text-center">
          <div class="custom-control custom-checkbox ml-2 mt-1">
            <input
              type="checkbox"
              class="custom-control-input chkCustomerPayment"
              id="chkCustomerPayment-<?= $payment->id ?>"
              value="<?= $payment->id ?>">

            <label
              class="custom-control-label"
              for="chkCustomerPayment-<?= $payment->id ?>">
            </label>
          </div>
        </td>

        <td class="text-center">
          <?= date('m/d/Y', strtotime($payment->payment_date)); ?>
        </td>

        <td class="text-center">
          <a href="<?= $payment->url ?>" class="text-olive">
            <?= htmlspecialchars($payment->payment_no); ?>
          </a>
        </td>

        <td <?= mb_strlen(htmlspecialchars($payment->customer_name)) > 30 ? 'data-toggle="tooltip" title="'.htmlspecialchars($payment->customer_name).'"' : '' ?>>
          <?php
            $customerName = htmlspecialchars($payment->customer_name);
            echo (mb_strlen($customerName) > 30)
              ? mb_strimwidth($customerName, 0, 30, '...')
              : $customerName;
          ?>
        </td>

        <td class="text-center">
          <?= htmlspecialchars(
            str_replace('_', ' ', $payment->payment_method)
          ); ?>
        </td>

        <td class="text-center">
          <?= htmlspecialchars($payment->reference_no ?? ''); ?>
        </td>

        <td>
          <?= htmlspecialchars($payment->collector_name ?? ''); ?>
        </td>

        <td class="text-right">
          <?= number_format($payment->amount_received, 2); ?>
        </td>

        <td class="text-right">
          <?= number_format($payment->amount_applied, 2); ?>
        </td>

        <td class="text-right">
          <?= number_format($payment->amount_unapplied, 2); ?>
        </td>

        <td class="text-center">
          <?= $status; ?>
        </td>

      </tr>

    <?php endforeach; ?>

  <?php endif; ?>
</tbody>