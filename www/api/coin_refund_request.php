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
		where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
	";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    $query = "select * from coin_t where mt_idx = ".$row['mt_idx']." and ct_type = 1 and ct_status = 2 and ct_code = '".$decoded_array['ct_code']."'";
    $pay = $DB->fetch_assoc($query);

    $paymentKey = $pay['ct_pg_pg_tid'];
    $orderId = $pay['ct_code'];
    $amount = $pay['ct_price'];

    $secretKey = 'test_key';

    $url = 'https://api.tosspayments.com/v1/payments/' . $paymentKey. "/cancel";

    $credential = base64_encode($secretKey . ':');

    $curlHandle = curl_init($url);
    if($pay) {
        if ($pay['ct_pay_type'] == 4) {      //편의점 결제 취소요청
            if ($decoded_array['ct_account_holder'] == "") {
                echo result_data('false', '잘못된 접근입니다. ct_account_holder', '');
                exit;
            }
            if ($decoded_array['ct_bank_name'] == "") {
                echo result_data('false', '잘못된 접근입니다. ct_bank_name', '');
                exit;
            }
            if ($decoded_array['ct_bank_number'] == "") {
                echo result_data('false', '잘못된 접근입니다. ct_bank_number', '');
                exit;
            }
            $arr = array(
                "ct_status" => 4,
                "ct_refund_status" => 1,
                "ct_refund_wdate" => "now()",
                "ct_account_num" => $decoded_array['ct_account_holder'],
                "ct_bank_name" => $decoded_array['ct_bank_name'],
                "ct_bank_number" => $decoded_array['ct_bank_number'],
            );

            $DB->update_query("coin_t", $arr, " idx = " . $pay['idx']);

            $payload['data']['ct_idx'] = $pay['idx'];
            $payload['data']['ct_code'] = $pay['ct_code'];

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 코인 편의점 결제 환불요청', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '코인 편의점 결제 환불요청', $jwt);
            }
        } else {
            $secretKey = 'test_key';

            $url = 'https://api.tosspayments.com/v1/payments/' . $paymentKey . "/cancel";

            $credential = base64_encode($secretKey . ':');

            $curlHandle = curl_init($url);
            if ($pay['ct_pay_type'] == 1) {             //가상계좌 결제 취소요청
                if ($decoded_array['ct_account_holder'] == "") {
                    echo result_data('false', '잘못된 접근입니다. ct_account_holder', '');
                    exit;
                }
                if ($decoded_array['ct_bank_name'] == "") {
                    echo result_data('false', '잘못된 접근입니다. ct_bank_name', '');
                    exit;
                }
                if ($decoded_array['ct_bank_number'] == "") {
                    echo result_data('false', '잘못된 접근입니다. ct_bank_number', '');
                    exit;
                }
                curl_setopt_array($curlHandle, [
                    CURLOPT_POST => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Basic ' . $credential,
                        'Content-Type: application/json'
                    ],
                    CURLOPT_POSTFIELDS => "{\"cancelReason\":\"고객이 취소를 원함\",\"cancelAmount\":" . $amount . ",\"refundReceiveAccount\":{\"bank\":\"" . $decoded_array['ct_bank_name'] . "\",\"accountNumber\":\"" . $decoded_array['ct_bank_number'] . "\",\"holderName\":\"" . $decoded_array['ct_account_holder'] . "\"},\"refundableAmount\":" . $amount . "}",
                ]);
            } else {         //카드 결제 취소요청
                curl_setopt_array($curlHandle, [
                    CURLOPT_POST => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Basic ' . $credential,
                        'Content-Type: application/json'
                    ],
                    CURLOPT_POSTFIELDS => "{\"cancelReason\":\"고객이 취소를 원함\"}",
                ]);
            }
            $response = curl_exec($curlHandle);

            $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
            $isSuccess = $httpCode == 200;
            $responseJson = json_decode($response);

            if ($isSuccess) {
                $arr = array(
                    "ct_status" => 4,
                    "ct_refund_status" => 2,
                    "ct_refund_wdate" => "now()",
                    "ct_refund_edate" => "now()",
                );

                if ($pay['ct_pay_type'] == 1) {
                    $arr['ct_account_num'] = $decoded_array['ct_account_holder'];
                    $arr['ct_bank_name'] = $decoded_array['ct_bank_name'];
                    $arr['ct_bank_number'] = $decoded_array['ct_bank_number'];
                }

                $DB->update_query("coin_t", $arr, " idx = " . $pay['idx']);
                $DB->update_query("member_t", array("mt_coin" => $row['mt_coin'] - $pay['ct_amount']), " idx = " . $row['idx']);

                $payload['data']['ct_idx'] = $pay['idx'];
                $payload['data']['ct_code'] = $pay['ct_code'];

                if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                    echo result_data('true', '[debug] 코인 환불완료', $payload);
                } else {
                    $jwt = JWT::encode($payload, $secret_key);
                    echo result_data('true', '코인 환불완료', $jwt);
                }
            } else {
                echo result_data("false", $responseJson->message, $responseJson->code);
                exit;
            }
        }
    } else {
        echo result_data('false', '존재하지 않는 결제정보입니다.', $arr);
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>