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
$row_m = $DB->fetch_assoc($query);

if($row_m['mt_idx']) {
    if($row_m['mt_level'] == 3) {
        $query = "select * from member_coupon_t where mt_idx = ".$row_m['mt_idx'];
        $coupon = $DB->count_query($query);
        $arr['info'] = array(
            "mt_nickname" => $row_m['mt_nickname'],
            "mt_image" => $row_m['mt_image'] ? $ct_img_url . '/' . $row_m['mt_image']."?cache=".$row_m['mt_udate'] : $ct_member_no_img_url,
            "mt_point" => (int)$row_m['mt_point'],
            "mt_coin" => (int)$row_m['mt_coin'],
            "coupon" => (int)$coupon,
        );

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row_m['mt_idx']." and ct_select = 2 and ct_status > 0 and ct_status = 2";
        $pay = $DB->count_query($query);
        $arr['order']['pay'] = (int)$pay;

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row_m['mt_idx']." and ct_select = 2 and ct_status > 0 and ct_status = 3";
        $ready = $DB->count_query($query);
        $arr['order']['ready'] = (int)$ready;

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row_m['mt_idx']." and ct_select = 2 and ct_status > 0 and ct_status = 4";
        $shipping  = $DB->count_query($query);
        $arr['order']['shipping'] = (int)$shipping;

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row_m['mt_idx']." and ct_select = 2 and ct_status > 0 and ct_status = 5";
        $finished = $DB->count_query($query);
        $arr['order']['finished'] = (int)$finished;

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row_m['mt_idx']." and ct_select = 2 and ct_status > 0 and ct_status in (7,8)";
        $cancel = $DB->count_query($query);
        $arr['order']['cancel'] = (int)$cancel;

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row_m['mt_idx']." and ct_select = 2 and ct_status > 0 and ct_status in (80,81,82)";
        $exchange = $DB->count_query($query);
        $arr['order']['exchange'] = (int)$exchange;

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row_m['mt_idx']." and ct_select = 2 and ct_status > 0 and ct_status in (90,91)";
        $refund = $DB->count_query($query);
        $arr['order']['refund'] = (int)$refund;

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 회원 마이페이지', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '회원 마이페이지', $jwt);
        }
    } else {
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 마이페이지', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 마이페이지', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>