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
    if($decoded_array['search_txt'] != "") {
        $where = " and (instr(mt_nickname, '".$decoded_array['search_txt']."'))";
    } else {
        $where = "";
    }
    $query = "select follow_t.*, mt_nickname, mt_image, mt_id, mt_udate from follow_t left join member_t on member_t.idx = follow_t.ft_mt_idx where mt_idx = " . $row['mt_idx']." and ft_hide = 'Y'";
    $list2 = $DB->select_query($query . $where . " limit " . $item_count . ", " . ($item_count + 20));
    $count2 = $DB->count_query($query . $where);
    $n_page = ceil($count2[0] / 20);
    if ($list2) {
        foreach ($list2 as $row_f2) {
            $arr['list'][] = array(
                "mt_idx" => $row_f2['ft_mt_idx'],
                "mt_id" => $row_f2['mt_id'],
                "mt_nickname" => $row_f2['mt_nickname'],
                "mt_image" => ($row_f2['mt_image'] ? $ct_img_url . '/' . $row_f2['mt_image']."?cache=".strtotime($row_f2['mt_udate']) : $ct_member_no_img_url),
                "ft_hide" => $row_f2['ft_hide'],
            );
        }
        $arr['count'] = (int)$count2;
        $arr['maxpage'] = (int)$n_page;
    }
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 숨김 유저 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '숨김 유저 리스트', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>