<?
    include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	$_act = $_REQUEST['act'];

    if($_POST['act']=='popup_image') {
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
					select * from review_product_t
					where idx = '".$_POST['idx']."'
				";
            $row = $DB->fetch_query($query);

            $rpt_image_t = "rpt_img".$_POST['num'];
            if($row[$rpt_image_t]) {
                ?>
                <div class="m-2"><img src="<?=$ct_img_url."/".$row[$rpt_image_t]?>" onerror="this.src='<?=$ct_no_img_url?>'" class="product-swipe" alt="<?=$_POST['num']?>"></div>
                <?
            }
            ?>
        </div>
    </div>
<?
    } else if($_POST['act']=='content_view') {
		$query = "
			select * from review_product_t
			where idx = '".$_POST['rpt_idx']."'
		";
		$row = $DB->fetch_query($query);
?>
	<div class="modal-header">
		<h5 class="modal-title" id="staticBackdropLabel">등록리뷰 상세보기</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
        <div>
            <?
                for($i=1; $i<6; $i++)
                {
                    if($row['rpt_img'.$i] != "")
                    {
                        echo '<img class="mb-2" style="height: 100px;" src="'.STATIC_HTTP.'/images/uploads/'.$row['rpt_img'.$i].'" /><br/>';
                    }
                }
            ?>
        </div>
        <hr>
		<p><?=nl2br($row['rpt_content'])?></p>
	</div>
<?
    } else if($_POST['act']=='delete') {
        $query = "select * from review_product_t where idx = ".$_POST['idx'];
        $row = $DB->fetch_query($query);
        for($i=1; $i<=5; $i++)
        {
            if($row['rpt_img'.$i] != null)
            {
                @unlink($ct_img_dir_a."/".$row['rpt_img'.$i]);
            }
        }
		$DB->del_query('review_product_t', " idx = '".$_POST['idx']."'");

		echo "Y";
	} else if($_POST['act']=='select_delete') {
        for($i=0; $i<count($_REQUEST['idx']); $i++)
        {
            $query = "select * from review_product_t where idx = ".$_REQUEST['idx'][$i];
            $row = $DB->fetch_query($query);
            for($j=1; $j<=5; $j++)
            {
                if($row['rpt_img'.$j] != null)
                {
                    @unlink($ct_img_dir_a."/".$row['rpt_img'.$j]);
                }
            }
            $DB->del_query('review_product_t', " idx = '".$_REQUEST['idx'][$i]."'");
        }
        echo json_encode(array('result' => '_ok', 'msg'=>'삭제 되었습니다.'));
    }

    include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>