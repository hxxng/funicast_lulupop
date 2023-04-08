<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "select * from notice_t order by idx desc";

$list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
$count = $DB->count_query("select * from notice_t order by idx desc");

$n_page = ceil($count / 20);

if($list) {
    unset($arr);
    foreach ($list as $row) {
        $arr['list'][] = array(
            "nt_idx" => $row['idx'],
            "nt_title" => $row['nt_title'],
            "nt_wdate" => substr($row['nt_wdate'], 0, 10),
        );
    }
    $arr['count'] = (int)$count;
    $arr['maxpage'] = (int)$n_page;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 공지사항 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '공지사항 리스트', $jwt);
    }
} else {
    echo result_data('false', '공지사항 리스트가 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>