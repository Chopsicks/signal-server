<!-- index.html -->
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Вход / Регистрация</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
    :root {
      --primary: #8a2be2;
      --primary-light: #9d45e5;
      --primary-dark: #7a1bc9;
      --secondary: #ff6b6b;
      --bg: #121212;
      --card-bg: #1e1e1e;
      --card-border: #333;
      --text: #f0f0f0;
      --text-secondary: #aaa;
      --radius: 12px;
      --transition: all 0.3s ease;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Montserrat', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
      background-image:
        radial-gradient(circle at 10% 20%, rgba(138, 43, 226, 0.15) 0%, transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(255, 107, 107, 0.15) 0%, transparent 20%);
      animation: bgAnimation 20s infinite alternate;
      overflow: hidden;
    }
    @keyframes bgAnimation { from { background-position:0% 0%,100% 100%; } to { background-position:100% 100%,0% 0%; } }
    /* Другие стили для входа и регистрации... */
  </style>
</head>
<body>
  <div class="container">
    <!-- Блоки формы входа/регистрации -->
    <form action="login.php" method="post" enctype="multipart/form-data">
      <!-- поля login, pass, avatar, кнопки -->
    </form>
  </div>
</body>
</html>


<!-- logout.php -->
<?php
session_start();
// Уничтожаем все данные сессии
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
// После уничтожения сессии показываем страницу logout.html
header('Location: logout.html');
exit;
?>


<!-- logout.html -->
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Выход...</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap');
    :root {
      --primary: #8a2be2;
      --secondary: #ff6b6b;
      --bg: #121212;
      --text: #f0f0f0;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Montserrat', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
      background-image:
        radial-gradient(circle at 10% 20%, rgba(138, 43, 226, 0.15) 0%, transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(255, 107, 107, 0.15) 0%, transparent 20%);
      animation: bgAnimation 20s infinite alternate;
    }
    @keyframes bgAnimation { from { background-position:0% 0%,100% 100%; } to { background-position:100% 100%,0% 0%; } }
    h1 {
      font-size: 2.5rem;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: fadeIn 1s ease;
    }
    .loader {
      width: 100px;
      height: 100px;
      border: 8px solid rgba(255,255,255,0.1);
      border-top: 8px solid var(--primary);
      border-radius: 50%;
      margin-top: 30px;
      animation: spin 2s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes fadeIn { from { opacity:0; transform:translateY(-20px);} to { opacity:1; transform:translateY(0);} }
  </style>
</head>
<body>
  <h1>Выход из аккаунта...</h1>
  <div class="loader"></div>
  <script>
    // Через 5 секунд перенаправляем на index.html
    setTimeout(() => window.location.href = 'index.html', 5000);
  </script>
</body>
</html>
