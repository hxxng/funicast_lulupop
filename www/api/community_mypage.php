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
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "SELECT ADDDATE( CURDATE(), - WEEKDAY(CURDATE()) + 0 ) AS mon, CURDATE() as today";
    $last_week = $DB->fetch_assoc($query);
    if($row['mt_level'] == 3) {
        if($row['mt_set_yn'] == "Y" && $row['mt_set_cate_yn'] == "Y") {
            $arr['info']['mt_image'] = ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image'] . "?cache=" . strtotime($row['mt_udate']) : $ct_member_no_img_url);
            $arr['info']['mt_nickname'] = $row['mt_nickname'];
            $query = "select * from community_t where mt_idx = " . $row['mt_idx'] . " and ct_status = 1";
            $community = $DB->count_query($query);
            $arr['info']['community_cnt'] = (int)$community;

            $query = "select follow_t.* from follow_t left join member_t on member_t.idx = follow_t.mt_idx and mt_level = 3 where ft_mt_idx = " . $row['mt_idx'] . " and ft_hide = 'N' and mt_id is not null";
            $follow_cnt = $DB->count_query($query);
            $arr['info']['follower_cnt'] = (int)$follow_cnt;

            $tab_id = $decoded_array['tab_id'];
            if ($tab_id == "") {
                $tab_id = "following";
            }

            //내가 팔로우한 사람의 게시글 중 안읽은 게시글 리스트
            $query = "select follow_t.* from follow_t left join member_t on member_t.idx = follow_t.ft_mt_idx and mt_level = 3 
    where mt_idx = " . $row['mt_idx'] . " and mt_idx not in (SELECT report_idx FROM report_t where report_idx=mt_idx and reported_idx=idx and rt_table = 'member_t') and mt_id is not null";
            $follow = $DB->select_query($query);
            $following_cnt = $DB->count_query($query);
            if ($follow) {
                foreach ($follow as $row_f) {
                    $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname from community_t 
                        where ct_wdate >= '" . $last_week['mon'] . " 00:00:00' and ct_wdate <= '" . $last_week['today'] . " 23:59:59' and ct_status = 1 and mt_idx = " . $row_f['ft_mt_idx'] . " 
                        and idx not in (SELECT ct_idx FROM community_view_log_t where mt_idx=" . $row['mt_idx'] . ")
                        and idx not in (SELECT report_idx FROM report_t where reporter_idx=" . $row['mt_idx'] . " and rt_table = 'community_t')
                        and idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=" . $row['mt_idx'] . " and ht_table = 'community_t')";
                    $count_query = "select * from community_t 
                            where ct_wdate >= '" . $last_week['mon'] . " 00:00:00' and ct_wdate <= '" . $last_week['today'] . " 23:59:59' and ct_status = 1 and mt_idx = " . $row_f['ft_mt_idx'] . " 
                            and idx not in (SELECT ct_idx FROM community_view_log_t where mt_idx=" . $row['mt_idx'] . ")
                            and idx not in (SELECT report_idx FROM report_t where reporter_idx=" . $row['mt_idx'] . " and rt_table = 'community_t')
                            and idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=" . $row['mt_idx'] . " and ht_table = 'community_t')";
                    if ($decoded_array['filter']) {
                        $query .= " and ct_cate_idx = " . $decoded_array['filter'];
                        $count_query .= " and ct_cate_idx = " . $decoded_array['filter'];
                    }
                    $query .= " order by ct_wdate desc";
                    $count_query .= " order by ct_wdate desc";
                    $following = $DB->select_query($query);
                    if ($following) {
                        if ($tab_id == "" || $tab_id == "following") {
                            $count = $DB->count_query($count_query);
                            foreach ($following as $f_row) {
                                $arr2['following_list'][] = array(
                                    "ct_idx" => $f_row['idx'],
                                    "ct_title" => $f_row['ct_title'],
                                    "mt_nickname" => $f_row['mt_nickname'],
                                    "ct_img1" => $ct_img_url . "/" . $f_row['ct_img1'] . "?cache=" . strtotime($f_row['ct_udate']),
                                );
                            }
                        }
                    }
                }
            }
            $arr['info']['following_cnt'] = (int)$following_cnt;

            //나의 관심 카테고리 게시글 중에서 안읽은 게시글 리스트
            $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname from community_t 
            where ct_status = 1 
            and mt_idx != " . $row['mt_idx'] . " and ct_cate_idx in (" . $row['mt_pct_idx'] . ") 
            and idx not in (SELECT ct_idx FROM community_view_log_t where mt_idx=" . $row['mt_idx'] . ")
            and idx not in (SELECT report_idx FROM report_t where reporter_idx=" . $row['mt_idx'] . " and rt_table = 'community_t')
            and idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=" . $row['mt_idx'] . " and ht_table = 'community_t')";
            $count_query = "select * from community_t 
            where ct_status = 1 
            and mt_idx != " . $row['mt_idx'] . " and ct_cate_idx in (" . $row['mt_pct_idx'] . ") 
            and idx not in (SELECT ct_idx FROM community_view_log_t where mt_idx=" . $row['mt_idx'] . ")
            and idx not in (SELECT report_idx FROM report_t where reporter_idx=" . $row['mt_idx'] . " and rt_table = 'community_t')
            and idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=" . $row['mt_idx'] . " and ht_table = 'community_t')";
            if ($decoded_array['filter']) {
                $query .= " and ct_cate_idx = " . $decoded_array['filter'];
                $count_query .= " and ct_cate_idx = " . $decoded_array['filter'];
            }
            $query .= " order by ct_wdate desc";
            $count_query .= " order by ct_wdate desc";
            $category = $DB->select_query($query);
            if ($category) {
                if ($tab_id == "category") {
                    $count = $DB->count_query($count_query);
                    foreach ($category as $c_row) {
                        $arr2['category_list'][] = array(
                            "ct_idx" => $c_row['idx'],
                            "ct_title" => $c_row['ct_title'],
                            "mt_nickname" => $c_row['mt_nickname'],
                            "ct_img1" => $ct_img_url . "/" . $c_row['ct_img1'] . "?cache=" . strtotime($c_row['ct_udate']),
                        );
                    }
                }
            }

            //좋아요 수가 10개 이상인 저번주 인기 게시글
            $query = "SELECT count(*) as cnt, ct_idx, ct_title, ct_img1, (SELECT mt_nickname FROM member_t WHERE idx=community_t.mt_idx) as mt_nickname
            FROM like_community_t left join community_t on community_t.idx = like_community_t.ct_idx
            where lct_wdate >= '" . $last_week['mon'] . " 00:00:00' and lct_wdate <= '" . $last_week['today'] . " 23:59:59'
            and ct_idx not in (SELECT ct_idx FROM community_view_log_t where mt_idx=" . $row['mt_idx'] . ")
            and ct_idx not in (SELECT report_idx FROM report_t where reporter_idx=" . $row['mt_idx'] . " and rt_table = 'community_t')
            and ct_idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=" . $row['mt_idx'] . " and ht_table = 'community_t')";
            $count_query = "select COUNT(ct_idx) AS cnt from (select like_community_t.ct_idx from  like_community_t left join community_t on community_t.idx = like_community_t.ct_idx
                    where lct_wdate >= '" . $last_week['mon'] . " 00:00:00' and lct_wdate <= '" . $last_week['today'] . " 23:59:59'
                    and ct_idx not in (SELECT ct_idx FROM community_view_log_t where mt_idx=" . $row['mt_idx'] . ")
                    and ct_idx not in (SELECT report_idx FROM report_t where reporter_idx=" . $row['mt_idx'] . " and rt_table = 'community_t')
                    and ct_idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=" . $row['mt_idx'] . " and ht_table = 'community_t')";
            if ($decoded_array['filter']) {
                $query .= " and ct_cate_idx = " . $decoded_array['filter'];
                $count_query .= " and ct_cate_idx = " . $decoded_array['filter'];
            }
            $query .= " group by ct_idx having cnt >= 10 order by cnt desc ";
            $count_query .= " group by ct_idx having count(*) >= 10 order by count(*) desc) A ";
            $hot = $DB->select_query($query);
            if ($hot) {
                if ($tab_id == "hot") {
                    $count = $DB->fetch_query($count_query);
                    foreach ($hot as $h_row) {
                        $arr2['hot_list'][] = array(
                            "ct_idx" => $h_row['ct_idx'],
                            "ct_title" => $h_row['ct_title'],
                            "mt_nickname" => $h_row['mt_nickname'],
                            "ct_img1" => $ct_img_url . "/" . $h_row['ct_img1'] . "?cache=" . strtotime($h_row['ct_udate']),
                        );
                    }
                }
            }
            $item_count = trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

            $j = 0;
            for ($i = $item_count; $i <= $item_count + 20; $i++) {
                if ($arr2[$tab_id . '_list'][$i] != []) {
                    $arr['list'][] = $arr2[$tab_id . '_list'][$i];
                    $j++;
                }
            }
            if ($j == 0) {
                $arr['list'] = [];
            }
            $arr['count'] = (int)$count[0];
            $n_page = ceil($count[0] / 20);
            $arr['maxpage'] = (int)$n_page;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 커뮤니티 마이페이지', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '커뮤니티 마이페이지', $jwt);
            }
        } else {
            echo result_data("false", "닉네임 설정 및 카테고리 설정 필요", "");
        }
    } else {
        //좋아요 수가 10개 이상인 저번주 인기 게시글
        $query = "SELECT count(*) as cnt, ct_idx, ct_title, ct_img1, (SELECT mt_nickname FROM member_t WHERE idx=community_t.mt_idx) as mt_nickname FROM like_community_t left join community_t on community_t.idx = like_community_t.ct_idx
            where lct_wdate >= '" . $last_week['mon'] . " 00:00:00' and lct_wdate <= '" . $last_week['today'] . " 23:59:59'";
        $count_query = "select COUNT(ct_idx) AS cnt from (select like_community_t.ct_idx from  like_community_t left join community_t on community_t.idx = like_community_t.ct_idx
                    where lct_wdate >= '" . $last_week['mon'] . " 00:00:00' and lct_wdate <= '" . $last_week['today'] . " 23:59:59' ";
        if ($decoded_array['filter']) {
            $query .= " and ct_cate_idx = " . $decoded_array['filter'];
            $count_query .= " and ct_cate_idx = " . $decoded_array['filter'];
        }
        $query .= " group by ct_idx having cnt >= 10 order by cnt desc ";
        $count_query .= " group by ct_idx having count(*) >= 10 order by count(*) desc) A ";
        $hot = $DB->select_query($query);
        if ($hot) {
            $count = $DB->fetch_query($count_query);
            foreach ($hot as $h_row) {
                $arr2['hot_list'][] = array(
                    "ct_idx" => $h_row['ct_idx'],
                    "ct_title" => $h_row['ct_title'],
                    "mt_nickname" => $h_row['mt_nickname'],
                    "ct_img1" => $ct_img_url . "/" . $h_row['ct_img1']."?cache=".strtotime($h_row['ct_udate']),
                );
            }
        }
        $item_count = trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

        $j = 0;
        for ($i = $item_count; $i <= $item_count + 20; $i++) {
            if ($arr2['hot_list'][$i] != []) {
                $arr['list'][] = $arr2['hot_list'][$i];
                $j++;
            }
        }
        if ($j == 0) {
            $arr['list'] = [];
        }
        $arr['count'] = (int)$count[0];
        $n_page = ceil($count[0] / 20);
        $arr['maxpage'] = (int)$n_page;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 커뮤니티 비회원 마이페이지', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '커뮤니티 비회원 마이페이지', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>