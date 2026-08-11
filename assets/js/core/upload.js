class AtlasUpload {

  /**
   * Get the first selected file from an input.
   */
  getFile(input) {
    const element = typeof input === 'string' ? document.querySelector(input) : input;
    if (!element || !element.files) {
      return null;
    }
    return element.files.length ? element.files[0] : null;
  }


  /**
   * Validate a selected file.
   *
   * options:
   *   types   => array of allowed MIME types
   *   maxSize => maximum size in bytes
   */
  validate(file, options = {}) {
    if (!file) {
      return {
        valid: false,
        message: 'Please select a file.'
      };
    }

    const types =
      Array.isArray(options.types)
        ? options.types
        : [];

    const maxSize = Number(options.maxSize) || 0;
    if (types.length && !types.includes(file.type)) {
      return {
        valid: false,
        message: 'Invalid file type.'
      };
    }

    if (maxSize > 0 && file.size > maxSize) {
      return {
        valid: false,
        message: 'File size exceeds the allowed limit.'
      };
    }

    return {
      valid: true,
      message: ''
    };
  }

  /**
   * Preview an image file.
   */
  preview(file, target) {
    const element = typeof target === 'string' ? document.querySelector(target) : target;
    if (!element || !file) {
      return false;
    }

    if (!file.type.startsWith('image/')) {
      return false;
    }

    const objectUrl = URL.createObjectURL(file);

    element.src = objectUrl;
    element.hidden = false;

    /**
     * Release the temporary object URL
     * after the image has loaded.
     */
    element.onload = () => {
      URL.revokeObjectURL(objectUrl);
    };

    return true;
  }

  /**
   * Clear a file input.
   */
  clear(input) {
    const element = typeof input === 'string' ? document.querySelector(input) : input;
    if (!element) {
      return;
    }
    element.value = '';
  }

  /**
   * Create a FormData object from a form.
   */
  formData(form) {
    const element = typeof form === 'string' ? document.querySelector(form) : form;
    if (!element) {
      return null;
    }
    return new FormData(element);
  }
}

window.Atlas = window.Atlas || {};
Atlas.upload = new AtlasUpload();