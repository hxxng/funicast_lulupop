<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act']=='update') {
		unset($arr_query);
		$arr_query = array(
			"tt_agree1" => $_POST['tt_agree1'],
			"tt_agree2" => $_POST['tt_agree2'],
			"tt_agree3" => $_POST['tt_agree3'],
		);

		$where_query = "idx = '1'";

		$DB->update_query('terms_t', $arr_query, $where_query);

		p_alert("수정되었습니다.");
	}

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>