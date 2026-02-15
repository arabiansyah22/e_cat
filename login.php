<?php
session_start();
require_once "config/db.php";

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && $password === $user['password']) {
        $_SESSION['login'] = true;
        $_SESSION['user'] = $user;
        header("Location: admin/dashboardAdmin.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login | E-CAT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- INLINE CSS -->
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body.login-body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 100%;
            max-width: 380px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            border-radius: 18px;
            padding: 32px;
            color: #fff;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.3);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            opacity: 0.85;
        }

        .form-group {
            margin-top: 20px;
        }

        .form-group label {
            font-size: 13px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            margin-top: 6px;
            border-radius: 12px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .form-group input::placeholder {
            color: #aaa;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 26px;
        }

        .btn-group .btn-login {
            width: 100%;
        }

        .btn-login {
            width: 100%;
            margin-top: 26px;
            padding: 12px;
            background: #4ade80;
            border: none;
            border-radius: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-login:hover {
            background: #22c55e;
            transform: translateY(-2px);
        }

        .error {
            margin-top: 15px;
            background: rgba(239, 68, 68, 0.95);
            padding: 10px;
            border-radius: 10px;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>

<body class="login-body">

    <div class="login-box">
        <h2>E-CAT</h2>
        <p class="subtitle">Login Penjualan Makanan Kucing</p>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="username">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <div class="btn-group">
                <button class="btn-login" name="login">Login</button>
                <button type="button" class="btn-login" onclick="window.location.href='index.php'">
                    Kembali
                </button>
            </div>
        </form>
    </div>

</body>

</html>