  <script>
    window.onload = function () {
      window.print();
      window.onafterprint = function () {
        window.close();
      };
    };
  </script>
  </body>
</html>