<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>login.php</title>
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
        <main id="contents">
            <h2>醫生系統登入</h2>
            <?php
            session_start();
            $username = "";
            $password = "";
            if (isset($_POST["Username"]))
                $username = $_POST["Username"];
            if (isset($_POST["Password"]))
                $password = $_POST["Password"];
            if ($username != "" && $password != "") {
                require_once("DB_open.php");
                $sql = "SELECT * FROM students WHERE password='";
                $sql .= $password . "' AND username='" . $username . "'";
                $result = mysqli_query($link, $sql);
                $total_records = mysqli_num_rows($result);
                if ($total_records > 0) {
                    $_SESSION["login_session"] = true;
                    $_SESSION["username"] = $username;
                    header("Location: index.php");
                } else {
                    echo "<center><font color='red'>";
                    echo "使用者名稱或密碼錯誤!<br/>";
                    echo "</font>";
                    $_SESSION["login_session"] = false;
                }
                require_once("DB_close.php");
            }
            ?>
            <form action="login.php" method="post">
                <table align="center" bgcolor="#FFCC99">
                    <tr>
                        <td>
                            <font size="2">使用者名稱:</font>
                        </td>
                        <td><input type="text" name="Username" size="15" maxlength="10" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size="2">使用者密碼:</font>
                        </td>
                        <td><input type="password" name="Password" size="15" maxlength="10" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center"><input type="submit" value="登入網站" /></td>
                    </tr>
                </table>
            </form>
        </main>
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
    </div>
</body>

</html>