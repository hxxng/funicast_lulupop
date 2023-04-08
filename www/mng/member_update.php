<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act']=='input') {

	} else if($_POST['act']=='update') {
		unset($arr_query);
		$arr_query = array(			
			"mt_level" => $_POST['mt_level'],
            "mt_recipient" => $_POST['mt_recipient'],
            "mt_tel" => $_POST['mt_tel'],
            "mt_phone" => $_POST['mt_phone'],
            "mt_zip" => $_POST['mt_zip'],
            "mt_add1" => $_POST['mt_add1'],
            "mt_add2" => $_POST['mt_add2'],
            "mt_account_name" => $_POST['mt_account_name'],
            "mt_bank" => $_POST['mt_bank'],
            "mt_account" => $_POST['mt_account']
		);
				
		$where_query = "idx = '".$_POST['mt_idx']."'";
		$DB->update_query('member_t', $arr_query, $where_query);	

		p_alert("수정되었습니다.");
	} else if($_POST['act']=='delete') {
		$DB->del_query('member_t', " idx = '".$_GET['mt_idx']."'");

		p_alert("삭제되었습니다.");
	
	} else if($_POST['act']=='retire') {
		unset($arr_query);
		$arr_query = array(
			"mt_level" => '1',
			"mt_status" => 'N',
			"mt_rdate" => "now()",
			"mt_retire_memo" => "관리자 권한 회원탈퇴 처리",
		);

		$where_query = "idx = '".$_POST['mt_idx_t']."'";

		$DB->update_query('member_t', $arr_query, $where_query);

		echo "Y";
	} else if($_POST['act'] == "status_update") {
        $idx = explode("|", $_POST['idx']['idx']);
        
        foreach($idx as $key => $val) {
            $idx_t = trim($val);
            unset($arr_query);
            $arr_query = array(
                "mt_grade" => $_POST['mt_grade'],
            );
            $where_query = "idx = '".$idx_t."'";
            $DB->update_query('member_t', $arr_query, $where_query);
        }
        echo json_encode(array('result' => '_ok', 'msg' => '구분이 변경되었습니다.'));
    } else if($_POST['act'] == "chg_level") {
        $idx = explode("|", $_POST['idx']['idx']);

        if($_POST['num'] == 1) {
            $mt_level = 2;
        } else {
            $mt_level = 1;
        }
        foreach($idx as $key => $val) {
            $idx_t = trim($val);
            unset($arr_query);
            $arr_query = array(
                "mt_level" => $mt_level,
                "mt_rdate" => null,
                "mt_retire_memo" => null,
            );
            $where_query = "idx = '".$idx_t."'";
            $DB->update_query('member_t', $arr_query, $where_query);
        }
        echo json_encode(array('result' => '_ok', 'msg' => '처리되었습니다.'));
    } else if($_POST['act'] == "plus_coin") {
        $query = "select * from member_t where idx = ".$_POST['idx'];
        $row = $DB->fetch_assoc($query);
        if($row) {
            unset($arr_query);
            $arr_query = array(
                "mt_coin" => ($row['mt_coin']+$_POST['mt_coin']),
            );
            $where_query = "idx = '".$_POST['idx']."'";
            $DB->update_query('member_t', $arr_query, $where_query);
            $DB->insert_query("coin_t", array("ct_type" => 2, "ct_amount" => $_POST['mt_coin'], "mt_idx" => $_POST['idx'], "ct_pdate" => "now()"));
            echo json_encode(array('result' => '_ok', 'msg' => '코인이 추가지급되었습니다.'));
        }
    }

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>