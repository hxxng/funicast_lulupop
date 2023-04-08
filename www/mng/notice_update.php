<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
echo '<script type="text/javascript" src="'.STATIC_HTTP.'/js/base.js"></script>';

$_act = $_REQUEST['act'];

if($_POST['act']=='update') {
    unset($arr_query);
    $arr_query = array(
        "nt_title" => $_POST['nt_title'],
        "nt_content" => $_POST['nt_content'],
        "nt_udate" => "now()",
    );

    $where_query = "idx = '".$_POST['nt_idx']."'";

    $DB->update_query('notice_t', $arr_query, $where_query);

    p_alert('수정되었습니다.');
} else if($_POST['act']=='delete') {
    if($_POST['nt_idx'] > 0) {
        $DB->del_query("notice_t", " idx = ".$_POST['nt_idx']);
        p_alert('삭제되었습니다.', "./notice_list.php");
    }
} else if($_POST['act']=='input') {

    unset($arr_query);
    $arr_query = array(
        "nt_title" => $_POST['nt_title'],
        "nt_content" => $_POST['nt_content'],
        "nt_wdate" => "now()",
    );

    $DB->insert_query('notice_t', $arr_query);
    $idx = $DB->insert_id();

    $query = "select * from member_t where mt_level in (3,5)";
    $list = $DB->select_query($query);

    if($list) {
        foreach ($list as $row) {
            if($row['mt_pushing'] == "Y" || $row['mt_pushing1'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }
            $token_list = array($row['mt_fcm']);
            $message = "새로운 공지사항을 확인해 주세요!";
            $title = "룰루팝 공지사항";

            $op_idx .= $row['idx'].",";

            send_notification2($token_list, $title, $message, "MyPage_Notice_Detail_Page", $idx, $chk);
        }
        unset($arr_query);
        $plt_set = array(
            'plt_title'=>$title,
            'plt_content'=>$message,
            'plt_table'=>"notice_t",
            'plt_type'=> 1,
            'plt_index'=>$idx,
            'mt_idx'=>1,
            'op_idx'=>$op_idx,
            'plt_wdate'=>'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);
    }

    p_alert('등록되었습니다.');
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>