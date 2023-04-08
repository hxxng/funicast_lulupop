<?
	include "./head_inc.php";
	$chk_menu = "7";
    $chk_sub_menu = '1';
    $list_url_t = "coin_list.php";

	include "./head_menu_inc.php";

    $query = "
        select *, a1.idx as ct_idx, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname from coin_t a1
        where a1.idx = '".$_GET['ct_idx']."'
    ";
    $row = $DB->fetch_query($query);

	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pg=".$_GET['pg'];
?>
<style>.tab-content {border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3}.col-form-label{text-align:center;background-color:#f7f7f7;}.tab-content{padding:0;}.row{margin-right:0;}</style>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <form method="post" name="frm_form" id="frm_form" action="./coin_update.php" target="hidden_ifrm">
                <input type="hidden" name="act" id="act" value="update" />
                <input type="hidden" name="ct_idx" id="ct_idx" value="<?=$_GET['ct_idx']?>" />
                <input type="hidden" name="mt_idx" id="mt_idx" value="<?=$row['mt_idx']?>" />
                <div class="card-body">
                    <h4 class="card-title">코인 결제 상세페이지</h4>
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row align-items-center">
                                    <label for="mt_id" class="col-sm-3 col-form-label">결제자</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=$row['mt_nickname']?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center">
                                    <label for="mt_id" class="col-sm-3 col-form-label">결제일</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=DateType($row['ct_pdate'],1)?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" style="border-top:none;">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row align-items-center">
                                    <label for="mt_id" class="col-sm-3 col-form-label">요청값</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?if($row['ct_refund_status'] == "") echo '-'; if($row['ct_refund_status'] == "1") echo '환불요청'; if($row['ct_refund_status'] == "2") echo '환불완료';?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center">
                                    <label for="mt_id" class="col-sm-3 col-form-label">결제금액</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=number_format($row['ct_price'])?>원</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="card-body <?if($row['ct_refund_status'] == "" || $row['ct_refund_status'] == 0) echo 'd-none';?>" id="refund_area">
					<div class="tab-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row align-items-center">
                                    <label for="mt_id" class="col-form-label" style="width: 191px;">상태변경</label>
                                    <div class="col-sm-8 custom-control">
                                        <div class="col-sm-5 pl-0">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="normal" name="ct_refund_status" <?if($row['ct_refund_status'] == "1") echo 'checked=""';?> value="1" class="custom-control-input">
                                                <label class="custom-control-label" for="normal">환불요청</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="blind" name="ct_refund_status" <?if($row['ct_refund_status'] == "2") echo 'checked=""';?> value="2" class="custom-control-input">
                                                <label class="custom-control-label" for="blind">환불완료</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" style="border-top:none;">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row align-items-center">
                                    <label for="mt_id" class="col-form-label" style="width: 191px;">메모</label>
                                    <div class="col-sm-3 custom-control">
                                        <span><?=$row['ct_refund_info']?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
					</div>
                </div>
                <p class="p-3 mt-3 text-center">
                    <input type="submit" value="저장" class="btn btn-info <?if($row['ct_refund_status'] == "" || $row['ct_refund_status'] == 0) echo 'd-none';?>" />
                    <input type="button" value="목록" onclick="location.href='./<?=$list_url_t?>?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
                </p>
                </form>
			</div>
		</div>
	</div>
</div>
<?
	include "./foot_inc.php";
?>