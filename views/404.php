<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>404 — Not Found</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700&family=Work+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
  body{ margin:0; background:#1B1410; color:#F3E8D6; font-family:'Work Sans',sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; text-align:center; }
  h1{ font-family:'Fraunces',serif; font-size:48px; margin:0 0 12px; color:#C89B3C; }
  p{ color:#C9B995; margin-bottom:24px; }
  a{ color:#E7C579; text-decoration:none; font-weight:600; }
</style>
</head>
<body>
  <div>
    <h1>404</h1>
    <p>That page doesn't exist at King's Cut Saloon.</p>
    <a href="<?= (defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '') ?>/">&larr; Back to Home</a>
  </div>
</body>
</html>
