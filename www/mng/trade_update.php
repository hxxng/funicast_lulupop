<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act'] == "del_comment") {
        $DB->update_query("comment_t", array("ct_status" => 3), " idx = ".$_POST['idx']);
        echo json_encode(array('result' => '_ok', 'msg' => '관리자의 권한으로 삭제처리되었습니다.'));
    } else if($_POST['act']=='popup_image') {
        ?>
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">이미지</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="slider" id="product-swiper">
                <?
                $query = "
					select * from trade_t
					where idx = '".$_POST['idx']."'
				";
                $row = $DB->fetch_query($query);

                $ct_image_t = "tt_img".$_POST['num'];
                if($row[$ct_image_t]) {
                    ?>
                    <div class="m-2"><img src="<?=$ct_img_url."/".$row[$ct_image_t]?>" onerror="this.src='<?=$ct_no_img_url?>'" class="product-swipe" alt="<?=$_POST['num']?>"></div>
                    <?
                }
            ?>
            </div>
        </div>
<?
    } else if($_POST['act']=='chg_status') {
        $DB->update_query("trade_t", array("tt_status" => $_POST['tt_status']), " idx = ".$_POST['idx']);
        p_alert("저장되었습니다");
    }

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>