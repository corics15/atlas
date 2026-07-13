<form>
  <div class="d-flex flex-wrap align-items-end mb-3" style="gap:12px;">

    <div style="min-width:160px;">
      <label>Date From</label>
      <input
        type="date"
        name="date_from"
        value="<?= htmlspecialchars($date_from ?: date('Y-m-d')); ?>"
        class="form-control form-control-sm">
    </div>

    <div style="min-width:160px;">
      <label>Date To</label>
      <input
        type="date"
        name="date_to"
        value="<?= htmlspecialchars($date_to ?: date('Y-m-d')); ?>"
        class="form-control form-control-sm">
    </div>

    <div style="min-width:280px; flex:1;">
      <label for="selSupplierFilter">Supplier</label>
      <select
        id="selSupplierFilter"
        name="supplier_id"
        class="form-control form-control-sm custom-select">
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

    <div style="min-width:150px;">
      <label for="selSupplierStatus">Status</label>
      <select
        id="selSupplierStatus"
        name="status"
        class="form-control form-control-sm custom-select">
        <option value="">All</option>
        <?php foreach ($statuses as $item): ?>
          <option
            value="<?= $item; ?>"
            <?= ($status == $item) ? 'selected' : ''; ?>>
            <?= $item; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <button
        type="submit"
        class="btn btn-sm btn-secondary">
      <i class="fas fa-search"></i>
      Search
      </button>
    </div>

  </div>
</form>