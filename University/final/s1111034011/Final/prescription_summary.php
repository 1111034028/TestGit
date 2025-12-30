<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>配藥單</title>
    <link rel="stylesheet" href="css/summary.css">
</head>

<body>
    <div class="summary-container">
        <h2>配藥單</h2>
        <table>
            <tr>
                <th>欄位</th>
                <th>內容</th>
            </tr>
            <tr>
                <td>藥名稱</td>
                <td><?php echo htmlspecialchars($_GET['medicine_name']); ?></td>
            </tr>
            <tr>
                <td>劑量</td>
                <td><?php echo htmlspecialchars($_GET['dosage']); ?></td>
            </tr>
            <tr>
                <td>服用方式</td>
                <td><?php echo htmlspecialchars($_GET['useage']); ?></td>
            </tr>
            <tr>
                <td>服用時間</td>
                <td><?php echo htmlspecialchars($_GET['schedule']); ?></td>
            </tr>
            <tr>
                <td>副作用</td>
                <td><?php echo htmlspecialchars($_GET['side_effects']); ?></td>
            </tr>
        </table>
        <a href="index.php" class="button">返回首頁</a>
    </div>
</body>

</html>
