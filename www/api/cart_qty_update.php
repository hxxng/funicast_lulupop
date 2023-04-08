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
if($decoded_array['ct_opt_qty']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_opt_qty', '');
    exit;
}

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_query($query);
if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $query_ct = "
        select * from cart_t a1
        where a1.idx = '" . $decoded_array['ct_idx'] . "' and mt_idx = " . $row['mt_idx'] . "
    ";
        $row_ct = $DB->fetch_query($query_ct);

        if ($row_ct['idx']) {
            unset($arr_query);
            $arr_query = array(
                "ct_opt_qty" => $decoded_array['ct_opt_qty'],
                "ct_price" => ($row_ct['pt_price'] * $decoded_array['ct_opt_qty']),
            );

            $where_query = "idx = '" . $row_ct['idx'] . "'";

            $DB->update_query('cart_t', $arr_query, $where_query);

            $payload['data'] = $arr_query;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 장바구니 수량 수정', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '장바구니 수량 수정', $jwt);
            }
        } else {
            echo result_data('false', '존재하지 않는 장바구니 내역입니다.', $arr);
        }
    } else {
        $query_ct = "
            select * from cart_t a1
            where a1.idx = '" . $decoded_array['ct_idx'] . "' and nmt_id = '" . $decoded_array['mt_id'] . "'
        ";
        $row_ct = $DB->fetch_query($query_ct);

        if ($row_ct['idx']) {
            unset($arr_query);
            $arr_query = array(
                "ct_opt_qty" => $decoded_array['ct_opt_qty'],
                "ct_price" => ($row_ct['pt_price'] * $decoded_array['ct_opt_qty']),
            );

            $where_query = "idx = '" . $row_ct['idx'] . "'";

            $DB->update_query('cart_t', $arr_query, $where_query);

            $payload['data'] = $arr_query;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 장바구니 수량 수정', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 장바구니 수량 수정', $jwt);
            }
        } else {
            echo result_data('false', '존재하지 않는 장바구니 내역입니다.', $arr);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>