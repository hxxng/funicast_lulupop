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
if($decoded_array['tt_cate_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_cate_idx', '');
    exit;
}
if($decoded_array['tt_title']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_title', '');
    exit;
}
if($decoded_array['tt_content']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_content', '');
    exit;
}
if($decoded_array['tt_sale_status']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_sale_status', '');
    exit;
}
if($decoded_array['tt_product_status']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_product_status', '');
    exit;
}
if($decoded_array['tt_exchange']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_exchange', '');
    exit;
}
if($decoded_array['tt_amount']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_amount', '');
    exit;
}
if($decoded_array['tt_price']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_price', '');
    exit;
}

$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);
if($row_m['mt_idx']) {
    if($decoded_array['tt_idx']) {
        $query = "select * from trade_t where mt_idx = ".$row_m['mt_idx']." and idx = ".$decoded_array['tt_idx'];
        $row = $DB->fetch_assoc($query);
        if($row['idx']) {
            $arr = array(
                "tt_cate_idx" => $decoded_array['tt_cate_idx'],
                "tt_title" => $decoded_array['tt_title'],
                "tt_content" => $decoded_array['tt_content'],
                "mt_idx" => $row_m['mt_idx'],
                "tt_status" => 1,
                "tt_sale_status" => $decoded_array['tt_sale_status'],
                "tt_product_status" => $decoded_array['tt_product_status'],
                "tt_exchange" => $decoded_array['tt_exchange'],
                "tt_amount" => $decoded_array['tt_amount'],
                "tt_price" => $decoded_array['tt_price'],
                "tt_hashtag" => $decoded_array['tt_hashtag'],
                "tt_udate" => "now()",
            );
            $DB->update_query("trade_t", $arr, " idx = ".$decoded_array['tt_idx']);

            $arr_tt_img = array();
            for($q=1;$q<=10;$q++) {
                $temp_img_txt = "tt_img".$q;
                if($decoded_array["tt_img".$q] == "") {
                    if($_FILES[$temp_img_txt]['name']) {
                        $file = $_FILES[$temp_img_txt]['tmp_name'];
                        $file_name = $_FILES[$temp_img_txt]['name'];
                        $file_size = $_FILES[$temp_img_txt]['size'];
                        $file_type = $_FILES[$temp_img_txt]['type'];

                        if($file_name!="") {
                            $arr_tt_img[$q] = "tt_img_".$decoded_array['tt_idx']."_".$q.".".get_file_ext($file_name);
                            upload_file($file, $arr_tt_img[$q], $ct_img_dir_a."/");
                            thumnail_width2($ct_img_dir_a."/".$arr_tt_img[$q], $arr_tt_img[$q], $ct_img_dir_a."/", "1000");
                        }
                    }
                } else {
                    $before_file = explode("url/images/uploads/", $decoded_array["tt_img".$q]);
                    $arr_tt_img[$q] = $before_file[1];
                }
            }

            if($arr_tt_img) {
                unset($arr_query);
                $arr_query = array(
                    "tt_img1" => $arr_tt_img[1],
                    "tt_img2" => $arr_tt_img[2],
                    "tt_img3" => $arr_tt_img[3],
                    "tt_img4" => $arr_tt_img[4],
                    "tt_img5" => $arr_tt_img[5],
                    "tt_img6" => $arr_tt_img[6],
                    "tt_img7" => $arr_tt_img[7],
                    "tt_img8" => $arr_tt_img[8],
                    "tt_img9" => $arr_tt_img[9],
                    "tt_img10" => $arr_tt_img[10],
                );
                $DB->update_query('trade_t', $arr_query, "idx = '".$decoded_array['tt_idx']."'");
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
            "tt_cate_idx" => $decoded_array['tt_cate_idx'],
            "tt_title" => $decoded_array['tt_title'],
            "tt_content" => $decoded_array['tt_content'],
            "mt_idx" => $row_m['mt_idx'],
            "tt_status" => 1,
            "tt_sale_status" => $decoded_array['tt_sale_status'],
            "tt_product_status" => $decoded_array['tt_product_status'],
            "tt_exchange" => $decoded_array['tt_exchange'],
            "tt_amount" => $decoded_array['tt_amount'],
            "tt_price" => $decoded_array['tt_price'],
            "tt_hashtag" => $decoded_array['tt_hashtag'],
            "tt_wdate" => "now()",
        );
        $DB->insert_query("trade_t", $arr);
        $_last_idx = $DB->insert_id();

        $arr_tt_img = array();
        for($q=1;$q<=10;$q++) {
            $temp_img_txt = "tt_img".$q;
            if($_FILES[$temp_img_txt]['name']) {
                $file = $_FILES[$temp_img_txt]['tmp_name'];
                $file_name = $_FILES[$temp_img_txt]['name'];
                $file_size = $_FILES[$temp_img_txt]['size'];
                $file_type = $_FILES[$temp_img_txt]['type'];

                if($file_name!="") {
                    $arr_tt_img[$q] = "tt_img_".$_last_idx."_".$q.".".get_file_ext($file_name);
                    upload_file($file, $arr_tt_img[$q], $ct_img_dir_a."/");
                    thumnail_width2($ct_img_dir_a."/".$arr_tt_img[$q], $arr_tt_img[$q], $ct_img_dir_a."/", "1000");
                }
            }
        }

        if($arr_tt_img) {
            unset($arr_query);
            $arr_query = array(
                "tt_img1" => $arr_tt_img[1],
                "tt_img2" => $arr_tt_img[2],
                "tt_img3" => $arr_tt_img[3],
                "tt_img4" => $arr_tt_img[4],
                "tt_img5" => $arr_tt_img[5],
                "tt_img6" => $arr_tt_img[6],
                "tt_img7" => $arr_tt_img[7],
                "tt_img8" => $arr_tt_img[8],
                "tt_img9" => $arr_tt_img[9],
                "tt_img10" => $arr_tt_img[10],
            );
            $DB->update_query('trade_t', $arr_query, "idx = '".$_last_idx."'");
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