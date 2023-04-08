<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/Point_class.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['ot_code']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_code', '');
    exit;
}

$arr = array();

$query_ot = "
    select * from order_t a1
    where a1.ot_code = '".$decoded_array['ot_code']."'
    and a1.ot_price = '".$decoded_array['ot_price']."'
";
$row_ot = $DB->fetch_query($query_ot);

if($row_ot['mt_idx']) {
    $query = "select *, idx as mt_idx from member_t where idx = ".$row_ot['mt_idx'];
} else {
    $query = "select *, idx as mt_idx from member_t where mt_id = '".$row_ot['nmt_id']."'";
}
$row = $DB->fetch_assoc($query);

if($row_ot['ot_pay_type'] == 1){
    $status = 1;
    $date = "";
} else {
    $status = 2;
    $date = "now()";
}
if($decoded_array['ot_duedate'] != "") {
    $ot_duedate = $decoded_array['ot_duedate'];
} else {
    $ot_duedate = null;
}
unset($arr_query);
$arr_query = array(
    "ot_status" => $status,
    "ot_pdate" => $date,
    "ot_pg_pg_tid" => $decoded_array['ot_paymentKey'],
    "ot_account_num" => $decoded_array['ot_accountNumber'],
    "ot_bank" => $decoded_array['ot_bank'],
    "ot_duedate" => $ot_duedate,
);

$where_query = " ot_code = '".$row_ot['ot_code']."'";

$DB->update_query('order_t', $arr_query, $where_query);
$DB->update_query('cart_t', array("ct_select" => 2, "ct_status" => $status, "ct_pdate" => $date), " ot_code = '".$row_ot['ot_code']."'");

if($row_ot['mt_idx'] != "") {
    if ($row_ot['ot_code'] != '') {
        if($status != 1) {
            //포인트 차감 쿼리
            $objPoint = new Point_class(array('db' => $DB, 'mt_idx' => $row_ot['mt_idx']));
            if ($row_ot['ot_use_point'] > 0) $objPoint->insert_point(array('point' => (-1) * $row_ot['ot_use_point'], 'ot_code' => $row_ot['ot_code'], 'plt_memo' => "주문번호 " . $row_ot['ot_code'] . " 결제",'plt_status'=>2));
        }

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = " . $row['mt_idx'] . " and cart_t.ot_code = '" . $row_ot['ot_code'] . "'";
        $sql_query = $query . " order by ot_wdate desc";
        $list_ot = $DB->select_query($sql_query);
        $list_fetch = $DB->fetch_assoc($sql_query);
        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
                $p_info = $DB->fetch_assoc($query_p);
                if ($p_info['pt_sale_chk'] == "Y") {
                    //할인 전 금액
                    $ct_opt_before_price = (int)$p_info['pt_selling_price'];
                } else {
                    $ct_opt_before_price = $row_ot['ct_opt_price'];
                }
                if ($p_info) {
                    $items[] = array(
                        'pt_idx' => $row_ot['pt_idx'],
                        'pt_title' => $row_ot['pt_title'],
                        'ct_opt_name' => $row_ot['ct_opt_name'],
                        'ct_opt_value' => $row_ot['ct_opt_value'],
                        'ct_opt_qty' => (int)$row_ot['ct_opt_qty'],
                        'ct_price' => (int)$row_ot['ct_price'],
                        'pt_image1' => ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".time() : $ct_no_img_url),
                    );
                }
                $product_price += $row_ot['ct_price'];

                if ($p_info['pt_stock_chk'] == "Y") {
                    //재고 빼기
                    $DB->update_query('product_t', array("pt_stock" => $p_info['pt_stock'] - $row_ot['ct_opt_qty']), " idx = " . $row_ot['pt_idx']);
                }

                $arr['info'] = array(
                    'ot_wdate' => str_replace('-', '.', substr($list_fetch['ot_wdate'], 0, 10)),
                    'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                    'ot_use_point' => ($list_fetch['ot_use_point'] == null ? 0 : (int)$list_fetch['ot_use_point']),
                    'ot_use_coupon_price' => ($list_fetch['ot_use_coupon_price'] == null ? 0 : (int)$list_fetch['ot_use_coupon_price']),
                    'ot_price' => (int)$list_fetch['ot_price'],                //주문금액
                    'ot_product_price' => (int)$product_price,    //상품금액
                    'ot_delivery_charge' => ($list_fetch['ot_delivery_charge'] == 0 ? "무료배송" : (int)$list_fetch['ot_delivery_charge']),
                    'ot_point' => (int)$list_fetch['ot_point']
                );
                $arr['orderer'] = array(
                    'ot_name' => $list_fetch['ot_name'],
                    'ot_hp' => $list_fetch['ot_hp'],
                    'ot_zip' => $list_fetch['ot_zip'],
                    'ot_add1' => $list_fetch['ot_add1'],
                    'ot_add2' => $list_fetch['ot_add2'],
                );
                $arr['recipient'] = array(
                    'ot_b_name' => $list_fetch['ot_b_name'],
                    'ot_b_hp' => $list_fetch['ot_b_hp'],
                    'ot_b_zip' => $list_fetch['ot_b_zip'],
                    'ot_b_addr1' => $list_fetch['ot_b_addr1'],
                    'ot_b_addr2' => $list_fetch['ot_b_addr2'],
                    'ot_requests' => $list_fetch['ot_requests'],
                );
                $arr['items'] = $items;
            }
        }

        //알림톡 토큰 생성
        $_apiURL	  =	'https://kakaoapi.aligo.in/akv10/token/create/30/s/';
        $_hostInfo	=	parse_url($_apiURL);
        $_port		  =	(strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
        $_variables	=	array(
            'apikey' => 'apikey',
            'userid' => 'userid'
        );

        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, $_port);
        curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $ret = curl_exec($oCurl);
        $error_msg = curl_error($oCurl);
        curl_close($oCurl);

        // JSON 문자열 배열 변환
        $retArr = json_decode($ret);

        //알림톡 발송
        $msg = '[주문완료 안내]
룰루팝스토어에서 구매하신 상품 주문완료되었습니다.
'.$list_fetch['ot_pt_name'].'
'.date("Y-m-d", time()).'
'.$list_fetch['ot_code'].'
'.number_format($list_fetch['ot_price']).'

배송은 영업일 기준 2~3일 정도 소요됩니다. 배송일은 배송지역에 따라 상이할 수 있습니다.';

        $_apiURL    =	'https://kakaoapi.aligo.in/akv10/alimtalk/send/';
        $_hostInfo  =	parse_url($_apiURL);
        $_port      =	(strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
        $_variables =	array(
            'apikey'      => 'apikey',
            'userid'      => 'userid',
            'token'       => $retArr->token,
            'senderkey'   => 'senderkey',
            'tpl_code'    => 'tpl_code',
            'sender'      => 'sender',
            'receiver_1'  => $list_fetch['ot_hp'],
            'recvname_1'  => $list_fetch['ot_name'],
            'subject_1'   => '룰루팝',
            'message_1'   => $msg,
//            'testMode'   => "Y",        //테스트모드
        );

        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, $_port);
        curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $ret = curl_exec($oCurl);
        $error_msg = curl_error($oCurl);
        curl_close($oCurl);

        // 리턴 JSON 문자열 확인
//            print_r($ret . PHP_EOL);

        // JSON 문자열 배열 변환
        $retArr = json_decode($ret);

        if ($list_fetch['ot_pay_type'] != 1) {
            //푸시발송
            if ($row['mt_pushing'] == "Y" || $row['mt_pushing2'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }
            $token_list = array($row['mt_fcm']);
            $message = "상품 구매를 완료하였습니다.";
            $title = "룰루팝";

            $message_status = send_notification2($token_list, $title, $message, "MyPage_Payment_Item_Detail_Page", $row_ot['ot_code'], $chk);

            if ($message_status) {
                unset($arr_query);
                $plt_set = array(
                    'plt_title' => $title,
                    'plt_content' => $message,
                    'plt_table' => "order_t",
                    'plt_type' => 2,
                    'plt_index' => $row_ot['ot_code'],
                    'mt_idx' => 1,
                    'op_idx' => $row['mt_idx'],
                    'plt_wdate' => 'now()'
                );
                $DB->insert_query("pushnotification_log_t", $plt_set);
            }
        }

        $payload['data'] = $arr;
        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 결제 완료되었습니다.', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '결제 완료되었습니다.', $jwt);
        }
    } else {
        echo result_data('false', $decoded_array['ot_code'] . ' | ' . $decoded_array['ot_price'] . ' 존재하지 않는 주문정보입니다.', $arr);
    }
} else {
    if($row_ot['ot_code']!='') {
        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where cart_t.ot_code = '".$row_ot['ot_code']."'";
        $sql_query = $query." order by ot_wdate desc";
        $list_ot = $DB->select_query($sql_query);
        $list_fetch = $DB->fetch_assoc($sql_query);
        if($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
                $p_info = $DB->fetch_assoc($query_p);
                if($p_info['pt_sale_chk'] == "Y") {
                    //할인 전 금액
                    $ct_opt_before_price = (int)$p_info['pt_selling_price'];
                } else {
                    $ct_opt_before_price = $row_ot['ct_opt_price'];
                }
                if ($p_info) {
                    $items[] = array(
                        'pt_idx' => $row_ot['pt_idx'],
                        'pt_title' => $row_ot['pt_title'],
                        'ct_opt_name' => $row_ot['ct_opt_name'],
                        'ct_opt_value' => $row_ot['ct_opt_value'],
                        'ct_opt_qty' => (int)$row_ot['ct_opt_qty'],
                        'ct_price' => (int)$row_ot['ct_price'],
                        'pt_image1' => ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".time() : $ct_no_img_url),
                    );
                }
                $product_price += $row_ot['ct_price'];

                //재고 빼기
                if($p_info['pt_stock_chk'] == "Y") {
                    if($p_info['pt_option_chk'] == 2) {
                        $query = "select * from product_option_t where pt_idx = ".$p_info['idx'];
                        $option = $DB->select_query($query);
                        if($option) {
                            foreach ($option as $row_o) {
                                if($row_o['pot_value'] == $row_ot['ct_opt_value']) {
                                    $DB->update_query('product_option_t', array("pt_stock" => $row_o['pot_jaego'] - $row_ot['ct_opt_qty']), " idx = ".$row_o['idx']);
                                }
                            }
                        }
                    } else{
                        $DB->update_query('product_t', array("pt_stock" => $p_info['pt_stock'] - $row_ot['ct_opt_qty']), " idx = ".$row_ot['pt_idx']);
                    }
                }

                $arr['info'] = array(
                    'ot_wdate' => str_replace('-', '.', substr($list_fetch['ot_wdate'], 0, 10)),
                    'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                    'ot_price' => (int)$list_fetch['ot_price'],                //주문금액
                    'ot_product_price' => (int)$product_price,    //상품금액
                    'ot_delivery_charge' => ($list_fetch['ot_delivery_charge'] == 0 ? "무료배송" : (int)$list_fetch['ot_delivery_charge']),
                );
                $arr['orderer'] = array(
                    'ot_name' => $list_fetch['ot_name'],
                    'ot_hp' => $list_fetch['ot_hp'],
                    'ot_zip' => $list_fetch['ot_zip'],
                    'ot_add1' => $list_fetch['ot_add1'],
                    'ot_add2' => $list_fetch['ot_add2'],
                );
                $arr['recipient'] = array(
                    'ot_b_name' => $list_fetch['ot_b_name'],
                    'ot_b_hp' => $list_fetch['ot_b_hp'],
                    'ot_b_zip' => $list_fetch['ot_b_zip'],
                    'ot_b_addr1' => $list_fetch['ot_b_addr1'],
                    'ot_b_addr2' => $list_fetch['ot_b_addr2'],
                    'ot_requests' => $list_fetch['ot_requests'],
                );
                $arr['items'] = $items;
            }
        }

        //알림톡 토큰 생성
        $_apiURL	  =	'https://kakaoapi.aligo.in/akv10/token/create/30/s/';
        $_hostInfo	=	parse_url($_apiURL);
        $_port		  =	(strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
        $_variables	=	array(
            'apikey' => 'apikey',
            'userid' => 'userid'
        );

        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, $_port);
        curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $ret = curl_exec($oCurl);
        $error_msg = curl_error($oCurl);
        curl_close($oCurl);

        // JSON 문자열 배열 변환
        $retArr = json_decode($ret);

        //알림톡 발송
        $msg = '[주문완료 안내]
룰루팝스토어에서 구매하신 상품 주문완료되었습니다.
'.$list_fetch['ot_pt_name'].'
'.date("Y-m-d", time()).'
'.$list_fetch['ot_code'].'
'.number_format($list_fetch['ot_price']).'

배송은 영업일 기준 2~3일 정도 소요됩니다. 배송일은 배송지역에 따라 상이할 수 있습니다.';

        $_apiURL    =	'https://kakaoapi.aligo.in/akv10/alimtalk/send/';
        $_hostInfo  =	parse_url($_apiURL);
        $_port      =	(strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
        $_variables =	array(
            'apikey'      => 'apikey',
            'userid'      => 'userid',
            'token'       => $retArr->token,
            'senderkey'   => 'senderkey',
            'tpl_code'    => 'tpl_code',
            'sender'      => 'sender',
            'receiver_1'  => $list_fetch['ot_hp'],
            'recvname_1'  => $list_fetch['ot_name'],
            'subject_1'   => '룰루팝',
            'message_1'   => $msg,
//            'testMode'   => "Y",        //테스트모드
        );

        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, $_port);
        curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $ret = curl_exec($oCurl);
        $error_msg = curl_error($oCurl);
        curl_close($oCurl);

        // 리턴 JSON 문자열 확인
//            print_r($ret . PHP_EOL);

        // JSON 문자열 배열 변환
        $retArr = json_decode($ret);

        if ($list_fetch['ot_pay_type'] != 1) {
            //푸시발송
            if ($row['mt_pushing'] == "Y" || $row['mt_pushing2'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }
            $token_list = array($row['mt_fcm']);
            $message = "상품 구매를 완료하였습니다.";
            $title = "룰루팝";

            $message_status = send_notification2($token_list, $title, $message, "MyPage_Payment_Item_Detail_Page", $row_ot['ot_code'], $chk);

            if ($message_status) {
                unset($arr_query);
                $plt_set = array(
                    'plt_title' => $title,
                    'plt_content' => $message,
                    'plt_table' => "order_t",
                    'plt_type' => 2,
                    'plt_index' => $row_ot['ot_code'],
                    'mt_idx' => 1,
                    'op_idx' => $row['mt_idx'],
                    'plt_wdate' => 'now()'
                );
                $DB->insert_query("pushnotification_log_t", $plt_set);
            }
        }

        $payload['data'] = $arr;
        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 결제 완료되었습니다.', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 결제 완료되었습니다.', $jwt);
        }
    } else {
        echo result_data('false', $decoded_array['ot_code'].' | '.$decoded_array['ot_price'].' 존재하지 않는 주문정보입니다.', $arr);
    }
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>