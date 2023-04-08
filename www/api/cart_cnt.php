<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $query = "
        select *, a1.idx as ct_idx from cart_t a1
        where a1.mt_idx = '" . $row['mt_idx'] . "' and ct_select = 0 and ct_status = 0 and ct_direct != 1";
        $count = $DB->count_query($query);

        $arr['count'] = (int)$count;

        $query = "select a1.* from pushnotification_log_t a1 left join pushnotification_read_log_t on pushnotification_read_log_t.plt_idx = a1.idx where plt_idx is null and instr(a1.op_idx, ".$row['mt_idx'].")";
        $push_cnt = $DB->count_query($query);
        if($push_cnt > 0) {
            $push = true;
        } else {
            $push = false;
        }

        $arr['push'] = $push;

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 장바구니 수량 + 알림', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '장바구니 수량 + 알림', $jwt);
        }
    } else {
        $query = "
        select *, a1.idx as ct_idx from cart_t a1
        where a1.nmt_id = '" . $decoded_array['mt_id'] . "' and ct_select = 0 and ct_status = 0 and ct_direct != 1";
        $count = $DB->count_query($query);

        $arr['count'] = (int)$count;

        $query = "select a1.* from pushnotification_log_t a1 left join pushnotification_read_log_t a2 on a2.plt_idx = a1.idx and a2.mt_idx = ".$row['mt_idx']."
                where a2.plt_idx is null and instr(a1.op_idx, ".$row['mt_idx'].")";
        $push_cnt = $DB->count_query($query);
        if($push_cnt > 0) {
            $push = true;
        } else {
            $push = false;
        }

        $arr['push'] = $push;

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 장바구니 수량 + 알림', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 장바구니 수량 + 알림', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>