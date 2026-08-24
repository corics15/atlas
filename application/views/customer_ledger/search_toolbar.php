<form data-atlas-filter>
  <div class="d-flex flex-wrap align-items-end mb-3" style="gap:12px;">

    <div style="min-width:160px;">
      <label>Date From</label>
      <input type="date" name="date_from" value="<?= htmlspecialchars($date_from ?: date('Y-m-01')); ?>" class="form-control form-control-sm">
    </div>

    <div style="min-width:160px;">
      <label>Date To</label>
      <input type="date" name="date_to" value="<?= htmlspecialchars($date_to ?: date('Y-m-d')); ?>" class="form-control form-control-sm">
    </div>

    <div class="col-lg-4">
      <label for="selCustomer">Customer</label>
      <select id="selCustomer" name="customer_id" class="form-control form-control-sm custom-select">
        <option value="">Select Customer</option>
        <?php foreach ($customers as $item): ?>
          <option
            value="<?= $item->id ?>"
            <?= ((int)$customer_id === (int)$item->id)
              ? 'selected'
              : '' ?>>
            <?= htmlspecialchars($item->customer_name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="">
      <button type="submit" class="btn btn-sm btn-default"><i class="fas fa-search mr-2"></i>Search</button>
    </div>
  </div>
</form>