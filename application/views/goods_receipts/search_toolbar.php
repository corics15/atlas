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
      <label for="txtGoodsReceiptsSearch">Search</label>
      <input
        id="txtGoodsReceiptsSearch"
        type="text"
        name="keyword"
        value="<?= htmlspecialchars($keyword ?? ''); ?>"
        class="form-control form-control-sm"
        placeholder="Search...">
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
