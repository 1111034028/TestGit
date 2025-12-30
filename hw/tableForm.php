<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>表格表單</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
    <link rel="stylesheet" href="css/tableForm.css" media="all">
</head>
<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php require "nav.html"; ?>
        </header>

        <main id="form-contents">
            <h2>表格與表單</h2>
            <div class="note">*為必填項目。</div>

            <form action="showData.php" method="post" enctype="multipart/form-data">
                
                <div class="section-title">貓咪資料</div>
                <table class="form-table">
                    <tr>
                        <th>貓咪名<span class="required">*</span></th>
                        <td><input type="text" name="catName" required></td>
                    </tr>
                    <tr>
                        <th>年齡<span class="required">*</span></th>
                        <td>
                            <select name="age">
                                <option value="">請選擇</option>
                                <?php
                                for($i=1; $i<=20; $i++){
                                    echo "<option value='{$i}'>{$i}歲</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>性別<span class="required">*</span></th>
                        <td>
                            <input type="radio" name="gender" value="男生" checked>男生
                            <input type="radio" name="gender" value="女生">女生
                        </td>
                    </tr>
                    <tr>
                        <th>最愛的食物</th>
                        <td>
                            <input type="checkbox" name="food[]" value="魚">魚
                            <input type="checkbox" name="food[]" value="肉">肉
                            <input type="checkbox" name="food[]" value="乾飼料">乾飼料
                            <input type="checkbox" name="food[]" value="貓罐頭">貓罐頭
                            <input type="checkbox" name="food[]" value="肉泥">肉泥
                            <input type="checkbox" name="food[]" value="其他">其他
                        </td>
                    </tr>
                    <tr>
                        <th>照片<span class="required">*</span></th>
                        <td><input type="file" name="photo" accept="image/*" required></td>
                    </tr>
                </table>

                <div class="section-title">飼主資料</div>
                <table class="form-table">
                    <tr>
                        <th>飼主名<span class="required">*</span></th>
                        <td>
                            <input type="text" name="ownerName" required>
                            <span class="hint">※可用暱稱</span>
                        </td>
                    </tr>
                    <tr>
                        <th>E-Mail<span class="required">*</span></th>
                        <td>
                            <input type="email" name="email" required placeholder="example@email.com"> 
                            <span class="hint">※請輸入半形文字</span>
                        </td>
                    </tr>
                    <tr>
                        <th>留言</th>
                        <td>
                            <textarea name="comment"></textarea>
                        </td>
                    </tr>
                </table>

                <div class="btn-container">
                    <input type="reset" value="清除充填">
                    <input type="submit" value="送出">
                </div>

            </form>
        </main>

        <footer id="footer">
            <?php require "foot.html"; ?>
        </footer>
    </div>
</body>
</html>
