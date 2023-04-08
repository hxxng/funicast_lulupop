<?
	include "./head_inc.php";
	$chk_menu = "6";
	$chk_sub_menu = "3";
	include "./head_menu_inc.php";
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">적립금관리</h4>
					<div class="row">
						<div class="col-sm-12">
                            <form method="get" name="frm_search" id="frm_search" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="return f_serach_date();">
							<div class="row justify-content-end">
                                <div class="form-inline mb-3">
                                    <input name="sel_search_sdate" id="sel_search_sdate" value="<?=$_GET['sel_search_sdate']?>" class="form-control" placeholder="시작날짜" />
                                    <span class="m-2">~</span>
                                    <input name="sel_search_edate" id="sel_search_edate" value="<?=$_GET['sel_search_edate']?>" class="form-control " placeholder="종료날짜" />
                                    <input type="submit" class="btn btn-secondary ml-2" value="기간검색" />
                                    <input type="button" class="btn btn-secondary ml-2" value="초기화" onclick="location.href='./point_list.php'" />
                                </div>
							</div>
                            <div class="row justify-content-end">
                                <div class="form-inline mb-3">
                                    <div class="form-group row align-items-center mb-0 mr-2">
                                        <div class="custom-control custom-checkbox custom-control-inline ">
                                            <input type="checkbox" id="purchase" name="purchase" value="1" <? if($_GET['purchase']) echo 'checked=""'; ?> onclick="frm_search.submit()" class="custom-control-input">
                                            <label class="custom-control-label" for="purchase">포인트구매적립</label>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center mb-0 mr-2">
                                        <div class="custom-control custom-checkbox custom-control-inline">
                                            <input type="checkbox" id="use" name="use" value="2" <? if($_GET['use']) echo 'checked=""'; ?> onclick="frm_search.submit()" class="custom-control-input">
                                            <label class="custom-control-label" for="use">구매시사용</label>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center mb-0 mr-2">
                                        <div class="custom-control custom-checkbox custom-control-inline">
                                            <input type="checkbox" id="join" name="join" value="3" <? if($_GET['join']) echo 'checked=""'; ?> onclick="frm_search.submit()" class="custom-control-input">
                                            <label class="custom-control-label" for="join">회원가입적립</label>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center mb-0 mr-2">
                                        <div class="custom-control custom-checkbox custom-control-inline">
                                            <input type="checkbox" id="community" name="community" value="4" <? if($_GET['community']) echo 'checked=""'; ?> onclick="frm_search.submit()" class="custom-control-input">
                                            <label class="custom-control-label" for="community">커뮤니티첫등록</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
							<script type="text/javascript">
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

								function f_serach_date() {
									var sel_search_sdate = $('#sel_search_sdate').val();
									var sel_search_edate = $('#sel_search_edate').val();

									// if(sel_search_sdate=="") {
									// 	$('#sel_search_sdate').focus();
									// 	return false;
									// }
									// if(sel_search_edate=="") {
									// 	$('#sel_search_edate').focus();
									// 	return false;
									// }

									// document.location.href = "./point_list.php?plt_wdate>="+met_sdate+"&plt_wdate<="+met_edate;

									return true;
								}
                                <? if($_GET['sel_search_sdate']) { ?>$('#sel_search_sdate').val('<?=$_GET['sel_search_sdate']?>');<? } ?>
                                <? if($_GET['sel_search_edate']) { ?>$('#sel_search_edate').val('<?=$_GET['sel_search_edate']?>');<? } ?>
							</script>

							<table id="point_on_list" class="table">
								<thead>
									<tr>
                                        <th class="text-center" style="width:60px;">번호</th>
                                        <th class="text-center">닉네임</th>
                                        <th class="text-center">포인트</th>
                                        <th class="text-center">구분</th>
										<th class="text-center">포인트적립일시</th>
										<th class="text-center">포인트소멸일시</th>
									</tr>
								</thead>
								<tbody>
									<?
                                        $_where = " and";
										unset($list_p);
										$query_p = "
											select * from point_log_t a1
											left outer join member_t a2 on a1.mt_idx = a2.idx where 1=1
										";

                                        if($_GET['sel_search_sdate'] && $_GET['sel_search_edate']) {
                                            $where_query .= $_where." plt_wdate ".$_GET['sel_search_date']." between '".$_GET['sel_search_sdate']." 00:00:00' and '".$_GET['sel_search_edate']." 23:59:59'";
                                        }
                                        if($_GET['purchase']) {
                                            $in_query .= "1,";
                                        }
                                        if($_GET['use']) {
                                            $in_query .= "2,";
                                        }
                                        if($_GET['join']) {
                                            $in_query .= "3,";
                                        }
                                        if($_GET['community']) {
                                            $in_query .= "4,";
                                        }
                                        if(!$_GET['purchase'] && !$_GET['use'] && !$_GET['join'] && !$_GET['community']) {
                                            $where_query .= $where_query;
                                        } else {
                                            $in_query = substr($in_query, 0 ,-1);
                                            $where_query .= $_where." plt_status in (".$in_query.") and plt_status is not null";
                                        }
                                        $query_p .= $where_query." order by a1.plt_wdate desc";
										$list_p = $DB->select_query($query_p);
										$count = $DB->count_query($query_p);

										$sum_pt_pay_price = 0;
										$sum_pt_point_num = 0;
                                        $i = 0;

										if($list_p) {
											foreach($list_p as $row_p) {
												if($row_p['plt_type']=='M') {
													$tr_class = " class='table-danger'";
													$row_p['plt_price'] = '-'.$row_p['plt_price'];
												} else {
													$tr_class = " class='table-primary'";
												}

												if($row_p['plt_type']=='M') {
													$td_class2 = "text-danger";
												} else {
													$td_class2 = "text-primary";
												}
									?>
									<tr<?=$tr_class?>>
                                        <td class="text-center"><?=$count?></td>
                                        <td class="text-center"><?=$row_p['mt_nickname']?></td>
                                        <td class="<?=$td_class2?> text-right"><?=$row_p['plt_price']?>P</td>
                                        <td class="text-center" style="width: 20%;">
                                            <? if($row_p['plt_status'] == 1) echo '상품구매적립'; if($row_p['plt_status'] == 2) echo '구매시 사용';
                                               if($row_p['plt_status'] == 3) echo '회원가입적립'; if($row_p['plt_status'] == 4) echo '커뮤니티첫등록'; ?>
                                        </td>
										<td class="text-center"><?=DateType($row_p['plt_wdate'], 8)?></td>
										<td class="text-center"><?=$row_p['plt_expire_date']?></td>
									</tr>
									<?
												$sum_pt_point_num += $row_p['plt_price'];
                                                $count--;
                                            }
										}
									?>
								</tbody>
							</table>
							<script type="text/javascript">
								(function($) {
									'use strict';
									$(function() {
										$('#point_on_list').DataTable({
											"dom": 'Bfrtip',
											"order": [[ 4, "desc" ]],
											"iDisplayLength": 10,
											"language": {
												"url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Korean.json"
											},
											"columnDefs": [
												{ "orderable": false, "targets": 4 }
											],
											"footerCallback": function ( row, data, start, end, display ) {
												var api = this.api(), data;
												if(api.search()) {
													var intVal = function ( i ) {
														return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
													};

													var total1 = api.column( 3, { page: 'current'} ).data().reduce( function (a, b) {
														return intVal(a) + intVal(b);
													}, 0 );

													$( api.column( 3 ).footer() ).html(comma_num(total1));

													var total3 = api.column( 5, { page: 'current'} ).data().reduce( function (a, b) {
														return intVal(a) + intVal(b);
													}, 0 );

													$( api.column( 5 ).footer() ).html(comma_num(total3));
												}
											},
											"buttons": [ {
												extend: 'excelHtml5',
												autoFilter: true,
												text: '엑셀',
												className: 'btn btn-success'
											} ]
										});
									});
								})(jQuery);
							</script>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?
	include "./foot_inc.php";
?>