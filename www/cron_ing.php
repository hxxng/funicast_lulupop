<?php
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/Point_class.php";

$mem_list = $DB->select_query("select * from member_t where mt_level in (3,5) and mt_rdate is null and mt_fcm is not null");

switch($_GET['push']){
	case '1':
		event();	//1.이벤트 시작 푸시
	break;
	case '2':
//		point_extinction();	//3.포인트 소멸알림
	break;
}

if($_GET['point'] == 1){
	point_del();
}

switch($_GET['order']){
	case '1':
		order_del();	//1. 무통장 입금처리 안할경우 일주일 뒤 삭제
	break;
	case '2':
		order_confirm();	//2 발송처리 후 한달 뒤 자동 확정
	break;
}

//1.이벤트 시작 알림
function event(){
	global $mem_list, $DB;
    $query = "select * from event_t where et_sdate = '".date("Y-m-d")."'";
    $event = $DB->fetch_assoc($query);
    if($event['idx']) {
        foreach($mem_list as $row) {
            if ($row['mt_pushing'] == "Y" || $row['mt_pushing1'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }

            $token_list = array($row['mt_fcm']);
            $message = "룰루팝 새로운 이벤트가 등록되었어요 확인해보세요!";
            $title = "룰루팝";

            $op_idx .= $row['idx'] . ",";

            send_notification2($token_list, $title, $message, "Event_Detail_Page", $event['idx'], $chk);
        }
        unset($arr_query);
        $plt_set = array(
            'plt_title' => $title,
            'plt_content' => $message,
            'plt_table' => "event_t",
            'plt_type' => 1,
            'plt_index' => $event['idx'],
            'mt_idx' => 1,
            'op_idx' => $op_idx,
            'plt_wdate' => 'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);
    }
}
//3.포인트 소멸알림
function point_extinction(){
	global $DB;
	$list = $DB->select_query("select plt_expire_date, sum(plt_price - plt_use_point) as sum_point, (select mt_id from member_t where member_t.idx = mt_idx limit 1) as mt_id from point_log_t where plt_expire_date = '".date("Y-m-d",strtotime("-8 day"))."' and plt_expired = '0' and plt_type='P' and plt_expire_date <> '9999-12-31'");
	foreach($list as $point){
		if($point['sum_point'] > 0){
			$date_arr = explode('-', $point['plt_expire_date']);
			$date = $date_arr[1].'월 '.$date_arr[2].'일';
			proc_noti('', ','.$point['mt_id'], 'point_extinction', '', array(), '', '', '', "포인트 ".$point['sum_point']."원이 ".$date." 소멸예정입니다");
		}
	}
}

//포인트 삭제
function point_del(){
	global $DB;
	$objPoint = new Point_class(array('db'=>$DB));
	$list = $DB->select_query("select plt_expire_date, sum(plt_price - plt_use_point) as sum_point, mt_idx, (select mt_point from member_t where member_t.idx = mt_idx limit 1) as mt_point from point_log_t where plt_expire_date = '".date("Y-m-d",strtotime("-1 day"))."' and plt_expired = '0' and plt_type='P' and plt_expire_date <> '9999-12-31'");
	foreach($list as $row){
		if($row['sum_point']>0){
			$content = '포인트 소멸';            
            $point = $row['sum_point'] * (-1);
            $plt_mt_point = $row['mt_point'] + $point;
            $plt_expire_date = date('Y-m-d');
            $plt_expired = 1;

            $sql = " insert into point_log_t
                        set mt_idx = ".$row['mt_idx'].",
                            plt_type = 'M',
                            plt_wdate = '".date('Y-m-d H:i:s')."',
                            plt_memo = '".addslashes($content)."',
                            plt_price = '$point',
                            plt_use_point = '0',
                            plt_mt_point = '$plt_mt_point',
                            plt_expired = '$plt_expired',
                            plt_expire_date = '$plt_expire_date'
                     ";
            $DB->db_query($sql);

            // 포인트를 사용한 경우 포인트 내역에 사용금액 기록
            if($point < 0) {
                $objPoint->insert_use_point($row['mt_idx'], $point);
            }

			// 유효기간이 있을 때 기간이 지난 포인트 expired 체크
			$sql = " update point_log_t
						set plt_expired = '1'
						where mt_idx = ".$row['mt_idx']."
						  and plt_expired <> '1'
						  and plt_expire_date <> '9999-12-31'
						  and plt_expire_date < '".date('Y-m-d')."' ";
			$DB->db_query($sql);

			// 포인트합
			$sql = " select sum(plt_price) as sum_po_point
						from point_log_t
						where mt_idx = '".$row['mt_idx']."' ";
			$point_row = $DB->fetch_query($sql);
			$DB->update_query("member_t", array('mt_point'=>$point_row['sum_po_point']), "idx=".$row['mt_idx']);

		}
	}
}

//2. 발송처리 후 한달 뒤 자동 확정
function order_confirm(){
	global $DB;
	$query = "SELECT * FROM cart_t WHERE ct_dsdate < '".date("Y-m-d",strtotime("-30 day"))."' AND ct_status IN(4,5) AND ct_cdate IS NULL";
	$list = $DB->select_query($query);
	if(is_array($list)){
		foreach($list as $row){
			$DB->update_query("cart_t", array("ct_cdate"=>"now()", "ct_status"=>6), "idx=".$row['idx']);
		}
	}

	$query = "select * from order_t where ot_dsdate < '".date("Y-m-d",strtotime("-30 day"))."' AND ot_status IN(4,5) AND ot_cdate IS NULL";
	$list = $DB->select_query($query);
	if(is_array($list)){
		foreach($list as $row){
			$cart_row = $DB->fetch_query("select count(0) as cnt from cart_t where ot_code='".$row['ot_code']."' and ct_status != 6");
			if($cart_row['cnt'] < 1){
				$DB->update_query("order_t", array("ot_status"=>6, "ot_dsdate"=>"now()"), "ot_code='".$row['ot_code']."'");
			}
		}
	}
}
?>