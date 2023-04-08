<?
header('Content-Type: application/json; charset=UTF-8');

include $_SERVER['DOCUMENT_ROOT']."/db_inc.php";

// 컨텐츠 타입이 JSON 인지 확인한다
if(!in_array('application/json',explode(';',$_SERVER['CONTENT_TYPE']))){
    echo json_encode(array('result_code' => '400'));
    exit;
}

$postData = file_get_contents('php://input');
$json = json_decode($postData);

function get_openssl_encrypt2($data) {
    $pass = 'pass';
    $iv = 'iv';

    $endata = openssl_encrypt($data , "aes-256-cbc", $pass, true, $iv);
    $endata = base64_encode($endata);

    return $endata;
}

function get_openssl_decrypt2($endata) {
    $pass = 'pass';
    $iv = 'iv';

    $data = base64_decode($endata);
    $dedata = openssl_decrypt($data , "aes-256-cbc", $pass, true, $iv);

    return $dedata;
}
//소멸 포인트
function get_expire_point($mt_idx)
{
    global $DB;

    $query = "select sum(plt_price - plt_use_point) as sum_point from point_log_t where mt_idx = ".$mt_idx." and plt_expired = '0' and plt_expire_date <> '9999-12-31' and plt_expire_date < '".date('Y-m-d')."' ";
    $row = $DB->fetch_query($query);
    return $row['sum_point'];
}

// 사용포인트 입력
function insert_use_point($mt_idx, $point, $plt_id='')
{
    global $DB;
    $sql_order = " order by plt_expire_date asc, idx asc ";

    $point1 = abs($point);
    $sql = " select idx, plt_price, plt_use_point
            from point_log_t
            where mt_idx = ".$mt_idx."
            and idx <> '".$plt_id."'
            and plt_expired = '0'
            and plt_price > plt_use_point 
            ".$sql_order;
    $result = $DB->select_query($sql);
    foreach($result as $row){
        $point2 = $row['plt_price'];
        $point3 = $row['plt_use_point'];

        if(($point2 - $point3) > $point1) {
            $sql = " update point_log_t
                    set plt_use_point = plt_use_point + ".$point1."
                    where idx = ".$row['idx'];
            $DB->db_query($sql);
            break;
        } else {
            $point4 = $point2 - $point3;
            $sql = " update point_log_t
                    set plt_use_point = plt_use_point + '$point4', plt_expired = '100'
                    where idx = ".$row['idx'];
            $DB->db_query($sql);
            $point1 -= $point4;
        }
    }
}
function get_point_sum($mt_idx)
{
    global $DB;
    $expire_point = get_expire_point($mt_idx);  //소멸 포인트
    if($expire_point > 0) {
        $query = "select * from member_t where idx = ".$mt_idx;
        $member = $DB->fetch_query($query);
        $content = '포인트 소멸';
        $point = $expire_point * (-1);
        $plt_mt_point = $member['mt_point'] + $point;
        $plt_expire_date = date('Y-m-d');
        $plt_expired = 1;

        $sql = " insert into point_log_t
                set mt_idx = ".$mt_idx.",
                plt_type = 'M',
                plt_wdate = '".date('Y-m-d H:i:s')."',
                plt_memo = '".addslashes($content)."',
                plt_price = '$point',
                plt_use_point = '0',
                plt_mt_point = '$plt_mt_point',
                plt_expired = '$plt_expired',
                plt_expire_date = '$plt_expire_date'
                 ";
        $DB->db_query($sql);

        // 포인트를 사용한 경우 포인트 내역에 사용금액 기록
        if($point < 0) {
            insert_use_point($mt_idx, $point);
        }
    }

    // 유효기간이 있을 때 기간이 지난 포인트 expired 체크
    $sql = " update point_log_t
            set plt_expired = '1'
            where mt_idx = ".$mt_idx."
            and plt_expired <> '1'
            and plt_expire_date <> '9999-12-31'
            and plt_expire_date < '".date('Y-m-d')."' ";
    $DB->db_query($sql);

    // 포인트합
    $sql = " select sum(plt_price) as sum_po_point
            from point_log_t
            where mt_idx = '$mt_idx' ";
    $row = $DB->fetch_query($sql);

    return $row['sum_po_point'];
}

function insert_point($arr)
{
    global $DB;
    if($arr['point']==0){ return false; }

    // 회원포인트
    $mt_point = get_point_sum($arr['mt_idx']);

    //포인트 생성
    $plt_expire_date = date("Y-m-d",strtotime("+1 year"));
    $plt_expired = 0;
    if($arr['point'] < 0) {
        $plt_expired = 1;
        $plt_expire_date = date('Y-m-d');
    }
    $plt_mt_point = $mt_point + $arr['point'];

    $set = array();
    $set['mt_idx'] = $arr['mt_idx'];
    if($arr['pt_idx']) $set['pt_idx'] = $arr['pt_idx'];
    if($arr['ot_code']) $set['ot_code'] = $arr['ot_code'];
    if($arr['ot_pcode']) $set['ot_pcode'] = $arr['ot_pcode'];

    $set['plt_type'] = ($arr['point'] < 0 ) ? 'M' : 'P';//$arr['plt_type'];   //P적립, M 차감
    $set['plt_price'] = $arr['point'];
    $set['plt_use_point'] = 0;
    $set['plt_mt_point'] = $plt_mt_point;
    $set['plt_expired'] = $plt_expired;
    $set['plt_expire_date'] = $plt_expire_date;
    $set['plt_memo'] = $arr['plt_memo'];
    $set['plt_status'] = $arr['plt_status'];
    $set['plt_wdate'] = date('Y-m-d H:i:s');
    $DB->insert_query('point_log_t', $set);

    // 포인트를 사용한 경우 포인트 내역에 사용금액 기록
    if($arr['point'] < 0) {
        insert_use_point($arr['mt_idx'], $arr['point']);
    }

    $DB->update_query('member_t', array('mt_point'=>$plt_mt_point), "idx=".$arr['mt_idx']);
    return true;
}
function send_notification2($token_list, $title, $message, $link1, $link2, $chk) {
    //$link1 : 페이지 이름, $link2 : 보내는 사람 아이디
    //FCM 인증키
    $FCM_KEY = 'FCM_KEY';
    //FCM 전송 URL
    $FCM_URL = 'https://fcm.googleapis.com/fcm/send';

    //푸시 알림 설정 꺼져있으면
    if($chk == "N") {
        //전송 데이터
        $fields = array (
            'registration_ids' => $token_list,
            'data' => array (
                'title' => $title,
                'message' => $message,
                'link1' => $link1,
                'link2' => $link2,
            ),
        );
    } else {
        //전송 데이터
        $fields = array (
            'registration_ids' => $token_list,
            'data' => array (
                'title' => $title,
                'message' => $message,
                'link1' => $link1,
                'link2' => $link2,
            ),
            'notification' => array (
                'title' => $title,
                'body' => $message,
                'link1' => $link1,
                'link2' => $link2,
                'badge' => 1,
            ),
        );
    }

    //설정
    $headers = array( 'Authorization:key='. $FCM_KEY, 'Content-Type:application/json' );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $FCM_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if($result === false) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
    $obj = $result;
    return $obj;
}

$code = get_openssl_decrypt2($json->transactionNo);
$price = get_openssl_decrypt2($json->amt);
$barcode = get_openssl_decrypt2($json->barcode);
$msgId = $json->msgId;

//수납요청
if($msgId == "1020" || $msgId == "1030" || $msgId == "1040") {
    $query = "select * from order_t where ot_barcode = '".$barcode."'";
    $count = $DB->count_query($query);
    //쇼핑몰 결제 수납요청
    if($count > 0) {
        $arr = array();
        $query_ot = "
            select * from order_t a1  where a1.ot_barcode = '" . $barcode . "' and a1.ot_price = '" . $price . "'
        ";
        $row_ot = $DB->fetch_query($query_ot);

        $en_ot_code = get_openssl_encrypt2($code);
        $en_amt = get_openssl_encrypt2($price);
        $en_barcode = get_openssl_encrypt2($barcode);
        $en_approvalNo = get_openssl_encrypt2($row_ot['ot_approvalNo']);
        $ot_pt_name = $row_ot['ot_pt_name'];

        if($msgId == "1030" || $msgId == "1040") {
            if($msgId == "1030") {
                $msg = "1031";
            } else {
                $msg = "1041";
            }
            if($row_ot['ot_status'] > 1) {      //결제완료 시 결제취소 못하게 처리
                $query = [];
                $query['msgId'] = $msg;
                $query['resCode'] = "3006";     //3006 : 제휴사 환불불가 상태(제휴사로 문의)
                $query['transactionNo'] = $en_ot_code;
                $query['barcode'] = $en_barcode;
                $query['approvalNo'] = $en_approvalNo;
                $query['approvalDate'] = date("Ymd");
                $query['approvalTime'] = date("His");
                $query['itemInfo'] = $ot_pt_name;
                $query['amt'] = $en_amt;

                echo json_encode($query);

                exit;
            }
        }

        if($row_ot['mt_idx']) {
            $query = "select *, idx as mt_idx from member_t where idx = " . $row_ot['mt_idx'];
        } else {
            $query = "select *, idx as mt_idx from member_t where mt_id = '" . $row_ot['nmt_id']."'";
        }
        $row = $DB->fetch_assoc($query);

        unset($arr_query);
        $arr_query = array(
            "ot_status" => 2,
            "ot_pdate" => "now()",
        );

        $where_query = " ot_code = '" . $row_ot['ot_code'] . "'";

        $DB->update_query('order_t', $arr_query, $where_query);
        $DB->update_query('cart_t', array("ct_select" => 2, "ct_status" => 2, "ct_pdate" => "now()"), " ot_code = '" . $row_ot['ot_code'] . "'");

        if ($row_ot['mt_idx'] != "") {
            if ($row_ot['ot_code'] != '') {
                //포인트 차감 쿼리
                if ($row_ot['ot_use_point'] > 0) {
                    $test = insert_point(array('mt_idx' => $row_ot['mt_idx'], 'point' => (-1) * $row_ot['ot_use_point'], 'ot_code' => $row_ot['ot_code'], 'plt_memo' => "주문번호 " . $row_ot['ot_code'] . " 결제",'plt_status'=>2));
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
                                'pt_image1' => ($p_info['pt_image1'] ? 'url/images/uploads/' . $p_info['pt_image1'] . "?cache=" . time() : "url/images/noimg.png"),
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
            }
        } else {
            if ($row_ot['ot_code'] != '') {
                $ot_pt_name = $row_ot['ot_pt_name'];
                $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where cart_t.ot_code = '" . $row_ot['ot_code'] . "'";
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
                                'pt_image1' => ($p_info['pt_image1'] ? 'url/images/uploads/' . $p_info['pt_image1'] . "?cache=" . time() : "url/images/noimg.png"),
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
            }
        }

        //알림톡 토큰 생성
        $_apiURL = 'https://kakaoapi.aligo.in/akv10/token/create/30/s/';
        $_hostInfo = parse_url($_apiURL);
        $_port = (strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
        $_variables = array(
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
상품명 : ' . $list_fetch['ot_pt_name'] . '
주문일 : ' . substr($list_fetch['ot_pdate'], 0, 10) . '
주문번호 : ' . $list_fetch['ot_code'] . '
주문금액 : ' . number_format($list_fetch['ot_price']) . '원

배송은 영업일 기준 2~3일 정도 소요됩니다. 배송일은 배송지역에 따라 상이할 수 있습니다.';

        $_apiURL = 'https://kakaoapi.aligo.in/akv10/alimtalk/send/';
        $_hostInfo = parse_url($_apiURL);
        $_port = (strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
        $_variables = array(
            'apikey' => 'apikey',
            'userid' => 'userid',
            'token' => $retArr->token,
            'senderkey' => 'senderkey',
            'tpl_code' => 'tpl_code',
            'sender' => 'sender',
            'receiver_1' => $list_fetch['ot_hp'],
            'recvname_1' => $list_fetch['ot_name'],
            'subject_1' => '룰루팝',
            'message_1' => $msg,
//            'testMode' => "Y",        //테스트모드
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
//
        //푸시발송
        if ($row['mt_pushing'] == "Y" || $row['mt_pushing2'] == "Y") {
            $chk = "Y";
        } else {
            $chk = "N";
        }
        $result = new stdClass();
        $result->info = $arr['info'];
        $result->orderer = $arr['orderer'];
        $result->recipient = $arr['recipient'];
        $result->items = $items;

        $token_list = array($row['mt_fcm']);
        $message = "구매하신 상품이 배송 준비중입니다.";
        $title = "룰루팝";

        $message_status = send_notification2($token_list, $title, $message, "Product_Order_Finish_Page", json_encode($result), $chk);

        if ($message_status) {
            unset($arr_query);
            $plt_set = array(
                'plt_title' => $title,
                'plt_content' => $message,
                'plt_table' => "order_t_pay",
                'plt_type' => 2,
                'plt_index' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'mt_idx' => 1,
                'op_idx' => $row['idx'],
                'plt_wdate' => 'now()'
            );
            $DB->insert_query("pushnotification_log_t", $plt_set);
        }
    } else {
        //코인 수납요청 처리
        $query = "select * from coin_t where ct_type = 1 and ct_status = 1 and ct_price = " . $price . " and ct_barcode = '" . $barcode . "' order by ct_wdate desc";
        $coin = $DB->fetch_assoc($query);

        $query = "select *, idx as mt_idx from member_t where idx = " . $coin['mt_idx'];
        $row = $DB->fetch_assoc($query);

        $bonus = 0;
        for ($i = 10000; $i <= $price; $i += 5000) {
            $bonus++;
            if ($i == 100000) {
                $bonus = 20;
                break;
            }
        }

        if ($coin['idx'] > 0) {
            $en_ot_code = get_openssl_encrypt2($code);
            $en_amt = get_openssl_encrypt2($price);
            $en_barcode = get_openssl_encrypt2($barcode);
            $en_approvalNo = get_openssl_encrypt2($coin['ct_approvalNo']);
            $ot_pt_name ="코인 적립 ".$coin['ct_amount']."개";

            if($msgId == "1030" || $msgId == "1040") {
                if($msgId == "1030") {
                    $msg = "1031";
                } else {
                    $msg = "1041";
                }
                if($coin['ct_status'] > 1) {      //결제완료 시 결제취소 못하게 처리
                    $query = [];
                    $query['msgId'] = "msgId";
                    $query['resCode'] = "3006";     //3006 : 제휴사 환불불가 상태(제휴사로 문의)
                    $query['transactionNo'] = $en_ot_code;
                    $query['barcode'] = $en_barcode;
                    $query['approvalNo'] = $en_approvalNo;
                    $query['approvalDate'] = date("Ymd");
                    $query['approvalTime'] = date("His");
                    $query['itemInfo'] = $ot_pt_name;
                    $query['amt'] = $en_amt;

                    echo json_encode($query);

                    exit;
                }
            }

            $arr = array(
                "ct_status" => 2,
                "ct_pdate" => "now()",
                "ct_amount" => $coin['ct_amount'] + $bonus,
            );

            $DB->update_query("coin_t", $arr, " idx = " . $coin['idx']);
            $DB->update_query("member_t", array("mt_coin" => (int)$row['mt_coin'] + (int)$coin['ct_amount'] + (int)$bonus), " idx = " . $row['mt_idx']);

            unset($arr);
            $arr['ct_amount'] = (int)$coin['ct_amount'] . "코인 충전";
            $arr['bonus'] = $bonus;
            $arr['ct_price'] = (int)$coin['ct_price'];
            $arr['mt_coin'] = (int)$row['mt_coin'] + (int)$coin['ct_amount'] + $bonus;
            $payload['data'] = $arr;
        }

        //푸시발송
        if ($row['mt_pushing'] == "Y" || $row['mt_pushing2'] == "Y") {
            $chk = "Y";
        } else {
            $chk = "N";
        }
        $result = new stdClass();
        $result->ct_amount = (int)$coin['ct_amount'] . "코인 충전";
        $result->bonus = $bonus;
        $result->ct_price = (int)$coin['ct_price'];
        $result->mt_coin = (int)$row['mt_coin'] + (int)$coin['ct_amount'] + $bonus;

        $token_list = array($row['mt_fcm']);
        $message = "결제완료 되었습니다.";
        $title = "룰루팝";

        $message_status = send_notification2($token_list, $title, $message, "Random_Coin_Payment_Finish_Page", json_encode($result), $chk);

        if ($message_status) {
            unset($arr_query);
            $plt_set = array(
                'plt_title' => $title,
                'plt_content' => $message,
                'plt_table' => "coin_t_pay",
                'plt_type' => 2,
                'plt_index' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'mt_idx' => 1,
                'op_idx' => $row['idx'],
                'plt_wdate' => 'now()'
            );
            $DB->insert_query("pushnotification_log_t", $plt_set);
        }
    }
    $query = [];
    $query['msgId'] = "msgId";
    $query['resCode'] = "0000";     //0000 : 정상처리 완료
    $query['transactionNo'] = $en_ot_code;
    $query['barcode'] = $en_barcode;
    $query['approvalNo'] = $en_approvalNo;
    $query['approvalDate'] = date("Ymd");
    $query['approvalTime'] = date("His");
    $query['itemInfo'] = $ot_pt_name;
    $query['amt'] = $en_amt;

    echo json_encode($query);
}
?>