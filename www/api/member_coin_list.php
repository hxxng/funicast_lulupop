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
if($decoded_array['ct_type']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_type', '');
    exit;
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    if($decoded_array['ct_type'] == 1) {
        $where = " ct_status > 1 and ct_type = ".$decoded_array['ct_type'];
    } else {
        $where = " ct_type = ".$decoded_array['ct_type'];
    }
    $coin_list = $DB->select_query("select * from coin_t where mt_idx = ".$row['mt_idx']." and ".$where." order by ct_wdate desc limit ".$item_count.", ".($item_count+20));
    $count = $DB->count_query("select * from coin_t where ct_status > 1 and mt_idx = ".$row['mt_idx']." and ct_type = ".$decoded_array['ct_type']);
    $n_page = ceil($count[0] / 20);

    if($coin_list) {
        foreach ($coin_list as $c_row) {
            $ct_refund_status = null;
            $detail = "N";
            if($c_row['ct_type'] == 1) {
                if($c_row['ct_status'] == 2) {
                    $ct_status = "구매 적립";
                    $status = "적립";
                    $ct_pdate = substr($c_row['ct_pdate'],0,10);
                } else if($c_row['ct_status'] == 3) {
                    $ct_status = "랜덤뽑기 사용";
                    $status = "사용";
                    $ct_pdate = substr($c_row['ct_wdate'],0,10);
                } else if($c_row['ct_status'] == 4) {
                    $detail = "Y";
                    $ct_status = "구매 적립";
                    $status = "적립";
                    $ct_pdate = substr($c_row['ct_pdate'],0,10);
                    if($c_row['ct_refund_status'] == 1) {
                        $ct_refund_status = "환불요청";
                    } else {
                        $ct_refund_status = "환불완료";
                    }
                }
            } else {
                if($c_row['ct_status'] == 3) {
                    $ct_status = "랜덤뽑기 사용";
                    $status = "사용";
                    $ct_pdate = substr($c_row['ct_wdate'], 0, 10);
                } else {
                    $ct_status = "보너스 코인";
                    $status = "적립";
                    $ct_pdate = substr($c_row['ct_pdate'], 0, 10);
                }
            }

            $query = "select * from coin_t where mt_idx = ".$row['mt_idx']." and ct_type = 1 and ct_status = 2 order by idx desc";
            $pay = $DB->fetch_assoc($query);

            $query = "select * from coin_t where mt_idx = ".$row['mt_idx']." and ct_type = 1 and ct_status = 3 order by idx desc";
            $use = $DB->fetch_assoc($query);

            if($c_row['ct_status'] == 2) {
                if($c_row['idx'] < $use['idx']) {
                    $refund = "N";
                } else {
                    $refund = "Y";
                }
            }  else {
                $refund = "N";
            }

            $arr['list'][] = array(
                "ct_idx" => $c_row['idx'],
                "ct_code" => $c_row['ct_code'],
                "status" => $status,
                "ct_status" => $ct_status,
                "ct_amount" => (int)$c_row['ct_amount'],
                "ct_price" => (int)$c_row['ct_price'],
                "ct_refund_status" => $ct_refund_status,
                "ct_pdate" => $ct_pdate,
                "ct_pay_type" => $c_row['ct_pay_type'],
                "refund" => $refund,
                "detail" => $detail,
            );
        }
    } else {
        $arr['list'] = [];
    }

    $arr['count'] = (int)$count;
    $arr['maxpage'] = (int)$n_page;

    $query = "select sum(ct_amount) as sum_ct_amount from coin_t where mt_idx = ".$row['mt_idx']." and ct_type = 1 and ct_status in (2,3)";
    $a_coin = $DB->fetch_assoc($query);

    $arr['a_coin'] = (int)$a_coin['sum_ct_amount'];

    $query = "select sum(ct_amount) as sum_ct_amount from coin_t where mt_idx = ".$row['mt_idx']." and ct_type = 2 ";
    $b_coin = $DB->fetch_assoc($query);

    $arr['b_coin'] = (int)$b_coin['sum_ct_amount'];

    $arr['sum_coin'] = $a_coin['sum_ct_amount'] + $b_coin['sum_ct_amount'];

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 보유 코인', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '보유 코인', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>