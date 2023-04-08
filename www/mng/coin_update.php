<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act']=='update') {
        $query = "select * from coin_t where idx = ".$_POST['ct_idx'];
        $row = $DB->fetch_assoc($query);

        $query = "select * from member_t where idx = ".$_POST['mt_idx'];
        $row_m = $DB->fetch_assoc($query);

		unset($arr_query);
        if($_POST['ct_refund_status'] == 2) {
            if($row['ct_status'] == 4) {
                $arr_query['ct_refund_edate'] = "now()";

                $DB->update_query("member_t", array("mt_coin" => (int)$row_m['mt_coin'] - (int)$row['ct_amount']), " idx = ".$row_m['idx']);
            } else {
                p_alert("실패하였습니다.");
                return false;
            }
        }
        $arr_query['ct_refund_status'] = $_POST['ct_refund_status'];

        $where_query = "idx = '".$_POST['ct_idx']."'";
		$DB->update_query('coin_t', $arr_query, $where_query);

        p_alert("저장되었습니다.");
	}

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>