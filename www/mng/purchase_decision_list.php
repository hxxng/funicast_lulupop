<?
	include "./head_inc.php";
	$chk_menu = '3';
	$chk_sub_menu = '2';
	include "./head_menu_inc.php";

	$n_limit = $n_limit_num;
	$pg = $_GET['pg'];
	$_colspan_txt = "7";
	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pg=";
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">구매확정</h4>
					<p class="card-description">
						구매확정 내역을 확인할 수 있습니다.
					</p>

					<form method="get" name="frm_search" id="frm_search" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="return frm_search_chk(this);">
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							<div class="form-group row align-items-center mb-0">
								<label for="sel_search_date" class="col-sm-2 col-form-label">조회기간</label>
								<div class="col-sm-1">
									<div class="input-group">
										<select name="sel_search_date" id="sel_search_date" class="form-control form-control-sm">
											<option value="a2.ct_rdate">구매확정일</option>
											<option value="a2.ct_ddate">발송처리일</option>
											<option value="a1.ot_pdate">결제일</option>
										</select>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="btn-group" role="group" aria-label="select_category">
										<button type="button" onclick="f_order_search_date_range('1', '<?=date('Y-m-d')?>', '<?=date('Y-m-d', strtotime("+2 days"))?>');" id="f_order_search_date_range1" class="btn btn-outline-secondary btn-sm c_pt_selling_date_range">3일</button>
										<button type="button" onclick="f_order_search_date_range('2', '<?=date('Y-m-d')?>', '<?=date('Y-m-d', strtotime("+4 days"))?>');" id="f_order_search_date_range2" class="btn btn-outline-secondary btn-sm c_pt_selling_date_range">5일</button>
										<button type="button" onclick="f_order_search_date_range('3', '<?=date('Y-m-d')?>', '<?=date('Y-m-d', strtotime("+6 days"))?>');" id="f_order_search_date_range3" class="btn btn-outline-secondary btn-sm c_pt_selling_date_range">7일</button>
										<button type="button" onclick="f_order_search_date_range('4', '<?=date('Y-m-d')?>', '<?=date('Y-m-d', strtotime("+14 days"))?>');" id="f_order_search_date_range4" class="btn btn-outline-secondary btn-sm c_pt_selling_date_range">15일</button>
										<button type="button" onclick="f_order_search_date_range('5', '<?=date('Y-m-d')?>', '<?=date('Y-m-d', strtotime("+29 days"))?>');" id="f_order_search_date_range5" class="btn btn-outline-secondary btn-sm c_pt_selling_date_range">30일</button>
										<button type="button" onclick="f_order_search_date_range('6', '<?=date('Y-m-d')?>', '<?=date('Y-m-d', strtotime("+59 days"))?>');" id="f_order_search_date_range6" class="btn btn-outline-secondary btn-sm c_pt_selling_date_range">60일</button>
										<button type="button" onclick="f_order_search_date_range('7', '<?=date('Y-m-d')?>', '<?=date('Y-m-d', strtotime("+89 days"))?>');" id="f_order_search_date_range7" class="btn btn-outline-secondary btn-sm c_pt_selling_date_range">90일</button>
										<button type="button" onclick="f_order_search_date_range('8', '<?=date('Y-m-d')?>', '<?=date('Y-m-d', strtotime("+119 days"))?>');" id="f_order_search_date_range8" class="btn btn-outline-secondary btn-sm c_pt_selling_date_range">120일</button>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="input-group">
										<input type="text" name="sel_search_sdate" id="sel_search_sdate" value="<?=$_GET['sel_search_sdate']?>" class="form-control" readonly /> <span class="m-2">~</span> <input type="text" name="sel_search_edate" id="sel_search_edate" value="<?=$_GET['sel_search_edate']?>" class="form-control" readonly />
									</div>
								</div>
							</div>
						</li>
						<li class="list-group-item">
							<div class="form-group row align-items-center mb-0">
								<label for="sel_search" class="col-sm-2 col-form-label">검색어</label>
								<div class="col-sm-2">
									<div class="input-group">
										<select name="sel_search" id="sel_search" class="form-control form-control-sm">
											<option value="all">통합검색</option>
											<option value="a1.ot_code">주문번호</option>
											<option value="a2.ot_pcode">상품주문번호</option>
											<option value="a2.pt_title">상품명</option>
											<option value="a1.mt_id">구매자ID</option>
											<option value="a1.ot_name">구매자명</option>
											<option value="a1.ot_tel">구매자연락처1</option>
											<option value="a1.ot_hp">구매자연락처2</option>
											<option value="a1.ot_rname">수령자명</option>
											<option value="a1.ot_rtel">수령자연락처1</option>
											<option value="a1.ot_rhp">수령자연락처2</option>
											<option value="a2.ct_delivery_number">송장번호</option>
										</select>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="input-group">
										<input type="text" name="search_txt" id="search_txt" value="<?=$_GET['search_txt']?>" class="form-control form-control-sm" />
									</div>
								</div>
							</div>
						</li>
						<li class="list-group-item">
							<div class="form-group row align-items-center mb-0">
								<div class="col-sm-12 text-center">
									<input type="submit" class="btn btn-primary" value="검색" />
									<input type="button" class="btn btn-secondary ml-2" value="초기화" onclick="location.href='./purchase_decision_list.php'" />
								</div>
							</div>
						</li>
					</ul>
					<p>&nbsp;</p>
					</form>
					<script type="text/javascript">
					<!--
						(function($) {
							'use strict';
							$(function() {
								jQuery.datetimepicker.setLocale('ko');

								jQuery(function () {
									jQuery('#sel_search_sdate').datetimepicker({
										format: 'Y-m-d',
										onShow: function (ct) {
											this.setOptions({
												maxDate: jQuery('#sel_search_edate').val() ? jQuery('#sel_search_edate').val() : false
											})
										},
										timepicker: false
									});
									jQuery('#sel_search_edate').datetimepicker({
										format: 'Y-m-d',
										onShow: function (ct) {
											this.setOptions({
												minDate: jQuery('#sel_search_sdate').val() ? jQuery('#sel_search_sdate').val() : false
											})
										},
										timepicker: false
									});
								});
							});
						})(jQuery);

						function frm_search_chk(f) {
							/*
							if(f.search_txt.value=="") {
								alert("검색어를 입력바랍니다.");
								f.search_txt.focus();
								return false;
							}
							*/

							return true;
						}

						function f_excel_down(act_t) {
							var f = document.frm_search;

							if(f.sel_search_sdate.value=="") {
								alert("조회기간을 입력바랍니다.");
								f.sel_search_sdate.focus();
								return false;
							}
							if(f.sel_search_edate.value=="") {
								alert("조회기간을 입력바랍니다.");
								f.sel_search_edate.focus();
								return false;
							}

							hidden_ifrm.document.location.href = './order_excel.php?act='+act_t+'&search_date='+f.sel_search_date.value+'&sdate='+f.sel_search_sdate.value+'&edate='+f.sel_search_edate.value;

							return false;
						}

						<? if($_GET['sel_search_date']) { ?>$('#sel_search_date').val('<?=$_GET['sel_search_date']?>');<? } ?>
						<? if($_GET['sel_search']) { ?>$('#sel_search').val('<?=$_GET['sel_search']?>');<? } ?>
					//-->
					</script>

					<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="text-center" style="width:80px;">
							<input type="button" class="btn btn-secondary btn-xs" value="선택" onclick="f_checkbox_all('chk_all')" />
						</th>
						<th class="text-center">
							주문번호
						</th>
						<th class="text-center">
							주문상태
						</th>
						<th class="text-center">
							상품
						</th>
						<th class="text-center">
							구매자
						</th>
						<th class="text-center">
							수령자
						</th>
						<th class="text-center">
							관리
						</th>
					</tr>
					</thead>
					<tbody>
					<?
						$_where = " where ";
						$query = "
							select *, a1.idx as ot_idx, a2.idx as ct_idx from order_t a1
							left outer join cart_t a2 on a1.ot_code = a2.ot_code
						";
						$query_count = "
							select count(*) from order_t a1
							left outer join cart_t a2 on a1.ot_code = a2.ot_code
						";

						if($_GET['search_txt']) {
							if($_GET['sel_search']=="all") {
								$where_query .= $_where."(instr(a1.ot_code, '".$_GET['search_txt']."') or instr(a2.ot_pcode, '".$_GET['search_txt']."') or instr(a2.pt_title, '".$_GET['search_txt']."') or instr(a1.ot_name, '".$_GET['search_txt']."') or instr(a1.ot_rname, '".$_GET['search_txt']."') or instr(a1.ot_tel, '".$_GET['search_txt']."') or instr(a1.ot_rtel, '".$_GET['search_txt']."') or instr(a1.ot_hp, '".$_GET['search_txt']."') or instr(a1.ot_rhp, '".$_GET['search_txt']."') or instr(a1.mt_id, '".$_GET['search_txt']."'))";
							} else {
								$where_query .= $_where."instr(".$_GET['sel_search'].", '".$_GET['search_txt']."')";
							}
							$_where = " and ";
						}

						if($_GET['sel_search_sdate'] && $_GET['sel_search_edate']) {
							$where_query .= $_where.$_GET['sel_search_date']." between '".$_GET['sel_search_sdate']." 00:00:00' and '".$_GET['sel_search_edate']." 23:59:59'";
							$_where = " and ";
						}

						$where_query .= $_where."a2.ct_status = '5'";
						$_where = " and ";

						$row_cnt = $DB->fetch_query($query_count.$where_query);
						$couwt_query = $row_cnt[0];
						$counts = $couwt_query;
						$n_page = ceil($couwt_query / $n_limit_num);
						if($pg=="") $pg = 1;
						$n_from = ($pg - 1) * $n_limit;
						$counts = $counts - (($pg - 1) * $n_limit_num);

						unset($list);
						$sql_query = $query.$where_query." order by a1.idx desc limit ".$n_from.", ".$n_limit;
						$list = $DB->select_query($sql_query);

						if($list) {
							foreach($list as $row) {
								$pt_info = get_product_t_info($row['pt_idx']);
					?>
					<tr>
						<td class="text-center">
							<input type="checkbox" id="chk_all" name="chk_all[]" value="<?=$row['ot_pcode']?>" class="custom-checkbox-list">
						</td>
						<td>
							<div class="media">
								<div class="media-body">
									<h5 class="font-weight-bold"><?=$row['ot_code']?></h5>
									<h5 class="mt-2"><?=$row['ot_pcode']?></h5>
								</div>
							</div>
						</td>
						<td>
							<div class="media">
								<div class="media-body">
									<h5 class="font-weight-bold"><?=$arr_ct_status[$row['ct_status']]?> / <?=$arr_pdt_type[$row['ct_delivery_type']]?></h5>
									<h6><span class="badge badge-info">확정</span> <?=DateType($row['ct_rdate'], 6)?></h6>
									<? if($row['ct_ldate']!=null || $row['ct_ldate']!='0000-00-00 00:00:00') { ?>
									<h6><span class="badge badge-secondary">발송</span> <?=DateType($row['ct_ddate'], 6)?></h6>
									<? } ?>
								</div>
							</div>
						</td>
						<td>
							<div class="media product_list_media">
								<a href="javascript:;" onclick="f_swipe_image('<?=$row['pt_idx']?>')"><img src="<?=$ct_img_url."/".$pt_info['pt_image1']?>" onerror="this.src='<?=$ct_no_img_url?>'" class="align-self-center mr-3" alt="<?=$pt_info['pt_title']?>"></a>
								<div class="media-body">
									<h5 class="font-weight-bold"><?=$pt_info['pt_title']?></h5>
									<p><small class="mt-2">선택옵션 : </small></p>
									<h5 class="text-info font-weight-bold"><?=number_format($row['ot_price'])?>원</h5>
								</div>
							</div>
						</td>
						<td>
							<div class="media">
								<div class="media-body">
									<h5 class="font-weight-bold"><?=$row['ot_name']?>(<?=$row['mt_id']?>)</h5>
									<h5 class="mt-2"><?=$row['ot_tel']?> / <?=$row['ot_hp']?></h5>
									<h5 class="mt-2"><?=$row['ot_add1']?> / <?=$row['ot_add2']?></h5>
								</div>
							</div>
						</td>
						<td>
							<div class="media">
								<div class="media-body">
									<h5 class="font-weight-bold"><?=$row['ot_rname']?></h5>
									<h5 class="mt-2"><?=$row['ot_rtel']?> / <?=$row['ot_rhp']?></h5>
									<h5 class="mt-2"><?=$row['ot_radd1']?> / <?=$row['ot_radd2']?></h5>
								</div>
							</div>
						</td>
						<td class="text-center">
							<input type="button" class="btn btn-outline-secondary btn-sm" value="상세보기" onclick="f_view_order('<?=$row['ot_pcode']?>')" />
						</td>
					</tr>
					<?
								$counts--;
							}
						} else {
					?>
					<tr>
						<td colspan="<?=$_colspan_txt?>" class="text-center"><b>자료가 없습니다.</b></td>
					</tr>
					<?
						}
					?>
					</tbody>
					</table>
					<?
						if($n_page>1) {
							echo page_listing($pg, $n_page, $_SERVER['PHP_SELF']."?".$_get_txt);
						}
					?>

					<p>&nbsp;</p>
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							<div class="form-group row align-items-center mb-0">
								<label for="ot_status_chk" class="col-sm-2 col-form-label">구매확정취소 <a data-toggle="popover" title="" data-trigger="hover" data-html='true' data-content="주문확인 관련 도움말" data-original-title="주문확인"><i class="mdi mdi-help-rhombus-outline"></i></a></label>
								<div class="col-sm-6">
									<button type="button" onclick="f_order_cancel('79');" id="ot_status_chk" class="btn btn-outline-secondary btn-sm">구매확정후취소</button>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<?
	include "./foot_inc.php";
?>