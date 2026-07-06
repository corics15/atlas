<script src="<?= base_url('assets/adminlte/plugins/jquery/jquery.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/adminlte/dist/js/adminlte.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js'); ?>"></script>

<script src="<?= base_url('assets/js/core/ajax.js'); ?>"></script>
<script src="<?= base_url('assets/js/core/toast.js'); ?>"></script>
<script src="<?= base_url('assets/js/core/loader.js'); ?>"></script>
<script src="<?= base_url('assets/js/core/dialog.js'); ?>"></script>
<script src="<?= base_url('assets/js/core/modal.js'); ?>"></script>

<?php if (!empty($pageScript)) : ?>
  <script src="<?= base_url('assets/js/modules/' . $pageScript . '.js'); ?>"></script>
<?php endif ?>