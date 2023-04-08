<?
    include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	$_act = $_REQUEST['act'];
    $type = $_REQUEST['type'];

	if($_POST['act']=='content_view') {
		$query = "
			select * from pushnotification_t
			where idx = '".$_POST['ft_idx']."'
		";
		$row = $DB->fetch_query($query);
?>
<script>
    $('#modal-default-size').css('max-width', '900px');
    if('<?=$_POST['ft_idx']?>' == "add")
    {
        $("#act").val("add");
    }
</script>
	<div class="modal-header">
		<h5 class="modal-title" id="staticBackdropLabel">알림 추가/수정</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
        <form method="post" name="frm_form" id="frm_form" action="./push_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm">
            <input type="hidden" name="act" id="act" value="update" />
            <input type="hidden" name="ft_idx" id="ft_idx" value="<?=$row['idx']?>" />
            <table class="table">
                <tbody>
                <tr>
                    <td class="text-center" style="width: 150px;background-color: #fafafa;">
                        발송일
                    </td>
                    <td colspan="3">
                        <input type="text" name="pst_sdate" class="form-control" value="<?php echo ($row['pst_sdate']);?>">
                        * 없으면 즉시 발송 등록 예)2022-04-25 15:19:23
                    </td>
                </tr>
                <tr>
                    <td class="text-center" style="width: 150px;background-color: #fafafa;">
                        제목
                    </td>
                    <td colspan="3">
                        <input type="text" name="pst_title" class="form-control" value="<?php echo $row['pst_title'];?>">
                    </td>
                </tr>
                <tr>
                    <td class="text-center" style="width: 150px;background-color: #fafafa;">
                        짧은내용
                    </td>
                    <td colspan="3">
                    <input type="text" name="pst_shot_memo" class="form-control" value="<?php echo $row['pst_shot_memo'];?>">
                    </td>
                </tr>
                <tr>
                    <td class="text-center" style="width: 150px;background-color: #fafafa;">
                        내용
                    </td>
                    <td colspan="3" class="text-center">
                        <textarea name="pst_content" id="pst_content" style="height: 200px;" class="form-control"><?=$row['pst_content']?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php if($row['message_status'] === null):?>
			<button type="submit" class="btn btn-primary" style="margin-left: 350px;">저장</button>
            <?php endif;?>
			<button type="button" onclick="$('#modal-default').modal('hide');" class="btn btn-light">취소</button>
		</form>

		<script type="text/javascript">
			function frm_form_chk(f) {
                if(f.pst_title.value=="") {
                    alert("제목을 입력해주세요.");
                    f.pst_title.focus();
                    return false;
                }
				if(f.pst_shot_memo.value=="") {
					alert("짧은 내용을 입력해주세요.");
					f.pst_shot_memo.focus();
					return false;
				}
                if(f.pst_content.value=="") {
					alert("내용을 입력해주세요.");
					f.pst_content.focus();
					return false;
				}
				$('#splinner_modal').modal('toggle');
				return true;
			}
		</script>
	</div>
<?
    } else if($_POST['act']=='add') {
        unset($arr_query);
        $pst_sdate = ($_POST['pst_sdate']) ? $_POST['pst_sdate'] : date('Y-m-d H:i:s');
        $arr_query = array(
            "pst_sdate" => $pst_sdate,
            "pst_title" => $_POST['pst_title'],
            "pst_shot_memo" => $_POST['pst_shot_memo'],
            "pst_content" => $_POST['pst_content'],
            "pst_type" => 1,
            "pst_wdate" => "now()",
        );

        $DB->insert_query('pushnotification_t', $arr_query);
        $_last_ft_idx = $DB->insert_id();
        if($_last_ft_idx){            
            if(!$_POST['pst_sdate']){                
                proc_noti('admin', '', '', $_last_ft_idx);
            }        
        }
        p_alert('등록되었습니다.');
    } else if($_POST['act']=='update') {
		unset($arr_query);
        $pst_sdate = ($_POST['pst_sdate']) ? $_POST['pst_sdate'] : date('Y-m-d H:i:s');
        $arr_query = array(
            "pst_sdate" => $pst_sdate,
            "pst_title" => $_POST['pst_title'],
            "pst_shot_memo" => $_POST['pst_shot_memo'],
            "pst_content" => $_POST['pst_content']            
        );
		$where_query = "idx = '".$_POST['ft_idx']."'";

		$DB->update_query('pushnotification_t', $arr_query, $where_query);
		p_alert('수정되었습니다.');
    } else if($_POST['act']=='delete') {
		$DB->del_query('pushnotification_t', " idx = '".$_POST['idx']."'");

		echo "Y";
	} else if($_POST['act']=='select_delete') {
        for($i=0; $i<count($_REQUEST['idx']); $i++)
        {
            $DB->del_query('pushnotification_t', " idx = '".$_REQUEST['idx'][$i]."'");
        }
        echo json_encode(array('result' => '_ok', 'msg'=>'삭제 되었습니다.'));
    }

    include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>