<?
	include "./head_inc.php";
	$chk_menu = "5";
    $list_url_t = "trade_list.php";

	$chk_post_code = 'Y';
	include "./head_menu_inc.php";

    $query = "
        select *, a1.idx as tt_idx, (select mt_nickname from member_t where idx=mt_idx) as mt_nickname, (SELECT pc_name FROM product_category_t WHERE pc_depth = 0 and idx=tt_cate_idx) as pc_name from trade_t a1
        where a1.idx = '".$_GET['tt_idx']."'
    ";
    $row = $DB->fetch_query($query);
	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pg=".$_GET['pg'];
?>
<style>.tab-content {border-bottom:1px solid #f3f3f3}.tab-content{border-top:1px solid #f3f3f3}.col-form-label{text-align:center;background-color:#f7f7f7;}.tab-content{padding:0;}.row{margin-right:0;}</style>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <form method="post" name="frm_form" id="frm_form" action="./trade_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm">
                    <input type="hidden" name="act" value="chg_status" />
                    <input type="hidden" name="idx" value="<?=$row['tt_idx']?>" />
                    <div class="card-body">
                        <div class="row">
                            <h4 class="card-title">중고거래 게시글 페이지</h4>
                            <div class="col-sm-5 ml-4">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="normal" name="tt_status" <? if($row['tt_status'] == 1) echo "checked=''";?> value="1" class="custom-control-input">
                                    <label class="custom-control-label" for="normal">정상</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="blind" name="tt_status" <? if($row['tt_status'] == 2) echo "checked=''";?> value="2" class="custom-control-input">
                                    <label class="custom-control-label" for="blind">블라인드</label>
                                </div>
                            </div>
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
                                        <span><?=DateType($row['tt_wdate'],1)?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" style="border-top:none;">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">상태값</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><? if($row['tt_sale_status'] == 1) echo "판매중"; else echo "판매완료";?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">판매수량</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=number_format($row['tt_amount'])?>개</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" style="border-top:none;">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">제품상태</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><? if($row['tt_product_status'] == 1) echo "새상품"; else echo "중고상품";?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">교환여부</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><? if($row['tt_exchange'] == 'Y') echo "교환가능"; else echo "교환불가";?></span>
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
                                    <label for="mt_id" class="col-form-label" style="width: 191px;">타이틀</label>
                                    <div class="col-sm-10 custom-control">
                                        <span><?=$row['tt_title']?></span>
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
                                                    <? for($q=1;$q<=$pt_image_num;$q++) {  if($row['tt_img'.$q] != "") {?>
                                                        <div class="media ">
                                                            <a href="javascript:;" onclick="f_popup_image('<?=$row['idx']?>', '<?=$q?>')"><img src="<?=$ct_img_url."/".$row['tt_img'.$q].'?v='.time()?>" onerror="this.src='<?=$ct_no_img_url?>'" class="align-self-center mr-3" alt="<?=$q?>" style="width: 150px; height: 150px;border-radius: 10px;"></a>
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
                                        <span class="p-1" style="background-color: #fff;border: none;"><?=$row['tt_content']?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?
                    $query = "select comment_t.*, mt_id, mt_nickname 
                    from comment_t left join member_t on member_t.idx = comment_t.mt_idx and mt_level = 3 
                    where ct_table = 'trade_t' and mt_id is not null and ct_idx = ".$row['idx']." order by IF(ISNULL(ct_parent_idx), comment_t.idx, ct_parent_idx) ";
                    $list = $DB->select_query($query);
                    $count = $DB->count_query($query);
                    ?>
                    <h4 class="card-title">댓글 <?=number_format($count)?>개 <? if($count > 0) echo '<span onclick="set_comment_area(this)" style="cursor: pointer;">(접기)</span>'; ?></h4>
                    <div id="comment_area">
                        <?
                        if($list) {
                            foreach ($list as $row) {
                                if($row['ct_parent_idx'] == "") {
                                    $stop = "N";
                                    $ct_content = $row['ct_content'];
                                    if($row['ct_status'] == 3) {
                                        $count = $DB->count_query("select * from comment_t where ct_parent_idx = ".$row['idx']);
                                        if($count > 0) {
                                            $ct_content = "관리자에 의해 삭제된 댓글입니다.";
                                        } else {
                                            $stop = "Y";
                                        }
                                    } else if($row['ct_status'] == 2) {
                                        $count = $DB->count_query("select * from comment_t where ct_parent_idx = ".$row['idx']);
                                        if($count > 0) {
                                            $ct_content = "신고처리된 댓글입니다.";
                                        } else {
                                            $stop = "Y";
                                        }
                                    }
                                    if($stop == "N") {
                                        ?>
                                        <div class="tab-content">
                                            <div class="row">
                                                <div class="col-sm-11">
                                                    <div class="row align-items-center" style="padding-left: 10px;">
                                                        <label for="mt_id" class="col-form-label" style="width: 191px;"><?=$row['mt_nickname'] == "" ? $row['mt_id'] : $row['mt_nickname']?></label>
                                                        <div class="custom-control" style="width: 976px;">
                                                            <span><?=$ct_content?></span>
                                                        </div>
                                                        <div class="col-sm-2 custom-control">
                                                            <span>좋아요 </span>
                                                            <span class="mr-2 ml-1"><?=number_format($row['ct_like'])?> </span>
                                                            <span><?=DateType($row['ct_wdate'],1)?></span>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-sm-1">
                                                    <div class="custom-control text-right mt-1">
                                                        <input type="button" class="btn btn-outline-danger" onclick="del_comment('<?=$row['idx']?>')" value="삭제" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?
                                    }
                                } else {
                                    ?>
                                    <div class="tab-content" style="margin-left:191px;">
                                        <div class="row">
                                            <div class="col-sm-11">
                                                <div class="row align-items-center" style="padding-left: 10px;">
                                                    <label for="mt_id" class="col-form-label" style="width: 191px;background-color: #C0C0C0;">└ <?=$row['mt_nickname'] == "" ? $row['mt_id'] : $row['mt_nickname']?></label>
                                                    <div class=" custom-control" style="width: 786px;">
                                                        <span><?=$row['ct_content']?></span>
                                                    </div>
                                                    <div class="col-sm-2 custom-control">
                                                        <span>좋아요 </span>
                                                        <span class="mr-2 ml-1"><?=number_format($row['ct_like'])?> </span>
                                                        <span><?=DateType($row['ct_wdate'],1)?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-1">
                                                <div class="custom-control text-right mt-1" style="padding-left: 0.5rem;">
                                                    <input type="button" class="btn btn-outline-danger" onclick="del_comment('<?=$row['idx']?>')" value="삭제">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        <?
                                }
                            }
                        }
                        ?>
                    </div>                </div>
                <p class="p-3 mt-3 text-center">
                    <input type="submit" value="저장" class="btn btn-info" />
                    <input type="button" value="목록" onclick="location.href='./<?=$list_url_t?>?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
                </p>
			</div>
		</div>
	</div>
</div>
<script>
    function del_comment(idx) {
        $.ajax({
            type: 'post',
            url: './community_update.php',
            dataType: 'json',
            data: { act : 'del_comment', idx : idx},
            success: function(d,s) {
                if(d.result=='_ok'){
                    alert(d['msg']);
                    location.reload();
                }
            },
            cache: false
        });
    }
    function f_popup_image(idx, num) {
        $.post('./trade_update.php', {act: 'popup_image', idx: idx, num: num}, function (data) {
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
    function set_comment_area(e) {
        if($(e).text() == "(접기)") {
            $(e).text('(펼치기)');
            $("#comment_area").hide();
        } else {
            $(e).text('(접기)');
            $("#comment_area").show();
        }
    }
</script>
<?
	include "./foot_inc.php";
?>