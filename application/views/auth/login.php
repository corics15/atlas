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

        <div class="login-brand">

          <img
            src="<?= atlas_asset('assets/images/atlas.png'); ?>"
            alt="ATLAS"
          >

          <h3 class="text-cyan">Welcome Back!</h3>

          <p>Sign in to continue to ATLAS</p>

        </div>

        <form id="frmLogin">

          <div class="form-group">
            <label for="username" class="sr-only">
              Username
            </label>

            <input
              type="text"
              id="username"
              class="form-control login-form-control"
              name="username"
              placeholder="Username"
              autocomplete="username"
              autofocus
            >
          </div>

          <div class="form-group">
            <label for="password" class="sr-only">
              Password
            </label>

            <div class="input-group">
              <input
                type="password"
                id="password"
                class="form-control login-form-control"
                name="password"
                placeholder="Password"
                autocomplete="current-password"
              >

              <div class="input-group-append">
                <button
                  type="button"
                  class="btn btn-outline-info login-password-toggle"
                  id="btnTogglePassword"
                  tabindex="-1"
                  aria-label="Show password"
                >
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>

          <button
            type="submit"
            class="btn btn-primary btn-block login-submit"
          >
            Sign In
          </button>

        </form>

        <div class="login-footer">
          © <?= '2026 - '.date('Y'); ?> ATLAS
        </div>

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

  <script>
    const btnTogglePassword = document.getElementById('btnTogglePassword');
    const passwordInput = document.getElementById('password');
    btnTogglePassword?.addEventListener('click', () => {
      const isPassword = passwordInput.type === 'password';

      passwordInput.type = isPassword ? 'text' : 'password';
      const icon = btnTogglePassword.querySelector('i');

      icon.classList.toggle(
        'fa-eye',
        !isPassword
      );

      icon.classList.toggle(
        'fa-eye-slash',
        isPassword
      );

      btnTogglePassword.setAttribute(
        'aria-label',
        isPassword
          ? 'Hide password'
          : 'Show password'
      );
    });
  </script>

</body>
</html>