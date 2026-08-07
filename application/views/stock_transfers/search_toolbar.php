<form>
  <div class="d-flex flex-wrap align-items-end mb-3" style="gap:12px;">

    <div style="min-width:160px;">
      <label>Date From</label>
      <input
        type="date"
        name="date_from"
        value="<?= htmlspecialchars($date_from ?: date('Y-m-01')); ?>"
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
      <label for="txtSearchGeneric">Search</label>
      <input
        id="txtSearchGeneric"
        type="text"
        name="keyword"
        value="<?= htmlspecialchars($keyword ?? ''); ?>"
        class="form-control form-control-sm"
        placeholder="<?= $searchPlaceHolder ?>">
    </div>

    <div style="min-width:150px;">
      <label for="selStockTransferStatus">Status</label>
      <select
        id="selStockTransferStatus"
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
        class="btn btn-sm btn-default">
        <i class="fas fa-search"></i>
        Search
      </button>
    </div>
  </div>
</form>