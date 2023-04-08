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
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_assoc($query);

if($row_m['mt_idx']) {
    if($decoded_array['mt_nickname']) $arr['mt_nickname'] = $decoded_array['mt_nickname'];
    if($decoded_array['mt_name']) $arr['mt_name'] = $decoded_array['mt_name'];
    if($decoded_array['mt_hp']) $arr['mt_hp'] = $decoded_array['mt_hp'];
    if($decoded_array['mt_zip']) $arr['mt_zip'] = $decoded_array['mt_zip'];
    if($decoded_array['mt_add1']) $arr['mt_add1'] = $decoded_array['mt_add1'];
    if($decoded_array['mt_add2']) $arr['mt_add2'] = $decoded_array['mt_add2'];
    if($decoded_array['mt_birth']) $arr['mt_birth'] = $decoded_array['mt_birth'];
    $arr['mt_udate'] = "now()";

    if($arr != "") {
        $DB->update_query("member_t", $arr, " idx = ".$row_m['mt_idx']);
    }

    $arr_ct_img = array();
    $temp_img_txt = "mt_image";
    if($_FILES[$temp_img_txt]['name']) {
        $file = $_FILES[$temp_img_txt]['tmp_name'];
        $file_name = $_FILES[$temp_img_txt]['name'];
        $file_size = $_FILES[$temp_img_txt]['size'];
        $file_type = $_FILES[$temp_img_txt]['type'];

        if($file_name!="") {
            $arr_ct_img = "mt_image_".$row_m['mt_idx'].".".get_file_ext($file_name);
            upload_file($file, $arr_ct_img, $ct_img_dir_a."/");
            thumnail_width2($ct_img_dir_a."/".$arr_ct_img, $arr_ct_img, $ct_img_dir_a."/", "1000");
        }
    }

    if($arr_ct_img) {
        unset($arr_query);
        $arr_query = array(
            "mt_image" => $arr_ct_img,
        );
        $DB->update_query('member_t', $arr_query, "idx = '" . $row_m['mt_idx'] . "'");
    }

    $query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.idx = '".$row_m['mt_idx']."'";
    $row = $DB->fetch_assoc($query);

    $arr = $row;
    $arr['mt_image'] = $ct_img_url."/".$row['mt_image']."?cache=".strtotime($row['mt_udate']);
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 내정보 상세보기', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '내정보 상세보기', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>