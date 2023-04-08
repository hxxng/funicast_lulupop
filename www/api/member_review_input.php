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
if($decoded_array['pt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. pt_idx', '');
    exit;
}
if($decoded_array['ot_pcode']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_pcode', '');
    exit;
}
if($decoded_array['rpt_content']=="") {
    echo result_data('false', '잘못된 접근입니다. rpt_content', '');
    exit;
}
if($decoded_array['rpt_score']=="") {
    echo result_data('false', '잘못된 접근입니다. rpt_score', '');
    exit;
}

$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);
if($row_m['mt_idx']) {
    $arr = array(
        "mt_idx" => $row_m['mt_idx'],
        "pt_idx" => $decoded_array['pt_idx'],
        "rpt_content" => $decoded_array['rpt_content'],
        "ot_pcode" => $decoded_array['ot_pcode'],
        "rpt_score" => $decoded_array['rpt_score'],
        "rpt_wdate" => "now()",
    );
    $DB->insert_query("review_product_t", $arr);
    $_last_idx = $DB->insert_id();

    $arr_rpt_img = array();
    for($q=1;$q<=5;$q++) {
        $temp_img_txt = "rpt_img".$q;
        if($_FILES[$temp_img_txt]['name']) {
            $file = $_FILES[$temp_img_txt]['tmp_name'];
            $file_name = $_FILES[$temp_img_txt]['name'];
            $file_size = $_FILES[$temp_img_txt]['size'];
            $file_type = $_FILES[$temp_img_txt]['type'];

            if($file_name!="") {
                $arr_rpt_img[$q] = "rpt_img_".$_last_idx."_".$q.".".get_file_ext($file_name);
                upload_file($file, $arr_rpt_img[$q], $ct_img_dir_a."/");
                thumnail_width2($ct_img_dir_a."/".$arr_rpt_img[$q], $arr_rpt_img[$q], $ct_img_dir_a."/", "1000");
            }
        }
    }

    if($arr_rpt_img) {
        unset($arr_query);
        $arr_query = array(
            "rpt_img1" => $arr_rpt_img[1],
            "rpt_img2" => $arr_rpt_img[2],
            "rpt_img3" => $arr_rpt_img[3],
            "rpt_img4" => $arr_rpt_img[4],
            "rpt_img5" => $arr_rpt_img[5],
        );
        $DB->update_query('review_product_t', $arr_query, "idx = '".$_last_idx."'");
    }
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 후기 등록', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '후기 등록', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>