<?php
    if(isset($_POST['gray']) && isset($_POST["sepia"])){
        $photoFile = $_FILES["photo"]["name"];
        $photoBright = $_POST["bright"] . "%";
        $photoContrast = $_POST["contrast"] . "%";
        $photoGray = $_POST["gray"] . "%";
        $photoSepia = $_POST["sepia"] . "%";
        $photoInvert = $_POST["invert"] . "%";
    }else{
        $photoFile = "Castorice.png";
        $photoBright = "100%";
        $photoContrast = "100%";
        $photoGray = "0%";
        $photoSepia = "0%";
        $photoInvert = "0%";
    }
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>影像處理</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/photo.css" type="text/css">
    <style>
        .card-header img{
            filter: 
            brightness(<?php echo $photoBright; ?>) 
            contrast(<?php echo $photoContrast; ?>) 
            grayscale(<?php echo $photoGray; ?>) 
            sepia(<?php echo $photoSepia; ?>) 
            invert(<?php echo $photoInvert; ?>);
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
        
        <main id="photo-contents">
            <h2>影像處理</h2>
            <!-- 卡片 -->
            <div class="card">
                <!-- 卡片首: 顯示參數與預覽圖 -->
                <div class="card-header">
                    <div class="photo-params">
                    <?php
                        echo "<strong>目前參數：</strong><br>";
                        echo "File: " . $photoFile . "<br>";
                        echo "Bright: " . $photoBright . "<br>";
                        echo "Contrast: " . $photoContrast . "<br>";
                        echo "Gray: " . $photoGray . "<br>";
                        echo "Sepia: " . $photoSepia . "<br>";
                        echo "Invert: " . $photoInvert;
                    ?>
                    </div>
                    <!-- 圖片容器 -->
                    <img src="img/<?php echo $photoFile; ?>" alt="Processed Image">
                </div>
                
                <!-- 卡片身: 控制表單 -->
                <div class="card-body">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                        <table>
                            <tr>
                                <th>上傳圖片</th>
                                <td><input type="file" name="photo" required></td>
                            </tr>
                            <tr>
                                <th>亮度 (0~200)</th>
                                <td><input class="text" name="bright" type="number" value="100" required>%</td>
                            </tr>
                            <tr>
                                <th>對比度 (0~200)</th>
                                <td><input class="text" name="contrast" type="number" value="100" required>%</td>
                            </tr>
                            <tr>
                                <th>灰階 (0~100)</th>
                                <td><input class="text" name="gray" type="number" value="0" required>%</td>
                            </tr>
                            <tr>
                                <th>褐色調 (0~100)</th>
                                <td><input class="text" name="sepia" type="number" value="0" required>%</td>
                            </tr>
                            <tr>
                                <th>負片 (0~100)</th>
                                <td><input class="text" name="invert" type="number" value="0" required>%</td>
                            </tr>
                            <tr>
                                <th></th>
                                <td><input type="submit" value="套用濾鏡"></td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </main>
        
        <footer id="footer">
            <?php
            require "foot.html";
            ?>
        </footer>
    </div>
</body>
</html>