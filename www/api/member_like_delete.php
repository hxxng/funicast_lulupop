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
if($decoded_array['wpt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. wpt_idx', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_assoc($query);

if($row_m['mt_idx']) {
    $wpt_idx = explode(",", $decoded_array['wpt_idx']);
    for ($i = 0; $i < count($wpt_idx); $i++) {
        if($wpt_idx[$i]) {
            $query = "select * from wish_product_t where wpt_status = 'Y' and mt_idx = " . $row_m['mt_idx'] . " and idx = " . $wpt_idx[$i];
            $list = $DB->fetch_assoc($query);
            if ($list) {
                $DB->update_query("wish_product_t", array("wpt_status" => 'N'), " idx = " . $wpt_idx[$i]);
            } else {
                echo result_data("false", "삭제할 찜이 존재하지 않습니다.", "");
                exit;
            }
        }
    }
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 나의 찜 삭제', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '나의 찜 삭제', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>