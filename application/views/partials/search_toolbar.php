<form>
  <div class="row mb-3 align-items-end">
    <div class="col-auto">
      <label for="txtGenericSearch">Search</label>
      <input
        id="txtGenericSearch"
        type="text"
        name="keyword"
        value="<?= htmlspecialchars($keyword ?? ''); ?>"
        class="form-control form-control-sm"
        placeholder="<?= $searchPlaceHolder ?? 'Search...' ?>">
    </div>
    <div class="col-auto">
      <button
        type="submit"
        class="btn btn-sm btn-secondary btn-block">
      <i class="fas fa-search"></i>
      Search
      </button>
    </div>
  </div>
</form>