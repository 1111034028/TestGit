<?php
    require_once('../final/inc/auth_guard.php');
    
    // Handle File Upload and Logic
    if(isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0){
        // Move uploaded file to img directory
        move_uploaded_file($_FILES["photo"]["tmp_name"], "img/" . $_FILES["photo"]["name"]);
        $photoFile = $_FILES["photo"]["name"];
    } elseif(isset($_POST['photo_name_hidden'])) {
        // Keep existing file if no new upload
        $photoFile = $_POST['photo_name_hidden'];
    } else {
        $photoFile = "Castorice.png";
    }

    if(isset($_POST['bright'])){
        $photoBright = $_POST["bright"] . "%";
        $photoContrast = $_POST["contrast"] . "%";
        $photoGray = $_POST["gray"] . "%";
        $photoSepia = $_POST["sepia"] . "%";
        $photoInvert = $_POST["invert"] . "%";
    }else{
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
        /* Custom Input Styles to match reference */
        input[type="number"], input[type="file"] {
            border: 1px solid #ccc;
            border-radius: 20px; /* Rounder inputs */
            padding: 8px 15px;
            width: 100%;
            box-sizing: border-box;
            font-size: 16px;
            color: #333;
            background-color: #fff;
        }
        input[type="number"]:focus, input[type="file"]:focus {
            border-color: #6c5ce7; /* Update focus color to match purple theme */
            outline: none;
            box-shadow: 0 0 5px rgba(108, 92, 231, 0.3);
        }
        /* Pill Button Style - Updated to Purple */
        .btn-pill {
            background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
            font-size: 16px;
            transition: transform 0.1s, box-shadow 0.1s;
        }
        .btn-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108, 92, 231, 0.3);
            background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%); /* Maintain gradient on hover */
        }
        .btn-pill:active {
            transform: translateY(0);
        }
        
        #previewImg {
            max-width: 100%;
            max-height: 500px; /* Fixed height constraint */
            width: auto;
            height: auto;
            object-fit: contain; /* Ensure image isn't distorted */
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: block;
            margin: 0 auto; /* Center image */
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
        
        <main id="contents">
            <h2>影像處理</h2>
            
            <!-- 參數顯示區 -->
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
            
            <!-- 圖片顯示 -->
            <div style="margin-bottom: 30px; text-align: center;"> <!-- Centered container -->
                <img id="previewImg" src="img/<?php echo $photoFile; ?>" alt="Processed Image" style="filter: brightness(<?php echo $photoBright; ?>) contrast(<?php echo $photoContrast; ?>) grayscale(<?php echo $photoGray; ?>) sepia(<?php echo $photoSepia; ?>) invert(<?php echo $photoInvert; ?>);">
            </div>
            
            <!-- 控制表單 -->
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                <!-- Keep track of current filename if no new file is uploaded -->
                 <input type="hidden" name="photo_name_hidden" value="<?php echo $photoFile; ?>">
                 
                <table style="max-width: 600px; margin: 0 auto;">
                    <tr>
                        <th style="text-align: right; padding-right: 15px;">上傳圖片</th>
                        <td><input type="file" name="photo" id="photoInput"></td>
                    </tr>
                    <tr>
                        <th style="text-align: right; padding-right: 15px;">亮度 (0~200)</th>
                        <td>
                            <div class="input-group">
                                <input class="text" name="bright" type="number" value="<?php echo isset($_POST['bright']) ? $_POST['bright'] : '100'; ?>" required>
                                <span>%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: right; padding-right: 15px;">對比度 (0~200)</th>
                        <td>
                            <div class="input-group">
                                <input class="text" name="contrast" type="number" value="<?php echo isset($_POST['contrast']) ? $_POST['contrast'] : '100'; ?>" required>
                                <span>%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: right; padding-right: 15px;">灰階 (0~100)</th>
                        <td>
                            <div class="input-group">
                                <input class="text" name="gray" type="number" value="<?php echo isset($_POST['gray']) ? $_POST['gray'] : '0'; ?>" required>
                                <span>%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: right; padding-right: 15px;">褐色調 (0~100)</th>
                        <td>
                            <div class="input-group">
                                <input class="text" name="sepia" type="number" value="<?php echo isset($_POST['sepia']) ? $_POST['sepia'] : '0'; ?>" required>
                                <span>%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: right; padding-right: 15px;">負片 (0~100)</th>
                        <td>
                            <div class="input-group">
                                <input class="text" name="invert" type="number" value="<?php echo isset($_POST['invert']) ? $_POST['invert'] : '0'; ?>" required>
                                <span>%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <!-- 使用統一的 class btn-pill -->
                        <td><input type="submit" value="套用濾鏡" class="btn-pill"></td>
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
    
    <script>
        // 即時預覽選擇的圖片
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
