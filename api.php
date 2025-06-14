<?php
session_start();
header('Content-Type: application/json');

$dataDir    = __DIR__ . '/data';
$avatarsDir = __DIR__ . '/avatars';
if (!is_dir($dataDir))    mkdir($dataDir,    0777, true);
if (!is_dir($avatarsDir)) mkdir($avatarsDir, 0777, true);

$contactsFile = "$dataDir/contacts.json";
$msgFile      = "$dataDir/messages.json";
$usersFile    = "$dataDir/users.json";

// Инициализация файлов
if (!file_exists($contactsFile)) file_put_contents($contactsFile, json_encode(new stdClass(), JSON_PRETTY_PRINT));
if (!file_exists($msgFile))      file_put_contents($msgFile,      json_encode([],              JSON_PRETTY_PRINT));
if (!file_exists($usersFile))    file_put_contents($usersFile,    json_encode(new stdClass(), JSON_PRETTY_PRINT));

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user = $_SESSION['user'];

function load_json($file) {
    return json_decode(file_get_contents($file), true) ?: [];
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function touch_user_activity(&$users, $user, $usersFile) {
    $users[$user]['last_activity'] = time();
    save_json($usersFile, $users);
}

$contacts = load_json($contactsFile);
$users    = load_json($usersFile);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'update_avatar':
        if (isset($_FILES['avatar'])) {
            $avatar     = $_FILES['avatar'];
            $validTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($avatar['type'], $validTypes)) {
                $ext      = pathinfo($avatar['name'], PATHINFO_EXTENSION);
                $filename = "{$user}_" . time() . ".{$ext}";
                $path     = "$avatarsDir/$filename";

                if (move_uploaded_file($avatar['tmp_name'], $path)) {
                    if (!empty($users[$user]['avatar']) && file_exists(__DIR__ . $users[$user]['avatar'])) {
                        unlink(__DIR__ . $users[$user]['avatar']);
                    }
                    $users[$user]['avatar'] = "/avatars/$filename";
                    touch_user_activity($users, $user, $usersFile);
                    echo json_encode(['ok' => true, 'avatar' => "/avatars/$filename"]);
                } else {
                    echo json_encode(['ok' => false, 'error' => 'File upload failed']);
                }
            } else {
                echo json_encode(['ok' => false, 'error' => 'Invalid file type']);
            }
        } else {
            echo json_encode(['ok' => false, 'error' => 'No file uploaded']);
        }
        break;
     case 'ping':
        // просто обновляем время последней активности
        touch_user_activity($users, $user, $usersFile);
        echo json_encode(['ok' => true]);
        break;
    case 'update_status':
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['online', 'offline', 'invisible'])) {
            $users[$user]['status'] = $status;
            touch_user_activity($users, $user, $usersFile);
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Invalid status']);
        }
        break;

    case 'add_friend':
        $f = trim($_POST['friend'] ?? '');
        if ($f === '' || $f === $user) {
            echo json_encode(['ok' => false, 'error' => 'Invalid friend']);
            exit;
        }
        if (!isset($users[$f])) {
            echo json_encode(['ok' => false, 'error' => 'User not found']);
            exit;
        }
        if (!isset($contacts[$user])) $contacts[$user] = ['friends' => [], 'requests' => []];
        if (!isset($contacts[$f]))    $contacts[$f]    = ['friends' => [], 'requests' => []];

        if (in_array($f, $contacts[$user]['friends']) || in_array($f, $contacts[$user]['requests'])) {
            echo json_encode(['ok' => false, 'error' => 'User already added']);
            exit;
        }
        $contacts[$f]['requests'][] = $user;
        save_json($contactsFile, $contacts);
        touch_user_activity($users, $user, $usersFile);
        echo json_encode(['ok' => true]);
        break;

    case 'respond_request':
        $f      = trim($_POST['friend'] ?? '');
        $accept = intval($_POST['accept'] ?? 0) === 1;

        if (!isset($contacts[$user]) || !in_array($f, $contacts[$user]['requests'])) {
            echo json_encode(['ok' => false, 'error' => 'No such request']);
            exit;
        }
        $contacts[$user]['requests'] = array_values(array_diff($contacts[$user]['requests'], [$f]));
        if ($accept) {
            $contacts[$user]['friends'][] = $f;
            $contacts[$f]['friends'][]    = $user;
        }
        save_json($contactsFile, $contacts);
        touch_user_activity($users, $user, $usersFile);
        echo json_encode(['ok' => true]);
        break;
    case 'clear_chat':
    $friend = trim($_POST['friend'] ?? '');

    // Проверка на наличие друга
    if (!in_array($friend, $contacts[$user]['friends'] ?? [])) {
        echo json_encode(['ok' => false, 'error' => 'Not friends']);
        exit;
    }

    $msgs = load_json($msgFile);
    $msgs = array_filter($msgs, function($m) use ($user, $friend) {
        return !(($m['from'] === $user && $m['to'] === $friend) ||
                 ($m['from'] === $friend && $m['to'] === $user));
    });

    save_json($msgFile, array_values($msgs));
    touch_user_activity($users, $user, $usersFile);
    echo json_encode(['ok' => true, 'message' => 'Чат с пользователем ' . $friend . ' очищен']);
    break;


    case 'remove_friend':
        $friend = trim($_POST['friend'] ?? '');
        if (!isset($contacts[$user]) || !isset($contacts[$friend])) {
            echo json_encode(['ok' => false, 'error' => 'Invalid friend']);
            exit;
        }
        $contacts[$user]['friends']   = array_values(array_diff($contacts[$user]['friends'],   [$friend]));
        $contacts[$friend]['friends'] = array_values(array_diff($contacts[$friend]['friends'], [$user]));
        save_json($contactsFile, $contacts);
        touch_user_activity($users, $user, $usersFile);
        echo json_encode(['ok' => true]);
        break;

    case 'send':
        $to   = trim($_POST['to'] ?? '');
        $text = trim($_POST['text'] ?? '');
        if ($to === '' || $text === '') {
            echo json_encode(['ok' => false, 'error' => 'Invalid message']);
            exit;
        }
        if (!isset($contacts[$user]) || !in_array($to, $contacts[$user]['friends'])) {
            echo json_encode(['ok' => false, 'error' => 'Not friends']);
            exit;
        }
        $msgs = load_json($msgFile);
        $msgs[] = ['from' => $user, 'to' => $to, 'text' => $text, 'time' => time()];
        save_json($msgFile, $msgs);
        touch_user_activity($users, $user, $usersFile);
        echo json_encode(['ok' => true]);
        break;

    case 'load':
        // При загрузке списка тоже обновляем активность
        touch_user_activity($users, $user, $usersFile);

        $msgs     = load_json($msgFile);
        $friends  = $contacts[$user]['friends']  ?? [];
        $reqs     = $contacts[$user]['requests'] ?? [];

        $now = time();
        $lastMsgTime = [];
        foreach ($msgs as $m) {
            $from = $m['from'];
            if (in_array($from, $friends)) {
                if (!isset($lastMsgTime[$from]) || $m['time'] > $lastMsgTime[$from]) {
                    $lastMsgTime[$from] = $m['time'];
                }
            }
        }

        $friendsData = [];
        foreach ($friends as $friend) {
            $uData = $users[$friend] ?? [];
            $lastAct = $uData['last_activity'] ?? 0;
            $lastMsg = $lastMsgTime[$friend]    ?? 0;
            $isOnline = ($now - $lastAct <= 20 || $now - $lastMsg <= 60);
            $showStatus = (($uData['status'] ?? '') === 'invisible') ? 'offline' : ($isOnline ? 'online' : 'offline');
            $friendsData[] = [
                'username'      => $friend,
                'avatar'        => $uData['avatar']      ?? null,
                'status'        => $showStatus,
                'last_activity' => $lastAct
            ];
        }

        $requestsData = [];
        foreach ($reqs as $r) {
            $rData = $users[$r] ?? [];
            $requestsData[] = [
                'username'      => $r,
                'avatar'        => $rData['avatar']      ?? null,
                'status'        => $rData['status']      ?? 'offline',
                'last_activity' => $rData['last_activity'] ?? 0
            ];
        }

        $filtered = array_filter($msgs, function($m) use ($user, $friends) {
            return ($m['from'] === $user && in_array($m['to'], $friends))
                || ($m['to']   === $user && in_array($m['from'], $friends));
        });

        echo json_encode([
            'contacts'  => $friendsData,
            'requests'  => $requestsData,
            'messages'  => array_values($filtered),
            'my_avatar' => $users[$user]['avatar'] ?? null
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Bad request']);
        break;
}
