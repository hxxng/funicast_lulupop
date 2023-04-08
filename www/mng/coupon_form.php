<?
	include "./head_inc.php";
	$chk_menu = '6';
	$chk_sub_menu = '2';
	include "./head_menu_inc.php";

    if($_GET['act']=="update") {
        $query_pt = "
                select *, a1.idx as ct_idx from coupon_t a1
                where a1.idx = '".$_GET['ct_idx']."'
            ";
        $row = $DB->fetch_query($query_pt);

        $_act = "update";
        $_act_txt = "수정";
    } else {
        $_act = "input";
        $_act_txt = "생성";
    }

    $_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pg=";
?>
<!-- 메인 시작 -->
<div class="content-wrapper">
    <div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <div class="card-body">
                    <p class="mb-0">
                    <form method="post" name="frm_form" id="frm_form" action="./coupon_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" >
                    <input type="hidden" name="act" id="act" value="<?=$_act?>" />
                    <input type="hidden" name="ct_idx" id="ct_idx" value="<?=$_GET['ct_idx']?>" />
                    <h4 class="card-title">쿠폰 <?=$_act_txt?></h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_delivery_price" class="col-sm-2 col-form-label">타이틀 <b class="text-danger">*</b></label>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <input type="text" name="ct_name" id="ct_name" value="<?=$row['ct_name']?>" class="form-control" >
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_refund_price" class="col-sm-2 col-form-label">사용가능일 <b class="text-danger">*</b></label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="date" name="ct_sdate" id="ct_sdate" value="<?=$row['ct_sdate']?>" class="form-control datepicker" />
                                        <span class="m-2">부터 </span>
                                        <input type="date" name="ct_edate" id="ct_edate" value="<?=$row['ct_edate']?>" class="form-control ml-4 datepicker" />
                                        <span class="m-2">까지 </span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_exchange_price" class="col-sm-2 col-form-label">할인가격 <b class="text-danger">*</b></label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <input type="text" name="ct_sale_price" id="ct_sale_price" value="<?=$row['ct_sale_price']?>" class="form-control" numberonly="" maxlength="10">
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
                                <label for="pt_exchange_price" class="col-sm-2 col-form-label">최소결제금액 <b class="text-danger">*</b></label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <input type="text" name="ct_min_price" id="ct_min_price" value="<?=$row['ct_min_price']?>" class="form-control" numberonly="" maxlength="10">
                                        <div class="input-group-append">
                                            <span class="input-group-text">원</span>
                                        </div>
                                        <span class="col-form-label">&nbsp; 이상부터 사용가능</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_exchange_price" class="col-sm-2 col-form-label">사용가능인원 <b class="text-danger">*</b></label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <input type="text" name="ct_use_person" id="ct_use_person" value="<?=$row['ct_use_person']?>" class="form-control" numberonly="" maxlength="10">
                                        <div class="input-group-append">
                                            <span class="input-group-text">명</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="p-2 mt-3 text-left">
                        <span><b class="text-danger">*</b> 쿠폰은 중복할인 불가합니다.</span>
                    </div>
                    <div class="p-2 mt-3 text-center">
                        <button type="submit" class="btn btn-primary"><?=$_act_txt?></button>
                        <input type="button" value="목록" onclick="location.href='./coupon_list.php?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
                    </div>
                    </p>
                    </form>
                </div>
            </div>
		</div>
	</div>
</div>
<script type="text/javascript">
        function frm_form_chk(f) {
            if(f.ct_name.value=="") {
                alert("타이틀을 입력해주세요.");
                f.ct_name.focus();
                return false;
            }
            if(f.ct_sdate.value=="") {
                alert("사용가능 시작날짜을 입력해주세요.");
                f.ct_sdate.focus();
                return false;
            }
            if(f.ct_edate.value=="") {
                alert("사용가능 종료날짜을 입력해주세요.");
                f.ct_edate.focus();
                return false;
            }
            if(f.ct_sale_price.value=="") {
                alert("할인가격을 입력해주세요.");
                f.ct_sale_price.focus();
                return false;
            }
            if(f.ct_min_price.value=="") {
                alert("최소결제금액을 입력해주세요.");
                f.ct_min_price.focus();
                return false;
            }
            if(f.ct_use_person.value=="") {
                alert("사용가능인원을 입력해주세요.");
                f.ct_use_person.focus();
                return false;
            }
        }
    </script>
<!-- 메인 끝 -->
<?
	include "./foot_inc.php";
?>