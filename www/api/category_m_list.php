<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['pc_m_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. pc_m_idx', '');
    exit;
}

$query = "select * from product_category_t where pc_depth = 1 and pc_m_idx = ".$decoded_array['pc_m_idx'];
$list = $DB->select_query($query);
$count = $DB->count_query($query);

if($list) {
    unset($arr);
    foreach ($list as $row) {
        $arr['category'][] = array("idx" => $row['idx'], "pc_name" => $row['pc_name']);
    }
    $arr['count'] = (int)$count;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 중분류 카테고리 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '중분류 카테고리 리스트', $jwt);
    }
} else {
    echo result_data('false', '중분류 카테고리가 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>