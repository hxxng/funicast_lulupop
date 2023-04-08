<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/http.class.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['ct_amount']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_amount', '');
    exit;
}
if($decoded_array['ct_price']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_price', '');
    exit;
}

function get_openssl_encrypt2($data) {
    $pass = DECODEKEY;
    $iv = IV;

    $endata = openssl_encrypt($data , "aes-256-cbc", $pass, true, $iv);
    $endata = base64_encode($endata);

    return $endata;
}

function get_openssl_decrypt2($endata) {
    $pass = DECODEKEY;
    $iv = IV;

    $data = base64_decode($endata);
    $dedata = openssl_decrypt($data , "aes-256-cbc", $pass, true, $iv);

    return $dedata;
}

$http = new http;

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level = 3
";
$row = $DB->fetch_query($query);

if($row['mt_idx'] < 1) {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
    exit;
}
$ct_code = get_ot_code();

$en_ct_code = get_openssl_encrypt2($ct_code);
$en_amt = get_openssl_encrypt2($decoded_array['ct_price']);

$query = [];
$query['msgId'] = "msgId";
$query['msgVer'] = "msgVer";
$query['transactionNo'] = $en_ct_code;
$query['custCode'] = "custCode";
$query['itemCode'] = "itemCode";
$query['dealDate'] = date("Ymd");
$query['dealTime'] = date("His");
$query['itemInfo'] = "코인 ".$decoded_array['ct_amount']."개";
$query['amt'] = $en_amt;
$query['smsFlag'] = "";
$query['expirationDate'] = "";

$getToken = $http->PostMethodData('https://tapi.cashgate.co.kr/numIssue', $query, $mReferer, '', $mCookie, true);

$result = json_decode($getToken, true);

if($result['resCode'] == 0000) {
    $approvalNo = get_openssl_decrypt2($result['approvalNo']);
    $barcode = get_openssl_decrypt2($result['barcode']);

    $arr = array(
        "ct_code" => $ct_code,
        "ct_amount" => $decoded_array['ct_amount'],
        "ct_price" => $decoded_array['ct_price'],
        "ct_pay_type" => 4,
        "ct_type" => 1,
        "ct_status" => 1,
        "mt_idx" => $row['mt_idx'],
        "ct_wdate" => "now()",
        "ct_approvalNo" => $approvalNo,
        "ct_barcode" => $barcode,
    );

    $DB->insert_query("coin_t", $arr);
    $idx = $DB->insert_id();
}

unset($arr);
$arr['ct_idx'] = $idx;
$arr['ct_code'] = $ct_code;
$arr['ct_amount'] = (int)$decoded_array['ct_amount'];
$arr['ct_price'] = (int)$decoded_array['ct_price'];
$arr['ct_approvalNo'] = $approvalNo;
$arr['ct_barcode'] = $barcode;
$payload['data'] = $arr;

if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
    echo result_data('true', '[debug] 코인 결제 요청', $payload);
} else {
    $jwt = JWT::encode($payload, $secret_key);
    echo result_data('true', '코인 결제 요청', $jwt);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>