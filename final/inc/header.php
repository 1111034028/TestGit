<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : '音樂串流平台'; ?></title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
