<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['mt_fcm']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_fcm', '');
    exit;
}

$arr = array();
$query = "
    select *, a1.idx as mt_idx from member_t a1
    where mt_fcm = '".$decoded_array['mt_fcm']."' and mt_level = 3 ";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    if($row['mt_fcm'] == $decoded_array['mt_fcm']) {
        unset($arr_query);
        $arr_query = array(
            "mt_ldate" => "now()",
            "mt_status" => "Y",
        );

        $where_query = "idx = '".$row['mt_idx']."'";

        $DB->update_query('member_t', $arr_query, $where_query);

        $query = "select *, idx as mt_idx from member_t where idx = ".$row['mt_idx'];
        $row = $DB->fetch_assoc($query);

        $arr = $row;
        $arr['mt_level'] = (int)$row['mt_level'];
        $arr['mt_image'] = ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url);

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 자동 로그인', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '자동 로그인', $jwt);
        }
    } else {
        echo result_data('false', '회원정보가 일치하지 않습니다.', $arr);
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>