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
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_assoc($query);

if($row_m['mt_idx']) {
    if($row_m['mt_level'] == 3) {
        $mt_catalog = substr($row_m['mt_catalog'], 0, -1);
        if ($mt_catalog == "") {
            $query = "select * from catalog_t ";
            $list_c = $DB->select_query($query);
            if($list_c) {
                foreach ($list_c as $row_c) {
                    if ($row_c['ct_img2']) {
                        $ct_img2 = $ct_img_url . "/" . $row_c['ct_img2'] . "?cache=" . strtotime($row_c['ct_udate']);
                    } else {
                        $ct_img2 = null;
                    }
                    $arr['list'][] = array(
                        "pt_idx" => $row_c['pt_idx'],
                        "ct_img" => $ct_img2,
                        "dt_effect" => null,
                        "active" => false,
                    );
                }
            } else {
                $arr['list'] = [];
            }
        } else {
            $query = "select * from catalog_t where idx in (" . $mt_catalog . ")";
            $list_c2 = $DB->select_query($query);
            if($list_c2) {
                foreach ($list_c2 as $row_c2) {
                    $query = "select * from draw_t where idx = ".$row_c2['ct_effect'];
                    $draw = $DB->fetch_assoc($query);
                    if ($row_c2['ct_img1']) {
                        $ct_img = $ct_img_url . "/" . $row_c2['ct_img1'] . "?cache=" . strtotime($row_c2['ct_udate']);
                    } else {
                        $ct_img = null;
                    }
                    if ($draw['dt_effect']) {
                        $dt_effect = $ct_img_url . "/" . $draw['dt_effect'] . "?cache=" . strtotime($row_c2['dt_edate']);
                    } else {
                        $dt_effect = null;
                    }
                    $arr['list'][] = array(
                        "pt_idx" => $row_c2['pt_idx'],
                        "ct_img" => $ct_img,
                        "dt_effect" => $dt_effect,
                        "active" => true,
                    );
                }
            }
            $query = "select * from catalog_t where idx not in (" . $mt_catalog . ")";
            $list_c = $DB->select_query($query);

            if($list_c) {
                foreach ($list_c as $row_c) {
                    if ($row_c['ct_img2']) {
                        $ct_img2 = $ct_img_url . "/" . $row_c['ct_img2'] . "?cache=" . strtotime($row_c['ct_udate']);
                    } else {
                        $ct_img2 = null;
                    }
                    $arr['list'][] = array(
                        "pt_idx" => $row_c['pt_idx'],
                        "ct_img" => $ct_img2,
                        "dt_effect" => null,
                        "active" => false,
                    );
                }
            }
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 도감', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '도감', $jwt);
        }
    } else {
        $query = "select * from catalog_t ";
        $list_c = $DB->select_query($query);
        foreach ($list_c as $row_c) {
            if ($row_c['ct_img2']) {
                $ct_img2 = $ct_img_url . "/" . $row_c['ct_img2'] . "?cache=" . strtotime($row_c['ct_udate']);
            } else {
                $ct_img2 = null;
            }
            $arr['list'][] = array(
                "pt_idx" => $row_c['pt_idx'],
                "ct_img" => $ct_img2,
                "active" => false,
            );
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 도감', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 도감', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>