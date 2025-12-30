<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>index.php</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>

<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.html";
            ?>
        </header>
        <?php
        session_start();
        if ($_SESSION["login_session"] != true)
            header("Location: login.php");
        echo "歡迎[" . $_SESSION["username"] . "]進入網站!<br/>";
        ?>
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
    </div>
</body>

</html>