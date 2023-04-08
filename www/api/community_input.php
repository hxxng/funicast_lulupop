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
if($decoded_array['ct_cate_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_cate_idx', '');
    exit;
}
if($decoded_array['ct_title']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_title', '');
    exit;
}
if($decoded_array['ct_content']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_content', '');
    exit;
}

$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);
if($row_m['mt_idx']) {
    if($decoded_array['ct_idx']) {
        $query = "select * from community_t where mt_idx = ".$row_m['mt_idx']." and idx = ".$decoded_array['ct_idx'];
        $row = $DB->fetch_assoc($query);
        if($row['idx']) {
//            if($decoded_array['ct_hashtag']) {
//                $hashtag = explode(" ", $decoded_array['ct_hashtag']);
//                foreach ($hashtag as $key) {
//                    if (substr($key, 0, 1) !== "#") {
//                        $ct_hashtag .= "#" . $key . " ";
//                    } else {
//                        $ct_hashtag .= $key . " ";
//                    }
//                }
//            }

            $arr = array(
                "ct_cate_idx" => $decoded_array['ct_cate_idx'],
                "ct_title" => $decoded_array['ct_title'],
                "ct_content" => $decoded_array['ct_content'],
                "ct_status" => 1,
                "ct_hashtag" => $decoded_array['ct_hashtag'],
                "ct_udate" => "now()",
            );
            $DB->update_query("community_t", $arr, " idx = ".$decoded_array['ct_idx']);

            $arr_ct_img = array();
            for($q=1;$q<=10;$q++) {
                $temp_img_txt = "ct_img".$q;
                if($decoded_array["ct_img".$q] == "") {
                    if($_FILES[$temp_img_txt]['name']) {
                        $file = $_FILES[$temp_img_txt]['tmp_name'];
                        $file_name = $_FILES[$temp_img_txt]['name'];
                        $file_size = $_FILES[$temp_img_txt]['size'];
                        $file_type = $_FILES[$temp_img_txt]['type'];

                        if($file_name!="") {
                            $arr_ct_img[$q] = "ct_img_".$decoded_array['ct_idx']."_".$q.".".get_file_ext($file_name);
                            upload_file($file, $arr_ct_img[$q], $ct_img_dir_a."/");
                            thumnail_width2($ct_img_dir_a."/".$arr_ct_img[$q], $arr_ct_img[$q], $ct_img_dir_a."/", "1000");
                        }
                    }
                } else {
                    $before_file = explode("url/images/uploads/", $decoded_array["ct_img".$q]);
                    $arr_ct_img[$q] = $before_file[1];
                }
            }

            if($arr_ct_img) {
                unset($arr_query);
                $arr_query = array(
                    "ct_img1" => $arr_ct_img[1],
                    "ct_img2" => $arr_ct_img[2],
                    "ct_img3" => $arr_ct_img[3],
                    "ct_img4" => $arr_ct_img[4],
                    "ct_img5" => $arr_ct_img[5],
                    "ct_img6" => $arr_ct_img[6],
                    "ct_img7" => $arr_ct_img[7],
                    "ct_img8" => $arr_ct_img[8],
                    "ct_img9" => $arr_ct_img[9],
                    "ct_img10" => $arr_ct_img[10],
                );
                $DB->update_query('community_t', $arr_query, "idx = '".$decoded_array['ct_idx']."'");
            }
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 게시글 수정', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '게시글 수정', $jwt);
            }
        } else {
            echo result_data("false", "수정할 게시글이 존재하지 않습니다.", "");
            exit;
        }
    } else {
        $arr = array(
            "ct_cate_idx" => $decoded_array['ct_cate_idx'],
            "ct_title" => $decoded_array['ct_title'],
            "ct_content" => $decoded_array['ct_content'],
            "mt_idx" => $row_m['mt_idx'],
            "ct_status" => 1,
            "ct_hashtag" => $decoded_array['ct_hashtag'],
            "ct_wdate" => "now()",
        );
        $DB->insert_query("community_t", $arr);
        $_last_idx = $DB->insert_id();

        $arr_ct_img = array();
        for($q=1;$q<=10;$q++) {
            $temp_img_txt = "ct_img".$q;
            if($_FILES[$temp_img_txt]['name']) {
                $file = $_FILES[$temp_img_txt]['tmp_name'];
                $file_name = $_FILES[$temp_img_txt]['name'];
                $file_size = $_FILES[$temp_img_txt]['size'];
                $file_type = $_FILES[$temp_img_txt]['type'];

                if($file_name!="") {
                    $arr_ct_img[$q] = "ct_img_".$_last_idx."_".$q.".".get_file_ext($file_name);
                    upload_file($file, $arr_ct_img[$q], $ct_img_dir_a."/");
                    thumnail_width2($ct_img_dir_a."/".$arr_ct_img[$q], $arr_ct_img[$q], $ct_img_dir_a."/", "1000");
                }
            }
        }
        if($arr_ct_img) {
            unset($arr_query);
            $arr_query = array(
                "ct_img1" => $arr_ct_img[1],
                "ct_img2" => $arr_ct_img[2],
                "ct_img3" => $arr_ct_img[3],
                "ct_img4" => $arr_ct_img[4],
                "ct_img5" => $arr_ct_img[5],
                "ct_img6" => $arr_ct_img[6],
                "ct_img7" => $arr_ct_img[7],
                "ct_img8" => $arr_ct_img[8],
                "ct_img9" => $arr_ct_img[9],
                "ct_img10" => $arr_ct_img[10],
            );
            $DB->update_query('community_t', $arr_query, "idx = '".$_last_idx."'");
        }
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 게시글 등록', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '게시글 등록', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>