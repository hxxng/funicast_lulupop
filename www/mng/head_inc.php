<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
	include $_SERVER['DOCUMENT_ROOT']."/config_mng_inc.php";

	$v_txt = "20210111_15";
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
<title><?=APP_TITLE?> ADMIN</title>
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
<link rel="stylesheet" type="text/css" href="<?=STATIC_HTTP?>/css/default_mng.css?v=<?=$v_txt?>" />

<script type="text/javascript" src="<?=STATIC_HTTP?>/js/base.js"></script>
<script type="text/javascript" src="<?=STATIC_HTTP?>/js/Chart.min.js"></script>
<script type="text/javascript" src="<?=STATIC_HTTP?>/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="<?=STATIC_HTTP?>/js/dataTables.bootstrap4.js"></script>
<script type="text/javascript" src="<?=STATIC_HTTP?>/js/default_mng.js?v=<?=$v_txt?>"></script>

<link rel="stylesheet" type="text/css" href="<?=STATIC_HTTP?>/lib/datepicker/jquery.datetimepicker.min.css"/ >
<script src="<?=STATIC_HTTP?>/lib/datepicker/jquery.datetimepicker.full.min.js"></script>

<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.2/dist/jquery.validate.min.js"></script>
<script type="text/javascript">
<!--
	$.extend($.validator.messages, {
		required: "필수 항목입니다.",
		remote: "항목을 수정하세요.",
		email: "유효하지 않은 E-Mail주소입니다.",
		url: "유효하지 않은 URL입니다.",
		date: "올바른 날짜를 입력하세요.",
		dateISO: "올바른 날짜(ISO)를 입력하세요.",
		number: "유효한 숫자가 아닙니다.",
		digits: "숫자만 입력 가능합니다.",
		creditcard: "신용카드 번호가 바르지 않습니다.",
		equalTo: "같은 값을 다시 입력하세요.",
		extension: "올바른 확장자가 아닙니다.",
		maxlength: $.validator.format("{0}자를 넘을 수 없습니다. "),
		minlength: $.validator.format("{0}자 이상 입력하세요."),
		rangelength: $.validator.format("문자 길이가 {0} 에서 {1} 사이의 값을 입력하세요."),
		range: $.validator.format("{0} 에서 {1} 사이의 값을 입력하세요."),
		max: $.validator.format("{0} 이하의 값을 입력하세요."),
		min: $.validator.format("{0} 이상의 값을 입력하세요.")
	});
//-->
</script>

<link rel="stylesheet" type="text/css" href="<?=STATIC_HTTP?>/lib/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="<?=STATIC_HTTP?>/lib/slick/slick-theme.css"/>
<script type="text/javascript" src="<?=STATIC_HTTP?>/lib/slick/slick.min.js"></script>

<script type="text/javascript" src="<?=STATIC_HTTP?>/lib/jquery.fileDownload-master/src/Scripts/jquery.fileDownload.js"></script>

<script type="text/javascript" src="<?=STATIC_HTTP?>/js/printThis.js"></script>
</head>
<body>