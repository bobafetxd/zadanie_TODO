<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container lefted">
    <h2>Rejestracja</h2>
    <form method="POST">
        <input type="text" name="login" placeholder="Login" required><br><br>
        <input type="password" name="haslo" placeholder="Hasło" required><br><br>
        <input type="password" name="potwierdzenie_hasla" placeholder="Potwierdź hasło" required><br><br>
        <button type="submit" name="zarejestruj">Zarejestruj</button>
        <button type="button" onclick="window.location.href='index.php'">Powrót do logowania</button>
    </form>
    <br>
    <?php
    if (isset($_POST['zarejestruj'])) {
        require_once "database.php";
        $login = $_POST['login'];
        $haslo = $_POST['haslo'];
        $potw = $_POST['potwierdzenie_hasla'];
        // Sprawdzenie wymagań hasła
        $wzor = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        if ($haslo !== $potw) {
            echo "<span style='color:red;'>Hasła nie są zgodne!</span>";
        } elseif (!preg_match($wzor, $haslo)) {
            echo "<span style='color:red;'>Hasło musi mieć min. 8 znaków, 1 małą i 1 dużą literę, cyfrę oraz znak specjalny.</span>";
        } else {
            $res = mysqli_query($conn, "SELECT id FROM loginy WHERE usernames = '$login'");
            if (mysqli_num_rows($res) > 0) {
                echo "<span style='color:red;'>Taki login już istnieje!</span>";
            } else {
                if (mysqli_query($conn, "INSERT INTO loginy (usernames, passwords) VALUES ('$login', '$haslo')")) {
                    echo "<span style='color:green;'>Rejestracja zakończona sukcesem!</span>";
                } else {
                    echo "<span style='color:red;'>Błąd rejestracji!</span>";
                }
            }
        }
    }
    ?>
</div>
</body>
</html>