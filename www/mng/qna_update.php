<?
    include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	$_act = $_REQUEST['act'];

	if($_POST['act']=='update') {
		unset($arr_query);
        if(!empty($_POST['qt_answer'])) {
            $qt_status = 2;
        } else {
            $qt_status = 1;
        }
		$arr_query = array(
			"qt_answer" => $_POST['qt_answer'],
			"qt_adate" => "now()",
			"qt_status" => $qt_status,
		);

		$where_query = "idx = '".$_POST['qt_idx']."'";

		$DB->update_query('qna_t', $arr_query, $where_query);

        $query = "select * from qna_t where idx = ".$_POST['qt_idx'];
        $row = $DB->fetch_assoc($query);
        if($row) {
            $query = "select * from member_t where idx = ".$row['mt_idx'];
            $row_m = $DB->fetch_assoc($query);
            if($row_m['mt_pushing'] == "Y" || $row_m['mt_pushing3'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }
            $token_list = array($row_m['mt_fcm']);
            $message = "1:1 문의에 답변을 확인해 주세요.";
            $title = "룰루팝 문의";

            $op_idx = $row_m['idx'];

            send_notification2($token_list, $title, $message, "MyPage_QnADetail_Page", $_POST['qt_idx'], $chk);
        }
        unset($arr_query);
        $plt_set = array(
            'plt_title'=>$title,
            'plt_content'=>$message,
            'plt_table'=>"qna_t",
            'plt_type'=> 3,
            'plt_index'=>$row['idx'],
            'mt_idx'=>1,
            'op_idx'=>$op_idx,
            'plt_wdate'=>'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);

		p_alert('처리되었습니다.');
    }

    include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>