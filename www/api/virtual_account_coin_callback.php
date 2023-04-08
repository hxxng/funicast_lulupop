<?php
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";

$postData = file_get_contents('php://input');
$json = json_decode($postData);

if ($json->status == 'DONE') {
    // handle deposit result
    $ct_code = $json->orderId;

    $test = "select * from coin_t where ct_code = '".$ct_code."'";
    $coin = $DB->fetch_assoc($test);

    $member = $DB->fetch_assoc("select * from member_t where idx = ".$coin['mt_idx']);

    $bonus = 0;
    for($i=10000; $i<=$coin['ct_price']; $i+=5000) {
        $bonus++;
        if($i == 100000) {
            $bonus = 20;
            break;
        }
    }

    $arr['ct_status'] = 2;
    $arr['ct_pdate'] = "now()";
    $arr['ct_amount'] = $coin['ct_amount'] + $bonus;
    $DB->update_query("coin_t", $arr, " idx = ".$coin['idx']);

    $DB->update_query("member_t", array("mt_coin" => (int)$member['mt_coin'] + (int)$coin['ct_amount'] + (int)$bonus), " idx = ".$member['idx']);

    //푸시발송
    if($member['mt_pushing'] == "Y" || $member['mt_pushing2'] == "Y") {
        $chk = "Y";
    } else {
        $chk = "N";
    }

    $result = new stdClass();
    $result->ct_amount = (int)$coin['ct_amount']."코인 충전";
    $result->bonus = $bonus;
    $result->ct_price = (int)$coin['ct_price'];
    $result->mt_coin = (int)$member['mt_coin'] + (int)$coin['ct_amount'] + $bonus;

    $token_list = array($member['mt_fcm']);
    $message = "입금완료 되었습니다.";
    $title = "룰루팝";


    $message_status = send_notification2($token_list, $title, $message, "Random_Coin_Payment_Finish_Page", json_encode($result), $chk);

    if ($message_status) {
        unset($arr_query);
        $plt_set = array(
            'plt_title'=>$title,
            'plt_content'=>$message,
            'plt_table'=>"coin_t_pay",
            'plt_type'=> 2,
            'plt_index'=>json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK),
            'mt_idx'=> 1,
            'op_idx'=>$member['idx'],
            'plt_wdate'=>'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);
    }
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);
?>

