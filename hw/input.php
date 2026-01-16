<?php require_once('../final/inc/auth_guard.php'); ?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>本金和</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <style>
        /* Rounder Input Fields */
        input[type="text"], input[type="number"], input[type="date"], input[type="amount"], input[type="decimal"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 20px;
            box-sizing: border-box; /* Ensure padding doesn't affect width */
            outline: none;
            transition: border-color 0.3s;
        }
        input:focus {
            border-color: #6c5ce7; /* Update focus color */
            box-shadow: 0 0 5px rgba(108, 92, 231, 0.3);
        }
        
        /* Flex container for Input + Unit */
        .input-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .input-group input {
            flex: 1; /* Input takes remaining space */
        }
        .input-group span {
            white-space: nowrap;
            font-weight: bold;
            color: #555;
        }
    </style>
</head>

<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.php";
            ?>
        </header>
        <main id="contents" class="clearheader">
            <form method="post" action="load.php">
                <h2>輸入貸款資料</h2>
                <table style="max-width: 500px; margin: 0 auto;">
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
                        <td>
                            <div class="input-group">
                                <input class="num" type="number" name="loan" value="5" require>
                                <span>萬元</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>年利率</th>
                        <td>
                            <div class="input-group">
                                <input class="num" type="number" step="0.01" name="rate" value="1.8" require>
                                <span>%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>還款年數</th>
                        <td>
                            <div class="input-group">
                                <input class="num" type="number" name="year" value="1" require>
                                <span>年</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><input type="submit" value="試算" style="background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%); color: white; border: none; padding: 12px 25px; border-radius: 50px; cursor: pointer; font-weight: bold; width: 100%; box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3); transition: transform 0.1s, box-shadow 0.1s;"></td>
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
