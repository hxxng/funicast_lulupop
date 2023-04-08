<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['pt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. pt_idx', '');
    exit;
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}

$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);
if($row_m['mt_idx']) {
    $query = "select * from wish_product_t where mt_idx = ".$row_m['mt_idx']." and pt_idx = ".$decoded_array['pt_idx'];
    $row = $DB->fetch_query($query);
    if($row['idx']) {
        if($row['wpt_status'] == "Y") {
            $wpt_status = "N";
        } else {
            $wpt_status = "Y";
        }
        $DB->update_query("wish_product_t", array("wpt_status" => $wpt_status, "wpt_wdate" => "now()"), " mt_idx = ".$row_m['mt_idx']." and pt_idx = ".$decoded_array['pt_idx']);
        $idx = $row['idx'];
    } else {
        $DB->insert_query("wish_product_t", array("mt_idx" => $row_m['mt_idx'], "pt_idx" => $decoded_array['pt_idx'], "wpt_status" => "Y", "wpt_wdate" => "now()"));
        $idx = $DB->insert_id();
        $wpt_status = "Y";
    }
    $arr['wpt_idx'] = $idx;
    $arr['wpt_status'] = $wpt_status;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 상품 찜하기', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '상품 찜하기', $jwt);
    }
} else {
    echo result_data('false', '회원이 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>