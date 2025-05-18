<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Lista zadań</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once "database.php";
$user_id = $_SESSION['user_id'];

// Pobierz login
$login = '';
$stmt = mysqli_prepare($conn, "SELECT usernames FROM loginy WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $login);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Wylogowanie
if (isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Dodawanie zadania
if (!empty($_POST['zadanie'])) {
    $tekst = $_POST['zadanie'];
    $stmt = mysqli_prepare($conn, "INSERT INTO zadania (user_id, tekst, wykonane) VALUES (?, ?, 0)");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $tekst);
    mysqli_stmt_execute($stmt);
    header("Location: strona_glowna.php");
    exit();
}

// Dodawanie podzadania
if (!empty($_POST['podzadanie']) && !empty($_POST['zadanie_id'])) {
    $zadanie_id = $_POST['zadanie_id'];
    $tekst = $_POST['podzadanie'];
    $stmt = mysqli_prepare($conn, "INSERT INTO podzadania (zadanie_id, tekst, wykonane) VALUES (?, ?, 0)");
    mysqli_stmt_bind_param($stmt, "is", $zadanie_id, $tekst);
    mysqli_stmt_execute($stmt);
    header("Location: strona_glowna.php");
    exit();
}

// Edycja zadania
if (isset($_POST['edytuj_zadanie'], $_POST['zadanie_id'], $_POST['nowy_tekst_zadania'])) {
    $zadanie_id = $_POST['zadanie_id'];
    $nowy_tekst = $_POST['nowy_tekst_zadania'];
    $stmt = mysqli_prepare($conn, "UPDATE zadania SET tekst = ? WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "sii", $nowy_tekst, $zadanie_id, $user_id);
    mysqli_stmt_execute($stmt);
    header("Location: strona_glowna.php");
    exit();
}

// Edycja podzadania
if (isset($_POST['edytuj_podzadanie'], $_POST['podzadanie_id'], $_POST['nowy_tekst_podzadania'])) {
    $podzadanie_id = $_POST['podzadanie_id'];
    $nowy_tekst = $_POST['nowy_tekst_podzadania'];
    $stmt = mysqli_prepare($conn, "UPDATE podzadania SET tekst = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $nowy_tekst, $podzadanie_id);
    mysqli_stmt_execute($stmt);
    header("Location: strona_glowna.php");
    exit();
}

// Usuwanie zadania
if (!empty($_POST['usun_zadanie'])) {
    $zadanie_id = $_POST['usun_zadanie'];
    mysqli_query($conn, "DELETE FROM podzadania WHERE zadanie_id = $zadanie_id");
    mysqli_query($conn, "DELETE FROM zadania WHERE id = $zadanie_id AND user_id = $user_id");
    header("Location: strona_glowna.php");
    exit();
}

// Usuwanie podzadania
if (!empty($_POST['usun_podzadanie'])) {
    $podzadanie_id = $_POST['usun_podzadanie'];
    mysqli_query($conn, "DELETE FROM podzadania WHERE id = $podzadanie_id");
    header("Location: strona_glowna.php");
    exit();
}

// Zmiana statusu zadania
if (!empty($_POST['ptaszek_zadanie'])) {
    $zadanie_id = $_POST['ptaszek_zadanie'];
    mysqli_query($conn, "UPDATE zadania SET wykonane = NOT wykonane WHERE id = $zadanie_id AND user_id = $user_id");
    header("Location: strona_glowna.php");
    exit();
}

// Zmiana statusu podzadania
if (!empty($_POST['ptaszek_podzadanie'])) {
    $podzadanie_id = $_POST['ptaszek_podzadanie'];
    mysqli_query($conn, "UPDATE podzadania SET wykonane = NOT wykonane WHERE id = $podzadanie_id");
    header("Location: strona_glowna.php");
    exit();
}

// Pobierz zadania i podzadania
$zadania = [];
$res = mysqli_query($conn, "SELECT id, tekst, wykonane FROM zadania WHERE user_id = $user_id");
while ($row = mysqli_fetch_assoc($res)) {
    $zadania[] = $row;
}
$podzadania_map = [];
$res = mysqli_query($conn, "SELECT id, zadanie_id, tekst, wykonane FROM podzadania");
while ($row = mysqli_fetch_assoc($res)) {
    $podzadania_map[$row['zadanie_id']][] = $row;
}

// Sprawdź czy edytujemy zadanie/podzadanie
$zadanie_do_edycji = isset($_POST['formularz_edytuj_zadanie']) ? $_POST['formularz_edytuj_zadanie'] : null;
$podzadanie_do_edycji = isset($_POST['formularz_edytuj_podzadanie']) ? $_POST['formularz_edytuj_podzadanie'] : null;
?>
<!-- Login w lewym górnym rogu -->
<div style="position: fixed; top: 18px; left: 24px; font-size: 18px; color: #6c63ff; font-weight: bold; z-index: 1000;">
    Zalogowano jako: <?= htmlspecialchars($login) ?>
</div>
<!-- Wyloguj w prawym górnym rogu -->
<form method="POST" style="position: fixed; top: 18px; right: 24px; z-index: 1000; margin: 0;">
    <button type="submit" name="wyloguj">Wyloguj</button>
</form>
<div class="container">
    <h1 style="text-align:center;">Lista zadań</h1>
    <form method="POST" style="margin-bottom:24px; text-align:center;">
        <input type="text" name="zadanie" placeholder="Dodaj zadanie">
        <button type="submit">Dodaj</button>
    </form>
    <ul style="padding-left:0;">
        <?php foreach ($zadania as $zadanie): 
            $style = $zadanie['wykonane'] ? "text-decoration:line-through;color:#888;" : "";
        ?>
        <li style="background:#f8fafc; border-radius:10px; margin-bottom:18px; padding:16px; list-style:none; text-align:left;">
            <form method="POST" style="display:inline;">
                <button type="submit" name="ptaszek_zadanie" value="<?= $zadanie['id'] ?>" style="background:none; border:none; font-size:20px; cursor:pointer;">
                    <?= $zadanie['wykonane'] ? '✅' : '⬜' ?>
                </button>
            </form>
            <?php if ($zadanie_do_edycji == $zadanie['id']): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="zadanie_id" value="<?= $zadanie['id'] ?>">
                    <input type="text" name="nowy_tekst_zadania" value="<?= htmlspecialchars($zadanie['tekst']) ?>" style="margin-right:8px;">
                    <button type="submit" name="edytuj_zadanie">Zapisz</button>
                </form>
            <?php else: ?>
                <span style="<?= $style ?>"><?= htmlspecialchars($zadanie['tekst']) ?></span>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="formularz_edytuj_zadanie" value="<?= $zadanie['id'] ?>">
                    <button type="submit">Edytuj</button>
                </form>
            <?php endif; ?>
            <form method="POST" style="display:inline;">
                <button type="submit" name="usun_zadanie" value="<?= $zadanie['id'] ?>">Usuń</button>
            </form>
            <!-- Dodawanie podzadania -->
            <form method="POST" style="margin-top:8px;">
                <input type="hidden" name="zadanie_id" value="<?= $zadanie['id'] ?>">
                <input type="text" name="podzadanie" placeholder="Dodaj podzadanie">
                <button type="submit">Dodaj</button>
            </form>
            <!-- Podzadania -->
            <?php if (!empty($podzadania_map[$zadanie['id']])): ?>
                <ul style="padding-left:20px;">
                <?php foreach ($podzadania_map[$zadanie['id']] as $pod): 
                    $pstyle = $pod['wykonane'] ? "text-decoration:line-through;color:#888;" : "";
                ?>
                    <li style="text-align:left;">
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="ptaszek_podzadanie" value="<?= $pod['id'] ?>" style="background:none; border:none; font-size:18px; cursor:pointer;">
                                <?= $pod['wykonane'] ? '✅' : '⬜' ?>
                            </button>
                        </form>
                        <?php if ($podzadanie_do_edycji == $pod['id']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="podzadanie_id" value="<?= $pod['id'] ?>">
                                <input type="text" name="nowy_tekst_podzadania" value="<?= htmlspecialchars($pod['tekst']) ?>" style="margin-right:8px;">
                                <button type="submit" name="edytuj_podzadanie">Zapisz</button>
                            </form>
                        <?php else: ?>
                            <span style="<?= $pstyle ?>; margin-right:16px;"><?= htmlspecialchars($pod['tekst']) ?></span>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="formularz_edytuj_podzadanie" value="<?= $pod['id'] ?>">
                                <button type="submit">Edytuj</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="usun_podzadanie" value="<?= $pod['id'] ?>">Usuń</button>
                        </form>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
</body>
</html>