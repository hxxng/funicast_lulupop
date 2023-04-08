<?
	exit;

	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	$rtn = rsa_generate_keys('lulupop', '2048', $_RSA_HASH_ALGORITHM);
    
	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>
