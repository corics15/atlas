<div class="table-responsive">

  <table
    class="table table-sm table-hover mb-0"
    id="tblDocumentNumbering">

    <thead class="thead-orange">

      <tr>
        <th>Document</th>
        <th width="120" class="text-center">Prefix</th>
        <th width="100" class="text-center">Year</th>
        <th width="110">Year Format</th>
        <th width="90" class="text-center">Separator</th>
        <th width="100" class="text-right">Length</th>
        <th width="110" class="text-right">Next No.</th>
        <th width="110" class="text-center">Active</th>
        <th width="220" class="text-center">Preview</th>
        <th width="90" class="text-center">Save</th>
      </tr>

    </thead>

    <tbody>

      <?php foreach ($documentNumbering as $row): ?>

        <tr
          data-id="<?= (int)$row->id; ?>"
          data-current-year="<?= (int)$row->current_year; ?>">

          <td class="align-middle font-weight-500">
              <?= htmlspecialchars($row->document_name); ?>

            <div class="text-muted small">
              <?= htmlspecialchars($row->document_code); ?>
            </div>
          </td>

          <td>
            <input type="text" class="form-control form-control-sm dn-prefix text-center" value="<?= htmlspecialchars($row->prefix ?? ''); ?>" maxlength="50">
          </td>

          <td class="text-center align-middle">

            <div class="custom-control custom-switch d-inline-block">

              <input type="checkbox" class="custom-control-input dn-include-year" id="dnYear<?= (int)$row->id; ?>" <?= $row->include_year ? 'checked' : ''; ?>>
              <label class="custom-control-label" for="dnYear<?= (int)$row->id; ?>"></label>

            </div>

          </td>

          <td>
            <select class="form-control form-control-sm dn-year-format custom-select">
              <option value="YYYY" <?= strtoupper($row->year_format) === 'YYYY' ? 'selected' : ''; ?>>YYYY</option>
              <option value="YY" <?= strtoupper($row->year_format) === 'YY' ? 'selected' : ''; ?>>YY</option>
            </select>
          </td>

          <td>
            <input type="text" class="form-control form-control-sm text-center dn-separator" value="<?= htmlspecialchars($row->separator); ?>" maxlength="10">
          </td>

          <td>
            <input type="number" class="form-control form-control-sm text-right dn-number-length" min="1" value="<?= (int)$row->number_length; ?>">
          </td>

          <td>
            <input type="number" class="form-control form-control-sm text-right dn-next-number" min="1" value="<?= (int)$row->next_number; ?>">
          </td>

          <td class="text-center">

            <div class="custom-control custom-switch d-inline-block">

              <input type="checkbox" class="custom-control-input dn-active" id="dnActive<?= (int)$row->id; ?>" <?= $row->is_active ? 'checked' : ''; ?>>
              <label class="custom-control-label" for="dnActive<?= (int)$row->id; ?>"></label>

            </div>

          </td>

          <td class="text-center">
            <span class="dn-preview font-sm font-weight-500 text-lightblue">-</span>
          </td>

          <td class="text-center align-middle">

            <button type="button" class="btn btn-sm btn-link btn-save-numbering" title="Save">
              <i class="fas fa-save"></i>
            </button>

          </td>

        </tr>

      <?php endforeach; ?>

    </tbody>

  </table>

</div>