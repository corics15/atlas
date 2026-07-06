<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#fbba6f">

    <title><?= $app['app_name']; ?></title>

    <link rel="stylesheet" href="<?= atlas_asset('assets/adminlte/plugins/fontawesome-free/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= atlas_asset('assets/adminlte/dist/css/adminlte.min.css'); ?>">
    <link rel="stylesheet" href="<?= atlas_asset('assets/css/bootstrap-4.css'); ?>">

    <link rel="shortcut icon" href="<?= $app['shortcut_ico'] ?>" type="image/x-icon">
  </head>

  <body class="hold-transition sidebar-mini layout-fixed">

    <div class="wrapper">
      <?php $this->load->view('partials/navbar'); ?>
      <?php $this->load->view('partials/sidebar'); ?>

      <div class="content-wrapper">
        <section class="content pt-3">
          <div class="container-fluid">
            <?php $this->load->view($content); ?>
          </div>
        </section>
      </div>

      <?php $this->load->view('partials/footer'); ?>
    </div>

    <script>
      window.Atlas = window.Atlas || {};
      Atlas.config = { baseUrl: "<?= base_url(); ?>" };
    </script>

    <?php $this->load->view('partials/scripts'); ?>

  </body>
</html>