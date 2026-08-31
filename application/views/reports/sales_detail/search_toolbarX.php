<form data-atlas-filter>
  <div class="row mb-3 align-items-end" style="gap:12px;">

    <div style="min-width:160px;">
      <label for="dtDateFrom">Date From</label>
      <input id="dtDateFrom" type="date" name="date_from" value="<?= htmlspecialchars($date_from ?: date('Y-m-01')); ?>" class="form-control form-control-sm">
    </div>

    <div style="min-width:160px;">
      <label for="dtDateTo">Date To</label>
      <input id="dtDateTo" type="date" name="date_to" value="<?= htmlspecialchars($date_to ?: date('Y-m-d')); ?>" class="form-control form-control-sm">
    </div>

    <div style="min-width:280px; flex:1;">
      <label for="selSupplierFilter">Supplier</label>
      <select id="selSupplierFilter" name="supplier_id" class="form-control form-control-sm custom-select">
        <option value="">All Suppliers</option>
        <?php foreach ($suppliers as $supplier): ?>
          <option
            value="<?= $supplier->id; ?>"
            <?= ($supplier_id == $supplier->id) ? 'selected' : ''; ?>>
            <?= htmlspecialchars($supplier->supplier_name); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="">
      <label for="selSalesman">Salesman</label>
      <select id="selSalesman" name="salesman_id" class="form-control form-control-sm custom-select">
        <option value="">Select Salesman</option>
        <?php foreach ($salesmen as $item): ?>
          <option
            value="<?= $item->id ?>"
            <?= ((int)($salesman_id ?? 0) === (int)$item->id)
              ? 'selected'
              : '' ?>>

            <?= htmlspecialchars($item->salesman_name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="">
      <label for="txtGenericSearch">Search</label>
      <input id="txtGenericSearch" type="text" name="keyword" value="<?= htmlspecialchars($keyword ?? ''); ?>" class="form-control form-control-sm" placeholder="<?= $searchPlaceHolder ?? 'Search...' ?>">
    </div>

    <div style="min-width:150px;">
      <button type="submit" class="btn btn-sm btn-default">
        <i class="fas fa-search mr-2"></i>
        Search
      </button>
    </div>

  </div>
</form>