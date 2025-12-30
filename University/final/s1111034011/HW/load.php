<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>貸款試算</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <style>
        div.welcome {
            color: green
        }

        div.error {
            color: red
        }
        p{
            text-align: left;
        }
        ul{
            text-align: left;
        }
    </style>
</head>

<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.html";
            ?>
        </header>
        <main id="contents" class="clearheader">
        <h2>貸款計算結果</h2>
            <?php
            if (!$_POST['uname'] || !$_POST['loan'] || !$_POST['rate'] || !$_POST['year']) {
                echo "<div class='error>輸入資料不完整，無法試算!</div>";
            } else {
                $username = $_POST['uname'];
                $loanDate = date(format: 'Y-m-d', timestamp: strtotime(datetime: $_POST['Ldate']));
                $loanAmount = $_POST['loan'] * 10000;
                $loanRate = $_POST['rate'];
                $loanYear = $_POST['year'];
                $str = $loanDate . "+" . $loanYear . "year";
                $endDate = date(format: 'Y-m-d', timestamp: strtotime(datetime: $str));

                $muliRate = pow(num: (1 + ($loanRate / 100)), exponent: $loanYear);
                $payment = $loanAmount * $muliRate;

                echo "<p>$username 會員您好，您的貸款試算如下</p>";
                echo "<ul>";
                echo "<li>貸款期間: $loanDate ~$endDate</li>";
                echo "<li>貸款金額: $loanAmount 元</li>";
                echo "<li>年利率: $loanRate %</li>";
                echo "<li>$loanYear 年後應還款: $payment 元</li>";
                echo "</ul>";
            }
            ?>
        </main>
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
    </div>
</body>
</html>