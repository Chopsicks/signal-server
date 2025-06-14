<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8');

// Путь к папке с аватарками
define('AVATARS_DIR', 'avatars/');

// Функция для получения аватарки пользователя
function getAvatar($username) {
    $avatarPath = AVATARS_DIR . $username . '.png';
    if (file_exists($avatarPath)) {
        return $avatarPath;
    }
    return null;
}

// Статусы пользователей (в реальном приложении это бы бралось из БД)
$userStatuses = [
    $user => 'online' // Текущий пользователь всегда онлайн
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>SecureChat</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
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
      --error: #ff5555;
      --success: #4caf50;
      --radius: 12px;
      --transition: all 0.3s ease;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Montserrat', sans-serif;
      height: 100vh;
      display: flex;
      overflow: hidden;
      background-image:
        radial-gradient(circle at 10% 20%, rgba(138, 43, 226, 0.15) 0%, transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(255, 107, 107, 0.15) 0%, transparent 20%);
    }

    /* Sidebar */
    #sidebar {
      width: 280px;
      background: var(--card-bg);
      display: flex;
      flex-direction: column;
      border-right: 1px solid var(--card-border);
      box-shadow: 5px 0 15px rgba(0,0,0,0.3);
      z-index: 10;
    }

    #sidebar header {
      padding: 1.2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: rgba(0,0,0,0.2);
      border-bottom: 1px solid var(--card-border);
    }

    #sidebar header strong {
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    #sidebar header button {
      background: none;
      border: none;
      color: var(--text-secondary);
      cursor: pointer;
      font-size: 1.2rem;
      position: relative;
      transition: var(--transition);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #sidebar header button:hover {
      color: var(--primary);
      background: rgba(138, 43, 226, 0.1);
    }

    #reqCount {
      position: absolute;
      top: -2px;
      right: -2px;
      background: var(--secondary);
      color: #fff;
      font-size: 0.7rem;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    #reqCount:empty {
      display: none;
    }

    #contacts {
      flex: 1;
      overflow-y: auto;
      list-style: none;
      padding: 10px 0;
    }

    #contacts li {
      padding: 12px 20px;
      cursor: pointer;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      transition: var(--transition);
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    #contacts li:hover, #contacts .active {
      background: rgba(138, 43, 226, 0.1);
    }

    #contacts li::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 4px;
      background: var(--primary);
      transform: translateX(-100%);
      transition: var(--transition);
    }

    #contacts li:hover::before, #contacts .active::before {
      transform: translateX(0);
    }

    #contacts li .avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 12px;
      font-size: 1rem;
      color: white;
      position: relative;
      overflow: hidden;
      background-size: cover;
      background-position: center;
    }

    #contacts li .avatar::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, transparent, rgba(255,255,255,0.2));
      opacity: 0;
      transition: var(--transition);
    }

    #contacts li:hover .avatar::after {
      opacity: 1;
    }

    #contacts li .info {
      flex: 1;
      min-width: 0;
    }

    #contacts li .name {
      font-weight: 500;
      font-size: 1rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    #contacts li .status {
      font-size: 0.8rem;
      color: var(--text-secondary);
      display: flex;
      align-items: center;
      gap: 5px;
    }

    #contacts li .status .indicator {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--text-secondary);
    }

    #contacts li .status.online .indicator {
      background: #4caf50;
      box-shadow: 0 0 8px #4caf50;
    }

    #contacts li .status.offline .indicator {
      background: #e53935;
    }

    #sidebar footer {
      padding: 15px 20px;
      background: rgba(0,0,0,0.2);
      border-top: 1px solid var(--card-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.95rem;
    }

    #sidebar footer .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    #sidebar footer .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      background-size: cover;
      background-position: center;
    }

    #sidebar footer .actions {
      display: flex;
      gap: 15px;
    }

    #sidebar footer i {
      cursor: pointer;
      color: var(--text-secondary);
      transition: var(--transition);
      font-size: 1.1rem;
    }

    #sidebar footer i:hover {
      color: var(--primary);
    }

    /* Chat area */
    #chat {
      flex: 1;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow: hidden;
    }

    /* User panel */
    #userPanel {
      display: flex;
      align-items: center;
      padding: 15px 25px;
      background: rgba(0,0,0,0.2);
      border-bottom: 1px solid var(--card-border);
      position: relative;
      z-index: 5;
    }

    #userPanel::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(138, 43, 226, 0.05));
      z-index: -1;
    }

    #userPanel .avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 1.2rem;
      color: white;
      font-weight: bold;
      flex-shrink: 0;
      position: relative;
      overflow: hidden;
      background-size: cover;
      background-position: center;
    }

    #userPanel .avatar::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, transparent, rgba(255,255,255,0.2));
    }

    #userPanel .info {
      flex: 1;
      min-width: 0;
    }

    #userPanel .info .name {
      font-weight: 600;
      font-size: 1.2rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 3px;
    }

    #userPanel .info .status {
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    #userPanel .info .status .indicator {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--text-secondary);
    }

    #userPanel .info .status.online .indicator {
      background: #4caf50;
      box-shadow: 0 0 8px #4caf50;
    }

    #userPanel .info .status.offline .indicator {
      background: #e53935;
    }

    #userPanel .actions {
      display: flex;
      align-items: center;
      margin-left: 15px;
      gap: 10px;
    }

    #userPanel .actions button {
      background: none;
      border: none;
      color: var(--text-secondary);
      cursor: pointer;
      padding: 8px;
      border-radius: 50%;
      transition: var(--transition);
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #userPanel .actions button:hover {
      color: var(--primary);
      background: rgba(138, 43, 226, 0.1);
    }

    #userPanel .actions button.delete:hover {
      color: var(--secondary);
      background: rgba(255, 107, 107, 0.1);
    }

    /* Messages area */
    #msgs {
      flex: 1;
      padding: 25px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    .msg {
      max-width: 75%;
      margin: 15px 0;
      padding: 15px 20px;
      border-radius: 20px;
      word-wrap: break-word;
      position: relative;
      transition: var(--transition);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      animation: msgAppear 0.3s ease-out;
    }

    @keyframes msgAppear {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .msg.other {
      align-self: flex-start;
      background: var(--card-bg);
      border: 1px solid rgba(255,255,255,0.05);
    }

    .msg.self {
      align-self: flex-end;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #fff;
    }

    .msg .text {
      font-size: 1.05rem;
      line-height: 1.5;
    }

    .msg .meta {
      font-size: 0.8rem;
      opacity: 0.8;
      margin-top: 8px;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 8px;
    }

    .msg .meta .time {
      font-style: italic;
    }

    .msg .meta .status {
      display: flex;
      align-items: center;
    }

    .typing-indicator {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      background: var(--card-bg);
      border-radius: 25px;
      margin: 10px 0;
      align-self: flex-start;
      max-width: 130px;
      border: 1px solid rgba(255,255,255,0.05);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .typing-indicator span {
      width: 8px;
      height: 8px;
      background: var(--text-secondary);
      border-radius: 50%;
      margin: 0 3px;
      animation: typing 1.4s infinite ease-in-out;
    }

    .typing-indicator span:nth-child(1) { animation-delay: 0s; }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typing {
      0%, 60%, 100% { transform: translateY(0); }
      30% { transform: translateY(-6px); }
    }

    /* Input area */
    #inputBar {
      display: flex;
      gap: 15px;
      padding: 20px;
      background: rgba(0,0,0,0.2);
      align-items: center;
      position: relative;
      border-top: 1px solid var(--card-border);
    }

    #inputBar::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(138, 43, 226, 0.05));
      z-index: -1;
    }

    .inputWrapper {
      position: relative;
      flex: 1;
      max-height: 120px;
    }

    .inputWrapper textarea {
      width: 100%;
      min-height: 50px;
      padding: 15px 50px 15px 20px;
      border: none;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.05);
      color: var(--text);
      font-size: 1.05rem;
      resize: none;
      line-height: 1.5;
      overflow: hidden;
      font-family: 'Montserrat', sans-serif;
      transition: var(--transition);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .inputWrapper textarea:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.08);
      box-shadow: 0 0 0 2px var(--primary);
    }

    .inputWrapper #emojiBtn {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-secondary);
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0;
      height: 100%;
      display: flex;
      align-items: center;
      z-index: 2;
      transition: var(--transition);
    }

    .inputWrapper #emojiBtn:hover {
      color: var(--primary);
      transform: translateY(-50%) scale(1.1);
    }

    .inputWrapper #sendBtn {
      position: absolute;
      right: 60px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-secondary);
      font-size: 1.4rem;
      cursor: pointer;
      padding: 0;
      height: 100%;
      display: flex;
      align-items: center;
      z-index: 2;
      transition: var(--transition);
    }

    .inputWrapper #sendBtn:hover {
      color: var(--primary);
      transform: translateY(-50%) scale(1.1);
    }

    /* Emoji picker */
    #emojiPicker {
      position: absolute;
      bottom: 5.5rem;
      right: 2.5rem;
      width: 300px;
      height: 250px;
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: var(--radius);
      display: none;
      flex-direction: column;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0,0,0,0.3);
      z-index: 100;
    }

    #emojiPicker.active {
      display: flex;
      animation: popIn 0.3s ease-out;
    }

    @keyframes popIn {
      0% { transform: translateY(20px); opacity: 0; }
      100% { transform: translateY(0); opacity: 1; }
    }

    #emojiPicker input {
      width: 90%;
      margin: 15px auto;
      padding: 12px 15px;
      border-radius: 8px;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: var(--text);
      font-family: 'Montserrat', sans-serif;
      font-size: 1rem;
      transition: var(--transition);
    }

    #emojiPicker input:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.08);
      box-shadow: 0 0 0 2px var(--primary);
    }

    #emojiGrid {
      flex: 1;
      padding: 10px;
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 8px;
      overflow-y: auto;
    }

    #emojiGrid span {
      cursor: pointer;
      font-size: 1.6rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s;
      padding: 5px;
      border-radius: 8px;
    }

    #emojiGrid span:hover {
      transform: scale(1.2);
      background: rgba(138, 43, 226, 0.1);
    }

    /* Custom scrollbars */
    #contacts::-webkit-scrollbar,
    #msgs::-webkit-scrollbar,
    #emojiPicker::-webkit-scrollbar {
      width: 8px;
    }

    #contacts::-webkit-scrollbar-thumb,
    #msgs::-webkit-scrollbar-thumb,
    #emojiPicker::-webkit-scrollbar-thumb {
      background: var(--primary);
      border-radius: 4px;
    }

    #contacts::-webkit-scrollbar-track,
    #msgs::-webkit-scrollbar-track,
    #emojiPicker::-webkit-scrollbar-track {
      background: rgba(255,255,255,0.05);
    }

    /* Toast-like notification */
    .toast {
      position: fixed;
      bottom: 1.5rem;
      right: 1.5rem;
      background: var(--card-bg);
      color: var(--text);
      padding: 15px 20px;
      border-radius: var(--radius);
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      z-index: 1000;
      display: flex;
      align-items: center;
      gap: 12px;
      border-left: 4px solid var(--primary);
      animation: slideIn 0.4s ease-out, fadeOut 0.4s ease 2.6s forwards;
    }

    .toast.success { border-left-color: var(--success); }
    .toast.error { border-left-color: var(--error); }

    .toast i {
      font-size: 1.5rem;
    }

    .toast.success i { color: var(--success); }
    .toast.error i { color: var(--error); }

    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    @keyframes fadeOut {
      to { opacity: 0; }
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal.active {
      display: flex;
      animation: fadeIn 0.3s ease-out;
    }

    .modal .box {
      background: var(--card-bg);
      padding: 30px;
      border-radius: var(--radius);
      width: 90%;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 20px 50px rgba(0,0,0,0.4);
      border: 1px solid var(--card-border);
      position: relative;
      overflow: hidden;
    }

    .modal .box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .modal .box h3 {
      margin-bottom: 25px;
      font-weight: 500;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
    }

    .modal .box h3 i {
      color: var(--primary);
      font-size: 1.8rem;
    }

    .modal .box input {
      width: 100%;
      padding: 14px 20px;
      margin-bottom: 20px;
      border: none;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.05);
      color: var(--text);
      font-family: 'Montserrat', sans-serif;
      font-size: 1rem;
      transition: var(--transition);
    }

    .modal .box input:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.08);
      box-shadow: 0 0 0 2px var(--primary);
    }

    .modal .box .buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 15px;
    }

    .modal .box button {
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-family: 'Montserrat', sans-serif;
      font-weight: 600;
      font-size: 1rem;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .modal .box button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: 0.5s;
    }

    .modal .box button:hover::before {
      left: 100%;
    }

    .modal .box #doAdd {
      background: var(--primary);
      color: white;
    }

    .modal .box #doAdd:hover {
      background: var(--primary-light);
      transform: translateY(-2px);
    }

    .modal .box .close {
      background: rgba(255,255,255,0.1);
      color: var(--text);
    }

    .modal .box .close:hover {
      background: rgba(255,255,255,0.2);
      transform: translateY(-2px);
    }

    /* Empty state */
    .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      text-align: center;
      padding: 30px;
      opacity: 0.6;
    }

    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      color: var(--primary);
      opacity: 0.7;
    }

    .empty-state h3 {
      font-size: 1.8rem;
      margin-bottom: 15px;
      font-weight: 500;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .empty-state p {
      font-size: 1.1rem;
      max-width: 500px;
      line-height: 1.6;
      color: var(--text-secondary);
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* Responsive */
    @media (max-width: 768px) {
      #sidebar {
        width: 70px;
      }

      #sidebar header strong span,
      #sidebar footer .username,
      #contacts li .info,
      #userPanel .info .status span {
        display: none;
      }

      #contacts li {
        justify-content: center;
        padding: 15px 0;
      }

      #contacts li .avatar {
        margin-right: 0;
      }

      #userPanel .info .name {
        font-size: 1rem;
      }

      .msg {
        max-width: 85%;
      }
    }
    /* В ваш основной CSS */
.avatar-img {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  object-fit: cover;
  display: block;
}
/* Контейнер‑обёртка для аватарки в футере */
.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;          /* обрезаем всё, что выходит за круг */
  display: inline-block;     /* чтобы не растягивался на всю строку */
  vertical-align: middle;
}

/* Сами картинки-аватарки */
.user-avatar img {
  width: 100%;               /* по ширине контейнера */
  height: 100%;              /* по высоте контейнера */
  object-fit: cover;         /* центрируем и обрезаем по краям */
  display: block;
}

#clearChatBtn {
  display: none; /* скрыта по умолчанию */
}
#clearChatBtn.visible {
  display: inline-block; /* или flex, если нужно */
}
#emojiGrid::-webkit-scrollbar {
  width: 6px;             /* Толщина полосы */
}

#emojiGrid::-webkit-scrollbar-track {
  background: transparent; /* Фон дорожки прокрутки */
  border-radius: 3px;
}

#emojiGrid::-webkit-scrollbar-thumb {
  background-color: #888;  /* Серый цвет ползунка */
  border-radius: 3px;
  border: 1.5px solid transparent; /* Отступы вокруг */
  background-clip: content-box;
  transition: background-color 0.3s ease;
}

#emojiGrid::-webkit-scrollbar-thumb:hover {
  background-color: #555; /* Темнее при наведении */
}

/* Для Firefox */
#emojiGrid {
  scrollbar-width: thin;
  scrollbar-color: #888 transparent;
}
#emojiGrid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 8px;
  overflow-y: auto;
  overflow-x: hidden;
  height: calc(100% - 50px);

  /* Добавляем: */
  align-content: start;  /* выравнивание сетки по верхнему краю */
}

  </style>

</head>
<body>
  <div id="sidebar">
    <header>
      <strong><i class="fa fa-comments"></i> <span>VrimeChat</span></strong>
      <div>
        <button id="requestsBtn" title="Уведомления"><i class="fa fa-bell"></i><span id="reqCount"></span></button>
        <button id="addBtn" title="Добавить друга"><i class="fa fa-user-plus"></i></button>
      </div>
    </header>
    <ul id="contacts"></ul>
    <footer>
      <div class="user-info">
        <div class="user-avatar" id="currentUserAvatarFooter"></div>
        <div class="username"><?= $user ?></div>
      </div>
      <div class="actions">
        <i class="fa fa-cog" title="Настройки" id="settingsBtn"></i>
        <i class="fa fa-sign-out-alt" title="Выход" onclick="location.href='logout.php'"></i>
      </div>
    </footer>
  </div>

  <div id="chat">
    <div id="userPanel">
      <div class="avatar" id="currentUserAvatar">
        <i class="fa fa-user"></i>
      </div>
      <div class="info">
        <div class="name" id="currentUserName">Выберите собеседника</div>
        <div class="status offline" id="currentUserStatus">
          <div class="indicator"></div>
          <span>offline</span>
        </div>
      </div>
      <!-- Добавляем кнопки звонков в интерфейс -->

<div class="actions">

  <button title="Удалить из друзей" class="delete" id="deleteFriendBtn">
    <i class="fa fa-user-times"></i>
  </button>

  <button title="Очистить чат" class="clear-chat" id="clearChatBtn">
    <i class="fa fa-trash-alt"></i>
  </button>
</div>


    </div>

    <div id="msgs"></div>

    <div id="inputBar">
      <div class="inputWrapper">
        <textarea id="text" rows="1" placeholder="Введите сообщение..."></textarea>
        <button id="sendBtn" title="Отправить"><i class="fa fa-paper-plane"></i></button>
        <button id="emojiBtn" title="Эмодзи"><i class="fa fa-smile"></i></button>
      </div>
      <div id="emojiPicker"></div>
    </div>
  </div>

  <!-- Modals -->
<div id="addModal" class="modal">
  <div class="box">
    <h3><i class="fa fa-user-plus"></i> Добавить друга</h3>
    <!-- Убираем disabled -->
    <input id="friendLogin" placeholder="Логин друга">
    <div class="buttons">
      <!-- Убираем disabled -->
      <button id="doAdd">Добавить</button>
      <button class="close" data-target="addModal">Отмена</button>
    </div>
  </div>
</div>

  <div id="reqModal" class="modal">
    <div class="box">
      <h3><i class="fa fa-bell"></i> Уведомления</h3>
      <ul id="reqList" style="list-style:none;padding:0;margin:20px 0;"></ul>
      <button class="close" data-target="reqModal">Закрыть</button>
    </div>
  </div>


<div id="settingsModal" class="modal">
  <div class="box">
    <h3><i class="fa fa-cog"></i> Настройки профиля</h3>

    <div style="text-align: center; margin-bottom: 20px;">
      <div id="avatarPreview" style="position: relative; display: inline-block;">
        <div class="avatar" style="width: 100px; height: 100px; font-size: 40px;">
          <?= strtoupper(substr($user, 0, 1)) ?>
        </div>
        <img id="avatarPreviewImg" src="" style="display: none; width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
        <label for="avatarUpload" style="position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer;">
          <i class="fa fa-camera"></i>
        </label>
        <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
      </div>
    </div>

    <div style="margin-bottom: 15px;">
      <label style="display: block; margin-bottom: 5px; font-weight: 500;">Статус</label>
      <div style="display: flex; gap: 10px;">
        <button id="statusRegular" class="status-btn" data-status="regular" style="flex:1; background: var(--light); border: none; padding: 10px; border-radius: 8px; cursor: pointer; color: white;">
          Обычный
        </button>
        <button id="statusInvisible" class="status-btn" data-status="invisible" style="flex:1; background: var(--light); border: none; padding: 10px; border-radius: 8px; cursor: pointer; color: white;">
          Невидимка
        </button>
      </div>
    </div>



    <div class="buttons">
      <button class="close" data-target="settingsModal">Закрыть</button>
    </div>
  </div>
  <!-- Модальное окно звонка -->
<div id="callModal" class="modal">
  <div class="box">
    <h3><i class="fa fa-phone"></i> Звонок</h3>
    <div id="callStatus">Установка соединения...</div>
    <div class="call-controls">
      <button id="endCallBtn" style="background:#e53935;">
        <i class="fa fa-phone-slash"></i> Завершить
      </button>
      <button id="toggleAudioBtn">
        <i class="fa fa-microphone"></i> Микрофон
      </button>
    </div>
    <audio id="remoteAudio" autoplay></audio>
  </div>
</div>
</div>
<button title="Позвонить" class="call" id="callBtn">
    <i class="fa fa-phone"></i>
</button>

<script>
// Добавьте (или проверьте) ниже всех определений функций:
document.getElementById('settingsBtn').onclick = () => {
  toggleModal('settingsModal', true);
};

// Функция для работы с API
const api = (action, params = {}, formData = false) => {
  if (formData) {
    return fetch(`api.php?action=${action}`, {
      method: 'POST',
      body: params
    }).then(r => r.json());
  }
  return fetch(`api.php?action=${action}`, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams(params)
  }).then(r => r.json());
};

// Глобальные переменные
let contacts = [], requests = [], current = null, typingTimeout = null,
    lastRenderedMsgs = [], currentUserAvatar = null;
let isInvisible = false, requestsDisabled = false;

// WebRTC переменные
let peerConnection = null;
let localStream = null;
let signalingSocket = null;
let currentCall = null;
let isAudioMuted = false;

// Элементы DOM
const contactsEl = document.getElementById('contacts');
const msgsEl = document.getElementById('msgs');
const reqCountEl = document.getElementById('reqCount');
const reqListEl = document.getElementById('reqList');
const currentUserNameEl = document.getElementById('currentUserName');
const currentUserStatusEl = document.getElementById('currentUserStatus');
const currentUserAvatarEl = document.getElementById('currentUserAvatar');
const currentUserAvatarFooterEl = document.getElementById('currentUserAvatarFooter');
const deleteFriendBtn = document.getElementById('deleteFriendBtn');
const textarea = document.getElementById('text');
const sendBtn = document.getElementById('sendBtn');
const avatarUpload = document.getElementById('avatarUpload');
const avatarPreviewImg = document.getElementById('avatarPreviewImg');
const statusRegularBtn = document.getElementById('statusRegular');
const statusInvisibleBtn = document.getElementById('statusInvisible');
const disableRequestsCheckbox = document.getElementById('disableRequests');
const addBtn = document.getElementById('addBtn');
const friendLoginInput = document.getElementById('friendLogin');
const doAddBtn = document.getElementById('doAdd');
const callBtn = document.getElementById('callBtn');
const endCallBtn = document.getElementById('endCallBtn');
const toggleAudioBtn = document.getElementById('toggleAudioBtn');

// === Общие функции ===
const toggleModal = (id, show) => {
  const modal = document.getElementById(id);
  if (modal) modal.classList.toggle('active', show);
};

// Исправленная функция для нормализации пути к аватару
function normalizeAvatarPath(path, folder = 'avatars') {
  if (!path) return null;
  if (path.includes(folder)) return path;
  const cleanPath = path.startsWith('/') ? path.substring(1) : path;
  return `/${folder}/${cleanPath}`;
}

// === Загрузка и обновление ===
const loadAll = () => api('load').then(data => {
  contacts = data.contacts || [];
  requests = data.requests || [];
  currentUserAvatar = data.my_avatar || null;

  renderContacts();
  renderMsgs(data.messages || []);
  reqCountEl.textContent = requests.length ? requests.length : '';
  updateAvatarUI();
}).catch(err => console.error('Ошибка загрузки:', err));

clearChatBtn.onclick = () => {
  if (!current) return showToast('Выберите друга', 'error');

  const modal = document.createElement('div');
  modal.className = 'modal active';
  modal.innerHTML = `
    <div class="box">
      <h3><i class="fa fa-trash-alt"></i> Очистить чат</h3>
      <p>Удалить все сообщения с <b>${current}</b>?</p>
      <div class="buttons">
        <button class="confirm-btn" style="background:#e53935;">Очистить</button>
        <button class="cancel-btn">Отмена</button>
      </div>
    </div>`;
  document.body.append(modal);

  modal.querySelector('.confirm-btn').onclick = () => {
    api('clear_chat', { friend: current }).then(() => {
      loadAll();
      showToast('Чат очищен');
      modal.remove();
    });
  };
  modal.querySelector('.cancel-btn').onclick = () => modal.remove();
};

// === Аватар ===
function uploadAvatar(file) {
  const formData = new FormData();
  formData.append('avatar', file);
  api('update_avatar', formData, true).then(response => {
    if (response.ok) {
      currentUserAvatar = normalizeAvatarPath(response.avatar, 'avatars');
      updateAvatarUI();
      showToast('Аватар успешно обновлен');
    } else {
      showToast(response.error || 'Ошибка загрузки аватара', 'error');
    }
  }).catch(() => showToast('Ошибка при обращении к серверу', 'error'));
}

function updateAvatarUI() {
  // футер
  if (currentUserAvatar) {
    const footerUrl = normalizeAvatarPath(currentUserAvatar, 'avatars');
    currentUserAvatarFooterEl.innerHTML =
      `<img src="${footerUrl}" alt="<?= $user ?>" class="avatar-sm">`;
  } else {
    currentUserAvatarFooterEl.textContent = "<?= strtoupper(substr($user, 0, 1)) ?>";
  }

  // превью
  if (currentUserAvatar) {
    avatarPreviewImg.src = currentUserAvatar;
    avatarPreviewImg.style.display = 'block';
    avatarPreviewImg.previousElementSibling.style.display = 'none';
  } else {
    avatarPreviewImg.style.display = 'none';
    avatarPreviewImg.previousElementSibling.style.display = 'flex';
  }
}

avatarUpload.addEventListener('change', function() {
  const file = this.files[0];
  if (!file) return;
  const valid = ['image/jpeg','image/png','image/gif'].includes(file.type);
  if (!valid) return showToast('Недопустимый тип файла', 'error');
  const reader = new FileReader();
  reader.onload = e => {
    avatarPreviewImg.src = e.target.result;
    avatarPreviewImg.style.display = 'block';
    avatarPreviewImg.previousElementSibling.style.display = 'none';
  };
  reader.readAsDataURL(file);
  uploadAvatar(file);
});

// === Статус ===
function updateStatus(status) {
  isInvisible = (status === 'invisible');
  api('update_status', {status}).then(() => {
    showToast(`Статус изменён: ${status==='invisible'?'Невидимка':'Обычный'}`);
  });
}
statusRegularBtn.onclick = () => updateStatus('online');
statusInvisibleBtn.onclick = () => updateStatus('invisible');

// === Отрисовка контактов ===
function renderContacts() {
  contactsEl.innerHTML = '';
  if (contacts.length === 0) {
    contactsEl.innerHTML = `<div style="text-align:center; padding:30px; color:#aaa;">
      <i class="fa fa-users" style="font-size:3rem; margin-bottom:15px;"></i>
      <p>У вас пока нет контактов</p>
    </div>`;
    return;
  }
  contacts.forEach(c => {
    const li = document.createElement('li');
    let avatarContent = c.avatar
      ? `<img src="${normalizeAvatarPath(c.avatar)}" alt="${c.username}" class="avatar-img">`
      : `<div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#3498db,#2980b9);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:1rem;">${c.username.charAt(0).toUpperCase()}</div>`;
    let status = (c.status==='online'&&!isInvisible) ? 'online' : 'offline';
    li.innerHTML = `<div class="avatar">${avatarContent}</div>
      <div class="info">
        <div class="name">${c.username}</div>
        <div class="status ${status}">
          <div class="indicator"></div>
          <span>${status}</span>
        </div>
      </div>`;
    if (current===c.username) li.classList.add('active');
    li.onclick = () => {
      current=c.username;
      loadAll();
      updateUserPanel();
      simulateTyping();
    };
    contactsEl.append(li);
  });
}

// === Панель пользователя ===
function updateUserPanel() {
  if (!current) {
    currentUserNameEl.textContent='Выберите собеседника';
    currentUserAvatarEl.innerHTML='<i class="fa fa-user"></i>';
    currentUserStatusEl.className='status offline';
    currentUserStatusEl.innerHTML='<div class="indicator"></div><span>offline</span>';
    deleteFriendBtn.style.display='none';
    return;
  }
  currentUserNameEl.textContent=current;
  const contact=contacts.find(c=>c.username===current);
  if (contact) {
    if (contact.avatar) {
      currentUserAvatarEl.innerHTML=`<img src="${normalizeAvatarPath(contact.avatar)}" class="avatar-img" style="width:48px;height:48px;">`;
    } else {
      currentUserAvatarEl.innerHTML=`<div style="width:48px;height:48px;border-radius:50%;background:var(--light);display:flex;align-items:center;justify-content:center;color:var(--dark);font-size:20px;font-weight:bold;">${contact.username.charAt(0).toUpperCase()}</div>`;
    }
    const st=(contact.status==='online'&&!isInvisible)?'online':'offline';
    currentUserStatusEl.className=`status ${st}`;
    currentUserStatusEl.innerHTML=`<div class="indicator"></div><span>${st}</span>`;
  }
  deleteFriendBtn.style.display='flex';
}

// === Сообщения ===
function renderMsgs(msgs) {
  if (!current) {
    msgsEl.innerHTML=`<div class="empty-state">
      <i class="fa fa-comments"></i>
      <h3>Начните общение</h3>
      <p>Выберите контакт из списка слева</p>
    </div>`;
    lastRenderedMsgs=[];
    return;
  }
  const filtered=msgs.filter(m=>
    (m.from===current&&m.to==="<?= $user ?>")||(m.to===current&&m.from==="<?= $user ?>")
  );
  if (JSON.stringify(filtered)===JSON.stringify(lastRenderedMsgs)) return;
  lastRenderedMsgs=filtered;
  msgsEl.innerHTML='';
  if (filtered.length===0) {
    msgsEl.innerHTML=`<div class="empty-state">
      <i class="fa fa-comment-alt"></i>
      <h3>Начало великой истории с ${current}</h3>
      <p>Напишите первое сообщение</p>
    </div>`;
    return;
  }
  filtered.forEach(m=>{
    const d=document.createElement('div');
    d.className='msg '+(m.from==="<?= $user ?>"?'self':'other');
    const t=new Date(m.time*1000).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
    d.innerHTML=`<div class="text">${m.text}</div><div class="meta"><div class="time">${t}</div></div>`;
    msgsEl.append(d);
    setTimeout(()=>d.classList.add('show'),10);
  });
  msgsEl.scrollTop=msgsEl.scrollHeight;
}

function simulateTyping() {
  if (!current) return;
  if (typingTimeout) clearTimeout(typingTimeout);
  const ex=document.querySelector('.typing-indicator');
  if (ex) ex.remove();
  const tp=document.createElement('div');
  tp.className='typing-indicator';
  tp.innerHTML='<span></span><span></span><span></span>';
  msgsEl.append(tp);
  msgsEl.scrollTop=msgsEl.scrollHeight;
  typingTimeout=setTimeout(()=>{tp.remove();typingTimeout=null;},3000);
}

// === Запросы ===
document.getElementById('requestsBtn').onclick=()=>{
  reqListEl.innerHTML='';
  if (requests.length===0) {
    reqListEl.innerHTML=`<li style="text-align:center;padding:20px;color:#aaa;">
      <i class="fa fa-bell-slash" style="font-size:2rem;margin-bottom:10px;"></i><p>Все пусто =(</p>
    </li>`;
  } else requests.forEach(r=>{
    const li=document.createElement('li');
    li.style.display='flex'; li.style.alignItems='center'; li.style.justifyContent='space-between'; li.style.marginBottom='15px';
    const av=r.avatar
      ? `<img src="${normalizeAvatarPath(r.avatar,'avatars')}" style="width:30px;height:30px;border-radius:50%;margin-right:10px;">`
      : `<div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#3498db,#2980b9);display:flex;align-items:center;justify-content:center;margin-right:10px;color:#fff;font-weight:bold;">${r.username.charAt(0).toUpperCase()}</div>`;
    li.innerHTML=`<div style="display:flex;align-items:center;">${av}<div style="font-weight:500;">${r.username}</div></div>
      <div style="display:flex;gap:8px;">
        <button class="accept" data-user="${r.username}" style="background:#4caf50;color:#fff;border:none;border-radius:6px;padding:8px 15px;cursor:pointer;"><i class="fa fa-check"></i></button>
        <button class="decline" data-user="${r.username}" style="background:#e53935;color:#fff;border:none;border-radius:6px;padding:8px 15px;cursor:pointer;"><i class="fa fa-times"></i></button>
      </div>`;
    li.querySelector('.accept').onclick=()=>respond(r.username,true);
    li.querySelector('.decline').onclick=()=>respond(r.username,false);
    reqListEl.append(li);
  });
  toggleModal('reqModal',true);
};

function respond(user,ok) {
  api('respond_request',{friend:user,accept:ok?1:0}).then(()=>{toggleModal('reqModal',false);loadAll();});
}

// === Добавление друга ===
addBtn.onclick=()=>toggleModal('addModal',true);
document.getElementById('doAdd').onclick=()=>{
  const f=friendLoginInput.value.trim();
  if (!f) return;
  api('add_friend',{friend:f}).then(()=>{friendLoginInput.value='';toggleModal('addModal',false);showToast('Приглашение отправлено');loadAll();});
};

// === Удаление друга ===
deleteFriendBtn.onclick=()=>{
  if (!current) return showToast('Выберите друга','error');
  const c=document.createElement('div');
  c.className='modal active';
  c.innerHTML=`<div class="box"><h3><i class="fa fa-user-times"></i> Удалить друга</h3><p>Удалить <b>${current}</b>?</p>
    <div class="buttons">
      <button class="confirm-btn" style="background:#e53935;">Удалить</button>
      <button class="cancel-btn">Отмена</button>
    </div></div>`;
  document.body.append(c);
  c.querySelector('.confirm-btn').onclick=()=>{
    api('remove_friend',{friend:current}).then(()=>{current=null;loadAll();updateUserPanel();showToast('Удалён');c.remove();});
  };
  c.querySelector('.cancel-btn').onclick=()=>c.remove();
};

// === Emoji Picker ===
const emojiBtn = document.getElementById('emojiBtn');
const emojiPicker = document.getElementById('emojiPicker');

const emojis = [
  // Smileys & Emotion (40+)
  { char: '😀', keywords: ['улыбающийся', 'счастливый', 'радость'], category: 'smileys' },
  { char: '😁', keywords: ['широкая улыбка', 'улыбка'], category: 'smileys' },
  { char: '😂', keywords: ['смех', 'слезы', 'радость'], category: 'smileys' },
  { char: '🤣', keywords: ['кататься', 'смех', 'очень смешно'], category: 'smileys' },
  { char: '😅', keywords: ['нервный', 'потеет'], category: 'smileys' },
  { char: '😊', keywords: ['улыбка', 'довольный'], category: 'smileys' },
  { char: '😍', keywords: ['любовь', 'сердечки', 'влюблённый'], category: 'smileys' },
  { char: '😘', keywords: ['поцелуй', 'любовь', 'романтика'], category: 'smileys' },
  { char: '😎', keywords: ['крутой', 'очки'], category: 'smileys' },
  { char: '🤔', keywords: ['думает', 'сомнение'], category: 'smileys' },
  { char: '😇', keywords: ['ангел', 'невинность'], category: 'smileys' },
  { char: '😉', keywords: ['подмигивание', 'флирт'], category: 'smileys' },
  { char: '😜', keywords: ['шутка', 'язык'], category: 'smileys' },
  { char: '😝', keywords: ['веселье', 'язык'], category: 'smileys' },
  { char: '😛', keywords: ['высунутый язык'], category: 'smileys' },
  { char: '🤑', keywords: ['деньги', 'жадность'], category: 'smileys' },
  { char: '🤗', keywords: ['объятия', 'радость'], category: 'smileys' },
  { char: '🤐', keywords: ['молчанка', 'секрет'], category: 'smileys' },
  { char: '😴', keywords: ['сон', 'усталость'], category: 'smileys' },
  { char: '😪', keywords: ['сонный', 'усталость'], category: 'smileys' },
  { char: '😷', keywords: ['болен', 'маска'], category: 'smileys' },
  { char: '🤒', keywords: ['болен', 'термометр'], category: 'smileys' },
  { char: '🤕', keywords: ['травма', 'голова'], category: 'smileys' },
  { char: '🤢', keywords: ['тошнота', 'неприятно'], category: 'smileys' },
  { char: '🤮', keywords: ['рвота'], category: 'smileys' },
  { char: '🤧', keywords: ['чихание', 'болезнь'], category: 'smileys' },
  { char: '😵', keywords: ['головокружение'], category: 'smileys' },
  { char: '🤯', keywords: ['взорванный мозг', 'шок'], category: 'smileys' },
  { char: '😡', keywords: ['очень злой', 'ярость'], category: 'smileys' },
  { char: '😠', keywords: ['злой', 'сердитый'], category: 'smileys' },
  { char: '😤', keywords: ['раздражение', 'фырканье'], category: 'smileys' },
  { char: '😭', keywords: ['плакать', 'горе'], category: 'smileys' },
  { char: '😢', keywords: ['слезы', 'грусть'], category: 'smileys' },
  { char: '😥', keywords: ['печаль', 'разочарование'], category: 'smileys' },
  { char: '😓', keywords: ['пот', 'стресс'], category: 'smileys' },
  { char: '😨', keywords: ['страх', 'тревога'], category: 'smileys' },
  { char: '😰', keywords: ['испуг', 'паникует'], category: 'smileys' },
  { char: '😱', keywords: ['крик', 'ужас'], category: 'smileys' },
  { char: '😳', keywords: ['стыд', 'неловкость'], category: 'smileys' },

  // People & Body (40+)
  { char: '👍', keywords: ['палец вверх', 'одобрение'], category: 'people' },
  { char: '👎', keywords: ['палец вниз', 'неодобрение'], category: 'people' },
  { char: '🙏', keywords: ['молится', 'пожалуйста'], category: 'people' },
  { char: '👏', keywords: ['аплодисменты', 'поздравление'], category: 'people' },
  { char: '💪', keywords: ['сила', 'мышцы'], category: 'people' },
  { char: '🤝', keywords: ['рукопожатие', 'дружба'], category: 'people' },
  { char: '👊', keywords: ['кулак', 'удар'], category: 'people' },
  { char: '✊', keywords: ['кулак вверх', 'борьба'], category: 'people' },
  { char: '🤛', keywords: ['кулак влево'], category: 'people' },
  { char: '🤜', keywords: ['кулак вправо'], category: 'people' },
  { char: '👋', keywords: ['приветствие', 'волна'], category: 'people' },
  { char: '🤙', keywords: ['жест'], category: 'people' },
  { char: '🖖', keywords: ['жест', 'Вулкан'], category: 'people' },
  { char: '✌️', keywords: ['мир', 'победа'], category: 'people' },
  { char: '👌', keywords: ['ок', 'согласен'], category: 'people' },
  { char: '👂', keywords: ['ухо', 'слушать'], category: 'people' },
  { char: '👃', keywords: ['нос'], category: 'people' },
  { char: '👀', keywords: ['глаза', 'смотрит'], category: 'people' },
  { char: '🧠', keywords: ['мозг'], category: 'people' },
  { char: '🦵', keywords: ['нога'], category: 'people' },
  { char: '🦶', keywords: ['стопа'], category: 'people' },
  { char: '👶', keywords: ['младенец'], category: 'people' },
  { char: '🧒', keywords: ['ребенок'], category: 'people' },
  { char: '👦', keywords: ['мальчик'], category: 'people' },
  { char: '👧', keywords: ['девочка'], category: 'people' },
  { char: '🧑', keywords: ['человек'], category: 'people' },
  { char: '👨', keywords: ['мужчина'], category: 'people' },
  { char: '👩', keywords: ['женщина'], category: 'people' },
  { char: '🧔', keywords: ['борода'], category: 'people' },
  { char: '👵', keywords: ['бабушка'], category: 'people' },
  { char: '👴', keywords: ['дедушка'], category: 'people' },
  { char: '👲', keywords: ['шляпа'], category: 'people' },
  { char: '👳', keywords: ['турбан'], category: 'people' },
  { char: '🧕', keywords: ['платок'], category: 'people' },
  { char: '🤱', keywords: ['мама с ребёнком'], category: 'people' },
  { char: '🤰', keywords: ['беременная'], category: 'people' },
  { char: '🤓', keywords: ['умный'], category: 'people' },
  { char: '😎', keywords: ['крутой'], category: 'people' },

  // Food & Drink (35+)
  { char: '🍎', keywords: ['яблоко', 'фрукты'], category: 'food' },
  { char: '🍔', keywords: ['бургер', 'еда'], category: 'food' },
  { char: '🍕', keywords: ['пицца', 'еда'], category: 'food' },
  { char: '🍩', keywords: ['пончик', 'сладкое'], category: 'food' },
  { char: '🍰', keywords: ['торт', 'десерт'], category: 'food' },
  { char: '🍇', keywords: ['виноград', 'фрукты'], category: 'food' },
  { char: '🍉', keywords: ['арбуз', 'фрукты'], category: 'food' },
  { char: '🍌', keywords: ['банан', 'фрукты'], category: 'food' },
  { char: '🥦', keywords: ['брокколи', 'овощи'], category: 'food' },
  { char: '🥕', keywords: ['морковь', 'овощи'], category: 'food' },
  { char: '🍗', keywords: ['курица', 'еда'], category: 'food' },
  { char: '🍖', keywords: ['мясо', 'еда'], category: 'food' },
  { char: '☕', keywords: ['кофе', 'напиток'], category: 'food' },
  { char: '🍵', keywords: ['чай', 'напиток'], category: 'food' },
  { char: '🍺', keywords: ['пиво', 'напиток'], category: 'food' },
  { char: '🍷', keywords: ['вино', 'напиток'], category: 'food' },
  { char: '🥤', keywords: ['напиток'], category: 'food' },
  { char: '🍣', keywords: ['суши', 'еда'], category: 'food' },
  { char: '🍜', keywords: ['лапша', 'еда'], category: 'food' },
  { char: '🍞', keywords: ['хлеб'], category: 'food' },
  { char: '🧀', keywords: ['сыр'], category: 'food' },
  { char: '🥩', keywords: ['стейк'], category: 'food' },
  { char: '🍤', keywords: ['креветки'], category: 'food' },
  { char: '🥗', keywords: ['салат'], category: 'food' },
  { char: '🍫', keywords: ['шоколад'], category: 'food' },
  { char: '🍬', keywords: ['конфеты'], category: 'food' },
  { char: '🍭', keywords: ['леденец'], category: 'food' },
  { char: '🍦', keywords: ['мороженое'], category: 'food' },
  { char: '🍪', keywords: ['печенье'], category: 'food' },
  { char: '🥛', keywords: ['молоко'], category: 'food' },
  { char: '🥚', keywords: ['яйцо'], category: 'food' },
  { char: '🍳', keywords: ['яичница'], category: 'food' },
  { char: '🥞', keywords: ['блины'], category: 'food' },

  // Animals & Nature (35+)
  { char: '🐶', keywords: ['собака'], category: 'animals' },
  { char: '🐱', keywords: ['кот'], category: 'animals' },
  { char: '🐭', keywords: ['мышь'], category: 'animals' },
  { char: '🐰', keywords: ['кролик'], category: 'animals' },
  { char: '🦊', keywords: ['лиса'], category: 'animals' },
  { char: '🐻', keywords: ['медведь'], category: 'animals' },
  { char: '🐼', keywords: ['панда'], category: 'animals' },
  { char: '🐨', keywords: ['коала'], category: 'animals' },
  { char: '🐯', keywords: ['тигр'], category: 'animals' },
  { char: '🦁', keywords: ['лев'], category: 'animals' },
  { char: '🐮', keywords: ['корова'], category: 'animals' },
  { char: '🐷', keywords: ['свинья'], category: 'animals' },
  { char: '🐸', keywords: ['лягушка'], category: 'animals' },
  { char: '🐵', keywords: ['обезьяна'], category: 'animals' },
  { char: '🐔', keywords: ['курица'], category: 'animals' },
  { char: '🦉', keywords: ['сова'], category: 'animals' },
  { char: '🦅', keywords: ['орёл'], category: 'animals' },
  { char: '🦆', keywords: ['утка'], category: 'animals' },
  { char: '🦢', keywords: ['лебедь'], category: 'animals' },
  { char: '🐝', keywords: ['пчела'], category: 'animals' },
  { char: '🐛', keywords: ['гусеница'], category: 'animals' },
  { char: '🦋', keywords: ['бабочка'], category: 'animals' },
  { char: '🐌', keywords: ['улитка'], category: 'animals' },
  { char: '🐢', keywords: ['черепаха'], category: 'animals' },
  { char: '🐍', keywords: ['змея'], category: 'animals' },
  { char: '🦎', keywords: ['ящерица'], category: 'animals' },
  { char: '🐙', keywords: ['осьминог'], category: 'animals' },
  { char: '🦀', keywords: ['краб'], category: 'animals' },
  { char: '🐡', keywords: ['рыба'], category: 'animals' },
  { char: '🐠', keywords: ['рыбка'], category: 'animals' },
  { char: '🐳', keywords: ['кит'], category: 'animals' },
  { char: '🐬', keywords: ['дельфин'], category: 'animals' },
  { char: '🦈', keywords: ['акула'], category: 'animals' },
  { char: '🐅', keywords: ['тигр'], category: 'animals' },
  { char: '🐆', keywords: ['леопард'], category: 'animals' },
  { char: '🦧', keywords: ['орангутан'], category: 'animals' },

  // Travel & Places (25+)
  { char: '🚗', keywords: ['машина', 'автомобиль'], category: 'travel' },
  { char: '🚕', keywords: ['такси'], category: 'travel' },
  { char: '🚙', keywords: ['внедорожник'], category: 'travel' },
  { char: '🚌', keywords: ['автобус'], category: 'travel' },
  { char: '🚎', keywords: ['троллейбус'], category: 'travel' },
  { char: '🏎️', keywords: ['гоночная машина'], category: 'travel' },
  { char: '🚓', keywords: ['полицейская машина'], category: 'travel' },
  { char: '🚑', keywords: ['скорая помощь'], category: 'travel' },
  { char: '🚒', keywords: ['пожарная машина'], category: 'travel' },
  { char: '🚐', keywords: ['фургон'], category: 'travel' },
  { char: '🚚', keywords: ['грузовик'], category: 'travel' },
  { char: '🚛', keywords: ['тяжелый грузовик'], category: 'travel' },
  { char: '🚜', keywords: ['трактор'], category: 'travel' },
  { char: '🏍️', keywords: ['мотоцикл'], category: 'travel' },
  { char: '🛵', keywords: ['скутер'], category: 'travel' },
  { char: '🚲', keywords: ['велосипед'], category: 'travel' },
  { char: '🛴', keywords: ['самокат'], category: 'travel' },
  { char: '🛺', keywords: ['рикша'], category: 'travel' },
  { char: '🚂', keywords: ['поезд'], category: 'travel' },
  { char: '✈️', keywords: ['самолет'], category: 'travel' },
  { char: '🛫', keywords: ['взлет самолета'], category: 'travel' },
  { char: '🛬', keywords: ['посадка самолета'], category: 'travel' },
  { char: '🚀', keywords: ['ракета'], category: 'travel' },
  { char: '🛳️', keywords: ['круизный лайнер'], category: 'travel' },
  { char: '⛴️', keywords: ['паром'], category: 'travel' },

  // Objects (30+)
  { char: '📱', keywords: ['смартфон', 'телефон'], category: 'objects' },
  { char: '💻', keywords: ['ноутбук', 'компьютер'], category: 'objects' },
  { char: '⌚', keywords: ['часы'], category: 'objects' },
  { char: '📷', keywords: ['камера', 'фото'], category: 'objects' },
  { char: '🎥', keywords: ['видеокамера'], category: 'objects' },
  { char: '📺', keywords: ['телевизор'], category: 'objects' },
  { char: '📻', keywords: ['радио'], category: 'objects' },
  { char: '🔦', keywords: ['фонарик'], category: 'objects' },
  { char: '💡', keywords: ['лампочка', 'идея'], category: 'objects' },
  { char: '🔌', keywords: ['вилка', 'электричество'], category: 'objects' },
  { char: '🖊️', keywords: ['ручка'], category: 'objects' },
  { char: '✏️', keywords: ['карандаш'], category: 'objects' },
  { char: '📚', keywords: ['книги'], category: 'objects' },
  { char: '📖', keywords: ['открытая книга'], category: 'objects' },
  { char: '🔒', keywords: ['замок', 'безопасность'], category: 'objects' },
  { char: '🔑', keywords: ['ключ'], category: 'objects' },
  { char: '🛏️', keywords: ['кровать'], category: 'objects' },
  { char: '🛋️', keywords: ['диван'], category: 'objects' },
  { char: '🚪', keywords: ['дверь'], category: 'objects' },
  { char: '🪑', keywords: ['стул'], category: 'objects' },
  { char: '🛒', keywords: ['тележка', 'магазин'], category: 'objects' },
  { char: '💣', keywords: ['бомба'], category: 'objects' },
  { char: '🔫', keywords: ['пистолет'], category: 'objects' },
  { char: '🧨', keywords: ['фейерверк'], category: 'objects' },
  { char: '🎈', keywords: ['шарик'], category: 'objects' },
  { char: '🎁', keywords: ['подарок'], category: 'objects' },
  { char: '⌛', keywords: ['песочные часы'], category: 'objects' },
  { char: '⏰', keywords: ['будильник'], category: 'objects' },
  { char: '🕰️', keywords: ['часы настенные'], category: 'objects' },

  // Symbols (20+)
  { char: '❤️', keywords: ['сердце', 'любовь'], category: 'symbols' },
  { char: '💔', keywords: ['разбитое сердце'], category: 'symbols' },
  { char: '⭐', keywords: ['звезда'], category: 'symbols' },
  { char: '🌟', keywords: ['сияние'], category: 'symbols' },
  { char: '✨', keywords: ['блеск', 'звездочки'], category: 'symbols' },
  { char: '⚡', keywords: ['молния', 'энергия'], category: 'symbols' },
  { char: '🔥', keywords: ['огонь'], category: 'symbols' },
  { char: '💧', keywords: ['капля воды'], category: 'symbols' },
  { char: '🌈', keywords: ['радуга'], category: 'symbols' },
  { char: '☀️', keywords: ['солнце'], category: 'symbols' },
  { char: '☁️', keywords: ['облако'], category: 'symbols' },
  { char: '☂️', keywords: ['зонтик'], category: 'symbols' },
  { char: '❄️', keywords: ['снег'], category: 'symbols' },
  { char: '⚠️', keywords: ['внимание', 'опасность'], category: 'symbols' },
  { char: '♻️', keywords: ['переработка'], category: 'symbols' },
  { char: '✅', keywords: ['галочка', 'да'], category: 'symbols' },
  { char: '❌', keywords: ['крестик', 'нет'], category: 'symbols' },
  { char: '🔔', keywords: ['колокольчик'], category: 'symbols' },
  { char: '🚫', keywords: ['запрет'], category: 'symbols' },
  { char: '💯', keywords: ['сто процентов'], category: 'symbols' },

  // Flags (20+)
  { char: '🇷🇺', keywords: ['флаг россия', 'russia'], category: 'flags' },
  { char: '🇺🇸', keywords: ['флаг сша', 'usa'], category: 'flags' },
  { char: '🇬🇧', keywords: ['флаг англия', 'england'], category: 'flags' },
  { char: '🇫🇷', keywords: ['флаг франция', 'france'], category: 'flags' },
  { char: '🇩🇪', keywords: ['флаг германия', 'germany'], category: 'flags' },
  { char: '🇮🇹', keywords: ['флаг италия', 'italy'], category: 'flags' },
  { char: '🇨🇳', keywords: ['флаг китай', 'china'], category: 'flags' },
  { char: '🇯🇵', keywords: ['флаг япония', 'japan'], category: 'flags' },
  { char: '🇰🇷', keywords: ['флаг корея', 'korea'], category: 'flags' },
  { char: '🇨🇦', keywords: ['флаг канада', 'canada'], category: 'flags' },
  { char: '🇧🇷', keywords: ['флаг бразилия', 'brazil'], category: 'flags' },
  { char: '🇮🇳', keywords: ['флаг индия', 'india'], category: 'flags' },
  { char: '🇲🇽', keywords: ['флаг мексика', 'mexico'], category: 'flags' },
  { char: '🇪🇸', keywords: ['флаг испания', 'spain'], category: 'flags' },
  { char: '🇦🇺', keywords: ['флаг австралия', 'australia'], category: 'flags' },

  // Other / Misc (10+)
  { char: '🎉', keywords: ['праздник', 'конфетти'], category: 'other' },
  { char: '🎂', keywords: ['торт', 'день рождения'], category: 'other' },
  { char: '🎶', keywords: ['музыка', 'нотки'], category: 'other' },
  { char: '🎵', keywords: ['музыка'], category: 'other' },
  { char: '🎤', keywords: ['микрофон'], category: 'other' },
  { char: '🎧', keywords: ['наушники'], category: 'other' },
  { char: '📣', keywords: ['громкоговоритель'], category: 'other' },
  { char: '🚀', keywords: ['ракета', 'старт'], category: 'other' },
  { char: '💡', keywords: ['идея', 'лампочка'], category: 'other' },
  { char: '⚡', keywords: ['молния', 'энергия'], category: 'other' }
];

emojiPicker.innerHTML = `
  <input type="text" id="emojiSearch" placeholder="Поиск...">
  <div id="emojiCategories" style="display:flex; justify-content: space-around; margin: 5px 0; font-size: 1.4rem; color: #555;">
    <button data-cat="all" title="Все" style="background:transparent; border:none; cursor:pointer; transition: color 0.3s;">
      <i class="fas fa-list"></i>
    </button>
    <button data-cat="smileys" title="Смайлики" style="background:transparent; border:none; cursor:pointer; transition: color 0.3s;">
      <i class="fas fa-smile"></i>
    </button>
  </div>
  <div id="emojiGrid" style="
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    overflow-y: auto;
    overflow-x: hidden;
    height: calc(100% - 90px);
    align-content: start;
  "></div>
`;

const emojiGrid = document.getElementById('emojiGrid');
const emojiSearch = document.getElementById('emojiSearch');
const emojiCategories = document.getElementById('emojiCategories');

let currentCategory = 'all';

function renderEmojis(filter = '', category = 'all') {
  emojiGrid.innerHTML = '';
  const term = filter.toLowerCase();

  emojis.forEach(({char, keywords, category: cat}) => {
    const matchCategory = category === 'all' || cat === category;
    const matchSearch = char.includes(term) || keywords.some(k => k.includes(term));
    if (matchCategory && matchSearch) {
      const s = document.createElement('span');
      s.textContent = char;
      s.style.cursor = 'pointer';
      s.style.fontSize = '1.6rem';
      s.style.display = 'flex';
      s.style.alignItems = 'center';
      s.style.justifyContent = 'center';
      s.style.padding = '6px';
      s.style.borderRadius = '8px';
      s.style.transition = 'transform 0.2s';
      s.onmouseenter = () => s.style.transform = 'scale(1.2)';
      s.onmouseleave = () => s.style.transform = 'scale(1)';
      s.onclick = () => {
        textarea.value += char;
        textarea.focus();
      };
      emojiGrid.append(s);
    }
  });
}

function highlightCategory(cat) {
  Array.from(emojiCategories.children).forEach(btn => {
    btn.style.color = (btn.getAttribute('data-cat') === cat) ? '#007BFF' : '#555';
  });
}

renderEmojis();
highlightCategory(currentCategory);

emojiSearch.addEventListener('input', ev => {
  renderEmojis(ev.target.value, currentCategory);
});

emojiCategories.addEventListener('click', ev => {
  const targetBtn = ev.target.closest('button');
  if (!targetBtn) return;
  currentCategory = targetBtn.getAttribute('data-cat');
  highlightCategory(currentCategory);
  renderEmojis(emojiSearch.value, currentCategory);
});

emojiBtn.onclick = () => emojiPicker.classList.toggle('active');

document.addEventListener('click', e => {
  if (!emojiBtn.contains(e.target) && !emojiPicker.contains(e.target)) {
    emojiPicker.classList.remove('active');
  }
});

// === Отправка сообщений ===
function sendMessage(){
  const text=textarea.value.trim();
  if (!current||!text) return;
  api('send',{to:current,text}).then(()=>{textarea.value='';loadAll();});
}
textarea.addEventListener('keydown',e=>{
  if(e.key==='Enter'&&!e.shiftKey){
    e.preventDefault();
    sendMessage();
  }
});
sendBtn.onclick=sendMessage;
textarea.addEventListener('input',()=>{
  textarea.style.height='auto';
  textarea.style.height=Math.min(textarea.scrollHeight,120)+'px';
});

// === WebRTC функции ===
function initSignaling() {
  try {
    signalingSocket = new WebSocket('wss://vrime-client.ru:3000');

    signalingSocket.onopen = () => {
      console.log("Signal server connected");
      signalingSocket.send(JSON.stringify({
        type: "login",
        username: "<?= $user ?>"
      }));
    };

    signalingSocket.onmessage = async (event) => {
      try {
        const data = JSON.parse(event.data);
        console.log("WebSocket message:", data);

        switch(data.type) {
          case "offer":
            handleOffer(data);
            break;
          case "answer":
            handleAnswer(data);
            break;
          case "candidate":
            handleCandidate(data);
            break;
          case "call":
            handleIncomingCall(data);
            break;
          case "end":
            endCall();
            break;
        }
      } catch (err) {
        console.error("Error processing WebSocket message:", err);
      }
    };

    signalingSocket.onerror = (error) => {
      console.error("WebSocket error:", error);
    };

    signalingSocket.onclose = () => {
      console.log("WebSocket connection closed");
    };
  } catch (err) {
    console.error("WebSocket initialization failed:", err);
    showToast("Ошибка подключения к серверу звонков", "error");
  }
}

// Обработка входящего звонка
function handleIncomingCall(data) {
  if (currentCall) {
    signalingSocket.send(JSON.stringify({
      type: "busy",
      to: data.from
    }));
    return;
  }

  const accept = confirm(`${data.from} звонит вам. Принять звонок?`);
  if (accept) {
    currentCall = data.from;
    toggleModal('callModal', true);
    createPeerConnection(false);
  } else {
    signalingSocket.send(JSON.stringify({
      type: "reject",
      to: data.from
    }));
  }
}

// Создание PeerConnection
async function createPeerConnection(isInitiator = false) {
  try {
    const config = {
      iceServers: [
        { urls: "stun:stun.l.google.com:19302" },
        { urls: "stun:stun1.l.google.com:19302" }
      ]
    };

    peerConnection = new RTCPeerConnection(config);

    // Получаем локальный поток
    localStream = await navigator.mediaDevices.getUserMedia({
      audio: true,
      video: false
    });

    // Добавляем треки в соединение
    localStream.getTracks().forEach(track => {
      peerConnection.addTrack(track, localStream);
    });

    // Обработчики событий ICE
    peerConnection.onicecandidate = ({ candidate }) => {
      if (candidate && signalingSocket.readyState === WebSocket.OPEN) {
        signalingSocket.send(JSON.stringify({
          type: "candidate",
          to: currentCall,
          candidate: candidate
        }));
      }
    };

    // Получение удаленного потока
    peerConnection.ontrack = (event) => {
      const remoteAudio = document.getElementById('remoteAudio');
      if (remoteAudio && event.streams && event.streams.length > 0) {
        remoteAudio.srcObject = event.streams[0];
        document.getElementById('callStatus').textContent = "Разговор активен";
      }
    };

    peerConnection.oniceconnectionstatechange = () => {
      console.log("ICE connection state:", peerConnection.iceConnectionState);
      if (peerConnection.iceConnectionState === 'disconnected' ||
          peerConnection.iceConnectionState === 'failed') {
        endCall();
      }
    };

    // Для инициатора создаем предложение
    if (isInitiator) {
      const offer = await peerConnection.createOffer();
      await peerConnection.setLocalDescription(offer);

      if (signalingSocket.readyState === WebSocket.OPEN) {
        signalingSocket.send(JSON.stringify({
          type: "offer",
          to: currentCall,
          offer: offer
        }));
      }
    }
  } catch (err) {
    console.error("Ошибка создания соединения:", err);
    showToast("Ошибка создания соединения", "error");
    endCall();
  }
}

// Обработка полученного предложения
async function handleOffer(data) {
  try {
    if (!peerConnection) await createPeerConnection(false);

    await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);

    if (signalingSocket.readyState === WebSocket.OPEN) {
      signalingSocket.send(JSON.stringify({
        type: "answer",
        to: data.from,
        answer: answer
      }));
    }
  } catch (err) {
    console.error("Ошибка обработки предложения:", err);
    endCall();
  }
}

// Обработка полученного ответа
async function handleAnswer(data) {
  if (!peerConnection) return;

  try {
    await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
  } catch (err) {
    console.error("Ошибка обработки ответа:", err);
    endCall();
  }
}

// Обработка ICE-кандидата
async function handleCandidate(data) {
  if (!peerConnection) return;

  try {
    await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
  } catch (err) {
    console.error("Ошибка добавления ICE-кандидата:", err);
  }
}

// Начало звонка
async function startCall() {
  console.log("Start call button clicked");

  if (!current) {
    showToast("Выберите собеседника", "error");
    return;
  }

  if (currentCall) {
    showToast("Завершите текущий звонок", "error");
    return;
  }

  currentCall = current;
  toggleModal('callModal', true);
  document.getElementById('callStatus').textContent = "Установка соединения...";

  try {
    if (signalingSocket.readyState === WebSocket.OPEN) {
      signalingSocket.send(JSON.stringify({
        type: "call",
        to: currentCall
      }));
    } else {
      console.error("WebSocket is not open");
      showToast("Ошибка подключения к серверу", "error");
      return;
    }

    await createPeerConnection(true);
  } catch (err) {
    console.error("Ошибка начала звонка:", err);
    showToast("Ошибка начала звонка", "error");
    endCall();
  }
}

// Завершение звонка
function endCall() {
  console.log("Ending call");

  if (peerConnection) {
    peerConnection.close();
    peerConnection = null;
  }

  if (localStream) {
    localStream.getTracks().forEach(track => track.stop());
    localStream = null;
  }

  if (currentCall && signalingSocket.readyState === WebSocket.OPEN) {
    try {
      signalingSocket.send(JSON.stringify({
        type: "end",
        to: currentCall
      }));
    } catch (err) {
      console.error("Error sending end call message:", err);
    }
    currentCall = null;
  }

  toggleModal('callModal', false);
  const callStatus = document.getElementById('callStatus');
  if (callStatus) {
    callStatus.textContent = "Установка соединения...";
  }
}

// Переключение микрофона
function toggleAudio() {
  if (!localStream) return;

  isAudioMuted = !isAudioMuted;
  localStream.getAudioTracks().forEach(track => {
    track.enabled = !isAudioMuted;
  });

  const icon = document.querySelector('#toggleAudioBtn i');
  if (icon) {
    icon.className = isAudioMuted ? 'fa fa-microphone-slash' : 'fa fa-microphone';
    showToast(isAudioMuted ? "Микрофон отключен" : "Микрофон включен",
              isAudioMuted ? "warning" : "success");
  }
}

// === Уведомления ===
function showToast(msg, type = 'success') {
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><div>${msg}</div>`;
  document.body.append(t);
  setTimeout(() => {
    if (t.parentNode) t.parentNode.removeChild(t);
  }, 3000);
}

// === Инициализация ===
document.querySelectorAll('.modal .close, .cancel-btn').forEach(btn=>{
  btn.onclick=()=>{
    const m=btn.closest('.modal');
    if(m) m.classList.remove('active');
  };
});

document.addEventListener('DOMContentLoaded', function() {
  // Инициализация кнопки звонка
  if (callBtn) {
    callBtn.addEventListener('click', startCall);
  }

  // Инициализация кнопок в модальном окне звонка
  if (endCallBtn) {
    endCallBtn.addEventListener('click', endCall);
  }

  if (toggleAudioBtn) {
    toggleAudioBtn.addEventListener('click', toggleAudio);
  }

  // Загрузка данных
  loadAll();
  updateUserPanel();
  updateStatus('regular');

  // Инициализация WebSocket
  initSignaling();

  // Обработка закрытия окна
  window.addEventListener('beforeunload', () => {
    navigator.sendBeacon('api.php?action=update_status', JSON.stringify({
      status: isInvisible ? 'invisible' : 'regular'
    }));
    endCall();
  });

  // Таймеры
  setInterval(loadAll, 3000);

  // Интервал для проверки соединения (15 секунд)
  const PING_INTERVAL = 15000;
  setInterval(() => {
    fetch('/api.php?action=ping', { credentials: 'include' })
      .then(res => res.json())
      .then(data => {
        if (!data.ok) console.error('Ping failed:', data);
      })
      .catch(err => console.error('Ping error:', err));
  }, PING_INTERVAL);
});
</script>

</body>
</html>