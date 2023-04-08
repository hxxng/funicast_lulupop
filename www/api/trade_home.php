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

$item_count = trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;


$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $arr['info']['mt_image'] = ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url);
        $arr['info']['mt_nickname'] = $row['mt_nickname'];
        $query = "select * from trade_t where mt_idx = " . $row['mt_idx'] . " and tt_status = 1 and tt_sale_status = 1";
        $sale = $DB->count_query($query);
        $arr['info']['sale'] = (int)$sale;

        $query = "select * from trade_t where mt_idx = " . $row['mt_idx'] . " and tt_status = 1 and tt_sale_status = 2";
        $soldout = $DB->count_query($query);
        $arr['info']['soldout'] = (int)$soldout;

        $query = "select trade_t.* from trade_t left join member_t on member_t.idx = trade_t.mt_idx where tt_status = 1 
        and trade_t.idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row['mt_idx']." and rt_table = 'trade_t') and mt_grade = 2 ";
        if ($decoded_array['filter']) {
            $query .= " and tt_cate_idx = " . $decoded_array['filter'];
        }
        $query .= " order by tt_wdate desc limit 4";
        $premium = $DB->select_query($query);
        if($premium) {
            foreach ($premium as $row_p) {
                if($row_p['tt_sale_status'] == 1) {
                    $tt_sale_status = "판매중";
                } else {
                    $tt_sale_status = "판매완료";
                }
                if($row_p['tt_img1']) {
                    $tt_img = $ct_img_url."/".$row_p['tt_img1']."?cache=".strtotime($row_p['tt_udate']);
                } else {
                    $tt_img = null;
                }
                $arr['preminum'][] = array(
                    "tt_idx" => $row_p['idx'],
                    "tt_cate_idx" => $row_p['tt_cate_idx'],
                    "tt_title" => $row_p['tt_title'],
                    "mt_idx" => $row_p['mt_idx'],
                    "tt_sale_status" => $tt_sale_status,
                    "tt_price" => (int)$row_p['tt_price'],
                    "tt_img" => $tt_img,
                );
            }
        }

        $query = "SELECT a.*, IFNULL(b.CNT2, 0) AS 'cnt'
                FROM trade_t AS a LEFT OUTER JOIN (
                    SELECT ct_idx, COUNT(*) AS CNT2
                    FROM comment_t
                    GROUP BY ct_idx
                ) AS b
                ON a.idx = b.ct_idx
                WHERE tt_status = 1 and idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row['mt_idx']." and rt_table = 'trade_t')";
        $count_query = "SELECT count(idx) as cnt FROM (SELECT a.idx
                FROM trade_t AS a LEFT OUTER JOIN (
                SELECT ct_idx, COUNT(*) AS CNT2
                FROM comment_t
                GROUP BY ct_idx
                ) AS b
                ON a.idx = b.ct_idx
                WHERE tt_status = 1 and idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row['mt_idx']." and rt_table = 'trade_t') ";
        if ($decoded_array['filter']) {
            $query .= " and tt_cate_idx = " . $decoded_array['filter'];
            $count_query .= " and tt_cate_idx = " . $decoded_array['filter'];
        }
        if($decoded_array['order']) {
            if($decoded_array['order'] == "hot") {
                $order = " cnt desc";
            } else if($decoded_array['order'] == "low") {
                $order = "tt_price ";
            } else if($decoded_array['order'] == "high") {
                $order = "tt_price desc";
            }
            $query .=" order by ".$order;
        } else {
            $query .=" order by a.tt_wdate desc";
        }
        $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
        $count = $DB->fetch_query($count_query." ) A ");
        if($premium) {
            if($list) {
                foreach ($list as $row_a) {
                    if ($row_a['tt_sale_status'] == 1) {
                        $tt_sale_status = "판매중";
                    } else {
                        $tt_sale_status = "판매완료";
                    }
                    if ($row_a['tt_img1']) {
                        $tt_img = $ct_img_url . "/" . $row_a['tt_img1'] . "?cache=" . strtotime($row_p['tt_udate']);
                    } else {
                        $tt_img = null;
                    }
                    $arr['all']['list'][] = array(
                        "tt_idx" => $row_a['idx'],
                        "tt_cate_idx" => $row_a['tt_cate_idx'],
                        "tt_title" => $row_a['tt_title'],
                        "mt_idx" => $row_a['mt_idx'],
                        "tt_sale_status" => $tt_sale_status,
                        "tt_price" => (int)$row_a['tt_price'],
                        "tt_img" => $tt_img,
                    );
                }
            }
        }

        $arr['all']['count'] = (int)$count[0];
        $n_page = ceil($count[0] / 20);
        $arr['all']['maxpage'] = (int)$n_page;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 중고거래 홈', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '중고거래 홈', $jwt);
        }
    } else {
        $arr['info']['mt_image'] = $ct_member_no_img_url;
        $arr['info']['mt_nickname'] = null;
        $arr['info']['sale'] = 0;
        $arr['info']['soldout'] = 0;

        $query = "select trade_t.* from trade_t left join member_t on member_t.idx = trade_t.mt_idx where tt_status = 1 and mt_grade = 2 ";
        if ($decoded_array['filter']) {
            $query .= " and tt_cate_idx = " . $decoded_array['filter'];
        }
        $query .= " order by tt_wdate desc limit 4";
        $premium = $DB->select_query($query);
        if($premium) {
            foreach ($premium as $row_p) {
                if($row_p['tt_sale_status'] == 1) {
                    $tt_sale_status = "판매중";
                } else {
                    $tt_sale_status = "판매완료";
                }
                if($row_p['tt_img1']) {
                    $tt_img = $ct_img_url."/".$row_p['tt_img1']."?cache=".strtotime($row_p['tt_udate']);
                } else {
                    $tt_img = null;
                }
                $arr['preminum'][] = array(
                    "tt_idx" => $row_p['idx'],
                    "tt_cate_idx" => $row_p['tt_cate_idx'],
                    "tt_title" => $row_p['tt_title'],
                    "mt_idx" => $row_p['mt_idx'],
                    "tt_sale_status" => $tt_sale_status,
                    "tt_price" => (int)$row_p['tt_price'],
                    "tt_img" => $tt_img,
                );
            }
        }

        $query = "SELECT a.*, IFNULL(b.CNT2, 0) AS 'cnt'
                FROM trade_t AS a LEFT OUTER JOIN (
                    SELECT ct_idx, COUNT(*) AS CNT2
                    FROM comment_t
                    GROUP BY ct_idx
                ) AS b
                ON a.idx = b.ct_idx
                WHERE tt_status = 1 ";
        $count_query = "SELECT count(idx) as cnt FROM (SELECT a.idx
                FROM trade_t AS a LEFT OUTER JOIN (
                SELECT ct_idx, COUNT(*) AS CNT2
                FROM comment_t
                GROUP BY ct_idx
                ) AS b
                ON a.idx = b.ct_idx
                WHERE tt_status = 1  ";
        if ($decoded_array['filter']) {
            $query .= " and tt_cate_idx = " . $decoded_array['filter'];
            $count_query .= " and tt_cate_idx = " . $decoded_array['filter'];
        }
        if($decoded_array['order']) {
            if($decoded_array['order'] == "hot") {
                $order = " cnt desc";
            } else if($decoded_array['order'] == "low") {
                $order = "tt_price ";
            } else if($decoded_array['order'] == "high") {
                $order = "tt_price desc";
            }
            $query .=" order by ".$order;
        } else {
            $query .=" order by a.tt_wdate desc";
        }
        $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
        $count = $DB->fetch_query($count_query." ) A ");
        if($premium) {
            if($list) {
                foreach ($list as $row_a) {
                    if ($row_a['tt_sale_status'] == 1) {
                        $tt_sale_status = "판매중";
                    } else {
                        $tt_sale_status = "판매완료";
                    }
                    if ($row_a['tt_img1']) {
                        $tt_img = $ct_img_url . "/" . $row_a['tt_img1'] . "?cache=" . strtotime($row_p['tt_udate']);
                    } else {
                        $tt_img = null;
                    }
                    $arr['all']['list'][] = array(
                        "tt_idx" => $row_a['idx'],
                        "tt_cate_idx" => $row_a['tt_cate_idx'],
                        "tt_title" => $row_a['tt_title'],
                        "mt_idx" => $row_a['mt_idx'],
                        "tt_sale_status" => $tt_sale_status,
                        "tt_price" => (int)$row_a['tt_price'],
                        "tt_img" => $tt_img,
                    );
                }
            }
        }

        $arr['all']['count'] = (int)$count[0];
        $n_page = ceil($count[0] / 20);
        $arr['all']['maxpage'] = (int)$n_page;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 중고거래 홈', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 중고거래 홈', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>