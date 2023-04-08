<?
	include "./head_inc.php";
	$chk_menu = "8";
    $list_url_t = "review_product_list.php";

	$chk_post_code = 'Y';
	include "./head_menu_inc.php";

    $query = "
        select *, a1.idx as rpt_idx, (select mt_nickname from member_t where idx=mt_idx) as mt_nickname from review_product_t a1
        where a1.idx = '".$_GET['rpt_idx']."'
    ";
    $row = $DB->fetch_query($query);

    $_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
?>
<style>.tab-content{border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3}.col-form-label{text-align:center;background-color:#f7f7f7;}.tab-content{padding:0;}.row{margin-right:0;}</style>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <div class="card-body">
                    <div class="row">
                        <h4 class="card-title">후기 상세보기</h4>
                    </div>

                    <div class="tab-content">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">작성자</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=$row['mt_nickname']?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">작성일</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=DateType($row['rpt_wdate'],1)?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" style="border-top:none;">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class=" col-form-label" style="width: 191px;">별점</label>
                                    <div class="col-sm-9 custom-control">
                                        <span>
                                        <?
                                        if($row['rpt_score']) {
                                            $star = "";
                                            for($i=0;$i<$row['rpt_score'];$i++) {
                                                $star .= "★";
                                            }
                                            echo $star;
                                        }
                                        ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-form-label" style="width: 191px;height: 173px;line-height: 130px;">첨부사진</label>
                                    <div class="col-sm-10 custom-control">
                                        <li class="list-group-item" style="border: none;">
                                            <div class="form-group row align-items-center mb-0">
                                                <div class="col-sm-12 row">
                                                    <? for($q=1;$q<=5;$q++) {  if($row['rpt_img'.$q] != "") {?>
                                                        <div class="media ">
                                                            <a href="javascript:;" onclick="f_popup_image('<?=$row['rpt_idx']?>', '<?=$q?>')"><img src="<?=$ct_img_url."/".$row['rpt_img'.$q].'?v='.time()?>" onerror="this.src='<?=$ct_no_img_url?>'" class="align-self-center mr-3" alt="<?=$q?>" style="width: 150px; height: 150px;border-radius: 10px;"></a>
                                                            <div class="media-body">
                                                            </div>
                                                        </div>
                                                    <? } } ?>
                                                </div>
                                            </div>
                                        </li>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-form-label" style="height: 200px;line-height: 160px;width: 191px;">상세내용</label>
                                    <div class="custom-control" style="width: 1338px;height: 200px;overflow-y: auto;">
                                        <span class="p-1" style="background-color: #fff;border: none;"><?=$row['rpt_content']?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="p-3 mt-3 text-center">
                    <input type="button" value="목록" onclick="location.href='./<?=$list_url_t?>?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
                </p>
			</div>
		</div>
	</div>
</div>
<script>
    function f_popup_image(idx, num) {
        $.post('./review_product_update.php', {act: 'popup_image', idx: idx, num: num}, function (data) {
            if(data) {
                $('#modal-default-content').html(data);

                $('#product-swiper').slick({
                    dots: true,
                    infinite: false,
                    speed: 300,
                    variableWidth: true,
                    slidesToShow: 1,
                });

                $('#modal-default').modal();
            }
        });

        return false;
    }
</script>
<?
	include "./foot_inc.php";
?>