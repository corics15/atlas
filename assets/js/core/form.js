class AtlasForm {

  async submit(options) {

    Atlas.validation.clear();

    const formData = new FormData(options.form);

    Atlas.loader.show();

    const result = await Atlas.ajax.post(
      options.url,
      formData
    );

    Atlas.loader.hide();

    if (result.success) {
      if (typeof options.onSuccess === 'function') {
        options.onSuccess(result);
      }
    } else {
      if (result.data?.errors) {
        Atlas.validation.show(result.data.errors);
      } else {
        Atlas.toast.error(result.message);
      }
      if (typeof options.onError === 'function') {
        options.onError(result);
      }
    }
  }
}

window.Atlas = window.Atlas || {};
window.Atlas.form = new AtlasForm();