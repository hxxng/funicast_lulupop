<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/config_mng_inc.php";

$query = "select * from terms_t where idx = 1";
$row = $DB->fetch_assoc($query);
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="<?=KEYWORDS?>" />
    <meta name="description" content="<?=DESCRIPTION?>" />
    <meta property="og:image" content="<?=CDN_HTTP?>/images/splash.jpg">
    <meta property="og:image:width" content="1066">
    <meta property="og:image:height" content="558">
    <meta property="og:url" content="http://<?=APP_DOMAIN?>">
    <meta property="og:title" content="<?=APP_TITLE?>">
    <meta property="og:description" content="<?=APP_TITLE?>">
    <title><?=APP_TITLE?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="<?=CDN_HTTP?>/images/favicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=CDN_HTTP?>/images/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=CDN_HTTP?>/images/favicon.png">
    <link rel="manifest" href="<?=CDN_HTTP?>/images/menifest.json">
    <link rel="mask-icon" href="<?=CDN_HTTP?>/images/safari-pinned-tab.svg" color="#ffffff">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#ffffff">

    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+KR&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.materialdesignicons.com/4.7.95/css/materialdesignicons.min.css">

    <link href="<?=STATIC_HTTP?>/css/base.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_HTTP?>/css/dataTables.bootstrap4.css" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_HTTP?>/css/default_mng.css" />

    <script type="text/javascript" src="<?=STATIC_HTTP?>/js/base.js"></script>
    <script type="text/javascript" src="<?=STATIC_HTTP?>/js/Chart.min.js"></script>
    <script type="text/javascript" src="<?=STATIC_HTTP?>/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="<?=STATIC_HTTP?>/js/dataTables.bootstrap4.js"></script>
    <script type="text/javascript" src="<?=STATIC_HTTP?>/js/default_mng.js"></script>
</head>
<body>
<?=$row['tt_agree1']?>
</body>
</html>