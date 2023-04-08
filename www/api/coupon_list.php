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

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $date = date('Y-m-d', time());
    $query = "select * from member_coupon_t left join coupon_t on coupon_t.ct_code = member_coupon_t.mct_ct_code where mt_idx = ".$row['mt_idx'];
    if($decoded_array['filter']) {
        if ($decoded_array['filter'] == "before") {
            $mct_status = "N";
            $query .= " and mct_status = 'N'";
        } else if ($decoded_array['filter'] == "after") {
            $query .= " and mct_status = 'Y'";
        }
    }
    $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
    $count= $DB->count_query($query);
    if($list) {
        foreach ($list as $row_c) {
            if($row_c['mct_status'] == "Y") {
                $status = "N";
            } else {
                if($row_c['ct_use_person'] <= $row_c['ct_used_person']) {
                    $status = "N";
                } else if(!((strtotime($row_c['ct_sdate']) <= strtotime($date)) && (strtotime($row_c['ct_edate']) >= strtotime($date)))) {
                    $status = "N";
                } else {
                    $status = "Y";
                }
                if($decoded_array['filter']) {
                    if($status == "N") {
                        continue;
                    }
                }
            }
            $arr['list'][] = array(
                "ct_name" => $row_c['ct_name'],
                "ct_code" => $row_c['ct_code'],
                "ct_edate" => $row_c['ct_edate'],
                "ct_min_price" => (int)$row_c['ct_min_price'],
                "ct_sale_price" => (int)$row_c['ct_sale_price'],
                "status" => $status,
            );
        }
    } else {
        $arr['list'] = [];
    }

    $arr['count'] = (int)$count;
    $n_page = ceil($count / 20);
    if($n_page == 0) {
        $arr['maxpage'] = 1;
    } else {
        $arr['maxpage'] = (int)$n_page;
    }

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 쿠폰 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '쿠폰 리스트', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>