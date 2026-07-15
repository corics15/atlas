<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="theme-color" content="#fbba6f">

  <title>Login</title>

  <link rel="stylesheet" href="<?= atlas_asset('assets/adminlte/plugins/fontawesome-free/css/all.min.css'); ?>">
  <link rel="stylesheet" href="<?= atlas_asset('assets/adminlte/dist/css/adminlte.min.css'); ?>">
  <link rel="stylesheet" href="<?= atlas_asset('assets/css/login.css'); ?>">
  <link rel="shortcut icon" href="<?= atlas_asset($app['shortcut_ico']) ?>" type="image/x-icon">

</head>

<body class="hold-transition login-page">

  <div class="login-box">
    <div class="card">

      <div class="card-body login-card-body">
        <h3 class="text-center">Atlas</h3>
        <hr>
        <form id="frmLogin">
          <input type="text"
                class="form-control form-control-sm mb-3"
                name="username"
                placeholder="Username">

          <input type="password"
                class="form-control form-control-sm mb-3"
                name="password"
                placeholder="Password">

          <button type="submit" class="btn btn-sm btn-primary btn-block">
              Login
          </button>
        </form>
      </div>

    </div>
  </div>

  <script>
    window.Atlas = window.Atlas || {};
    Atlas.config = { baseUrl: "<?= base_url(); ?>" };
  </script>

  <script src="<?= base_url('assets/adminlte/plugins/jquery/jquery.min.js'); ?>"></script>
  <script src="<?= base_url('assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
  <script src="<?= base_url('assets/adminlte/dist/js/adminlte.min.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js'); ?>"></script>
  <script src="<?= atlas_asset('assets/js/core/ajax.js'); ?>"></script>
  <script src="<?= atlas_asset('assets/js/core/toast.js'); ?>"></script>
  <script src="<?= atlas_asset('assets/js/core/loader.js'); ?>"></script>
  <script src="<?= atlas_asset('assets/js/core/dialog.js'); ?>"></script>
  <script src="<?= atlas_asset('assets/js/modules/auth.js'); ?>"></script>

</body>
</html>