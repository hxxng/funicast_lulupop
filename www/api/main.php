<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/http.class.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

$arr = array();
//메인비주얼
$query = "select * from main_visual_t where idx = 1";
unset($mvt_list);
$mvt_list = $DB->fetch_assoc($query);
$mvt_count = 0;
for($i=1; $i<=6; $i++) {
    if($mvt_list['mvt_img'.$i] != "" && $mvt_list['mvt_url'.$i] != "") {
        $arr["main_visual"][] = array(
            'mvt_url' => $mvt_list['mvt_url'.$i],
            'mvt_img' => $ct_img_url.'/'.$mvt_list['mvt_img'.$i]."?cache=".strtotime($mvt_list['mvt_udate']),
        );
        $mvt_count++;
    }
}
if($mvt_count == 0) {
    $arr["main_visual"] = [];
}

//NEW 룰루팝
$query = "
    select *, idx as pt_idx from product_t a1 where pt_new_chk = 'Y' and pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N')
";
unset($new_list);
$new_list = $DB->select_query($query);

if($new_list) {
    foreach($new_list as $row) {
        $arr["new"][] = array(
            'pt_idx' => $row['pt_idx'],
            'pt_title' => $row['pt_title'],
            'pt_new_date' => $row['pt_new_date'],
            'pt_new_img' => $ct_img_url.'/'.$row['pt_new_img']."?cache=".strtotime($row['pt_udate']),
        );
    }
    $new = array();
    foreach ( $arr["new"] as $k=>$v ) $new[floor($k/2)]['item'][] = $v;
}
$arr['new'] = $new;

//hot한 상품
$query = "
    select *, idx as pt_idx from product_t a1 where pt_best_chk = 'Y' and pt_show = 'Y' and pt_sale_now = 'Y'
";

unset($hot_list);
$hot_list = $DB->select_query($query);

if($hot_list) {
    foreach($hot_list as $row) {
        $arr["hot"][] = array(
            'pt_idx' => $row['pt_idx'],
            'pt_title' => $row['pt_title'],
            'pt_selling_price' => (int)$row['pt_selling_price'],
            'pt_sale_chk' => $row['pt_sale_chk'],
            'pt_discount_per' => (int)$row['pt_discount_per'],
            'pt_price' => (int)$row['pt_price'],
            'pt_image1' => $ct_img_url.'/'.$row['pt_image1']."?cache=".strtotime($row['pt_udate']),
        );
    }
}
$http = new http;
//룰루팝 movies
$query = "select * from main_visual_t where idx = 1";
unset($mvt_list2);
$mvt_list2 = $DB->fetch_assoc($query);
$mvt_count = 0;
for($i=1; $i<=3; $i++) {
    if($mvt_list2['mvt_movies_img'.$i] != "" && $mvt_list2['mvt_movies_url'.$i] != "") {
        $id = str_replace("https://www.youtube.com/watch?v=", "", $mvt_list2['mvt_movies_url'.$i]);

        $youtube_url = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=".$id."&format=json";
        $getPaymentData = $http->GetMethodData($youtube_url, '', '', '', true, '', '');
        $getPaymentDataJson = json_decode($getPaymentData, true);

        $arr["movies"]['movie_'.$i] = array(
            'title' => $getPaymentDataJson['title'],
            'mvt_movies_url' => $id,
            'mvt_movies_img' => $ct_img_url.'/'.$mvt_list2['mvt_movies_img'.$i]."?cache=".strtotime($mvt_list2['mvt_udate']),
        );
        $mvt_count++;
    } else {
        $arr["movies"]['movie_'.$i] = null;
    }
}
if($mvt_count == 0) {
    $arr["movies"] = [];
}

//커뮤니티
//좋아요 수가 10개 이상인 저번주 인기 게시글
$query = "SELECT ADDDATE( CURDATE(), - WEEKDAY(CURDATE()) + 0 ) AS mon, CURDATE() as today";
$last_week = $DB->fetch_assoc($query);

$query = "SELECT count(*) as cnt, ct_idx, ct_title, ct_img1, (SELECT mt_nickname FROM member_t WHERE idx=community_t.mt_idx) as mt_nickname FROM like_community_t left join community_t on community_t.idx = like_community_t.ct_idx
            where lct_wdate >= '" . $last_week['mon'] . " 00:00:00' and lct_wdate <= '" . $last_week['today'] . " 23:59:59'
            and community_t.mt_idx not in (SELECT mt_idx FROM hide_t) ";
$query .= " group by ct_idx having cnt >= 10 order by cnt desc limit 9";
$community_list = $DB->select_query($query);
if($community_list) {
    foreach($community_list as $row) {
        $arr["community"][] = array(
            'ct_idx' => $row['ct_idx'],
            'ct_title' => $row['ct_title'],
            'mt_nickname' => $row['mt_nickname'],
            'ct_img1' => $ct_img_url.'/'.$row['ct_img1']."?cache=".strtotime($row['ct_udate']),
        );
    }
    $community = array();
    foreach ( $arr["community"] as $k=>$v ) $community[floor($k/3)]['item'][] = $v;
} else {
    $community = [];
}
$arr['community'] = $community;


$payload['data'] = $arr;

if($decoded_array['debug_jwt']==DEBUG_JWT) {
    echo result_data('true', '[debug] 쇼핑몰 메인', $payload);
}else {
    $jwt = JWT::encode($payload, $secret_key);
    echo result_data('true', '쇼핑몰 메인', $jwt);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>
