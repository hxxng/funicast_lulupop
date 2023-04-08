<?
    include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	$_act = $_REQUEST['act'];

	if($_POST['act']=='update') {
		unset($arr_query);
		$arr_query = array(
			"rt_status" => $_POST['rt_status'],
			"rt_edate" => "now()",
		);

		$where_query = "idx = '".$_POST['rt_idx']."'";

		$DB->update_query('report_t', $arr_query, $where_query);

        if($_POST['rt_table'] == "comment_t") {
            if($_POST['rt_status'] == 2){
                $query = "select * from report_t where idx = ".$_POST['rt_idx'];
                $row = $DB->fetch_assoc($query);
                $DB->update_query("comment_t", array("ct_status" => 2), " idx = ".$row['report_idx']);
            }
        }
		p_alert('처리되었습니다.');
    }

    include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>