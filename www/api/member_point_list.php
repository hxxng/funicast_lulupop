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

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select * from point_log_t where mt_idx = ".$row['mt_idx']." order by plt_wdate desc";
    $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
    $count= $DB->count_query($query);
    if($list) {
        foreach ($list as $row_c) {
            if($row_c['plt_status'] == 1) {
                $plt_status = "상품구매적립";
            } else if($row_c['plt_status'] == 2) {
                $plt_status = "구매시 사용";
            } else if($row_c['plt_status'] == 3) {
                $plt_status = "회원가입적립";
            } else if($row_c['plt_status'] == 4) {
                $plt_status = "커뮤니티첫등록";
            }
            $arr['list'][] = array(
                "plt_idx" => $row_c['idx'],
                "plt_memo" => $row_c['plt_memo'],
                "plt_price" => (int)$row_c['plt_price'],
                "plt_type" => ($row_c['plt_type'] == "P" ? "적립" : "사용"),
                "plt_use_point" => (int)$row_c['plt_use_point'],
                "plt_status" => $plt_status,
                "plt_wdate" => substr($row_c['plt_wdate'],0,10),
            );
        }
    } else {
        $arr['list'] = [];
    }

    $arr['count'] = (int)$count;
    $n_page = ceil($count / 20);
    if($n_page == 0) {
        $arr['maxpage'] = 1;
    } else {
        $arr['maxpage'] = (int)$n_page;
    }

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 포인트 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '포인트 리스트', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>