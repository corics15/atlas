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

    <div class="">
      <label for="txtInventoryAdjustmentSearch">Search</label>
      <input
        id="txtInventoryAdjustmentSearch"
        type="text"
        name="keyword"
        value="<?= htmlspecialchars($keyword ?? ''); ?>"
        class="form-control form-control-sm"
        placeholder="Search Adjustment No...">
    </div>

    <div class="" style="min-width:150px;">
      <label for="selInventoryAdjustmentStatus">Status</label>
      <select
        id="selInventoryAdjustmentStatus"
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

    <div class="">
      <button
        type="submit"
        class="btn btn-sm btn-secondary">
        <i class="fas fa-search"></i>
        Search
      </button>
    </div>
  </div>
</form>
