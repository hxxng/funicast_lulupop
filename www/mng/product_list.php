<?
	include "./head_inc.php";
	$chk_menu = '2';
	$chk_sub_menu = '1';
	include "./head_menu_inc.php";

	$n_limit = $n_limit_num;
	$pg = $_GET['pg'];
	$_colspan_txt = "9";
	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pt_sale_now=".$_GET['pt_sale_now']."&pt_show=".$_GET['pt_show']."&sel_ct_id1=".$_GET['sel_ct_id1']."&sel_ct_id2=".$_GET['sel_ct_id2']."&sel_ct_id3=".$_GET['sel_ct_id3']."&sel_ct_id4=".$_GET['sel_ct_id4']."&pg=";

	if($_GET['pt_sale_now']=='') {
		$_GET['pt_sale_now'] = 'Y';
	}
	if($_GET['pt_show']=='') {
		$_GET['pt_show'] = 'Y';
	}
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">상품관리</h4>
					<p class="card-description">
						상품을 관리 할 수 있습니다.
					</p>
                    <ul class="list-group list-group-flush">
                        <div class="p-3 float-right">
                            <form class="form-inline">
                                <div class="form-group mx-sm-1">
                                    <input type="button" class="btn btn-secondary" value="품절" onclick="select_status('select_stop', '0')" />
                                </div>
                                <div class="form-group mx-sm-1">
                                    <input type="button" class="btn btn-secondary" value="판매중" onclick="select_status('select_stop', 'N')" />
                                </div>
                                <div class="form-group mx-sm-1">
                                    <input type="button" class="btn btn-secondary" value="판매중지" onclick="select_status('select_stop', 'N')" />
                                </div>
                                <div class="form-group mx-sm-1">
                                    <input type="button" class="btn btn-primary" value="+ 상품 등록" onclick="location.href='./product_form.php?act=input'" />
                                </div>
                            </form>
                        </div>
                    </ul>

					<form method="get" name="frm_search" id="frm_search" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="return frm_search_chk(this);">
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							<div class="form-group row align-items-center mb-0">
								<label for="sel_search" class="col-sm-2 col-form-label">검색어</label>
								<div class="col-sm-4">
									<div class="input-group">
										<input type="text" name="search_txt" id="search_txt" value="<?=$_GET['search_txt']?>" class="form-control form-control-sm" />
									</div>
								</div>
							</div>
						</li>
						<li class="list-group-item">
							<div class="form-group row align-items-center mb-0">
								<label for="select_category" class="col-sm-2 col-form-label">판매상태</label>
								<div class="col-sm-6">
									<input type="hidden" name="pt_sale_now" id="pt_sale_now" value="<?=$_GET['pt_sale_now']?>" />
									<div class="btn-group" role="group" aria-label="pt_sale_now">
										<button type="button" onclick="f_pt_sale_now('Y');" id="f_pt_sale_now_btn1" class="btn btn-outline-secondary<? if($_GET['pt_sale_now']=='Y') { ?> btn-info text-white<? } ?>">판매함</button>
										<button type="button" onclick="f_pt_sale_now('N');" id="f_pt_sale_now_btn2" class="btn btn-outline-secondary<? if($_GET['pt_sale_now']=='N') { ?> btn-info text-white<? } ?>">판매안함</button>
										<button type="button" onclick="f_pt_sale_now('0');" id="f_pt_sale_now_btn3" class="btn btn-outline-secondary<? if($_GET['pt_sale_now']=='0') { ?> btn-info text-white<? } ?>">품절</button>
									</div>

									<input type="hidden" name="pt_show" id="pt_show" value="<?=$_GET['pt_show']?>" />
									<div class="btn-group ml-2" role="group" aria-label="pt_show">
										<button type="button" onclick="f_pt_show('Y');" id="f_pt_show_btn1" class="btn btn-outline-secondary<? if($_GET['pt_show']=='Y') { ?> btn-info text-white<? } ?>">노출함</button>
										<button type="button" onclick="f_pt_show('N');" id="f_pt_show_btn2" class="btn btn-outline-secondary<? if($_GET['pt_show']=='N') { ?> btn-info text-white<? } ?>">노출안함</button>
									</div>
								</div>
							</div>
						</li>
						<li class="list-group-item">
							<div class="form-group row align-items-center mb-0">
								<div class="col-sm-12 text-center">
									<input type="submit" class="btn btn-primary" value="검색" />
									<input type="button" class="btn btn-secondary ml-2" value="초기화" onclick="location.href='./product_list.php'" />
								</div>
							</div>
						</li>
					</ul>
					<p>&nbsp;</p>
					</form>
					<script type="text/javascript">
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

						<? if($_GET['sel_search']) { ?>$('#sel_search').val('<?=$_GET['sel_search']?>');<? } ?>
					</script>

					<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="text-center" style="width:50px;">
                            <input type="checkbox" id="chk_all"/>
						</th>
						<th class="text-center" style="width: 400px;">
							상품명
						</th>
						<th class="text-center">
							상품번호
						</th>
                        <th class="text-center">
                            상품가격
                        </th>
						<th class="text-center" style="width:80px;">
							재고
						</th>
						<th class="text-center">
							등록일
						</th>
						<th class="text-center" style="width:100px;">
							판매상태
						</th>
                        <th class="text-center" style="width:100px;">
                            노출상태
                        </th>
						<th class="text-center">
							관리
						</th>
					</tr>
					</thead>
					<tbody>
					<?
						$_where = " where 1 and ";
						$query = "
							select *, a1.idx as pt_idx from product_t a1
						";
						$query_count = "
							select count(*) from product_t a1
						";

						if($_GET['search_txt']) {
                            $where_query .= $_where."(instr(a1.pt_code, '".$_GET['search_txt']."') or instr(a1.pt_title, '".$_GET['search_txt']."'))";
							$_where = " and ";
						}

						if($_GET['pt_sale_now'] != "") {
							$where_query .= $_where."a1.pt_sale_now = '".$_GET['pt_sale_now']."'";
							$_where = " and ";
						}

						if($_GET['pt_show']) {
							$where_query .= $_where."a1.pt_show = '".$_GET['pt_show']."'";
							$_where = " and ";
						}

						if($_GET['pct_id']) {							
							$where_query .= $_where." INSTR(a1.pct_id, '".$_GET['pct_id']."')";
							$_where = " and ";
						}

						$row_cnt = $DB->fetch_query($query_count.$where_query);
						$count_query = $row_cnt[0];
						$counts = $count_query;
						$n_page = ceil($count_query / $n_limit_num);
						if($pg=="") $pg = 1;
						$n_from = ($pg - 1) * $n_limit;
						$counts = $counts - (($pg - 1) * $n_limit_num);

						unset($list);
						$sql_query = $query.$where_query." order by a1.idx desc limit ".$n_from.", ".$n_limit;
						$list = $DB->select_query($sql_query);
						
						if($list) {
							foreach($list as $row) {
								$ca_name_breadcrumb_t = '';
								if($row['pct_id']) $ca_name_breadcrumb_t = get_ca_name_breadcrumb($row['pct_id']);
					?>
					<tr>
						<td class="text-center">
                            <input type="checkbox" name="chk_box" id="chk_box_<?= $row['pt_idx'] ?>"/>
						</td>
						<td class="text-center">
							<?=$row['pt_title']?>
						</td>
                        <td class="text-center">
                            <?=$row['pt_code']?>
                        </td>
                        <td class="text-center">
                            <?=number_format($row['pt_price'])?>원
                        </td>
						<td class="text-center">
                            <?
                            if($row['pt_stock_chk']=='N') echo '무제한';
                            if($row['pt_stock_chk']=='Y') echo number_format($row['pt_stock']);
                            ?>
						</td>
						<td class="text-center">
                            <?=DateType($row['pt_wdate'],1)?>
                        </td>
						<td class="text-center">
                        <?
                            if($row['pt_sale_now']=='N') echo '판매중지';
                            if($row['pt_sale_now']=='Y') echo '판매중';
                            if($row['pt_sale_now']=='0') echo '품절';
                        ?>
						</td>
                        <td class="text-center">
                        <?
                            if($row['pt_show']=='N') echo '미노출';
                            if($row['pt_show']=='Y') echo '노출';
                        ?>
                        </td>
						<td class="text-center">
                            <input type="button" class="btn btn-outline-primary btn-sm" value="자세히" onclick="location.href='./product_form.php?act=update&pt_idx=<?=$row['pt_idx']?>&<?=$_get_txt.$_GET['pg']?>'" />
							<!--<input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_product_del('<?=$row['pt_idx']?>');" />-->
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
				</div>
			</div>
		</div>
	</div>
</div>
<script>
    $("#chk_all").on('click', function () {
        f_checkbox_all('chk_box');
    });

    function f_checkbox_all(obj) {
        $('input:checkbox[name="' + obj + '"]').each(function () {
            if ($(this).prop('checked') == true) {
                $(this).prop('checked', false);
            } else {
                $(this).prop('checked', true);
            }
        });

        return false;
    }	
    //상품 상태값 변경 함수
    function f_product_change_status(obj, idx) {
        if(confirm("상품 상태값을 변경하시겠습니까?")) {
            $.ajax({
                type : 'post',
                url : './ajax.product.php',
                dataType : 'json',
                data : { act : 'change_status', pt_sale_now : obj.value, idx : idx},
                success : function(d, s){
                    alert(d.msg);
                },
                cache : false
            });
        }

        return false;
    }

    function select_status(act='select_stop', status='') {
        var list = $("input[name='chk_box']");
        var ids = [];
        for (var i = 0; i < list.length; i++) {
            if ($("#" + list[i].id).is(":checked")) {
                var id = list[i].id;
                id = id.replace("chk_box_", "");
                ids.push(id);
            }
        }
        $.ajax({
            type: 'post',
            url: './product_update.php',
            dataType: 'json',
            data: {act: act, pt_idx: ids, status : status},
            success: function (d, s) {
                if (d['result'] == "_ok") {
                    alert(d['msg']);
                    location.reload();
                }
            },
            cache: false
        });
    }

</script>
<?
	include "./foot_inc.php";
?>