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
if($decoded_array['mt_nickname']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_nickname', '');
    exit;
}
if($decoded_array['mt_pct_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_pct_idx', '');
    exit;
}

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'
";
$row = $DB->fetch_assoc($query);
if($row['mt_idx']) {
    $query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_nickname = '".$decoded_array['mt_nickname']."' and mt_id != '".$decoded_array['mt_id']."'
    ";
    $chk_nickname = $DB->fetch_query($query);

    if($chk_nickname['mt_idx']) {
        echo result_data('false', '이미 사용중인 닉네임입니다.', $arr);
        exit;
    } else {
        $arr_query = array(
            "mt_pct_idx" => $decoded_array['mt_pct_idx'],
            "mt_nickname" => $decoded_array['mt_nickname'],
            "mt_set_yn" => "Y",
            "mt_set_cate_yn" => "Y",
        );

        $DB->update_query('member_t', $arr_query, " idx = ".$row['mt_idx']);

        $query = "select *, idx as mt_idx from member_t where idx = ".$row['mt_idx'];
        $row = $DB->fetch_assoc($query);

        $arr = $row;
        $arr['mt_level'] = (int)$row['mt_level'];
        $arr['mt_image'] = ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url);
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 닉네임 설정 + 관심 카테고리 설정', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '닉네임 설정 + 관심 카테고리 설정', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>