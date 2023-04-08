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
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_assoc($query);

if($row_m['mt_idx']) {
    if($row_m['mt_birth'] != "" && $row_m['mt_birth'] != "0000-00-00") {
        $birth = explode("-", $row_m['mt_birth']);
    } else {
        $birth = [];
    }
    $arr = array(
        "mt_idx" => $row_m['mt_idx'],
        "mt_image" => ($row_m['mt_image'] != "") ? $ct_img_url."/".$row_m['mt_image']."?cache=".strtotime($row_m['mt_udate']) : $ct_member_no_img_url,
        "mt_nickname" => $row_m['mt_nickname'],
        "mt_name" => $row_m['mt_name'],
        "mt_hp" => $row_m['mt_hp'],
        "mt_zip" => $row_m['mt_zip'],
        "mt_add1" => $row_m['mt_add1'],
        "mt_add2" => $row_m['mt_add2'],
        "mt_birth" => $birth,
    );
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 내정보 상세보기', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '내정보 상세보기', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>