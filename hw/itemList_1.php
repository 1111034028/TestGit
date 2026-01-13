<?php require_once('../final/auth_check.php'); ?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>永續發展目標 SDGs (資料庫版)</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <?php
    function printItem($image, $title, $detail)
    {
        // Escape for JS
        $safetitle = htmlspecialchars(json_encode($title), ENT_QUOTES, 'UTF-8');
        $safedetail = htmlspecialchars(json_encode($detail), ENT_QUOTES, 'UTF-8');
        // print with onclick
        echo "<div class='item' onclick='openModal(\"$image\", $safetitle, $safedetail)' style='cursor: pointer;'>";
        echo "<img src='img/" . $image . "' alt=''>";
        echo "<h4>" . $title . "</h4>";
        echo "<p>" . $detail . "</p>";
        echo "</div>";
    }
    ?>
    <style>
        /* Lightbox Modal CSS */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.8); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
            position: relative;
            animation: zoomIn 0.3s;
        }

        @keyframes zoomIn {
            from {transform: scale(0.7); opacity: 0;}
            to {transform: scale(1); opacity: 1;}
        }

        .modal-img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .modal-header h3 {
            margin-top: 0;
            color: #333;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 5px;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div id="wrap">
        <!-- 功能列表 -->
        <header id="header" class="clearheader">
            <?php
            include "nav.php";
            ?>
        </header>

        <!-- 內容區域 -->
        <main class="clearheader">
            <h2>永續發展目標 SDGs (從資料庫讀取)</h2>
            <div class="list">
                <?php
                require_once("../DB/DB_open.php");      // 引入資料庫連結設定檔
                $sql = "SELECT * FROM sdgs";      // 設定SQL查詢字串
                $result = mysqli_query($link, $sql); // 執行SQL查詢

                while ($rows = mysqli_fetch_array($result, MYSQLI_NUM)) {
                    $m = $rows[1]; // img
                    $t = $rows[2]; // title
                    $d = $rows[3]; // detail
                    printItem($m, $t, $d);
                }
                
                mysqli_free_result($result);      // 釋放佈局的記憶體
                require_once("../DB/DB_close.php");     // 引入資料庫關閉設定檔
                ?>
            </div>
        </main>

        <!-- 頁尾資訊 -->
        <footer id="footer">
            <?php
            include "foot.html";
            ?>
        </footer>
    </div>

    <!-- Modal Elements -->
    <div id="sdgModal" class="modal" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <span class="close" onclick="closeModal()">&times;</span>
            <img id="modalImg" class="modal-img" src="" alt="">
            <div class="modal-header">
                <h3 id="modalTitle"></h3>
            </div>
            <div class="modal-body">
                <p id="modalDetail" style="color: #555; line-height: 1.6;"></p>
            </div>
        </div>
    </div>

    <script>
        function openModal(image, title, detail) {
            var modal = document.getElementById("sdgModal");
            var modalImg = document.getElementById("modalImg");
            var modalTitle = document.getElementById("modalTitle");
            var modalDetail = document.getElementById("modalDetail");

            modal.style.display = "flex";
            modalImg.src = "img/" + image;
            modalTitle.innerText = title;
            modalDetail.innerText = detail;
        }

        function closeModal() {
            var modal = document.getElementById("sdgModal");
            modal.style.display = "none";
        }
        
        // Close on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeModal();
            }
        });
    </script>
</body>

</html>
