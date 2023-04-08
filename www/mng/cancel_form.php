<?
include "./head_inc.php";
$chk_menu = '3';
$chk_sub_menu = '4';
include "./head_menu_inc.php";

$_act = $_GET['act'];

$_get_txt = "search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
if($_GET['act']=='view_order') {
    $query_ot = "
			select * from order_t a1 left join cart_t on cart_t.ot_code = a1.ot_code
			where a1.ot_code = '".$_GET['ot_code']."' and ct_status in (7,8)
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
                            <h4 class="card-title">취소 상세 내역
                                <div class="text-right">
                                    <input type="button" value="목록" onclick="location.href='./refund_list.php?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
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
                                            <h5 class="mb-0">배송지</h5>
                                        </div>
                                        <div id="order_collapse_<?=$order_acc_iid?>" class="collapse show" aria-labelledby="order_head_<?=$order_acc_iid?>" data-parent="#order_accordion">
                                            <div class="card-body">
                                                <p class="mb-0">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">받는사람</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_code" value="<?=$row_ot['ot_b_name']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">연락처</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pcode" value="<?=$row_ot['ot_b_hp']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">주소</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pay_type" value="<?="(".$row_ot['ot_b_zip'].") ".$row_ot['ot_b_addr1']." ".$row_ot['ot_b_addr2']?>">
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label class="col-sm-2 col-form-label">배송시 전달사항</label>
                                                            <div class="col-sm-4">
                                                                <input type="text" readonly class="form-control-plaintext" id="ot_pay_type" value="<?=$row_ot['ot_requests']?>">
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
                                            <h5 class="mb-0">취소요청상품</h5>
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
                                            <h5 class="mb-0">취소요청사유</h5>
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
<!--                                                <p class="p-3 text-center">-->
<!--                                                    <button type="button" class="btn btn-primary" onclick="refund()">환불완료</button>-->
<!--                                                </p>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function refund() {
            $.ajax({
                type : 'post',
                url : './cancel_update.php',
                dataType : 'json',
                data : { act : 'refund', ot_code : $("#ot_code").val()},
                success : function(d, s){
                    if(d.result == "_ok") {
                        alert(d.msg);
                        location.reload();
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