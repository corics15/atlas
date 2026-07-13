<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="chkSelectAllSupplier">
        <label
          class="custom-control-label"
          for="chkSelectAllSupplier">
        </label>
      </div>
    </th>
    <th>Supplier</th>
    <th>Terms</th>
    <th>Contact Person</th>
    <th>Contact No.</th>
    <th class="text-center">Email</th>
    <th>Address</th>
    <th>TIN</th>
    <th class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($suppliers)) : ?>
  <?php foreach ($suppliers as $supplier) : ?>
  <tr>
    <td class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input chkSupplier"
          id="chkSupplier<?= $supplier->id; ?>"
          value="<?= $supplier->id; ?>">
        <label
          class="custom-control-label"
          for="chkSupplier<?= $supplier->id; ?>">
        </label>
      </div>
    </td>
    <td><?= htmlspecialchars($supplier->supplier_name); ?></td>
    <td><?= htmlspecialchars($supplier->terms_name); ?></td>
    <td>
      <?= htmlspecialchars($supplier->contact_person); ?>
    </td>
    <td>
      <?php
        $contactNo = null;
        $mobile    = trim($supplier->mobile_no ?? '');
        $telephone = trim($supplier->telephone_no ?? '');

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
    <td class="text-center"><?= htmlspecialchars($supplier->email_address) ?></td>
    <td class="supplier-address"><?= htmlspecialchars($supplier->address) ?></td>
    <td><?= htmlspecialchars($supplier->tin_no) ?></td>
    <td class="text-center">
      <?= $supplier->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php else : ?>
  <tr>
    <td colspan="5" class="text-center">
      No records found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>