<?
include "./head_inc.php";
$chk_menu = '3';
$chk_sub_menu = '2';
include "./head_menu_inc.php";

$_act = $_GET['act'];

$_get_txt = "search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
if($_GET['act']=='view_order') {
    $query_ot = "
			select * from order_t a1 left join cart_t on cart_t.ot_code = a1.ot_code
			where a1.ot_code = '".$_GET['ot_code']."' and ct_status in (80,81,82)
		";
    $row_ot = $DB->fetch_query($query_ot);
    $list_pt = $DB->select_query($query_ot);

    $pt_info = get_product_t_info($row_ot['pt_idx']);
    ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form method="post" name="frm_form" id="frm_form" action="order_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm">
                            <h4 class="card-title">교환 상세 내역
                                <div class="text-right">
                                    <input type="button" value="목록" onclick="location.href='./exchange_list.php?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
                                    <!--                                <input type="submit" value="확인" class="btn btn-info" />-->
                                </div>
                            </h4>
                            <input type="hidden" name="act" id="act" value="order_detail_update" />
                            <input type="hidden" name="ot_code" id="ot_code" value="<?=$row_ot['ot_code']?>" />
                            <div class="faq-section">
                                <div id="order_accordion" class="accordion acc_card">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">주문 상세 정보</h5>
                                        </div>
                                        <div id="order_collapse_<?=$order_acc_iid?>" class="collapse show" aria-labelledby="order_head_<?=$order_acc_iid?>" data-parent="#order_accordion">
                                            <div class="card-body">
                                                <p class="mb-0">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">주문번호</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_code" value="<?=$row_ot['ot_code']?>">
                                                            </div>
                                                            <label class="col-sm-2 col-form-label">구매자명</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_code" value="<?=$row_ot['ot_name']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">주문일</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pcode" value="<?=DateType($row_ot['ot_wdate'],1)?>">
                                                            </div>
                                                            <label class="col-sm-2 col-form-label">주문수량</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_code" value="<?=$row_ot['ot_qty']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">주문상태</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pay_type" value="<?=$arr_ct_status[$row_ot['ct_status']]?>">
                                                            </div>
                                                            <label class="col-sm-2 col-form-label">결제방식</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pay_type" value="<?=$arr_ct_method[$row_ot['ot_pay_type']]?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">교환 요청 정보</h5>
                                        </div>
                                        <div id="order_collapse_<?=$order_acc_iid?>" class="collapse show" aria-labelledby="order_head_<?=$order_acc_iid?>" data-parent="#order_accordion">
                                            <div class="card-body">
                                                <p class="mb-0">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">교환요청번호</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ct_request_code" value="<?=$row_ot['ct_request_code']?>">
                                                            </div>
                                                            <label class="col-sm-2 col-form-label">교환요청일</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_code" value="<?=DateType($row_ot['ct_request_wdate'],1)?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">교환완료일</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pay_type" value="<?=DateType($row_ot['ct_request_ydate'],1)?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header row" id="order_head_<?=$order_acc_iid?>">
                                            <h5 class="mb-0 col-6">회수 요청 정보</h5>
                                            <div class="row col-6 align-items-center">
                                                <select class="form-control col-2" id="ct_collect_com">
                                                    <option value="">택배사선택</option>
                                                    <?=$ct_delivery_com_option?>
                                                </select>
                                                <input type="text" class="form-control col-6 ml-2" id="ct_collect_number" numberonly="" value="<?=$row_ot['ct_collect_number']?>" placeholder="송장번호를 입력해주세요.">
                                                <button type="button" class="btn btn-sm btn-primary ml-2" style="height: 100%" onclick="save_collect_delivery()">저장</button>
                                            </div>
                                        </div>
                                        <div id="order_collapse_<?=$order_acc_iid?>" class="collapse show" aria-labelledby="order_head_<?=$order_acc_iid?>" data-parent="#order_accordion">
                                            <div class="card-body">
                                                <p class="mb-0">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">요청자</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ct_collect_name" value="<?=$row_ot['ct_collect_name']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">연락처</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ct_collect_hp" value="<?=$row_ot['ct_collect_hp']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">주소</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pay_type" value="<?="(".$row_ot['ct_collect_zip'].") ".$row_ot['ct_collect_addr1']." ".$row_ot['ct_collect_addr2']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        $("#ct_collect_com").val("<?=$row_ot['ct_collect_com']?>").prop("selected", true);
                                    </script>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">교환 요청 상품</h5>
                                        </div>
                                        <div id="order_collapse_<?=$order_acc_iid?>" class="collapse show" aria-labelledby="order_head_<?=$order_acc_iid?>" data-parent="#order_accordion">
                                            <div class="card-body">
                                                <p class="mb-0">
                                                <table class="table">
                                                    <tbody>
                                                    <tr>
                                                        <table class="table table-striped">
                                                            <thead>
                                                            <tr>
                                                                <th class="text-center">
                                                                    번호
                                                                </th>
                                                                <th class="text-center">
                                                                    상품코드
                                                                </th>
                                                                <th class="text-center" style="width: 400px;">
                                                                    상품명
                                                                </th>
                                                                <th class="text-center">
                                                                    상품금액
                                                                </th>
                                                                <th class="text-center">
                                                                    상품개수
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?
                                                            if($list_pt) {
                                                                $i = 1;
                                                                foreach ($list_pt as $row_ct) {
                                                                    ?>
                                                                    <tr>
                                                                        <td class="text-center">
                                                                            <?=$i?>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <?=$row_ct['pt_code']?>
                                                                        </td>
                                                                        <td class="text-center" style="width: 50%">
                                                                            <?=$row_ct['pt_title']?>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <?=number_format($row_ct['ct_opt_price'])?>원
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <?=$row_ct['ct_opt_qty']?>
                                                                        </td>
                                                                    </tr>
                                                                    <?
                                                                    $i++;
                                                                }
                                                            }
                                                            ?>
                                                            </tbody>
                                                        </table>
                                                        <nav id="page" class="m-3" aria-label="Page navigation">
                                                        </nav>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">교환요청사유</h5>
                                        </div>
                                        <div id="order_collapse_<?=$order_acc_iid?>" class="collapse show" aria-labelledby="order_head_<?=$order_acc_iid?>" data-parent="#order_accordion">
                                            <div class="card-body">
                                                <p class="mb-0">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">분류</label>
                                                            <div class="col-sm-4">
                                                                <?
                                                                if($row_ot['ct_request_type'] == 1) $type = "상품 오배송/불량";
                                                                if($row_ot['ct_request_type'] == 2) $type = "물건 퀄리티 문제";
                                                                if($row_ot['ct_request_type'] == 3) $type = "기타";
                                                                ?>
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_code" value="<?=$type?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">상세내용</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pcode" value="<?=$row_ot['ct_request_reason']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">재발송요청주소</h5>
                                        </div>
                                        <div id="order_collapse_<?=$order_acc_iid?>" class="collapse show" aria-labelledby="order_head_<?=$order_acc_iid?>" data-parent="#order_accordion">
                                            <div class="card-body">
                                                <p class="mb-0">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">받는사람</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ct_request_name" value="<?=$row_ot['ct_request_name']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">연락처</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ct_request_hp" value="<?=$row_ot['ct_request_hp']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">주소</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pay_type" value="<?="(".$row_ot['ct_request_zip'].") ".$row_ot['ct_request_addr1']." ".$row_ot['ct_request_addr2']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card" id="print_this3">
                                        <div class="card-header row" id="order_head_<?=$order_acc_iid?>">
                                            <h5 class="mb-0 col-6">재배송처리 정보</h5>
                                            <div class="row col-6 align-items-center">
                                                <select class="form-control col-2" id="ct_delivery_com">
                                                    <option value="">택배사선택</option>
                                                    <?=$ct_delivery_com_option?>
                                                </select>
                                                <input type="text" class="form-control col-6 ml-2" id="ct_delivery_number" numberonly="" placeholder="송장번호를 입력해주세요.">
                                                <button type="button" class="btn btn-sm btn-primary ml-2" style="height: 100%" onclick="save_delivery()">저장</button>
                                            </div>
                                        </div>
                                        <div id="order_collapse_<?=$order_acc_iid?>" class="collapse show" aria-labelledby="order_head_<?=$order_acc_iid?>" data-parent="#order_accordion">
                                            <div class="card-body">
                                                <p class="mb-0">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">택배사</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" class="form-control-plaintext" value="<?=$row_ot['ct_delivery_com']?>">
                                                            </div>
                                                            <label class="col-sm-2 col-form-label">송장번호</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" class="form-control-plaintext" value="<?=$row_ot['ct_delivery_number']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <!--                                <p class="p-3 mt-3 text-center">-->
                                    <!--                                    <input type="button" value="인쇄" id="print_this" class="btn btn-outline-secondary mx-2" />-->
                                    <!--                                    <input type="submit" value="등록" class="btn btn-info" />-->
                                    <!--                                </p>-->
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function save_delivery() {
            if($("#ct_delivery_com").val() == "") {
                alert("택배사를 선택해주세요.");
                return false;
            }
            if($("#ct_delivery_number").val() == "") {
                alert("송장번호를 입력해주세요.");
                $("#ct_delivery_number").focus();
                return false;
            }
            $.ajax({
                type : 'post',
                url : './exchange_update.php',
                dataType : 'json',
                data : { act : 'save_delivery', ot_code : $("#ot_code").val(), ct_delivery_com : $("#ct_delivery_com").val(), ct_delivery_number: $("#ct_delivery_number").val()},
                success : function(d, s){
                    if(d.result == "_ok") {
                        alert(d.msg);
                        location.reload();
                    } else {
                        console.log(d.msg);
                    }
                },
                cache : false
            });
        }
        function save_collect_delivery() {
            if($("#ct_collect_com").val() == "") {
                alert("택배사를 선택해주세요.");
                return false;
            }
            if($("#ct_collect_number").val() == "") {
                alert("송장번호를 입력해주세요.");
                $("#ct_collect_number").focus();
                return false;
            }
            $.ajax({
                type : 'post',
                url : './exchange_update.php',
                dataType : 'json',
                data : { act : 'save_collect_delivery', ot_code : $("#ot_code").val(), ct_collect_com : $("#ct_collect_com").val(), ct_collect_number: $("#ct_collect_number").val()},
                success : function(d, s){
                    if(d.result == "_ok") {
                        alert(d.msg);
                        location.reload();
                    } else {
                        console.log(d.msg);
                    }
                },
                cache : false
            });
        }
    </script>
    <?
}

include "./foot_inc.php";
?>