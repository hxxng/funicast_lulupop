<?
	include "../lib_inc.php";

	session_unset();
	session_destroy();
	unset($_SESSION);
	unset($_COOKIE);

	gotourl("./login.php");

	include "../tail_inc.php";
?>