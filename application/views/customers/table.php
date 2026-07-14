<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="chkSelectAllCustomer">
        <label
          class="custom-control-label"
          for="chkSelectAllCustomer">
        </label>
      </div>
    </th>
    <th>Customer</th>
    <th>Contact Person</th>
    <th>Contact No.</th>
    <th>Salesman</th>
    <th class="text-center">Terms</th>
    <th class="text-right">Credit Limit</th>
    <th class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($customers)) : ?>
  <?php foreach ($customers as $customer) : ?>
  <tr>
    <td class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input chkCustomer"
          id="chkCustomer<?= $customer->id; ?>"
          value="<?= $customer->id; ?>">
        <label
          class="custom-control-label"
          for="chkCustomer<?= $customer->id; ?>">
        </label>
      </div>
    </td>
    <td><?= htmlspecialchars($customer->customer_name); ?></td>
    <td><?= htmlspecialchars($customer->contact_person); ?></td>
    <td>
      <?php
        $contactNo = null;
        $mobile    = trim($customer->mobile_no ?? '');
        $telephone = trim($customer->telephone_no ?? '');

        if ($mobile !== '' && $telephone !== '') {
            $contactNo = htmlspecialchars($mobile . ' / ' . $telephone);
        } elseif ($mobile !== '') {
            $contactNo = htmlspecialchars($mobile);
        } elseif ($telephone !== '') {
            $contactNo = htmlspecialchars($telephone);
        }
        if ($contactNo !== null) {
            echo $contactNo;
        }
      ?>
    </td>
    <td><?= $customer->salesman_name ?></td>
    <td class="text-center"><?= $customer->terms_name ?></td>
    <td class="text-right"><?= $customer->credit_limit ?></td>
    <td class="text-center">
      <?= $customer->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php else : ?>
  <tr>
    <td colspan="8" class="text-center">
      No records found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>