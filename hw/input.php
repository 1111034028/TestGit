<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>本金和</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>

<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.html";
            ?>
        </header>
        <main id="contents" class="clearheader">
            <form method="post" action="load.php">
                <h2>輸入貸款資料</h2>
                <table>
                    <tr>
                        <th>姓名</th>
                        <td><input type="text" name="uname" require></td>
                    </tr>
                    <tr>
                        <th>貸款日期</th>
                        <td><input type="date" name="loan_date" value="<?php echo date('Y-m-d'); ?>" /></td>
                    </tr>
                    <tr>
                        <th>貸款金額</th>
                        <td><input class="num" type="amount" name="loan" value="5" require>萬元</td>
                    </tr>
                    <tr>
                        <th>年利率</th>
                        <td><input class="num" type="decimal" name="rate" value="1.8" require>%</td>
                    </tr>
                    <tr>
                        <th>還款年數</th>
                        <td><input class="num" type="number" name="year" value="1" require>年</td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><input type="submit" value="試算"></td>
                    </tr>
                </table>
            </form>
        </main>
        <footer id="footer">
            <?php
            require "foot.html";
            ?>
        </footer>
    </div>
</body>

</html>