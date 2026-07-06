class AtlasValidation {
  clear() {
    document
      .querySelectorAll('.text-danger')
      .forEach(el => el.textContent = '');
  }

  show(errors) {
    this.clear();
    Object.keys(errors).forEach(field => {
      const element = document.getElementById(
        'err' + this.toPascalCase(field)
      );

      if (element) {
        element.textContent = errors[field];
      }
    });
  }

  toPascalCase(text) {
    return text
      .split('_')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join('');
  }
}
window.Atlas = window.Atlas || {};
window.Atlas.validation = new AtlasValidation();