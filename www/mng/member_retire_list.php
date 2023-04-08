<?
	include "./head_inc.php";
	$chk_menu = '1';
	$chk_sub_menu = '3';
	include "./head_menu_inc.php";

	$n_limit = $n_limit_num;
	$pg = $_GET['pg'];
	$_colspan_txt = "9";
	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&mt_seller_chk=".$_GET['mt_seller_chk']."&pg=";
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">탈퇴회원</h4>
					<p class="card-description">
						탈퇴회원 수정 할 수 있습니다.<br/>
						탈퇴회원된 회원은 같은 아이디로 가입할 수 없습니다.
					</p>

					<div class="p-3 float-right">
						<form method="get" name="frm_search" id="frm_search" action="<?=$_SERVER['PHP_SELF']?>" class="form-inline" onsubmit="return frm_search_chk(this);">
							<div class="form-group mx-sm-1">
								<select name="sel_search" id="sel_search" class="form-control form-control-sm">
									<option value="all">통합검색</option>
									<option value="a1.mt_id">아이디</option>
									<option value="a1.mt_name">성명</option>
									<option value="a1.mt_nickname">닉네임</option>
									<option value="a1.mt_hp">연락처</option>
									<option value="a1.mt_email">이메일</option>
								</select>
							</div>

							<div class="form-group mx-sm-1">
								<input type="text" class="form-control form-control-sm" style="width:200px;" name="search_txt" id="search_txt" value="<?=$_GET['search_txt']?>" />
							</div>

							<div class="form-group mx-sm-1">
								<input type="submit" class="btn btn-primary" value="검색" />
							</div>

							<div class="form-group mx-sm-1">
								<input type="button" class="btn btn-secondary" value="초기화" onclick="location.href='./member_retire_list.php'" />
							</div>
						</form>
						<script type="text/javascript">
						<!--
							function frm_search_chk(f) {
								if(f.search_txt.value=="") {
									alert("검색어를 입력바랍니다.");
									f.search_txt.focus();
									return false;
								}

								return true;
							}

							<? if($_GET['sel_search']) { ?>$('#sel_search').val('<?=$_GET['sel_search']?>');<? } ?>
						//-->
						</script>
					</div>

					<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="text-center" style="width:80px;">
							번호
						</th>
						<th class="text-center">
							아이디
						</th>
						<th class="text-center">
							성명
						</th>
						<th class="text-center">
							닉네임
						</th>
						<th class="text-center">
							연락처
						</th>
						<th class="text-center">
							리뷰캐시
						</th>
						<th class="text-center">
							탈퇴일시
						</th>
						<th class="text-center">
							탈퇴사유
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
							select *, a1.idx as mt_idx from member_t a1
						";
						$query_count = "
							select count(*) from member_t a1
						";

						$where_query .= $_where."mt_level = '1'";
						$_where = " and ";

						if($_GET['search_txt']) {
							if($_GET['sel_search']=="all") {
								$where_query .= $_where."(instr(a1.mt_id, '".$_GET['search_txt']."') or instr(a1.mt_name, '".$_GET['search_txt']."') or instr(a1.mt_nickname, '".$_GET['search_txt']."') or instr(a1.mt_hp, '".$_GET['search_txt']."') or instr(a1.mt_email, '".$_GET['search_txt']."'))";
							} else {
								$where_query .= $_where."instr(".$_GET['sel_search'].", '".$_GET['search_txt']."')";
							}
							$_where = " and ";
						}

						if($_GET['mt_seller_chk']=='Y') {
							$where_query .= $_where."mt_seller = 'D'";
							$_where = " and ";
						}

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
					?>
					<tr>
						<td class="text-center">
							<?=$counts?>
						</td>
						<td>
							<?=$row['mt_id']?>
						</td>
						<td class="text-center">
							<?=$row['mt_name']?>
						</td>
						<td class="text-center">
							<?=$row['mt_nickname']?>
						</td>
						<td class="text-center">
							<?=$row['mt_hp']?>
						</td>
						<td class="text-center">
							<?=number_format($row['mt_review_cash'])?>
						</td>
						<td class="text-center">
							<?=DateType($row['mt_rdate'], 6)?>
						</td>
						<td class="text-center">
							<?=cut_str($row['mt_retire_memo'], 0, 30, '...')?>
						</td>
						<td class="text-center">
							<input type="button" class="btn btn-outline-primary btn-sm" value="수정" onclick="location.href='./member_form.php?act=update&form_type=R&mt_idx=<?=$row['mt_idx']?>&<?=$_get_txt.$_GET['pg']?>'" />
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
<?
	include "./foot_inc.php";
?>