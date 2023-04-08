<?
	include "./head_inc.php";
	$chk_menu = '12';
	$chk_sub_menu = '0';
	include "./head_menu_inc.php";

    $query = "select * from policy_t where idx = 1";
    $row = $DB->fetch_assoc($query);
?>
<!-- 메인 시작 -->
<div class="content-wrapper">
    <div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <div class="card-body">
                    <p class="mb-0">
                    <form method="post" name="frm_form" id="frm_form" action="./delivery_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" >
                    <h4 class="card-title">기본 배송비 설정</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_delivery_price" class="col-sm-2 col-form-label">단건 <b class="text-danger">*</b></label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <input type="text" name="pt_delivery_price" id="pt_delivery_price" value="<?=$row['pt_delivery_price']?>" class="form-control" numberonly="" maxlength="10">
                                        <div class="input-group-append">
                                            <span class="input-group-text">원</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_refund_price" class="col-sm-2 col-form-label">반품 <b class="text-danger">*</b></label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <input type="text" name="pt_refund_price" id="pt_refund_price" value="<?=$row['pt_refund_price']?>" class="form-control" numberonly="" maxlength="10">
                                        <div class="input-group-append">
                                            <span class="input-group-text">원</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_exchange_price" class="col-sm-2 col-form-label">교환 <b class="text-danger">*</b></label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <input type="text" name="pt_exchange_price" id="pt_exchange_price" value="<?=$row['pt_exchange_price']?>" class="form-control" numberonly="" maxlength="10">
                                        <div class="input-group-append">
                                            <span class="input-group-text">원</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <div class="col-sm-1 col-form-label custom-control custom-checkbox custom-control-inline">
                                    <input type="checkbox" id="pt_free_delivery_chk" name="pt_free_delivery_chk" value="Y" class="custom-control-input">
                                    <label class="custom-control-label" for="pt_free_delivery_chk">무료배송설정</label>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group row align-items-center mb-0">
                                        <div class="input-group">
                                            <input type="text" name="pt_free_delivery_price" id="pt_free_delivery_price" value="<?=$row['pt_free_delivery_price']?>" class="form-control" numberonly="" maxlength="10">
                                            <div class="input-group-append">
                                                <span class="input-group-text">원</span>
                                            </div>
                                            <span class="col-form-label">&nbsp; 이상 구매 시 무료 배송</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <script>
                                <? if($row['pt_free_delivery_chk']) { ?> $('#pt_free_delivery_chk').attr("checked", true);<? } ?>
                            </script>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_exchange_price" class="col-sm-2 col-form-label">배송정보 <b class="text-danger">*</b></label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <textarea name="pt_delivery_info" id="pt_delivery_info" class="form-control form-control-sm" style="height: 200px;" placeholder="배송정보"><?=$row['pt_delivery_info']?></textarea>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_exchange_price" class="col-sm-2 col-form-label">교환 및 반품정보 <b class="text-danger">*</b></label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <textarea name="pt_return_info" id="pt_return_info" class="form-control form-control-sm" style="height: 200px;" placeholder="교환 및 반품정보"><?=$row['pt_return_info']?></textarea>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="p-2 mt-3 text-center">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                    </p>
                    </form>
                </div>
            </div>
		</div>
	</div>
</div>
<!-- 메인 끝 -->
<?
	include "./foot_inc.php";
?>