<?php
$message = $_GET['message'];
$code = $_GET['code'];

$result = new stdClass();
$result->result = false;
$result->code = $code;

echo "<script> window.ReactNativeWebView.postMessage(`".json_encode($result)."`); </script>";
?>
