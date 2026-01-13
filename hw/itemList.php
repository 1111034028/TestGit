<?php require_once('../final/auth_check.php'); ?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>SDGs</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <?php
    function printItem($image, $title, $detail){
        // Escape for JS
        $safetitle = htmlspecialchars(json_encode($title), ENT_QUOTES, 'UTF-8');
        $safedetail = htmlspecialchars(json_encode($detail), ENT_QUOTES, 'UTF-8');
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
        <header id="header" class="clearheader">
            <?php
            require "nav.php";
            ?>
        </header>
        <main  class="clearheader">
            <h2>永續發展目標 SDGs</h2>
            <div class="list">
                <?php
                //圖片檔案
                $imgs = array("1.jpg", "2.jpg", "3.jpg", "4.jpg", "5.jpg", "6.jpg", "7.jpg", "8.jpg", "9.jpg", "10.jpg", "11.jpg", "12.jpg", "13.jpg", "14.jpg", "15.jpg", "16.jpg", "17.jpg"); 
                
                //圖片標題
                $titles = array(
                    "SDG 1 終結貧窮",
                    "SDG 2 消除飢餓",
                    "SDG 3 健康與福祉",
                    "SDG 4 優質教育",
                    "SDG 5 性別平權",
                    "SDG 6 淨水及衛生",
                    "SDG 7 可負擔的潔淨能源",
                    "SDG 8 合適的工作及經濟成長",
                    "SDG 9 工業化、創新及基礎建設",
                    "SDG 10 減少不平等",
                    "SDG 11 永續城鄉",
                    "SDG 12 責任消費及生產",
                    "SDG 13 氣候行動",
                    "SDG 14 保育海洋生態",
                    "SDG 15 保育陸域生態",
                    "SDG 16 和平、正義及健全制度",
                    "SDG 17 多元夥伴關係"
                ); 

                //圖片說明
                $details = array(
                    "消除各地一切形式的貧窮", 
                    "確保糧食安全，消除飢餓，促進永續農業", 
                    "確保及促進各年齡層健康生活與福祉", 
                    "確保有教無類、公平以及高品質的教育，及提倡終身學習", 
                    "實現性別平等，並賦予婦女權力", 
                    "確保所有人都能享有水、衛生及其永續管理", 
                    "確保所有的人都可取得負擔得起、可靠、永續及現代的能源", 
                    "促進包容且永續的經濟成長，讓每個人都有一份好工作", 
                    "建立具有韌性的基礎建設，促進包容且永續的工業，並加速創新", 
                    "減少國內及國家間的不平等", 
                    "建構具包容、安全、韌性及永續特質的城市與鄉村", 
                    "促進綠色經濟，確保永續消費及生產模式", 
                    "完備減緩調適行動，以因應氣候變遷及其影響", 
                    "保育及永續利用海洋生態系，以確保生物多樣性並防止海洋環境劣化", 
                    "保育及永續利用陸域生態系，確保生物多樣性並防止土地劣化", 
                    "促進和平多元的社會，確保司法平等，建立具公信力且廣納民意的體系", 
                    "建立多元夥伴關係，協力促進永續願景");
                //呼叫函數
                for ($i = 0; $i < count($titles); $i++) {
                    $m = $imgs[$i];
                    $t = $titles[$i];
                    $d = $details[$i];
                    printItem($m, $t, $d); 
                }
                ?>
            </div>
        </main>
        <footer id="footer">
            <?php
            require "foot.html";
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
