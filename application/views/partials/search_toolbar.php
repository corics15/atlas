<form data-atlas-filter>
  <div class="d-flex flex-wrap align-items-end mb-3" style="gap:12px;">

    <?php if (!empty($showDateFilter)) : ?>
      <div style="min-width:160px;">
        <label>Date From</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from ?: date('Y-m-01')); ?>" class="form-control form-control-sm">
      </div>

      <div style="min-width:160px;">
        <label>Date To</label>
        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to ?: date('Y-m-d')); ?>" class="form-control form-control-sm">
      </div>
    <?php endif; ?>

    <?php if (!empty($suppliers)) : ?>
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
    <?php endif; ?>

    <?php if (!empty($branches)) : ?>
      <div style="min-width:200px;">
        <label for="selBranchFilter">Branch</label>
        <select id="selBranchFilter" name="branch_id" class="form-control form-control-sm custom-select">
          <option value="">All Branches</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= $branch->id; ?>" <?= ($branch_id == $branch->id) ? 'selected' : ''; ?>>
              <?= htmlspecialchars($branch->branch_name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <?php if (!empty($statuses)) : ?>
      <div style="min-width:150px;">
        <label for="selGenericStatus">Status</label>
        <select id="selGenericStatus" name="status" class="form-control form-control-sm custom-select">
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
    <?php endif; ?>

    <div class="">
      <label for="txtSearchGeneric">Search</label>
      <input id="txtSearchGeneric" type="text" name="keyword" value="<?= htmlspecialchars($keyword ?? ''); ?>" class="form-control form-control-sm" placeholder="<?= $searchPlaceHolder ?>">
    </div>

    <div class="">
      <button type="submit" class="btn btn-sm btn-default"><i class="fas fa-search mr-2"></i>Search</button>
    </div>
  </div>
</form>