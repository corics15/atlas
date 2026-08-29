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

    <?php if (!empty($suppliers)): ?>
      <div class="col-md-3">
        <label for="selSupplier">Supplier</label>
        <select id="selSupplier" name="supplier_id" class="form-control form-control-sm custom-select">
          <option value="">Select Supplier</option>
          <?php foreach ($suppliers as $item): ?>
            <option
              value="<?= $item->id ?>"
              <?= ((int)$supplier_id === (int)$item->id)
                ? 'selected'
                : '' ?>>
              <?= htmlspecialchars($item->supplier_name) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <?php if (!empty($customers)): ?>
      <div class="col-md-3">
        <label for="selCustomer">Customer</label>
        <select id="selCustomer" name="customer_id" class="form-control form-control-sm custom-select">
          <option value="">Select Customer</option>
          <?php foreach ($customers as $item): ?>
            <option
              value="<?= $item->id ?>"
              <?= ((int)($customer_id ?? 0) === (int)$item->id)
                ? 'selected'
                : '' ?>>

              <?= htmlspecialchars($item->customer_name) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <div class="">
      <label for="selBranch">Branch</label>
      <select id="selBranch" name="branch_id" class="form-control form-control-sm custom-select">
        <?php foreach ($branches as $branch): ?>
          <option
            value="<?= $branch->id; ?>"
            <?= (int)$branch->id === (int)$branch_id
              ? 'selected'
              : ''; ?>>
            <?= htmlspecialchars($branch->branch_name); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="">
      <button type="submit" class="btn btn-sm btn-default mr-2">
        <i class="fas fa-search mr-1"></i>
        Generate
      </button>

      <a href="<?= base_url('reports/sales-per-supplier'); ?>" class="btn btn-sm btn-default">
        <i class="fas fa-sync-alt mr-1"></i>
        Reset
      </a>
    </div>
  </div>
</form>