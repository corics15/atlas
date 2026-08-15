<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= config_item('base_url').'assets/images/atlas.ico' ?>" type="image/x-icon">
    <title>Page Not Found | ATLAS</title>
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

      .error-code {
        margin-bottom: 10px;
        font-size: 64px;
        line-height: 1;
        font-weight: 700;
        color: #9f2d2d;
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
      <img src="<?= config_item('base_url').'assets/images/errors/404.png'; ?>" alt="Page not found" class="error-image">
      <div class="error-code">
        404
      </div>
      <h1>
        Page Not Found
      </h1>
      <p>
        The page or record you requested
        could not be found.
      </p>
      <a href="<?= base_url('dashboard') ?>" class="back-link">
        Back to ATLAS
      </a>
    </div>
  </body>
</html>