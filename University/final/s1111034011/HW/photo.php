<?php
if (isset($_POST['gray']) && isset($_POST['sepia'])) {
    $photoFile = $_FILES["photo"]["name"];
    $photoBright = $_POST['bright'] . "%";
    $photoContrast = $_POST['contrast'] . "%";
    $photoGray = $_POST['gray'] . "%";
    $photoSepia = $_POST['sepia'] . "%";
    $photoInvert = $_POST['invert'] . "%";
} else {
    $photoFile = "soyeon.jpg";
    $photoBright = "100%";
    $photoContrast = "100%";
    $photoGray = "0%";
    $photoSepia = "0%";
    $photoInvert = "0%";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>影像處理</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/itemList.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link href="css/photo.css" rel="stylesheet" type="text/css">
    <style>
        img {
            filter: brightness(<?php echo $photoBright; ?>) contrast(<?php echo $photoContrast; ?>) grayscale(<?php echo $photoGray; ?>) sepia(<?php echo $photoSepia; ?>) invert(<?php echo $photoInvert; ?>)
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
            <h2>影像處理</h2>
            <div class="card">
                <div class="card-header">
                    <?php
                    echo "*photoFile: " . $photoFile . "<br>";
                    echo "*photoBright: " . $photoBright . "<br>";
                    echo "*photoContrast: " . $photoContrast . "<br>";
                    echo "*photoGray: " . $photoGray . "<br>";
                    echo "*photoSepia: " . $photoSepia . "<br>";
                    echo "*photoInvert: " . $photoInvert . "<br>";
                    ?>
                    <img src="img/<?php echo $photoFile ?>" width="100%">
                </div>
                <div class="card-body">
                    <form method="post" action="<?php $_SERVER["PHP_SELF"] ?>" enctype="multipart/form-data">
                        <table>
                            <tr>
                                <th>圖片</th>
                                <td><input type="file" name="photo" required></td>
                            </tr>
                            <tr>
                                <th>亮度(~100~)</th>
                                <td><input class="text" type="number" name="bright" value="100">%</td>
                            </tr>
                            <tr>
                                <th>對比(~100~)</th>
                                <td><input class="text" type="number" name="contrast" value="100">%</td>
                            </tr>
                            <tr>
                                <th>灰階(0~100)</th>
                                <td><input class="text" type="number" name="gray" value="0">%</td>
                            </tr>
                            <tr>
                                <th>懷舊(0~100)</th>
                                <td><input class="text" type="number" name="sepia" value="0">%</td>
                            </tr>
                            <tr>
                                <th>負片(0~100)</th>
                                <td><input class="text" type="number" name="invert" value="0">%</td>
                            </tr>
                            <tr>
                                <th></th>
                                <td><input type="submit" name="送出"></td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </main>
        <footer id="footer">
            <?php
            require "infoFooter.html";
            ?>
        </footer>
    </div>
</body>

</html>