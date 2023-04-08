<?
    include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
    include $_SERVER['DOCUMENT_ROOT']."/lib/http.class.php";
    include $_SERVER['DOCUMENT_ROOT']."/lib/Membership_class.php";
    include $_SERVER['DOCUMENT_ROOT']."/lib/Point_class.php";

    $chk_menu = '5';
    $chk_sub_menu = '3';

	$_act = $_GET['act'];

    $_get_txt = "sel_ct_status=".$_GET['sel_ct_status']."&sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pg=".$_GET['pg'];

	if($_POST['act']=='cancel_modal') {
		if($_POST['ct_status']=='2') {
			$modal_title = '취소거부';
			$modal_body_help = '* 구매자로부터 취소 요청된 주문건을 취소 거부하는 기능입니다. 발송대기 상태로 변경되며 발송처리를 완료하셔야 합니다.';
		} else if($_POST['ct_status']=='90') {
			$modal_title = '반품거부';
			$modal_body_help = '* 반품 상품에 대해 특정사유로 환불이 불가한 경우 거부처리 하는 기능입니다. 반품 거부처리 전, 구매자와 협의를 진행해 주세요. 주문상태는 배송완료로 처리됩니다.';
		} else if($_POST['ct_status']=='80') {
			$modal_title = '교환거부';
			$modal_body_help = '* 교환 요청한 상품에 대해 특정사유로 교환이 불가한 경우 거부처리 하는 기능입니다. 단, 이미 교환상품에 대해 재배송처리를 하셨다면 거부가 불가하니 구매자와 협의를 진행해주세요.';
		} else {
			$modal_title = '취소처리';
			$modal_body_help = '* 취소 처리하는 기능입니다.';
		}
?>
	<div class="modal-header">
		<h5 class="modal-title" id="staticBackdropLabel"><?=$modal_title?></h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		<p class="text-danger"><?=$modal_body_help?></p>

		<hr/>

		<form method="post" name="frm_form" id="frm_form" action="order_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm">
		<input type="hidden" name="act" id="act" value="cancel_input" />
		<input type="hidden" name="ot_pcode" id="ot_pcode" value="<?=$_POST['ot_pcode']['ot_pcode']?>" />
		<input type="hidden" name="ot_status" id="ot_status" value="<?=$_POST['ct_status']?>" />
			<div class="form-group">
				<label for="ct_cancel_reason">사유</label>
				<textarea name="ot_refund_info" id="ot_refund_info" style="height: 100px;" class="form-control"></textarea>
			</div>
			<button type="submit" class="btn btn-primary">확인</button>
		</form>

		<script type="text/javascript">
			function frm_form_chk(f) {
				if(f.ot_refund_info.value=="") {
					alert("취소사유를 입력해주세요.");
					f.ot_refund_info.focus();
					return false;
				}
				return true;
			}
		</script>
	</div>
<?
	} else if($_POST['act']=='cancel_input') {

		if($_POST['ot_pcode']=='') {
			p_alert('잘못된 접근입니다.');
		} else {
			$ot_pcode_ex = explode("|", $_POST['ot_pcode']);
			if($_POST['ct_status']) {
				if($_POST['ct_status']=='7') {
                    $ot_status_t = $_POST['ct_status'];
				} else {
                    p_alert("잘못된 접근입니다.");
                }
			}
            $objPayment = new Membership_class(array('db' => $DB, 'act' => $merchant_arr[0]));
            $objPoint = new Point_class(array('db'=>$DB, 'mt_idx'=>$_SESSION['_mt_idx']));
            $ot_pcode = str_replace("|", "','", $_POST['ot_pcode']);
            $query_c = "select *, cart_t.ot_pcode as ot_pcode_c from cart_t left join order_t on order_t.ot_code = cart_t.ot_code where cart_t.ot_pcode in ('" . $ot_pcode . "')";
            $list = $DB->select_query($query_c);
            if($list) {
                foreach ($list as $row) {
                    $imp_uid = $row['ot_pg_pg_tid'];
                    $count = $DB->count_query("select * from cart_t where ot_code = '" . $row['ot_code'] . "'");
                    $result = ["code"=>200, "message"=>"success"];
                    if($row['ot_pay_type'] == 1) {     //무통장입금
                        $pay_arr = array();
                        //결제 내역 테이블 update
                        $ot_use_point = $row['ot_use_point'];
                        if ($row['ot_use_point']) {
                            //포인트 사용 환불 처리
                            $query_r = "select * from cart_t left join order_t on cart_t.ot_code = order_t.ot_code where cart_t.ot_code = '" . $row['ot_code'] . "' and cart_t.ot_pcode in ('" . $ot_pcode . "')";
                            $count_refund = $DB->count_query($query_r);

                            if ($count - $count_refund < 1) {
                                $cnt = 1;
                            } else {
                                $cnt = $count;
                            }

                            $arr = array('point' => round($row['ot_use_point'] / $cnt), 'ot_code' => $row['ot_code'], 'plt_memo' => "상품 [" . $row['pt_title'] . "] 환불");
                            $mt_point = $objPoint->get_point_sum($row['mt_idx']);

                            //포인트 생성
                            $plt_expire_date = date("Y-m-d", strtotime("+1 year"));
                            $plt_expired = 0;
                            if ($arr['point'] < 0) {
                                $plt_expired = 1;
                                $plt_expire_date = date('Y-m-d');
                            }
                            $plt_mt_point = $mt_point + $arr['point'];

                            $set = array();
                            $set['mt_idx'] = $row['mt_idx'];
                            if ($arr['pt_idx']) $set['pt_idx'] = $arr['pt_idx'];
                            if ($arr['ot_code']) $set['ot_code'] = $arr['ot_code'];
                            if ($arr['ot_pcode']) $set['ot_pcode'] = $arr['ot_pcode'];

                            $set['plt_type'] = ($arr['point'] < 0) ? 'M' : 'P';//$arr['plt_type'];   //P적립, M 차감
                            $set['plt_price'] = $arr['point'];
                            $set['plt_use_point'] = 0;
                            $set['plt_mt_point'] = $plt_mt_point;
                            $set['plt_expired'] = $plt_expired;
                            $set['plt_expire_date'] = $plt_expire_date;
                            $set['plt_memo'] = $arr['plt_memo'];
                            $set['plt_wdate'] = date('Y-m-d H:i:s');
                            $DB->insert_query('point_log_t', $set);
                            $DB->update_query('member_t', array('mt_point' => $plt_mt_point), "idx=" . $row['mt_idx']);

                            $ot_use_point = $row['ot_use_point'] - $arr['point'];
                        }

                        unset($arr_query);
                        $arr_query = array(
//                                "ot_status" => 9,
                            "ot_cedate" => "now()",
                            "ot_pg_cancel_rem_mny" => $row['ot_amount'] - $row['ct_price'],
                            "ot_cancel_price" => $row['ct_price'],
                            "ot_refund_info" => $_POST['ot_refund_info'],
                            "ot_use_point" => $ot_use_point,
                        );
                        $DB->update_query('order_t', $arr_query, " ot_cdoe = '" . $row['ot_cdoe'] . "'");
                        $DB->update_query("cart_t", array("ct_status" => 8, 'ct_request_ydate' => "now()", "ct_return_complete_price" => $row['ct_price']), " ot_pcode = '" . $row['ot_pcode_c'] . "'");
                    } else {
                        $http = new http;
                        try {
                            $query['imp_key'] = $imp_key;
                            $query['imp_secret'] = $imp_secret;
                            $getToken = $http->PostMethodData('https://api.iamport.kr/users/getToken', $query, $mReferer, '', $mCookie, true);

                            $getTokenJson = json_decode($getToken, true);
                            $access_token = $getTokenJson['response']['access_token'];

                            $data['reason'] = $row['ct_request_reason'];
                            $data['imp_uid'] = $imp_uid;
                            $data['amount'] = $amount;
                            $data['checksum'] = $cancelableAmount;

                            $postPaymentData = $objPayment->PostMethodData2('https://api.iamport.kr/payments/cancel', $data, $mReferer, '', $mCookie, true, "", $access_token);
                            $postPaymentDataJson = json_decode($postPaymentData, true);

                            //아임포트에 요청한 실제 결제 정보
                            $responseData = $postPaymentDataJson['response'];

                            $pay_arr = array();
                            //결제 내역 테이블 update
                            if ($postPaymentDataJson['code'] == 0) {    //성공
                                $ot_use_point = $row['ot_use_point'];
                                if ($row['ot_use_point']) {
                                    //포인트 사용 환불 처리
                                    $query_r = "select * from cart_t left join order_t on cart_t.ot_code = order_t.ot_code where cart_t.ot_code = '" . $row['ot_code'] . "' and cart_t.ot_pcode in ('" . $ot_pcode . "')";
                                    $count_refund = $DB->count_query($query_r);

                                    if ($count - $count_refund < 1) {
                                        $cnt = 1;
                                    } else {
                                        $cnt = $count;
                                    }

                                    $arr = array('point' => round($row['ot_use_point'] / $cnt), 'ot_code' => $row['ot_code'], 'plt_memo' => "상품 [" . $row['pt_title'] . "] 환불");
                                    $mt_point = $objPoint->get_point_sum($row['mt_idx']);

                                    //포인트 생성
                                    $plt_expire_date = date("Y-m-d", strtotime("+1 year"));
                                    $plt_expired = 0;
                                    if ($arr['point'] < 0) {
                                        $plt_expired = 1;
                                        $plt_expire_date = date('Y-m-d');
                                    }
                                    $plt_mt_point = $mt_point + $arr['point'];

                                    $set = array();
                                    $set['mt_idx'] = $row['mt_idx'];
                                    if ($arr['pt_idx']) $set['pt_idx'] = $arr['pt_idx'];
                                    if ($arr['ot_code']) $set['ot_code'] = $arr['ot_code'];
                                    if ($arr['ot_pcode']) $set['ot_pcode'] = $arr['ot_pcode'];

                                    $set['plt_type'] = ($arr['point'] < 0) ? 'M' : 'P';//$arr['plt_type'];   //P적립, M 차감
                                    $set['plt_price'] = $arr['point'];
                                    $set['plt_use_point'] = 0;
                                    $set['plt_mt_point'] = $plt_mt_point;
                                    $set['plt_expired'] = $plt_expired;
                                    $set['plt_expire_date'] = $plt_expire_date;
                                    $set['plt_memo'] = $arr['plt_memo'];
                                    $set['plt_wdate'] = date('Y-m-d H:i:s');
                                    $DB->insert_query('point_log_t', $set);
                                    $DB->update_query('member_t', array('mt_point' => $plt_mt_point), "idx=" . $row['mt_idx']);

                                    $ot_use_point = $row['ot_use_point'] - $arr['point'];
                                }

                                unset($arr_query);
                                $arr_query = array(
//                                "ot_status" => 9,
                                    "ot_cedate" => "now()",
                                    "ot_pg_cancel_rem_mny" => $row['ot_amount'] - $responseData['cancel_amount'],
                                    "ot_cancel_price" => $responseData['cancel_amount'],
                                    "ot_refund_info" => $_POST['ot_refund_info'],
                                    "ot_use_point" => $ot_use_point,
                                );

                                $DB->update_query('order_t', $arr_query, " ot_pg_pg_tid = '" . $responseData['imp_uid'] . "'");
                                $DB->update_query("cart_t", array("ct_status" => 8, 'ct_request_ydate' => "now()", "ct_return_complete_price" => $responseData['cancel_amount']), " ot_pcode = '" . $row['ot_pcode_c'] . "'");

                                echo json_encode($result);
                                p_alert('취소 처리되었습니다.', "./order_list.php");
                            } else {
                                p_alert('취소 처리 실패 하였습니다');
                            }
                        } catch (Exception $e) {
                            $result = [
                                'code' => 410,
                                'message' => $e->getMessage()
                            ];
                        }
                    }
//                    $DB->insert_query("zzz", array("pagename"=>$_SERVER['PHP_SELF'] ,"contents"=>json_encode($postPaymentDataJson), "remoteip"=>$_SERVER['REMOTE_ADDR'], "regdate"=>"now()"));
                }
            }
		}
	} else if($_POST['act']=='status_chg') {
		if($_POST['ot_pcode']=='') {
			echo "N";
		} else {
			$ot_pcode_ex = explode("|", $_POST['ot_pcode']['ot_pcode']);
			foreach($ot_pcode_ex as $key => $val) {
				if($val) {
					$ot_pcode_t = trim($val);
					unset($arr_query);
					$arr_query = array(
						"ct_status" => $_POST['chg_status'],
						"ot_ldate" => "now()",
					);
                    $query = "select * from cart_t where ot_pcode = '".$ot_pcode_t."'";
                    $list = $DB->fetch_assoc($query);
                    
                    if($_POST['chg_status'] == 2) {     //무통장입금 결제 확인처리
                        $DB->update_query('order_t', array("ot_pdate" => "now()", "ot_status" => 2), "ot_code = '".$list['ot_code']."'");
						$DB->update_query('cart_t', array("ct_pdate" => "now()", "ct_status" => $_POST['chg_status']), "ot_pcode = '".$ot_pcode_t."'");
                    } else if($_POST['chg_status'] == 3) {    //발주 처리
                        $DB->update_query('order_t', array("ot_ldate" => "now()"), "ot_code = '".$list['ot_code']."'");
						$DB->update_query('cart_t', array("ct_ldate" => "now()", "ct_status" => $_POST['chg_status']), "ot_pcode = '".$ot_pcode_t."'");
                    }
				}
			}
			echo "Y";
		}
	} else if($_POST['act']=='delivery_update') {
		if($_POST['ot_pcode'][0]=='') {
			p_alert('잘못된 접근입니다.');
		} else {
			foreach($_POST['ot_pcode'] as $key => $val) {
				if($val) {
					$ot_pcode_t = trim($val);

					unset($arr_query);
                    $arr_query = array(
                        "ot_dsdate" => "now()",
                        "ot_delivery_com" => $_POST['ot_delivery_com'][$key],
                        "ot_delivery_number" => $_POST['ot_delivery_number'][$key],
                    );

                    $query = "select * from cart_t where ot_pcode = '".$ot_pcode_t."'";
                    $list = $DB->fetch_assoc($query);

					$DB->update_query('cart_t', array("ct_dsdate"=>"now()", "ct_status" => 4),  "ot_pcode = '".$ot_pcode_t."'");
					$DB->update_query('order_t', $arr_query,  "ot_code = '".$list['ot_code']."'");
				}
			}
			p_alert('등록되었습니다.');
		}
	} else if($_POST['act']=='delivery_modal') {
?>
	<div class="modal-header">
		<h5 class="modal-title" id="staticBackdropLabel">발송처리</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		<form method="post" name="frm_form" id="frm_form" action="order_update.php" target="hidden_ifrm">
		<input type="hidden" name="act" id="act" value="delivery_update" />
			<table class="table">
                <?
                $ot_pcode_ex = explode("|", $_POST['ot_pcode']['ot_pcode']);

                $q = 1;
                foreach($ot_pcode_ex as $key => $val) {
                    if($val) {
                        $ot_pcode_t = trim($val);
                ?>
                <input type="hidden" name="ot_pcode[]" id="ot_pcode<?=$q?>" value="<?=$ot_pcode_t?>" />
                <tr>
                    <td>
                        상품주문번호
                    </td>
                    <td>
                        <?=$ot_pcode_t?>
                    </td>
                </tr>
                <tr>
                    <td>
                        택배사명
                    </td>
                    <td>
                        <select class="custom-select col-4" name="ot_delivery_com[]" id="ot_delivery_com<?=$q?>"><?=$ct_delivery_com_option?></select>
                    </td>
                </tr>
                <tr>
                    <td>
                        송장번호
                    </td>
                    <td>
                        <input type="text" class="form-control col-4" name="ot_delivery_number[]" id="ot_delivery_number<?=$q?>" />
                    </td>
                </tr>
                <?
                        $q++;
                    }
                }
            ?>
			</table>
			<p class="text-center pt-3">
				<button type="submit" class="btn btn-primary">확인</button>
				<button class="btn btn-secondary" data-dismiss="modal" aria-label="Close">취소</button>
			</p>
		</form>
	</div>
<?
	} else if($_POST['act']=='excel_delivery_upload') {
?>
	<div class="modal-header">
		<h5 class="modal-title" id="staticBackdropLabel">엑셀일괄발송</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		<form method="post" name="frm_form" id="frm_form" action="order_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm">
		<input type="hidden" name="act" id="act" value="delivery_excel_update" />
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<div class="form-group row align-items-center mb-0">
						<label class="col-sm-3 col-form-label">엑셀파일 <b class="text-danger">*</b></label>
						<div class="col-sm-9">
							<input type="file" name="ot_excel_delivery" id="ot_excel_delivery" value="" accept=".xls" />
							<small id="select_category_help" class="form-text text-muted">
							양식파일 : <a href="<?=STATIC_HTTP?>/images/excel_delivery_sample.xls" target="_blank">일괄발송엑셀양식</a><br/>
							엑셀저장형식은 97-2003 통합문서 형식입니다.
							</small>
						</div>
					</div>
				</li>
			</ul>
			<p class="text-center pt-3">
				<button type="submit" class="btn btn-primary">발송처리</button>
			</p>
		</form>
		<script type="text/javascript">
		<!--
			function frm_form_chk(f) {
				if(f.ot_excel_delivery.value=="") {
					alert("엑셀파일을 입력해주세요.");
					f.ot_excel_delivery.focus();
					return false;
				}

				return true;
			}
		//-->
		</script>
	</div>
<?
	} else if($_POST['act']=='excel_order_down') {
		echo "Y";
	} else if($_POST['act'] == "order_detail_update") {
        $query = "select * from cart_t where ot_pcode = '".$_POST['ot_pcode']."'";
        $list = $DB->fetch_assoc($query);
        unset($arr_query);
        $arr_query = array(
            "ot_cancel_reason" => $_POST['ot_cancel_reason'],
            "ot_refund_info" => $_POST['ot_refund_info'],
            "ot_memo" => $_POST['ot_memo'],
            "ot_delivery_memo" => $_POST['ot_delivery_memo'],
            "ot_udate" => "now()"
        );
        $DB->update_query("order_t", $arr_query, "ot_code = '".$list['ot_code']."'");

        p_alert("수정되었습니다.");
    } else if($_POST['act'] == 'save_delivery') {
        unset($arr_query);
        $arr_query = array(
            "ot_delivery_com" => $_POST['ot_delivery_com'],
            "ot_delivery_number" => $_POST['ot_delivery_number'],
            "ot_status" => 4,
            "ot_udate" => "now()"
        );
        $DB->update_query("order_t", $arr_query, "ot_code = '".$_POST['ot_code']."'");
        $DB->update_query("cart_t", array("ct_status" => 4), "ot_code = '".$_POST['ot_code']."'");

        $query = "select * from order_t where ot_code = '".$_POST['ot_code']."'";
        $row = $DB->fetch_assoc($query);
        if($row) {
            $query = "select * from member_t where idx = ".$row['mt_idx'];
            $row_m = $DB->fetch_assoc($query);
            if($row_m['mt_pushing'] == "Y" || $row_m['mt_pushing2'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }
            $token_list = array($row_m['mt_fcm']);
            $message = "구매하신 상품에 배송이 시작됩니다.";
            $title = "룰루팝 배송 시작";

            $op_idx = $row_m['idx'];

            send_notification2($token_list, $title, $message, "MyPage_Payment_Item_Detail_Page", $_POST['ot_code'], $chk);
        }
        unset($arr_query);
        $plt_set = array(
            'plt_title'=>$title,
            'plt_content'=>$message,
            'plt_table'=>"order_t",
            'plt_type'=> 2,
            'plt_index'=>$_POST['ot_code'],
            'mt_idx'=>1,
            'op_idx'=>$op_idx,
            'plt_wdate'=>'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);

        echo json_encode(array("result" => "_ok", "msg" => "저장되었습니다."));
    } else if($_POST['act'] == "confirm_deposit") {
        $ot_code = explode("|", $_POST['idx']['ot_code']);

        foreach($ot_code as $key => $val) {
            $ot_code_t = trim($val);
            $query = "select * from order_t where ot_code = '".$ot_code_t."' and ot_status = 1";
            $row = $DB->fetch_assoc($query);
            if($row) {
                $DB->update_query('order_t', array("ot_status" => 3, "ot_pdate" => "now()"), "ot_code = '".$ot_code_t."'");
                $DB->update_query('cart_t', array("ct_status" => 3), "ot_code = '".$ot_code_t."'");

                $query = "select * from member_t where idx = ".$row['mt_idx'];
                $row_m = $DB->fetch_assoc($query);
                if($row_m['mt_pushing'] == "Y" || $row_m['mt_pushing2'] == "Y") {
                    $chk = "Y";
                } else {
                    $chk = "N";
                }
                $token_list = array($row_m['mt_fcm']);
                $message = "구매하신 상품이 배송 준비중입니다.";
                $title = "룰루팝 배송 준비";

                $op_idx .= $row_m['idx'].",";
                $plt_index .= $row['ot_code'];

                send_notification2($token_list, $title, $message, "MyPage_Payment_Item_Detail_Page", $ot_code_t, $chk);
            }
        }
        unset($arr_query);
        $plt_set = array(
            'plt_title'=>$title,
            'plt_content'=>$message,
            'plt_table'=>"order_t",
            'plt_type'=> 2,
            'plt_index'=>$plt_index,
            'mt_idx'=>1,
            'op_idx'=>$op_idx,
            'plt_wdate'=>'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);
        
        echo json_encode(array('result' => '_ok', 'msg' => '처리되었습니다.'));
    }
?>