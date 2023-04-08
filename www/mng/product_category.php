<?php
	include "./head_inc.php";
	$chk_menu = '12';
	$chk_sub_menu = '1';
	include "./head_menu_inc.php";

	$n_limit = $n_limit_num;
	$pg = $_GET['pg'];
	$_colspan_txt = "5";
	
	$_get_txt = "pg=";

	$_where = " where 1 ";
	$query = "
		select *, a1.idx as pc_idx from product_category_t a1
	";
	$query_count = "
		select count(*) from product_category_t a1
	";
	
	$where_query = $_where;

	$row_cnt = $DB->fetch_query($query_count.$where_query);
	$couwt_query = $row_cnt[0];
	$counts = $couwt_query;
	$n_page = ceil($couwt_query / $n_limit_num);
	if($pg=="") $pg = 1;
	$n_from = ($pg - 1) * $n_limit;
	$counts = $counts - (($pg - 1) * $n_limit_num);

	unset($list);
	$sql_query = $query.$where_query." order by IF(ISNULL(pc_m_idx),  idx, pc_m_idx), IF(ISNULL(pc_s_idx), idx, pc_s_idx), pc_depth, pc_orderby limit ".$n_from.", ".$n_limit;
	$list = $DB->select_query($sql_query);
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">카테고리 관리</h4>

					<div class="p-3 float-right">
						<form class="form-inline">
							<div class="form-group mx-sm-1">
								<input type="button" class="btn btn-primary" value="카테고리 추가" onclick="f_view_category()"/>
							</div>
							<div class="form-group mx-sm-1">
								<input type="button" class="btn btn-secondary" value="선택 삭제" id="product_category_del"/>
							</div>
						</form>
					</div>

					<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th style="width: 40%">
							분류명
						</th>
						<th class="text-center">
							depth 순서
						</th>
						<th class="text-center" style="width: 20%">
							관리
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
						if($list) {
							foreach($list as $row) {
					?>
					<tr>
						<td style="<? if($row['pc_depth'] == 1) { echo 'text-indent:30px;'; } if($row['pc_depth'] == 2) { echo 'text-indent:70px;'; } ?>" >
                            <? if($row['pc_depth'] > 0) { echo '└'; }?>
							<?=$row['pc_name']?>
						</td>
						<td class="text-center">
							<?=$row['pc_depth']?>
						</td>
						<td class="text-center">
                            <?
                            if($row['pc_depth'] < 2) {
                            ?>
                            <input type="button" class="btn btn-outline-primary btn-sm" value="추가" onclick="f_view_category('<?=$row['pc_idx'];?>', '<?=$row['pc_m_idx'];?>', '<?=$row['pc_s_idx'];?>', 'write', '<?=$row['pc_depth']?>')" />
                            <?
                            }
                            ?>
							<input type="button" class="btn btn-outline-primary btn-sm" value="수정" onclick="f_view_category('<?=$row['pc_idx'];?>')" />
							<input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="product_category_del('<?php echo $row['idx'];?>');" />
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
isChange = false;
jQuery(function () {
    $('select[name=pc_view').on('change', function(e) {		//컨텐츠 카테고리 노출여부 변경
        var pc_view = $(this).val();
        var pc_idx = $(this).parent().parent().find('#chk_all').val();
        $.ajax({
            type : 'post',
            url : './ajax.product.php',
            dataType : 'json',
            data : { act : 'pc_view', pc_view : pc_view, pc_idx : pc_idx},
            success : function(d, s){
                alert(d.msg);                
            },
            cache : false
        });
    });

	$('#product_category_del').click(function(){		//컨텐츠 카테고리 선택 삭제
		var obj = f_checkbox_cnt();
		var pc_idx_obj = obj.ot_pcode;		
		if(obj=='') {
			return false;
		}
		product_category_del(pc_idx_obj);
	});
});

function product_category_del(idx){	//컨텐츠 카테고리 삭제
	if(confirm("정말 삭제 하시겠습니까?")) {
		$.ajax({
				type : 'post',
				url : './ajax.product.php',
				dataType : 'json',
				data : { act : 'pc_del', pc_idx_obj : idx},
				success : function(d, s){
					alert(d.msg);
					if(d.result=='_ok')	location.reload();
				},
				cache : false
			});
	}
}

function product_category_update(){		//컨텐츠 카테고리 등록 및 수정
	$.ajax({
		type : 'post',
		url : './ajax.product.php',
		dataType : 'json',
		data : $("form[name=f_product_category]").serialize(),
		success : function(d, s){
			alert(d.msg);
			if(d.result=='_ok')	location.reload();
		},
		cache : false
	});
}

function f_view_category(idx, m_idx, s_idx, act, depth){		//모달창
	$.post('./ajax.product.php', {type: 'pc_modal', idx: idx, m_idx:m_idx, s_idx:s_idx, act:act, depth:depth}, function (data) {
	if(data){
			$('#modal-default-content').html(data);
			$('#modal-default-size').css('max-width', '800px');
			$('#modal-default').addClass('modal-dialog-centered');
			$('#modal-default').addClass('modal-dialog-scrollable');
			$('#modal-default').modal();			
		}
	});
	return false;
}

</script>
<?
	include "./foot_inc.php";
?>