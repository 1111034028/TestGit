<?php require_once('../final/auth_check.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>貸款計算</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/load.css" media="all">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.php";
            ?>
        </header>
        <main id="contents" class="clearheader">
        <?php
        // 取得表單資料
        if( !$_POST["uname"] || !$_POST["loan"] || !$_POST["year"] || !$_POST["rate"] ) {
            echo '<div class="error">請完整填寫表單資料！</div>';
            exit;
        }
        else {
            $name = $_POST["uname"];
            $loan = floatval($_POST["loan"]) * 10000; // 將萬元轉為元
            $year = intval($_POST["year"]);
            $rate = floatval($_POST["rate"]);
            $loan_date = date("Y-m-d", timestamp: strtotime($_POST["loan_date"]));


            //計算貸款日期+n年
            $str = $loan_date."+".$year." years";
            $endDate = date("Y-m-d", strtotime($str));
            
            //以年複利計算n年後的本利和
            $muliRate = pow((1 + $rate/100), $year);
            $payment = $loan * $muliRate;

            echo "<p>$name 您好，您的貸款試算結果如下： </p>";
            echo "<ul>";
            echo "<li>貸款期間：$loan_date - $endDate</li>";
            echo "<li>貸款金額：$loan 元</li>";
            echo "<li>年利率：$rate %</li>";
            echo "<li>$year 年後還款金額：$payment 元</li>";
            echo "</ul>";
        }
        ?>
        </main>
        <footer id="footer">
            <?php
            require "foot.html";
            ?>
        </footer> 
    </div>
</body>
</html>
