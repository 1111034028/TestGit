<?php require_once('../final/auth_check.php'); ?>
<?php
require_once("../DB/DB_open.php");

$msg = "";
// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $album_date = $_POST["album_date"];
    $location = $_POST["location"];
    
    // 檔案上傳處理
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $upload_dir = "img/";
        // 確保目錄存在
        if (!file_exists($upload_dir)) { mkdir($upload_dir, 0777, true); }
        
        $filename = basename($_FILES["photo"]["name"]);
        // 避免檔名重複，加上時間戳記
        $target_file = $upload_dir . time() . "_" . $filename;
        $db_filename = time() . "_" . $filename; // 存入 DB 的檔名
        
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO album (title, album_date, location, picurl) VALUES ('$title', '$album_date', '$location', '$db_filename')";
            if (mysqli_query($link, $sql)) {
                echo "<script>alert('新增成功！'); window.location.href='album.php';</script>";
                exit;
            } else {
                $msg = "資料庫錯誤: " . mysqli_error($link);
            }
        } else {
            $msg = "檔案上傳失敗。";
        }
    } else {
        $msg = "請選擇有效的照片檔案。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增相簿</title>
    <link rel="stylesheet" href="css/navCSS.css">
    <link rel="stylesheet" href="css/indexCSS.css">
    <link rel="stylesheet" href="css/album.css">
    <link rel="stylesheet" href="css/editForm.css">
   
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.php"; ?>
        </header>

        <main id="contents">
            <h2>相簿管理-新增相簿</h2>
            
            <?php if ($msg) echo "<div style='color:red; text-align:center;'>$msg</div>"; ?>

            <form action="albumAdd.php" method="post" enctype="multipart/form-data">
                <table class="entryTable" style="margin: 0 auto;">
                    <tr>
                        <th>相片名稱 :</th>
                        <td><input type="text" name="title" required></td>
                    </tr>
                    <tr>
                        <th>拍攝時間 :</th>
                        <td><input type="datetime-local" name="album_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required></td>
                    </tr>
                    <tr>
                        <th>拍攝地點 :</th>
                        <td><input type="text" name="location"></td>
                    </tr>
                    <tr>
                        <th>照片 :</th>
                        <td>
                            <input type="file" name="photo" id="photo-input" accept="image/*" required onchange="previewImage(this)">
                            <div id="preview-container" style="margin-top: 10px; display: none;">
                                <img id="img-preview" src="#" alt="預覽圖" style="max-width: 200px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                            </div>
                            <small class="hint">請選取 JPEG, PNG 或 GIF 格式</small>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="btn-container">
                            <div class="btn-container-inner">
                                <input type="submit" value="確定新增" class="btn-album">
                                <a href="album.php" style="text-decoration: none;">
                                    <input type="button" value="回相簿管理" class="btn-album">
                                </a>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>

            <script>
            function previewImage(input) {
                var previewContainer = document.getElementById('preview-container');
                var imgPreview = document.getElementById('img-preview');
                
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                } else {
                    imgPreview.src = '#';
                    previewContainer.style.display = 'none';
                }
            }
            </script>

        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
