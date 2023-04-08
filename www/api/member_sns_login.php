<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/Point_class.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['mt_login_type']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_login_type', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (1,3) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {    //로그인
    if ($row['mt_level'] == 1) {
        echo result_data("false", "탈퇴한 사용자입니다.","");
        exit;
    }
    unset($arr_query);
    $arr_query = array(
        "mt_fcm" => $decoded_array['mt_fcm'],
        "mt_ldate" => "now()",
        "mt_status" => "Y",
    );

    $where_query = "idx = '".$row['mt_idx']."'";

    $DB->update_query('member_t', $arr_query, $where_query);

    $arr = $row;
    $arr['mt_level'] = (int)$row['mt_level'];
    $arr['mt_image'] = ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url);

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] sns 로그인 정보', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', 'sns 로그인 정보', $jwt);
    }
} else {       //회원가입
    unset($arr_query);
    $arr_query = array(
        "mt_login_type" => $decoded_array['mt_login_type'],
        "mt_id" => $decoded_array['mt_id'],
        "mt_level" => 3,
        "mt_app_token" => $decoded_array['mt_app_token'],
        "mt_status" => "Y",
        "mt_wdate" => "now()",
        "mt_ldate" => "now()",
        "mt_grade" => 1,
        "mt_fcm" => $decoded_array['mt_fcm'],
    );

    $DB->insert_query('member_t', $arr_query);
    $_last_mt_idx = $DB->insert_id();

    //회원가입 시 1000 포인트 적립
    $DB->update_query("member_t", array("mt_point" => 1000), " idx = ".$_last_mt_idx);
    $objPoint = new Point_class(array('db'=>$DB, 'mt_idx'=>$_last_mt_idx));
    $objPoint->insert_point(array('point'=>1000, 'mt_idx'=>$_last_mt_idx, 'plt_status'=>3, 'plt_memo'=>"회원가입 축하 포인트"));

    $query = "select *, idx as mt_idx from member_t where idx = ".$_last_mt_idx;
    $row = $DB->fetch_assoc($query);

    $arr = $row;
    $arr['mt_level'] = (int)$row['mt_level'];
    $arr['mt_image'] = ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url);
    $payload['data'] = $arr;

    if($row['mt_pushing'] == "Y" || $row['mt_pushing3'] == "Y") {
        $chk = "Y";
    } else {
        $chk = "N";
    }
    $token_list = array($row['mt_fcm']);
    $message = "마이페이지에서 축하 포인트 선물을 확인해 보세요!";
    $title = "회원가입을 축하합니다.";

    $message_status = send_notification2($token_list, $title, $message, "MyPage_Point_Page", null, $chk);

    if ($message_status) {
        unset($arr_query);
        $plt_set = array(
            'plt_title'=>$title,
            'plt_content'=>$message,
            'plt_table'=>"member_t",
            'plt_type'=> 3,
            'plt_index'=>$row['mt_id'],
            'mt_idx'=>1,
            'op_idx'=>$row['mt_idx'],
            'plt_wdate'=>'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);
    }

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 회원가입', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '회원가입', $jwt);
    }
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>