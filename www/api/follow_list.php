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
if($decoded_array['follow']=="") {
    echo result_data('false', '잘못된 접근입니다. follow', '');
    exit;
}
if($decoded_array['op_id']=="") {
    echo result_data('false', '잘못된 접근입니다. op_id', '');
    exit;
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['op_id']."'";
$row2 = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    if($decoded_array['search_txt'] != "") {
        $where = " and (instr(mt_nickname, '".$decoded_array['search_txt']."'))";
    } else {
        $where = "";
    }
    if($decoded_array['follow'] == "follower") {
        $query = "select follow_t.*, mt_nickname, mt_image, mt_id, mt_udate from follow_t left join member_t on member_t.idx = follow_t.mt_idx and mt_level = 3 where mt_id is not null and ft_mt_idx = " . $row['mt_idx'];
        if($row['mt_idx'] == $row2['mt_idx']) {
            $query .= " and ft_hide = 'N' ";
            $chk = "Y";
        } else {
            $chk = "N";
        }
        $list = $DB->select_query($query . $where . " limit " . $item_count . ", " . ($item_count + 20));
        $count1 = $DB->count_query($query . $where);
        $n_page = ceil($count1[0] / 20);
        if ($list) {
            foreach ($list as $row_f1) {
                if($chk == "Y") {
                    $follow_list[] = array(
                        "mt_idx" => $row_f1['mt_idx'],
                        "mt_id" => $row_f1['mt_id'],
                        "mt_nickname" => $row_f1['mt_nickname'],
                        "mt_image" => ($row_f1['mt_image'] ? $ct_img_url . '/' . $row_f1['mt_image']."?cache=".strtotime($row_f1['mt_udate']) : $ct_member_no_img_url),
                    );
                } else {
                    $query = "select follow_t.*, mt_nickname, mt_image, mt_id, mt_udate from follow_t left join member_t on member_t.idx = follow_t.mt_idx and mt_level = 3 where mt_id is not null and follow_t.idx = ".$row_f1['idx'];
                    $list2 = $DB->select_query($query);
                    if($list2) {
                        foreach ($list2 as $row22) {
                            $follow_list[] = array(
                                "mt_idx" => $row22['mt_idx'],
                                "mt_id" => $row22['mt_id'],
                                "mt_nickname" => $row22['mt_nickname'],
                                "mt_image" => ($row22['mt_image'] ? $ct_img_url . '/' . $row22['mt_image']."?cache=".strtotime($row22['mt_udate']) : $ct_member_no_img_url),
                            );
                        }
                    }
                }
            }
            $arr['list'] = $follow_list;
            $arr['count'] = (int)$count1;
            $arr['maxpage'] = (int)$n_page;
        }
    } else if($decoded_array['follow'] == "following") {
        $query = "select follow_t.*, mt_nickname, mt_image, mt_id, mt_udate from follow_t left join member_t on member_t.idx = follow_t.ft_mt_idx and mt_level = 3 where mt_id is not null and mt_idx = " . $row['mt_idx'];
        if($row['mt_idx'] == $row2['mt_idx']) {
            $query .= " and ft_hide = 'N' ";
            $chk = "Y";
        } else {
            $chk = "N";
        }
        $list2 = $DB->select_query($query . $where . " limit " . $item_count . ", " . ($item_count + 20));
        $count2 = $DB->count_query($query . $where);
        $n_page = ceil($count2[0] / 20);
        if ($list2) {
            foreach ($list2 as $row_f2) {
                if($chk == "Y") {
                    $following_list[] = array(
                        "mt_idx" => $row_f2['ft_mt_idx'],
                        "mt_id" => $row_f2['mt_id'],
                        "mt_nickname" => $row_f2['mt_nickname'],
                        "mt_image" => ($row_f2['mt_image'] ? $ct_img_url . '/' . $row_f2['mt_image']."?cache=".strtotime($row_f2['mt_udate']) : $ct_member_no_img_url),
                    );
                } else {
                    $query = "select follow_t.*, mt_nickname, mt_image, mt_id, mt_udate from follow_t left join member_t on member_t.idx = follow_t.ft_mt_idx where follow_t.idx = ".$row_f2['idx'];
                    $list2 = $DB->select_query($query);
                    if($list2) {
                        foreach ($list2 as $row22) {
                            $following_list[] = array(
                                "mt_idx" => $row22['ft_mt_idx'],
                                "mt_id" => $row22['mt_id'],
                                "mt_nickname" => $row22['mt_nickname'],
                                "mt_image" => ($row22['mt_image'] ? $ct_img_url . '/' . $row22['mt_image']."?cache=".strtotime($row22['mt_udate']) : $ct_member_no_img_url),
                            );
                        }
                    }
                }
            }
            $arr['list'] = $following_list;
            $arr['count'] = (int)$count2;
            $arr['maxpage'] = (int)$n_page;
        } else {
            $following_list = [];
        }
    }
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 팔로우/팔로워 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '팔로우/팔로워 리스트', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>