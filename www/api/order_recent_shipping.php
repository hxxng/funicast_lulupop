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
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $query_ot = "select * from order_t where ot_status > 1 and mt_idx = " . $row['mt_idx']." order by ot_pdate desc";
    } else {
        $query_ot = "select * from order_t where ot_status > 1 and nmt_id = '" . $row['mt_id']."' order by ot_pdate desc";
    }
    $row_ot = $DB->fetch_query($query_ot);
    if($row_ot) {
        $arr = array(
            'ot_b_name' => $row_ot['ot_b_name'],
            'ot_b_hp' => $row_ot['ot_b_hp'],
            'ot_b_zip' => $row_ot['ot_b_zip'],
            'ot_b_addr1' => $row_ot['ot_b_addr1'],
            'ot_b_addr2' => $row_ot['ot_b_addr2'],
        );

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 최근 배송지', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '최근 배송지', $jwt);
        }
    } else {
        $payload['data'] = null;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 최근 배송지', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '최근 배송지', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>