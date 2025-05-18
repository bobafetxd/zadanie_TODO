<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container lefted">
    <h1>Logowanie</h1>
    <p>Witaj w aplikacji do zarządzania zadaniami!</p>
    <form method="post">
        <input type="text" name="login" placeholder="Login" required><br><br>
        <input type="password" name="haslo" placeholder="Hasło" required><br><br>
        <button type="submit" name="zaloguj">Zaloguj</button>
        <button type="reset">Wyczyść</button>
        <button type="button" onclick="window.location.href='rejestracja.php'">Zarejestruj</button>
    </form>
    <br>
    <?php
    if (isset($_POST['zaloguj'])) {
        require_once "database.php";
        $login = $_POST['login'];
        $haslo = $_POST['haslo'];
        $res = mysqli_query($conn, "SELECT id, passwords FROM loginy WHERE usernames = '$login'");
        if ($row = mysqli_fetch_assoc($res)) {
            if ($haslo === $row['passwords']) {
                session_start();
                $_SESSION['login'] = $login;
                $_SESSION['user_id'] = $row['id'];
                header("Location: strona_glowna.php");
                exit();
            }
        }
        echo "<span style='color:red;'>Niepoprawny login lub hasło!</span>";
    }
    ?>
</div>
</body>
</html>