<?
	include "./head_inc.php";
	$chk_menu = '2';
	$chk_sub_menu = '2';
	include "./head_menu_inc.php";

    $n_limit = $n_limit_num;
    $pg = $_GET['pg'];
    $_colspan_txt = "8";

    $_get_txt = "pg=";
?>
<!-- 메인 시작 -->
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">랜덤상품매칭</h4>
                    <div class="p-3 float-right">
                        <form method="get" name="" id="" class="form-inline">
                        <div class="form-group mx-sm-1">
                            <select name="sel_effect" id="sel_effect" class="form-control form-control-sm">
                                <option value="">효과영상선택</option>
                                <option value="1">효과1</option>
                                <option value="2">효과2</option>
                            </select>
                        </div>
                        <div class="form-group mx-sm-1">
                            <input type="button" class="btn btn-secondary" value="삭제" onclick="delete_product()">
                        </div>

                        <div class="form-group mx-sm-1">
                            <input type="button" class="btn btn-primary" value="추가" onclick="pop_up()">
                        </div>
                        </form>
                    </div>
					<p class="card-description">
						<table class="table">
						<tbody>
						<tr style="background-color: #f3f3f3;">
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
                            <th class="text-center">
                                이펙트
                            </th>
                            <th class="text-center" style="width: 10%">
                                확률
                            </th>
						</tr>
						<?
							unset($list);
							$query = "
								select *, idx as pt_idx from product_t a1 where pt_random_chk = 'Y' 
							";
                            $query_count = "
                                select count(*) from product_t a1 where pt_random_chk = 'Y' 
                            ";

                            $row_cnt = $DB->fetch_query($query_count);
                            $count_query = $row_cnt[0];
                            $counts = $count_query;
                            $n_page = ceil($count_query / $n_limit_num);
                            if($pg=="") $pg = 1;
                            $n_from = ($pg - 1) * $n_limit;
                            $counts = $counts - (($pg - 1) * $n_limit_num);

                            unset($list);
                            $sql_query = $query." order by a1.idx desc limit ".$n_from.", ".$n_limit;
                            $list = $DB->select_query($sql_query);

                            if($list) {
                                foreach($list as $row) {
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
                                        if($row['pt_stock_chk'] == "Y") {
                                            echo number_format($row['pt_stock']);
                                        } else {
                                            echo "무제한";
                                        }
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
                                        if($row['pt_random_effect']=='1') echo '효과1';
                                        if($row['pt_random_effect']=='2') echo '효과2';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="text" class="form-control" name="pt_random_percentage" id="pt_random_percentage<?= $row['pt_idx'] ?>" value="<?if($row['pt_random_percentage'] == null) echo '0'; else echo $row['pt_random_percentage'];?>" data-idx="<?= $row['pt_idx'] ?>" numberonly=""/>
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
					</p>
                    <div class="p-3 float-right">
                        <form method="get" name="" id="" class="form-inline">
                            <div class="form-group mx-sm-1">
                                <input type="button" class="btn btn-primary" value="확률 저장" onclick="save_percentage()">
                            </div>
                        </form>
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>
<? include_once("../inc/product_modal.php"); ?>
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
    var previous;
    $("#sel_effect").on('focus', function () {
        previous = this.value;
    }).change(function() {
        var list = $("input[name='chk_box']");
        var ids = [];
        for (var i = 0; i < list.length; i++) {
            if ($("#" + list[i].id).is(":checked")) {
                var id = list[i].id;
                id = id.replace("chk_box_", "");
                ids.push(id);
            }
        }
        if($("#sel_effect").val() != "") {
            if(confirm("효과를 변경하시겠습니까?")) {
                if(ids.length == 0) {
                    alert("효과를 변경할 상품을 선택해주세요.");
                    $("#sel_effect").val(previous);
                    return false;
                }
                $.ajax({
                    type: 'post',
                    url: './product_random_update.php',
                    dataType: 'json',
                    data: {act: 'sel_effect', pt_idx: ids, effect:$("#sel_effect").val()},
                    success: function (d, s) {
                        if (d['result'] == "_ok") {
                            alert(d['msg']);
                            location.reload();
                        }
                    },
                    cache: false
                });
            } else {
                $("#sel_effect").val(previous);
            }
        }
    });
    function delete_product() {
        var list = $("input[name='chk_box']");
        var ids = [];
        for (var i = 0; i < list.length; i++) {
            if ($("#" + list[i].id).is(":checked")) {
                var id = list[i].id;
                id = id.replace("chk_box_", "");
                ids.push(id);
            }
        }
        if(ids.length == 0) {
            alert("랜덤상품에서 삭제할 상품을 선택해주세요.");
            return false;
        }
        $.ajax({
            type: 'post',
            url: './product_random_update.php',
            dataType: 'json',
            data: {act: 'delete_product', pt_idx: ids},
            success: function (d, s) {
                if (d['result'] == "_ok") {
                    alert(d['msg']);
                    location.reload();
                }
            },
            cache: false
        });
    }
    function pop_up() {
        $.post('./product_random_update.php', {act: 'content_view'}, function (data) {
            if(data) {
                $('#product_modal-content').html(data);
                $('#product_modal').modal();

                get_list();
            }
        });

        return false;
    }
    function save_percentage() {
        var list = $("input[name='pt_random_percentage']");
        var sum = 0;
        var ids = [];
        var val = [];
        for(var i=0; i<list.length; i++) {
            sum += parseInt(list[i].value);
            ids.push($("#"+list[i].id).data("idx"));
            val.push(parseInt(list[i].value));
        }
        if(sum > 100) {
            alert("확률의 합이 100을 넘을 수 없습니다.");
            return false;
        } else {
            $.ajax({
                type: 'post',
                url: './product_random_update.php',
                dataType: 'json',
                data: {act: 'save_percentage', pt_idx: ids, pt_random_percentage: val},
                success: function (d, s) {
                    if (d['result'] == "_ok") {
                        alert(d['msg']);
                        location.reload();
                    }
                },
                cache: false
            });
        }
    }
</script>
<!-- 메인 끝 -->
<?
	include "./foot_inc.php";
?>