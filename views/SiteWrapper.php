<html>
  <head>
    <title><?=$this->title?></title>
    <style type="text/css">
      body {
        background-color: white;
      }
      .bueno.body div,
      .bueno.body td {
        font: normal 12px arial;
      }
      .bueno.body table {
        border: 1px solid #cccccc;
        border-collapse: collapse;
      }
      .bueno.body div,
      .bueno.body table {
        margin: 0 auto;
      }
      .bueno.body input {
        border: 1px solid #555555;
        background-color: #dedede;
      }
      .bueno.body input.submit {
        background-color: #ffe2ac;
        border: 1px solid orange;
      }
      .bueno.body div.wrap {
        margin: 0 auto;
        padding: 25px 0px;
        width: 700px;
      }
      .bueno.body div.body {
        width: 100%;
        border: 1px solid #cccccc;
        background-color: transparent;
        overflow: auto;
      }
    </style>
  </head>
  <body>
    <div class="bueno wrap">
      <div class="bueno body">
        <?=$this->body ?>
      </div>
      <?=$this->poweredby?>
    </div>
  </body>
</html>
