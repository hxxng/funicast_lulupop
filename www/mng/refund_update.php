<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

$_act = $_GET['act'];
$exchange_confirm = "";

if($_POST['act'] == "update") {
    if($_POST['ct_status'] == 81) {
        $ct_request_ydate = "now()";
        $exchange_confirm = "Y";
    }
    else{
        $ct_request_ydate = "null";
    }

    unset($arr_query);
    $arr_query = array(
        "ct_status" => $_POST['ct_status'],
        "ct_delivery_com" => $_POST['ct_delivery_com'],
        "ct_delivery_number" => $_POST['ct_delivery_number'],
        "ct_request_ydate" => $ct_request_ydate
    );
    $DB->update_query("cart_t", $arr_query, "ot_pcode = '".$_POST['ot_pcode']."'");

    if($exchange_confirm == "Y") {
        unset($arr_query2);
        $arr_query2 = array(
            "ot_status" => 7,
        );
        $query = "select * from cart_t where ot_pcode = '".$_POST['ot_pcode']."'";
        $list = $DB->fetch_assoc($query);
        $DB->update_query("order_t", $arr_query2, "ot_code = '".$list['ot_code']."'");
    }

    p_alert("수정되었습니다.");
} else if($_POST['act'] == "save_delivery") {
    $count = $DB->count_query("select * from cart_t where ot_code = '".$_POST['ot_code']."' and ct_status = 90");
    if($count > 0) {
        $DB->update_query("cart_t", array("ct_collect_com" => $_POST['ct_collect_com'], "ct_collect_number" => $_POST['ct_collect_number'], "ct_request_status" => 2), "ot_code = '".$_POST['ot_code']."'");
        echo json_encode(array("result" => "_ok", "msg" => "회수요청이 완료되었습니다."));
    } else {
        echo json_encode(array("result" => "false", "msg" => "해당하는 반품상품이 없습니다."));
    }
} else if($_POST['act'] == "refund") {
    $query = "select * from cart_t where ot_pcode = '".$_POST['ot_pcode']."' and ct_status = 90";
    $row = $DB->fetch_assoc($query);
    if($row['idx']) {

        $query = "select * from order_t where ot_code = '".$_POST['ot_code']."'";
        $row_ot  = $DB->fetch_assoc($query);
        if($row_ot) {
            $paymentKey = $row_ot['ot_pg_pg_tid'];
            $orderId = $row_ot['ot_code'];
            $amount = $row['ct_price'];

            $secretKey = 'test_key';

            $url = 'https://api.tosspayments.com/v1/payments/' . $paymentKey. "/cancel";

            $credential = base64_encode($secretKey . ':');

            $curlHandle = curl_init($url);
            if($row_ot['ot_pay_type'] == 1) {
                curl_setopt_array($curlHandle, [
                    CURLOPT_POST => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Basic ' . $credential,
                        'Content-Type: application/json'
                    ],
                    CURLOPT_POSTFIELDS => "{\"cancelReason\":\"고객이 취소를 원함\",\"cancelAmount\":".$amount.",\"refundReceiveAccount\":{\"bank\":\"".$row['ct_bank_name']."\",\"accountNumber\":\"".$row['ct_bank_number']."\",\"holderName\":\"".$row['ct_account_holder']."\"},\"refundableAmount\":".$row_ot['ot_price']."}",
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

            if($isSuccess) {
                $DB->update_query("cart_t", array("ct_status" => 91, "ct_request_ydate" => "now()"), "ot_pcode = '".$row['ot_pcode']."' and ct_status = 90");
                $DB->update_query("order_t", array("ot_cancel_rem_mny" => $responseJson->balanceAmount), "ot_code = '".$row['ot_code']."'");

                unset($arr_query);
                $arr_query = array(
                    "ot_status" => 91,
                    "ot_return_complete_price" => $amount,
                    "ot_return_ydate" => "now()",
                );
                $DB->update_query('claim_return_t', $arr_query, "ot_pcode = '".$row['ot_pcode']."'");
                $payload['data']['ot_code'] = $row_ot['ot_code'];
            } else {
                echo json_encode(array("result" => "false", "msg" => $responseJson->message));
                exit;
            }
        }
        echo json_encode(array("result" => "_ok", "msg" => "반품이 완료되었습니다."));
    } else {
        echo json_encode(array("result" => "false", "msg" => "해당하는 반품주문이 없습니다."));
    }
}
?>