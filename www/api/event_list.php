<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "select * from event_t where et_status = 1 and (et_sdate <= curdate() and et_edate >= curdate())";
$list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
$count = $DB->count_query($query);

$n_page = ceil($count[0] / 20);

if($list) {
    unset($arr);
    foreach ($list as $row) {
        $arr['event'][] = array(
            "et_idx" => $row['idx'],
            "et_title" => $row['et_title'],
            "et_url" => $row['et_url'],
            "et_img" => $ct_img_url.'/'.$row['et_img']."?cache=".strtotime($row['et_udate']),
            "et_sdate" => substr($row['et_sdate'],0,10),
            "et_edate" => substr($row['et_edate'],0,10),
            "et_wdate" => substr($row['et_wdate'],0,10),
        );
    }
    $arr['count'] = (int)$count;
    $arr['maxpage'] = (int)$n_page;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 이벤트 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '이벤트 리스트', $jwt);
    }
} else {
    echo result_data('false', '이벤트가 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>