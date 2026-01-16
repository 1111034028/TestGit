<?php require_once('../final/inc/auth_guard.php'); ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>資料顯示</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/contacts.css" media="all"> <!-- 重用樣式顯示結果 -->

</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.php"; ?>
        </header>

        <main id="form-contents">
            <h2>填寫資料確認</h2>

            <?php
            // 檔案上傳處理
            $uploadDir = 'img/';
            $uploadedFile = '';
            
            if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK){
                $tmpName = $_FILES['photo']['tmp_name'];
                $name = basename($_FILES['photo']['name']);
                // 為了避免檔名衝突，可以加上時間戳記 (選用，依作業需求可簡化)
                $targetFile = $uploadDir . $name;
                
                if(move_uploaded_file($tmpName, $targetFile)){
                    $uploadedFile = $targetFile;
                } else {
                    echo "<div class='error'>圖片上傳失敗</div>";
                }
            }
            ?>

            <div class="section-title">貓咪資料</div>
            <table class="form-table">
                <tr>
                    <th>貓咪名</th>
                    <td><?php echo htmlspecialchars($_POST['catName'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>年齡</th>
                    <td><?php echo htmlspecialchars($_POST['age'] ?? ''); ?> 歲</td>
                </tr>
                <tr>
                    <th>性別</th>
                    <td><?php echo htmlspecialchars($_POST['gender'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>最愛的食物</th>
                    <td>
                        <?php 
                        if(isset($_POST['food'])){
                            echo implode('、', $_POST['food']);
                        } else {
                            echo "無";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>照片</th>
                    <td>
                        <?php if($uploadedFile): ?>
                            <img src="<?php echo $uploadedFile; ?>" alt="上傳的圖片" class="result-img">
                        <?php else: ?>
                            未上傳或上傳失敗
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <div class="section-title">飼主資料</div>
            <table class="form-table">
                <tr>
                    <th>飼主名</th>
                    <td><?php echo htmlspecialchars($_POST['ownerName'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>E-Mail</th>
                    <td><?php echo htmlspecialchars($_POST['email'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>留言</th>
                    <td><?php echo nl2br(htmlspecialchars($_POST['comment'] ?? '')); ?></td>
                </tr>
            </table>

            <div class="btn-container">
                <input type="button" value="回上一頁" onclick="history.back();" class="btn btn-t" id="btn-back">
            </div>

        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
