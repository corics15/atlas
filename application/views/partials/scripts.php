<script src="<?= base_url('assets/adminlte/plugins/jquery/jquery.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/dist/js/adminlte.min.js'); ?>"></script>
<script src="<?= atlas_asset('assets/adminlte/plugins/select2/js/select2.full.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js'); ?>"></script>

<script src="<?= atlas_asset('assets/js/core/ajax.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/toast.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/loader.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/dialog.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/modal.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/validation.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/form.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/layout.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/page.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/print.js'); ?>"></script>

<script src="<?= atlas_asset('assets/js/core/select.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/table.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/product-finder.js'); ?>"></script>
<script src="<?= atlas_asset('assets/js/core/format.js'); ?>"></script>

<?php if (!empty($pageScript)) : ?>
  <script src="<?= atlas_asset('assets/js/modules/' . $pageScript . '.js'); ?>"></script>
<?php endif ?>