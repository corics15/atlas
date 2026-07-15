class AtlasTable {

  init(options) {
    this.options = options;

    const selectAll = document.querySelector(options.selectAll);

    const refresh = () => {
      const checkboxes = Array.from(document.querySelectorAll(options.checkbox));
      const checked = Array.from(document.querySelectorAll(`${options.checkbox}:checked`));

      if (selectAll) {
        selectAll.checked =
          checkboxes.length > 0 &&
          checked.length === checkboxes.length;

        selectAll.indeterminate =
          checked.length > 0 &&
          checked.length < checkboxes.length;
      }

      if (typeof options.onChange === 'function') {
        options.onChange(checked);
      }
    };

    if (selectAll) {
      selectAll.addEventListener('change', () => {
        document.querySelectorAll(options.checkbox)
          .forEach(chk => {
            chk.checked = selectAll.checked;
          });
        refresh();
      });

    }

    document.querySelectorAll(options.checkbox)
      .forEach(chk => {
        chk.addEventListener('change', refresh);
      });
    refresh();
  }

  selected(selector = null) {
    const checkbox = selector ?? this.options?.checkbox;
    if (!checkbox) {
      return [];
    }

    return Array.from(document.querySelectorAll(`${checkbox}:checked`));
  }

  selectedCount() {
    return this.selected().length;
  }

  selectedId() {
    const selected = this.selected();

    if (selected.length !== 1) {
      return null;
    }

    return selected[0].value;
  }

  selectedIds() {
    return this.selected().map(chk => chk.value);
  }
}

window.Atlas = window.Atlas || {};
Atlas.table = new AtlasTable();