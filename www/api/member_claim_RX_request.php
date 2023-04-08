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
if($decoded_array['ot_pcode']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_pcode', '');
    exit;
}
if($decoded_array['act']=="") {
    echo result_data('false', '잘못된 접근입니다. act', '');
    exit;
}

$query = "
		select *, a1.idx as mt_idx from member_t a1
		where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
	";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    $ot_pcode = explode(",", $decoded_array['ot_pcode']);
    if($row['mt_level'] == 3) {
        $return_price = 0;
        if ($decoded_array['act'] == "R") {    //반품
            for ($i = 0; $i < count($ot_pcode); $i++) {
                if ($ot_pcode[$i]) {
                    unset($arr_query);
                    $arr_query = array(
                        "ct_status" => 90,
                        "ct_request_type" => $decoded_array['ct_request_type'],
                        "ct_request_reason" => $decoded_array['ct_request_reason'],
                        "ct_request_wdate" => "now()",
                        "ct_request_delivery" => $decoded_array['ct_request_delivery'],
                        "ct_collect_name" => $decoded_array['ct_collect_name'],
                        "ct_collect_hp" => $decoded_array['ct_collect_hp'],
                        "ct_collect_zip" => $decoded_array['ct_collect_zip'],
                        "ct_collect_addr1" => $decoded_array['ct_collect_addr1'],
                        "ct_collect_addr2" => $decoded_array['ct_collect_addr2'],
                        "ct_account_holder" => $decoded_array['ct_account_holder'],
                        "ct_bank_name" => $decoded_array['ct_bank_name'],
                        "ct_bank_number" => $decoded_array['ct_bank_number'],
                        "ct_request_status" => 1,
                    );
                    $where_query = " ot_pcode = '" . $ot_pcode[$i] . "'";
                    if ($DB->update_query("cart_t", $arr_query, $where_query)) {
                        $cart_t = $DB->fetch_query("select * from cart_t where ot_pcode = '" . $ot_pcode[$i] . "'");
                        $ot_code = $cart_t['ot_code'];
                        $return_price += $cart_t['ct_price'];
                    }

                    unset($arr_query);
                    $arr_query = array(
                        "mt_idx" => $row['mt_idx'],
                        "ot_code" => $ot_code,
                        "ot_pcode" => $ot_pcode[$i],
                        "ot_return_wdate" => "now()",
                        "ot_status" => 90,
                        "ot_return_reason" => $decoded_array['ct_request_reason'],
                        "ot_return_zip" => $decoded_array['ct_collect_zip'],
                        "ot_return_addr1" => $decoded_array['ct_collect_addr1'],
                        "ot_return_addr2" => $decoded_array['ct_collect_addr2'],
                        "ot_return_name" => $decoded_array['ct_collect_name'],
                        "ot_return_hp" => $decoded_array['ct_collect_hp'],
                        "ot_pt_price" => $return_price,
                    );
                    $DB->insert_query('claim_return_t', $arr_query);
                }
            }
            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 반품요청', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '반품요청', $jwt);
            }
        } else if ($decoded_array['act'] == "X") {    //교환
            for ($i = 0; $i < count($ot_pcode); $i++) {
                if ($ot_pcode[$i]) {
                    unset($arr_query);
                    $arr_query = array(
                        "ct_status" => 80,
                        "ct_request_reason" => $decoded_array['ct_request_reason'],
                        "ct_request_wdate" => "now()",
                        "ct_request_zip" => $decoded_array['ct_request_zip'],
                        "ct_request_addr1" => $decoded_array['ct_request_addr1'],
                        "ct_request_addr2" => $decoded_array['ct_request_addr2'],
                        "ct_request_name" => $decoded_array['ct_request_name'],
                        "ct_request_hp" => $decoded_array['ct_request_hp'],
                        "ct_request_delivery" => $decoded_array['ct_request_delivery'],
                        "ct_collect_name" => $decoded_array['ct_collect_name'],
                        "ct_collect_hp" => $decoded_array['ct_collect_hp'],
                        "ct_collect_zip" => $decoded_array['ct_collect_zip'],
                        "ct_collect_addr1" => $decoded_array['ct_collect_addr1'],
                        "ct_collect_addr2" => $decoded_array['ct_collect_addr2'],
                        "ct_account_holder" => $decoded_array['ct_account_holder'],
                        "ct_bank_name" => $decoded_array['ct_bank_name'],
                        "ct_bank_number" => $decoded_array['ct_bank_number'],
                        "ct_request_status" => 1,
                    );
                    $where_query = " ot_pcode = '" . $ot_pcode[$i] . "'";
                    if ($DB->update_query("cart_t", $arr_query, $where_query)) {
                        $cart_t = $DB->fetch_query("select * from cart_t where  ot_pcode = '" . $ot_pcode[$i] . "'");
                        $ot_code = $cart_t['ot_code'];
                    }

                    unset($arr_query);
                    $arr_query = array(
                        "mt_idx" => $row['mt_idx'],
                        "ot_code" => $ot_code,
                        "ot_pcode" => $ot_pcode[$i],
                        "et_exchange_wdate" => "now()",
                        "ot_status" => 80,
                        "et_exchange_reason" => $decoded_array['ct_request_reason'],
                        "et_zip" => $decoded_array['ct_request_zip'],
                        "et_addr1" => $decoded_array['ct_request_addr1'],
                        "et_add2" => $decoded_array['ct_request_addr2'],
                        "et_name" => $decoded_array['ct_request_name'],
                        "et_hp" => $decoded_array['ct_request_hp'],
                    );
                    $DB->insert_query('claim_exchange_t', $arr_query);
                }
            }
            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 교환요청', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '교환요청', $jwt);
            }
        }
    } else {
        $return_price = 0;
        if ($decoded_array['act'] == "R") {    //반품
            for ($i = 0; $i < count($ot_pcode); $i++) {
                if ($ot_pcode[$i]) {
                    unset($arr_query);
                    $arr_query = array(
                        "ct_status" => 90,
                        "ct_request_type" => $decoded_array['ct_request_type'],
                        "ct_request_reason" => $decoded_array['ct_request_reason'],
                        "ct_request_wdate" => "now()",
                        "ct_request_delivery" => $decoded_array['ct_request_delivery'],
                        "ct_collect_name" => $decoded_array['ct_collect_name'],
                        "ct_collect_hp" => $decoded_array['ct_collect_hp'],
                        "ct_collect_zip" => $decoded_array['ct_collect_zip'],
                        "ct_collect_addr1" => $decoded_array['ct_collect_addr1'],
                        "ct_collect_addr2" => $decoded_array['ct_collect_addr2'],
//                    "ct_return_delivery_price" => $decoded_array['ct_return_delivery_price'],
                        "ct_account_holder" => $decoded_array['ct_account_holder'],
                        "ct_bank_name" => $decoded_array['ct_bank_name'],
                        "ct_bank_number" => $decoded_array['ct_bank_number'],
                        "ct_request_status" => 1,
                    );
                    $where_query = " ot_pcode = '" . $ot_pcode[$i] . "'";
                    if ($DB->update_query("cart_t", $arr_query, $where_query)) {
                        $cart_t = $DB->fetch_query("select * from cart_t where ot_pcode = '" . $ot_pcode[$i] . "'");
                        $ot_code = $cart_t['ot_code'];
                        $return_price += $cart_t['ct_price'];
                    }

                    unset($arr_query);
                    $arr_query = array(
                        "mt_idx" => $row['mt_idx'],
                        "ot_code" => $ot_code,
                        "ot_pcode" => $ot_pcode[$i],
                        "ot_return_wdate" => "now()",
                        "ot_status" => 90,
                        "ot_return_reason" => $decoded_array['ct_request_reason'],
                        "ot_return_zip" => $decoded_array['ct_collect_zip'],
                        "ot_return_addr1" => $decoded_array['ct_collect_addr1'],
                        "ot_return_addr2" => $decoded_array['ct_collect_addr2'],
                        "ot_return_name" => $decoded_array['ct_collect_name'],
                        "ot_return_hp" => $decoded_array['ct_collect_hp'],
                        "ot_pt_price" => $return_price,
                    );
                    $DB->insert_query('claim_return_t', $arr_query);
                }
            }
            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 반품요청', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 반품요청', $jwt);
            }
        } else if ($decoded_array['act'] == "X") {    //교환
            for ($i = 0; $i < count($ot_pcode); $i++) {
                if ($ot_pcode[$i]) {
                    unset($arr_query);
                    $arr_query = array(
                        "ct_status" => 80,
                        "ct_request_reason" => $decoded_array['ct_request_reason'],
                        "ct_request_wdate" => "now()",
                        "ct_request_zip" => $decoded_array['ct_request_zip'],
                        "ct_request_addr1" => $decoded_array['ct_request_addr1'],
                        "ct_request_addr2" => $decoded_array['ct_request_addr2'],
                        "ct_request_name" => $decoded_array['ct_request_name'],
                        "ct_request_hp" => $decoded_array['ct_request_hp'],
                        "ct_request_delivery" => $decoded_array['ct_request_delivery'],
                        "ct_collect_name" => $decoded_array['ct_collect_name'],
                        "ct_collect_hp" => $decoded_array['ct_collect_hp'],
                        "ct_collect_zip" => $decoded_array['ct_collect_zip'],
                        "ct_collect_addr1" => $decoded_array['ct_collect_addr1'],
                        "ct_collect_addr2" => $decoded_array['ct_collect_addr2'],
                        "ct_account_holder" => $decoded_array['ct_account_holder'],
                        "ct_bank_name" => $decoded_array['ct_bank_name'],
                        "ct_bank_number" => $decoded_array['ct_bank_number'],
                        "ct_request_status" => 1,
                    );
                    $where_query = " ot_pcode = '" . $ot_pcode[$i] . "'";
                    if ($DB->update_query("cart_t", $arr_query, $where_query)) {
                        $cart_t = $DB->fetch_query("select * from cart_t where  ot_pcode = '" . $ot_pcode[$i] . "'");
                        $ot_code = $cart_t['ot_code'];
                    }

                    unset($arr_query);
                    $arr_query = array(
                        "mt_idx" => $row['mt_idx'],
                        "ot_code" => $ot_code,
                        "ot_pcode" => $ot_pcode[$i],
                        "et_exchange_wdate" => "now()",
                        "ot_status" => 80,
                        "et_exchange_reason" => $decoded_array['ct_request_reason'],
                        "et_zip" => $decoded_array['ct_request_zip'],
                        "et_addr1" => $decoded_array['ct_request_addr1'],
                        "et_add2" => $decoded_array['ct_request_addr2'],
                        "et_name" => $decoded_array['ct_request_name'],
                        "et_hp" => $decoded_array['ct_request_hp'],
                    );
                    $DB->insert_query('claim_exchange_t', $arr_query);
                }
            }
            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 교환요청', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 교환요청', $jwt);
            }
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>