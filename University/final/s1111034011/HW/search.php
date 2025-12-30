<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>期末報告</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php include "nav.html"; ?>
        </header>
        <main id="contents">
            <h2>搜尋通訊錄</h2>
            <?php
                session_start();
                if (isset($_POST["Search"])){
                    $sql = "SELECT * FROM students ";
                    if (chop($_POST["id"]) != ""){
                        $number = "sno LIKE '".$_POST["id"]."' ";
                    }
                    else{
                        $number = "";
                    }
                    if (chop($_POST["Name"]) != ""){
                        $name = "name LIKE '".$_POST["Name"]."' ";
                    }
                    else{
                        $name = "";
                    }
                    if (chop($id) != "" && chop($name) != ""){
                        $sql.= "WHERE ".$id." AND ".$name;
                    }
                    elseif (chop($id) != ""){
                        $sql.= "WHERE ".$id;
                    }
                    elseif (chop($name) != ""){
                        $sql.= "WHERE ".$name;
                    }
                    $sql.= " ORDER BY name";
                    $_SESSION["SQL"] = $sql;
                    header("Location: contacts.php");
                }
            ?>
            <form action="search.php" method="post">
                <table border="1" style='margin: 0 auto'>
                    <tr><td>搜尋學號: </td>
                    <td><input type="text" name="id" size="10" maxlength="20"></td></tr>
                    <tr><td>搜尋姓名: </td>
                    <td><input type="text" name="Name" size="10" maxlength="20"></td></tr>
                    <tr><td colspan="2" align="center"><input type="submit" name="Search" value="搜尋"></td></tr>
                </table>
            </form>
            <hr/><a href="contacts.php">首頁</a>
            <a href="add.php">新增聯絡資料</a>
            <a href="search.php">搜尋通訊錄</a>
        </main>
        <footer id="footer">
            <?php include "infoFooter.html"; ?>
        </footer>
    </div>
</body>
</html>