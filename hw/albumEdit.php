<?php require_once('../final/inc/auth_guard.php'); ?>
<?php
require_once("../DB/DB_open.php");

$id = $_GET['id'];
$sql = "SELECT * FROM album WHERE album_id = $id";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("找不到該筆資料");
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯相簿</title>
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
            <h2>相簿管理-編輯相簿</h2>
            
            <form action="albumEditAct.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="album_id" value="<?php echo $row['album_id']; ?>">
                
                <table class="entryTable" style="margin: 0 auto;">
                    <tr>
                        <th>相片名稱 :</th>
                        <td><input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required></td>
                    </tr>
                    <tr>
                        <th>拍攝時間 :</th>
                        <td><input type="datetime-local" name="album_date" value="<?php echo date('Y-m-d\TH:i', strtotime($row['album_date'])); ?>" required></td>
                    </tr>
                    <tr>
                        <th>拍攝地點 :</th>
                        <td><input type="text" name="location" value="<?php echo htmlspecialchars($row['location']); ?>"></td>
                    </tr>
                    <tr>
                        <th>照片 :</th>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <img id="img-preview" src="img/<?php echo $row['picurl']; ?>" style="max-width: 200px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                            </div>
                            <input type="file" name="photo" accept="image/*" onchange="previewImage(this)">
                            <div class="hint">若不修改照片請留空</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="btn-container">
                            <div class="btn-container-inner">
                                <input type="submit" value="確定修改" class="btn-album">
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
                var imgPreview = document.getElementById('img-preview');
                
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                    }
                    
                    reader.readAsDataURL(input.files[0]);
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