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
if($decoded_array['ot_hp']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_hp', '');
    exit;
}

$arr = array();
$query = "
select *, a1.idx as mt_idx from member_t a1
where a1.mt_level = 5 and a1.mt_id = '".$decoded_array['mt_id']."'
";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    /****************** 인증정보 시작 ******************/
    $sms_url = "https://apis.aligo.in/send/"; // 전송요청 URL
    $sms['user_id'] = "user_id"; // SMS 아이디
    $sms['key'] = "key";//인증키
    /****************** 인증정보 끝 ********************/

    $num = mt_rand(10000, 99999);

    /****************** 전송정보 설정시작 ****************/
    $_POST['msg'] = '[룰루팝] 인증번호 ['.$num."] 를 입력해주세요."; // 메세지 내용 : euc-kr로 치환이 가능한 문자열만 사용하실 수 있습니다. (이모지 사용불가능)
    $_POST['receiver'] = $decoded_array['ot_hp']; // 수신번호
    $_POST['destination'] = $decoded_array['ot_hp'].'|'.$row['mt_id']; // 수신인 %고객명% 치환
    $_POST['sender'] ="sender"; // 발신번호
    $_POST['testmode_yn'] = ''; // Y 인경우 실제문자 전송X , 자동취소(환불) 처리
    $_POST['msg_type'] = 'SMS'; //  SMS, LMS, MMS등 메세지 타입을 지정
    /****************** 전송정보 설정끝 ***************/

    $sms['msg'] = stripslashes($_POST['msg']);
    $sms['receiver'] = $_POST['receiver'];
    $sms['destination'] = $_POST['destination'];
    $sms['sender'] = $_POST['sender'];
    $sms['rdate'] = $_POST['rdate'];
    $sms['rtime'] = $_POST['rtime'];
    $sms['testmode_yn'] = empty($_POST['testmode_yn']) ? '' : $_POST['testmode_yn'];
    $sms['title'] = $_POST['subject'];
    $sms['msg_type'] = $_POST['msg_type'];
    /*****/
    $host_info = explode("/", $sms_url);
    $port = $host_info[0] == 'https:' ? 443 : 80;

    $oCurl = curl_init();
    curl_setopt($oCurl, CURLOPT_PORT, $port);
    curl_setopt($oCurl, CURLOPT_URL, $sms_url);
    curl_setopt($oCurl, CURLOPT_POST, 1);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sms);
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    $ret = curl_exec($oCurl);
    curl_close($oCurl);

    $retArr = json_decode($ret); // 결과배열

    if($retArr->result_code == 1) {
        $arr = array(
            "mt_idx" => $row['mt_idx'],
            "smt_receiver" => $_POST['receiver'],
            "smt_destination" => $_POST['destination'],
            "smt_sender" => $_POST['sender'],
            "smt_msg_type" => $_POST['msg_type'],
            "smt_num" => $num,
            "smt_wdate" => "now()",
        );
        $DB->insert_query("send_msg_t",$arr);

        unset($arr);
        $arr['num'] = $num;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 휴대폰 인증번호 발송', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 휴대폰 인증번호 발송', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 비회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>