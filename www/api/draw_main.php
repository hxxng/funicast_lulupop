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
    $query = "select *, (SELECT ct_img1 FROM catalog_t WHERE pt_idx=member_draw_t.pt_idx) as ct_img1, (SELECT ct_img2 FROM catalog_t WHERE pt_idx=member_draw_t.pt_idx) as ct_img2,
       (SELECT pt_title FROM product_t WHERE idx=pt_idx) as pt_title
        from member_draw_t where mt_idx = ".$row_m['mt_idx'];
    $list = $DB->select_query($query);

    if($list) {
        for($i=0; $i<6; $i++) {
            if($list[$i]['ct_img1']) {
                $ct_img = $ct_img_url."/".$list[$i]['ct_img1']."?cache=".strtotime($list[$i]['ct_udate']);
                $active = true;
            } else {
                $ct_img = null;
                $active = false;
            }
            $arr['list'][] = array(
                "pt_idx" => $list[$i]['pt_idx'],
                "pt_title" => $list[$i]['pt_title'],
                "ct_img" => $ct_img,
                "active" => $active,
            );
        }
    } else {
        for($i=0; $i<6; $i++) {
            $arr['list'][] = array(
                "pt_idx" => null,
                "pt_title" => null,
                "ct_img" => null,
                "active" => null,
            );
        }
    }

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 랜덤 뽑기', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '랜덤 뽑기', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>