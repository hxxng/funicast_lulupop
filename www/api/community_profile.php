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
if($decoded_array['profile_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. profile_idx', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.idx = '".$decoded_array['profile_idx']."'";
$row_p = $DB->fetch_assoc($query);

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

if($row['mt_idx']) {
    if($row_p['mt_idx']) {
        $arr['info']['mt_idx'] = $row_p['mt_idx'];
        $arr['info']['mt_id'] = $row_p['mt_id'];
        $arr['info']['mt_image'] = ($row_p['mt_image'] ? $ct_img_url . '/' . $row_p['mt_image']."?cache=".strtotime($row_p['mt_udate']) : $ct_member_no_img_url);
        $arr['info']['mt_nickname'] = $row_p['mt_nickname'];

        $query = "select * from community_t where mt_idx = " . $row_p['mt_idx'] . " and ct_status = 1 
        and idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row['mt_idx']." and rt_table = 'community_t')
        and idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=".$row['mt_idx']." and ht_table = 'community_t')
         order by ct_wdate desc";
        $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
        $count = $DB->count_query($query);
        $arr['info']['community_cnt'] = (int)$count;

        if($row['mt_idx'] == $decoded_array['profile_idx']) {
            $query = "select follow_t.* from follow_t left join member_t on member_t.idx = follow_t.mt_idx and mt_level = 3
                    where ft_mt_idx = " . $row_p['mt_idx']." and mt_idx not in (SELECT report_idx FROM report_t WHERE rt_status = 3 and rt_table = 'member_t') 
                    and ft_hide = 'N' and mt_id is not null ";
        } else {
            $query = "select follow_t.* from follow_t left join member_t on member_t.idx = follow_t.mt_idx and mt_level = 3 
            where ft_mt_idx = " . $row_p['mt_idx']." and mt_idx not in (SELECT report_idx FROM report_t WHERE rt_status = 3 and rt_table = 'member_t') and mt_id is not null";
        }
        $follow_cnt = $DB->count_query($query);
        $arr['info']['follower_cnt'] = (int)$follow_cnt;


        if($row['mt_idx'] == $decoded_array['profile_idx']) {
            $query = "select follow_t.* from follow_t left join member_t on member_t.idx = follow_t.ft_mt_idx and mt_level = 3
where mt_idx = " . $row_p['mt_idx']." and ft_mt_idx not in (SELECT report_idx FROM report_t WHERE rt_status = 3 and rt_table = 'member_t') and ft_hide = 'N' and mt_id is not null";
        } else {
            $query = "select follow_t.* from follow_t left join member_t on member_t.idx = follow_t.ft_mt_idx and mt_level = 3 
where mt_idx = " . $row_p['mt_idx']." and ft_mt_idx not in (SELECT report_idx FROM report_t WHERE rt_status = 3 and rt_table = 'member_t') and mt_id is not null";
        }
        $following_cnt = $DB->count_query($query);
        $arr['info']['following_cnt'] = (int)$following_cnt;

        if($list) {
            foreach ($list as $row_c) {
                $arr['list'][] = array(
                    "ct_idx" => $row_c['idx'],
                    "ct_title" => $row_c['ct_title'],
                    "ct_img1" => ($row_c['ct_img1'] ? $ct_img_url . '/' . $row_c['ct_img1']."?cache=".strtotime($row_c['ct_udate']) : $ct_no_img_url),
                );
            }
        }

        $query = "select * from follow_t where mt_idx = ".$row['mt_idx']." and ft_mt_idx = ".$row_p['mt_idx'];
        $follow = $DB->fetch_assoc($query);
        if($follow['idx'] > 0) {
            $arr['follow_status'] = "Y";
        } else {
            $arr['follow_status'] = "N";
        }
        if($follow['ft_hide'] == "") {
            $arr['ft_hide'] = "N";
        } else {
            $arr['ft_hide'] = $follow['ft_hide'];
        }

        $arr['count'] = (int)$count[0];
        $n_page = ceil($count[0] / 20);
        $arr['maxpage'] = (int)$n_page;

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 커뮤니티 프로필', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '커뮤니티 프로필', $jwt);
        }
    } else {
        echo result_data('false', '존재하지 않는 회원입니다.', $arr);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>