<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
      <?= isset($heading) ? strip_tags($heading) : 'Something went wrong'; ?>
    </title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/atlas.ico') ?>" type="image/x-icon">
    <style>
      * {
        box-sizing: border-box;
      }

      html,
      body {
        height: 100%;
        margin: 0;
      }

      body {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px;
        font-family:
          -apple-system,
          BlinkMacSystemFont,
          "Segoe UI",
          Roboto,
          Arial,
          sans-serif;
        background: #f4f6f9;
        color: #343a40;
      }

      .error-card {
        width: 100%;
        max-width: 560px;
        padding: 40px;
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow:
          0 4px 18px rgba(0, 0, 0, 0.08);
        text-align: center;
      }

      .error-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f8d7da;
        color: #721c24;
        font-size: 28px;
        font-weight: 700;
      }

      h1 {
        margin: 0 0 12px;
        font-size: 24px;
        font-weight: 600;
      }

      p {
        margin: 0;
        color: #6c757d;
        line-height: 1.6;
      }

      .error-message {
        margin-top: 20px;
        padding: 12px 15px;
        background: #f8f9fa;
        border-radius: 5px;
        font-size: 14px;
        color: #495057;
      }

      .back-link {
        display: inline-block;
        margin-top: 25px;
        padding: 9px 18px;
        border-radius: 4px;
        background: #007bff;
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
      }

      .back-link:hover {
        background: #0069d9;
        color: #ffffff;
      }

      .error-image {
        display: block;

        width: 100%;
        max-width: 360px;
        height: auto;

        margin: 0 auto 20px;
      }
    </style>
  </head>
  <body>
    <div class="error-card">
      <img src="<?= base_url('assets/images/errors/404.png'); ?>" alt="Page not found" class="error-image">
      <div class="error-icon">
        !
      </div>
      <h1>
        Something went wrong
      </h1>
      <p>
        We couldn't complete your request.
        Please try again or contact your
        System Administrator.
      </p>
      <?php if (ENVIRONMENT === 'development' && !empty($message)): ?>
      <div class="error-message">
        <?= $message; ?>
      </div>
      <?php endif; ?>
      <a href="javascript:void(0)" onclick="history.back()" class="back-link">
        Back to ATLAS
      </a>
    </div>
  </body>
</html>