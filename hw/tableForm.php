<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>貓咪資料表單</title>
    <link rel="stylesheet" href="css/navCSS.css" media="all">
    <link rel="stylesheet" href="css/contacts.css" media="all">
    <link rel="stylesheet" href="css/indexCSS.css" media="all">
</head>

<body>
    <div id="wrap">
        <header id="header" class="clearheader">
            <?php
            require "nav.php";
            ?>
        </header>
        <main id="contents">
            <h2 id="cat">表格與表單</h2>
            <form id="entryFrom" action="#" method="post">
                <p><strong><span class="require">*</span>為必填項目。</strong></p>
                <table class="entryTable">
                    <caption>貓咪資料</caption>
                    <tr>
                        <th>貓咪名*</th>
                        <td><input type="text" name="cat-name" required autofocus></td>
                    </tr>
                    <tr>
                        <th>年齡*</th>
                        <td>
                            <select name="age" required>
                                <option value="" selected>請選擇</option>
                                <option value="0">未滿一歲</option>
                                <option value="1">1~5歲</option>
                                <option value="2">6~10歲</option>
                                <option value="3">11~15歲</option>
                                <option value="4">16~20歲</option>
                                <option value="5">20歲以上</option>
                                <option value="6">不明</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>性別*</th>
                        <td>
                            <input type="radio" name="sex" id="male" value="男生" checked><label for="male">男生</label>
                            <input type="radio" name="sex" id="female" value="女生"><label for="female">女生</label>
                        </td>
                    </tr>
                    <tr>
                        <th>最愛的食物</th>
                        <td>
                            <input type="checkbox" name="favorite" id="favo1" value="魚"><label for="favo1">魚</label>
                            <input type="checkbox" name="favorite" id="favo2" value="肉"><label for="favo2">肉</label>
                            <input type="checkbox" name="favorite" id="favo3" value="乾飼料"><label for="favo3">乾飼料</label>
                            <input type="checkbox" name="favorite" id="favo4" value="貓罐頭"><label for="favo4">貓罐頭</label>
                            <input type="checkbox" name="favorite" id="favo5" value="肉泥"><label for="favo5">肉泥</label>
                            <input type="checkbox" name="favorite" id="favo6" value="其他"><label for="favo6">其他</label>
                        </td>
                    </tr>
                    <tr>
                        <th>照片*</th>
                        <td><input type="file" name="photo" required></td>
                    </tr>
                </table>
                <table class="entryTable">
                    <caption id="owner">飼主資料</caption>
                    <tr>
                        <th>飼主名*</th>
                        <td><input type="text" name="name" required placeholder="黑貓小町" required><small>※可用暱稱</small>
                        </td>
                    </tr>
                    <tr>
                        <th>E-Mail*</th>
                        <td><input type="email" name="email" required placeholder="sample@gmail.com"
                                required><small>※請輸入半形文字</small></td>
                    </tr>
                    <tr>
                        <th>留言</th>
                        <td><textarea name="comment" rows="4" cols="40"></textarea></td>
                    </tr>
                </table>
                <div class="entryBtns">
                    <input type="reset" value="清除充填">
                    <input type="submit" value="送出">
                </div>
            </form>
        </main>
        <footer id="footer">
            <?php
            require "foot.html";
            ?>
        </footer>
    </div>
</body>