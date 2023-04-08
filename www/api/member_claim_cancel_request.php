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
if($decoded_array['ot_code']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_code', '');
    exit;
}

$query = "
		select *, a1.idx as mt_idx from member_t a1
		where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
	";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $query = "select * from order_t where ot_code = '".$decoded_array['ot_code']."' and mt_idx = ".$row['mt_idx'];
        $row_ot  = $DB->fetch_assoc($query);
        if($row_ot) {
            $paymentKey = $row_ot['ot_pg_pg_tid'];
            $orderId = $row_ot['ot_code'];
            $amount = $row_ot['ot_price'];

            if ($row_ot['ot_pay_type'] == 4) {      //편의점결제 취소
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
                unset($arr_query);
                $arr_query = array(
                    "ct_status" => 7,
                    "ct_request_wdate" => "now()",
                    "ct_account_holder" => $decoded_array['ct_account_holder'],
                    "ct_bank_name" => $decoded_array['ct_bank_name'],
                    "ct_bank_number" => $decoded_array['ct_bank_number'],
                );
                $where_query = " ot_code = '" . $orderId . "'";
                $DB->update_query("cart_t", $arr_query, $where_query);

                unset($arr_query);
                $arr_query = array(
                    "ot_status" => 7,
                    "ot_cidate" => "now()",
                );
                $DB->update_query("order_t", $arr_query, $where_query);

                unset($arr_query);
                $arr_query = array(
                    "mt_idx" => $row['mt_idx'],
                    "ot_code" => $orderId,
                    "cct_cancel_wdate" => "now()",
                );
                $DB->insert_query('claim_cancel_t', $arr_query);

                $payload['data']['ot_code'] = $orderId;

                if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                    echo result_data('true', '[debug] 편의점 취소요청', $payload);
                } else {
                    $jwt = JWT::encode($payload, $secret_key);
                    echo result_data('true', '편의점 취소요청', $jwt);
                }
            } else {
                $secretKey = 'test_key';

                $url = 'https://api.tosspayments.com/v1/payments/' . $paymentKey . "/cancel";

                $credential = base64_encode($secretKey . ':');

                $curlHandle = curl_init($url);
                if ($row_ot['ot_pay_type'] == 1) {      //가상계좌
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
                } else {
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
                    unset($arr_query);
                    $arr_query = array(
                        "ct_status" => 8,
                        "ct_request_wdate" => "now()",
                        "ct_account_holder" => $decoded_array['ct_account_holder'],
                        "ct_bank_name" => $decoded_array['ct_bank_name'],
                        "ct_bank_number" => $decoded_array['ct_bank_number'],
                    );
                    $where_query = " ot_code = '" . $orderId . "'";
                    $DB->update_query("cart_t", $arr_query, $where_query);

                    unset($arr_query);
                    $arr_query = array(
                        "ot_status" => 8,
                        "ot_cedate" => "now()",
                    );
                    $DB->update_query("order_t", $arr_query, $where_query);

                    unset($arr_query);
                    $arr_query = array(
                        "mt_idx" => $row['mt_idx'],
                        "ot_code" => $orderId,
                        "cct_cancel_wdate" => "now()",
                    );
                    $DB->insert_query('claim_cancel_t', $arr_query);
                    $payload['data']['ot_code'] = $row_ot['ot_code'];

                    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                        echo result_data('true', '[debug] 가상계좌 취소', $payload);
                    } else {
                        $jwt = JWT::encode($payload, $secret_key);
                        echo result_data('true', '가상계좌 취소', $jwt);
                    }
                } else {
                    echo result_data("false", $responseJson->message, $responseJson->code);
                    exit;
                }
            }
        } else {
            return result_data("false", "주문코드가 존재하지 않습니다.", "");
        }
    } else {
        $query = "select * from order_t where ot_code = '".$decoded_array['ot_code']."' and nmt_id = '".$decoded_array['mt_id']."'";
        $row_ot  = $DB->fetch_assoc($query);
        if($row_ot) {
            $paymentKey = $row_ot['ot_pg_pg_tid'];
            $orderId = $row_ot['ot_code'];
            $amount = $row_ot['ot_price'];

            if ($row_ot['ot_pay_type'] == 4) {      //편의점 결제취소
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
                unset($arr_query);
                $arr_query = array(
                    "ct_status" => 7,
                    "ct_request_wdate" => "now()",
                    "ct_account_holder" => $decoded_array['ct_account_holder'],
                    "ct_bank_name" => $decoded_array['ct_bank_name'],
                    "ct_bank_number" => $decoded_array['ct_bank_number'],
                );
                $where_query = " ot_code = '" . $orderId . "'";
                $DB->update_query("cart_t", $arr_query, $where_query);

                unset($arr_query);
                $arr_query = array(
                    "ot_status" => 7,
                    "ot_cidate" => "now()",
                );
                $DB->update_query("order_t", $arr_query, $where_query);

                unset($arr_query);
                $arr_query = array(
                    "mt_idx" => $row['mt_idx'],
                    "ot_code" => $orderId,
                    "cct_cancel_wdate" => "now()",
                );
                $DB->insert_query('claim_cancel_t', $arr_query);

                $payload['data']['ot_code'] = $orderId;

                if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                    echo result_data('true', '[debug] 비회원 편의점 취소요청', $payload);
                } else {
                    $jwt = JWT::encode($payload, $secret_key);
                    echo result_data('true', '비회원 편의점 취소요청', $jwt);
                }
            } else {
                $secretKey = 'test_key';

                $url = 'https://api.tosspayments.com/v1/payments/' . $paymentKey . "/cancel";

                $credential = base64_encode($secretKey . ':');

                $curlHandle = curl_init($url);
                if ($row_ot['ot_pay_type'] == 1) {      //가상계좌
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
                } else {
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
                    unset($arr_query);
                    $arr_query = array(
                        "ct_status" => 8,
                        "ct_request_wdate" => "now()",
                        "ct_account_holder" => $decoded_array['ct_account_holder'],
                        "ct_bank_name" => $decoded_array['ct_bank_name'],
                        "ct_bank_number" => $decoded_array['ct_bank_number'],
                    );
                    $where_query = " ot_code = '" . $orderId . "'";
                    $DB->update_query("cart_t", $arr_query, $where_query);

                    unset($arr_query);
                    $arr_query = array(
                        "ot_status" => 8,
                        "ot_cedate" => "now()",
                    );
                    $DB->update_query("order_t", $arr_query, $where_query);

                    unset($arr_query);
                    $arr_query = array(
                        "mt_idx" => $row['mt_idx'],
                        "ot_code" => $orderId,
                        "cct_cancel_wdate" => "now()",
                    );
                    $DB->insert_query('claim_cancel_t', $arr_query);
                    $payload['data']['ot_code'] = $row_ot['ot_code'];

                    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                        echo result_data('true', '[debug] 비회원 가상계좌 취소', $payload);
                    } else {
                        $jwt = JWT::encode($payload, $secret_key);
                        echo result_data('true', '비회원 가상계좌 취소', $jwt);
                    }
                } else {
                    echo result_data("false", $responseJson->message, $responseJson->code);
                    exit;
                }
            }
        } else {
            return result_data("false", "주문코드가 존재하지 않습니다.", "");
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>