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
if($decoded_array['ct_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_idx', '');
    exit;
}

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $ct_idx = explode(",", $decoded_array['ct_idx']);
        for ($i = 0; $i < count($ct_idx); $i++) {
            $count = $DB->count_query("select * from cart_t where mt_idx = '" . $row['mt_idx'] . "' and idx = " . $ct_idx[$i]);
            if ($count > 0) {
                $DB->del_query('cart_t', " mt_idx = '" . $row['mt_idx'] . "' and idx = " . $ct_idx[$i]);
                $arr['ct_idx'][] = $ct_idx[$i];
            } else {
                echo result_data('false', '존재하지 않는 장바구니입니다.', $arr);
                return false;
            }
        }
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 장바구니 삭제', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '장바구니 삭제', $jwt);
        }
    } else {
        $ct_idx = explode(",", $decoded_array['ct_idx']);
        for ($i = 0; $i < count($ct_idx); $i++) {
            $count = $DB->count_query("select * from cart_t where nmt_id = '" . $decoded_array['mt_id'] . "' and idx = " . $ct_idx[$i]);
            if ($count > 0) {
                $DB->del_query('cart_t', " nmt_id = '" . $decoded_array['mt_id'] . "' and idx = " . $ct_idx[$i]);
                $arr['ct_idx'][] = $ct_idx[$i];
            } else {
                echo result_data('false', '존재하지 않는 장바구니입니다.', $arr);
                return false;
            }
        }
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 장바구니 삭제', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 장바구니 삭제', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>