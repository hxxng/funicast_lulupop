<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act']=='pay_type') {
		if($_POST['pt_idx']) {
			$query_ptc = "select * from product_deliveryInfo_t where pt_idx = '".$_POST['pt_idx']."'";
			$row_ptc = $DB->fetch_query($query_ptc);
		}

		if($_POST['ptl_idx']) {
			$query_ptc = "select * from template_t where idx = '".$_POST['ptl_idx']."'";
			$row_ptc = $DB->fetch_query($query_ptc);
		}

		if($_POST['pdt_pay_type']=='1') { //무료
?>

<?
		} else if($_POST['pdt_pay_type']=='2') { //조건부무료
?>
<ul class="pdt_price_type_c list-group list-group-flush">
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="pdt_price" class="col-sm-2 col-form-label">기본 배송비 <b class="text-danger">*</b></label>
			<div class="col-sm-3">
				<div class="input-group">
					<input type="text" name="pdt_price" id="pdt_price" value="<?=$row_ptc['pdt_price']?>" class="form-control" numberOnly />
					<div class="input-group-append">
						<span class="input-group-text">원</span>
					</div>
				</div>
			</div>
		</div>
	</li>
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="pdt_if_price" class="col-sm-2 col-form-label">배송비 조건 <b class="text-danger">*</b></label>
			<div class="col-sm-10">
				<p>상품 판매가 합계 (할인이 적용되지 않은 판매가+옵션가/추가상품가 포함 금액)</p>
				<div class="form-inline">
					<input type="text" name="pdt_if_price" id="pdt_if_price" value="<?=$row_ptc['pdt_if_price']?>" class="form-control form-control-sm" placeholder="숫자만 입력" numberOnly />
					<span class="ml-2">원 이상 무료</span>
				</div>
			</div>
		</div>
	</li>
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="ppt_chk" class="col-sm-2 col-form-label">결제방식 <b class="text-danger">*</b></label>
			<div class="col-sm-10">
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type1" name="pdt_pay_type" value="1" class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type1">착불</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type2" name="pdt_pay_type" value="2" checked class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type2">선결제</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type3" name="pdt_pay_type" value="3" class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type3">착불 또는 선결제</label>
				</div>
			</div>
		</div>
	</li>
</ul>
<? if($row_ptc['pdt_pay_type']) { ?>
<script type="text/javascript">
<!--
	$(document).ready(function () {
		$("input:radio[id='pdt_pay_type<?=$row_ptc['pdt_pay_type']?>']").prop("checked", true);
	});
//-->
</script>
<? } ?>
<?
		} else if($_POST['pdt_pay_type']=='3') { //유료
?>
<ul class="pdt_price_type_c list-group list-group-flush">
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="pdt_price" class="col-sm-2 col-form-label">기본 배송비 <b class="text-danger">*</b></label>
			<div class="col-sm-3">
				<div class="input-group">
					<input type="text" name="pdt_price" id="pdt_price" value="<?=$row_ptc['pdt_price']?>" class="form-control" numberOnly />
					<div class="input-group-append">
						<span class="input-group-text">원</span>
					</div>
				</div>
			</div>
		</div>
	</li>
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="ppt_chk" class="col-sm-2 col-form-label">결제방식 <b class="text-danger">*</b></label>
			<div class="col-sm-10">
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type1" name="pdt_pay_type" value="1" class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type1">착불</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type2" name="pdt_pay_type" value="2" checked class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type2">선결제</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type3" name="pdt_pay_type" value="3" class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type3">착불 또는 선결제</label>
				</div>
			</div>
		</div>
	</li>
</ul>
<? if($row_ptc['pdt_pay_type']) { ?>
<script type="text/javascript">
<!--
	$(document).ready(function () {
		$("input:radio[id='pdt_pay_type<?=$row_ptc['pdt_pay_type']?>']").prop("checked", true);
	});
//-->
</script>
<? } ?>
<?
		} else if($_POST['pdt_pay_type']=='4') { //수량별
?>
<ul class="pdt_price_type_c list-group list-group-flush">
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="pdt_price" class="col-sm-2 col-form-label">기본 배송비 <b class="text-danger">*</b></label>
			<div class="col-sm-3">
				<div class="input-group">
					<input type="text" name="pdt_price" id="pdt_price" value="<?=$row_ptc['pdt_price']?>" class="form-control" numberOnly />
					<div class="input-group-append">
						<span class="input-group-text">원</span>
					</div>
				</div>
			</div>
		</div>
	</li>
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="pdt_pay_qty" class="col-sm-2 col-form-label">배송비 조건 <b class="text-danger">*</b></label>
			<div class="col-sm-10">
				<div class="form-inline">
					<input type="text" name="pdt_pay_qty" id="pdt_pay_qty" value="<?=$row_ptc['pdt_pay_qty']?>" class="form-control form-control-sm" placeholder="숫자만 입력" numberOnly />
					<span class="ml-2">개마다 기본 배송비 반복 부과</span>
				</div>
			</div>
		</div>
	</li>
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="ppt_chk" class="col-sm-2 col-form-label">결제방식 <b class="text-danger">*</b></label>
			<div class="col-sm-10">
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type1" name="pdt_pay_type" value="1" class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type1">착불</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type2" name="pdt_pay_type" value="2" checked class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type2">선결제</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type3" name="pdt_pay_type" value="3" class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type3">착불 또는 선결제</label>
				</div>
			</div>
		</div>
	</li>
</ul>
<? if($row_ptc['pdt_pay_type']) { ?>
<script type="text/javascript">
<!--
	$(document).ready(function () {
		$("input:radio[id='pdt_pay_type<?=$row_ptc['pdt_pay_type']?>']").prop("checked", true);
	});
//-->
</script>
<? } ?>
<?
		} else if($_POST['pdt_pay_type']=='5') { //구간별
?>
<ul class="pdt_price_type_c list-group list-group-flush">
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="pdt_price" class="col-sm-2 col-form-label">기본 배송비 <b class="text-danger">*</b></label>
			<div class="col-sm-3">
				<div class="input-group">
					<input type="text" name="pdt_price" id="pdt_price" value="<?=$row_ptc['pdt_price']?>" class="form-control" numberOnly />
					<div class="input-group-append">
						<span class="input-group-text">원</span>
					</div>
				</div>
			</div>
		</div>
	</li>
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="pdt_pay_qty" class="col-sm-2 col-form-label">배송비 조건 <b class="text-danger">*</b></label>
			<div class="col-sm-10">
				<p>
					<div class="custom-control custom-radio custom-control-inline" onclick="f_pdt_price_section_type('2');">
						<input type="radio" id="pdt_price_section_type2" name="pdt_price_section_type" value="2" checked class="custom-control-input">
						<label class="custom-control-label" for="pdt_price_section_type2">2구간</label>
					</div>
					<div class="custom-control custom-radio custom-control-inline" onclick="f_pdt_price_section_type('3');">
						<input type="radio" id="pdt_price_section_type3" name="pdt_price_section_type" value="3" class="custom-control-input">
						<label class="custom-control-label" for="pdt_price_section_type3">3구간</label>
					</div>
				</p>
				<div id="pdt_price_section_type2_box">
					<div class="form-inline">
						<div class="input-group col-sm-4 pl-0">
							<input type="text" name="pdt_price_section_price1" id="pdt_price_section_price1" value="<?=$row_ptc['pdt_price_section_price1']?>" class="form-control" numberOnly />
							<div class="input-group-append">
								<span class="input-group-text">개</span>
							</div>
						</div>
						<span class="ml-2">까지 추가배송비 없음</span>
					</div>
					<p class="mt-2">초과 구매시 추가배송비</p>
					<div class="input-group col-sm-4 pl-0">
						<input type="text" name="pdt_price_section_price2" id="pdt_price_section_price2" value="<?=$row_ptc['pdt_price_section_price2']?>" class="form-control" numberOnly />
						<div class="input-group-append">
							<span class="input-group-text">원</span>
						</div>
					</div>
				</div>
				<div id="pdt_price_section_type3_box">
					<div class="form-inline">
						<div class="input-group col-sm-4 pl-0">
							<input type="text" name="pdt_price_section_price3" id="pdt_price_section_price3" value="<?=$row_ptc['pdt_price_section_price1']?>" class="form-control" numberOnly />
							<div class="input-group-append">
								<span class="input-group-text">개</span>
							</div>
						</div>
						<span class="ml-2">까지 추가배송비 없음</span>
					</div>
					<div class="form-inline mt-2">
						<div class="input-group col-sm-4 pl-0">
							<input type="text" name="pdt_price_section_price4" id="pdt_price_section_price4" value="<?=$row_ptc['pdt_price_section_price2']?>" class="form-control" numberOnly />
							<div class="input-group-append">
								<span class="input-group-text">개</span>
							</div>
						</div>
						<span class="ml-2">까지 추가배송비</span>
					</div>
					<div class="input-group mt-2 col-sm-4 pl-0">
						<input type="text" name="pdt_price_section_price5" id="pdt_price_section_price5" value="<?=$row_ptc['pdt_price_section_price3']?>" class="form-control" numberOnly />
						<div class="input-group-append">
							<span class="input-group-text">원</span>
						</div>
					</div>
					<p class="mt-2">초과 구매시 추가배송비</p>
					<div class="input-group col-sm-4 pl-0">
						<input type="text" name="pdt_price_section_price6" id="pdt_price_section_price6" value="<?=$row_ptc['pdt_price_section_price4']?>" class="form-control" numberOnly />
						<div class="input-group-append">
							<span class="input-group-text">원</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</li>
	<li class="list-group-item">
		<div class="form-group row align-items-center mb-0">
			<label for="ppt_chk" class="col-sm-2 col-form-label">결제방식 <b class="text-danger">*</b></label>
			<div class="col-sm-10">
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type1" name="pdt_pay_type" value="1" class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type1">착불</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type2" name="pdt_pay_type" value="2" checked class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type2">선결제</label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="pdt_pay_type3" name="pdt_pay_type" value="3" class="custom-control-input">
					<label class="custom-control-label" for="pdt_pay_type3">착불 또는 선결제</label>
				</div>
			</div>
		</div>
	</li>
</ul>
<? if($row_ptc['pdt_pay_type']) { ?>
<script type="text/javascript">
<!--
	$(document).ready(function () {
		$("input:radio[id='pdt_price_section_type<?=$row_ptc['pdt_price_section_type']?>']").prop("checked", true);
		f_pdt_price_section_type('<?=$row_ptc['pdt_price_section_type']?>');
		$("input:radio[id='pdt_pay_type<?=$row_ptc['pdt_pay_type']?>']").prop("checked", true);
	});
//-->
</script>
<? } ?>
<?
		}
	}

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>