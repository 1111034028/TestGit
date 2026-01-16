<?php require_once('../final/inc/auth_guard.php'); ?>
<?php
require_once("../DB/DB_open.php");

// 分頁設定
$limit = 8; // 每頁顯示 8 筆 (4欄x2列)
if (isset($_GET["page"])) { $page  = $_GET["page"]; } else { $page=1; };  
$start_from = ($page-1) * $limit;  

// 查詢總筆數
$sql_count = "SELECT COUNT(*) FROM album";
$rs_result = mysqli_query($link, $sql_count);  
$row = mysqli_fetch_row($rs_result);  
$total_records = $row[0];  
$total_pages = ceil($total_records / $limit); 

// 查詢資料
$sql = "SELECT * FROM album ORDER BY album_date DESC LIMIT $start_from, $limit";
$result = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>相簿管理</title>
    <link rel="stylesheet" href="css/navCSS.css">
    <link rel="stylesheet" href="css/indexCSS.css">
    <link rel="stylesheet" href="css/album.css"> <!-- 引入相簿專用 CSS -->
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.php"; ?>
        </header>

        <main id="contents" style="max-width: 1200px; width: 95%;">
            <h2>相簿管理</h2>
            
            <div style="text-align: center; margin-bottom: 20px; font-weight: bold;">
                照片總數: <?php echo $total_records; ?> 
                <a href="albumAdd.php" class="add-link">新增照片</a>
            </div>

            <div class="album-grid">
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <div class="album-item">
                    <!-- 圖片容器 (灰色背景) -->
                    <div class="photo-container">
                        <img src="img/<?php echo htmlspecialchars($row['picurl']); ?>" 
                             alt="<?php echo htmlspecialchars($row['title']); ?>"
                             onclick="openLightbox('img/<?php echo htmlspecialchars($row['picurl']); ?>')">
                    </div>
                    
                    <div class="album-info">
                        <?php echo htmlspecialchars($row['title']); ?><br>
                        <?php echo $row['album_date']; ?><br>
                        <?php echo htmlspecialchars($row['location']); ?>
                    </div>
                    <div class="album-actions">
                        <a href="albumEdit.php?id=<?php echo $row['album_id']; ?>" class="btn-album">編輯</a>
                        <a href="albumDelete.php?id=<?php echo $row['album_id']; ?>" class="btn-album" onclick="return confirm('確定要刪除嗎？');">刪除</a>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="pagination">
                <?php 
                if($page > 1){
                    echo "<a href='album.php?page=1'>|<</a>";
                    echo "<a href='album.php?page=".($page-1)."'><<</a>";
                }
                
                for ($i=1; $i<=$total_pages; $i++) {
                    $active = ($i == $page) ? "active" : "";
                    echo "<a href='album.php?page=".$i."' class='".$active."'>".$i."</a>"; 
                }

                if($page < $total_pages){
                    echo "<a href='album.php?page=".($page+1)."'>>></a>";
                    echo "<a href='album.php?page=".$total_pages."'>>|</a>";
                }
                ?>
            </div>
        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>

    <!-- Lightbox 結構 -->
    <div id="lightbox" class="lightbox-overlay" onclick="closeLightbox(event)">
        <div class="lightbox-content">
            <span class="lightbox-close" onclick="closeLightbox(event)">×</span>
            <img id="lightbox-img" src="" alt="Full Size">
        </div>
    </div>

    <!-- Lightbox Script -->
    <script>
        function openLightbox(imgSrc) {
            var lightbox = document.getElementById('lightbox');
            var img = document.getElementById('lightbox-img');
            img.src = imgSrc;
            lightbox.style.display = 'flex';
        }

        function closeLightbox(event) {
            // 點擊背景或關閉按鈕才關閉，避免點擊圖片本身也關閉 (視需求而定，這裡設為點擊背景可關)
            if (event.target.id === 'lightbox' || event.target.className === 'lightbox-close') {
                document.getElementById('lightbox').style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
