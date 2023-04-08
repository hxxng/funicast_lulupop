<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

    /* ============================================================================== */
    /* =   PAGE : 결과 처리 PAGE                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   pp_cli_hub.php 파일에서 처리된 결과값을 출력하는 페이지입니다.           = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://kcp.co.kr/technique.requestcode.do                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2016  NHN KCP Inc.   All Rights Reserverd.                = */
    /* ============================================================================== */
?>
<?
    /* ============================================================================== */
    /* =   지불 결과                                                                = */
    /* = -------------------------------------------------------------------------- = */
    $site_cd          = $_POST[ "site_cd"        ];      // 사이트코드
    $req_tx           = $_POST[ "req_tx"         ];      // 요청 구분(승인/취소)
    $use_pay_method   = $_POST[ "use_pay_method" ];      // 사용 결제 수단
    $bSucc            = $_POST[ "bSucc"          ];      // 업체 DB 정상처리 완료 여부
    /* = -------------------------------------------------------------------------- = */
    $res_cd           = $_POST[ "res_cd"         ];      // 결과코드
    $res_msg          = $_POST[ "res_msg"        ];      // 결과메시지
    $res_msg_bsucc    = "";
    /* = -------------------------------------------------------------------------- = */
    $amount           = $_POST[ "amount"         ];      // KCP 실제 거래 금액
    $ordr_idxx        = $_POST[ "ordr_idxx"      ];      // 주문번호
    $tno              = $_POST[ "tno"            ];      // KCP 거래번호
    $good_name        = $_POST[ "good_name"      ];      // 상품명
    $buyr_name        = $_POST[ "buyr_name"      ];      // 구매자명
    $buyr_tel1        = $_POST[ "buyr_tel1"      ];      // 구매자 전화번호
    $buyr_tel2        = $_POST[ "buyr_tel2"      ];      // 구매자 휴대폰번호
    $buyr_mail        = $_POST[ "buyr_mail"      ];      // 구매자 E-Mail
    /* = -------------------------------------------------------------------------- = */
    // 공통
    $pnt_issue        = $_POST[ "pnt_issue"      ];      // 포인트 서비스사
    $app_time         = $_POST[ "app_time"       ];      // 승인시간 (공통)
    /* = -------------------------------------------------------------------------- = */
    // 신용카드
    $card_cd          = $_POST[ "card_cd"        ];      // 카드코드
    $card_name        = $_POST[ "card_name"      ];      // 카드명
    $noinf            = $_POST[ "noinf"          ];      // 무이자 여부
    $quota            = $_POST[ "quota"          ];      // 할부개월
    $app_no           = $_POST[ "app_no"         ];      // 승인번호
    /* = -------------------------------------------------------------------------- = */
    // 계좌이체
    $bank_name        = $_POST[ "bank_name"      ];      // 은행명
    $bank_code        = $_POST[ "bank_code"      ];      // 은행코드
    /* = -------------------------------------------------------------------------- = */
    // 가상계좌
    $bankname         = $_POST[ "bankname"       ];      // 입금할 은행
    $depositor        = $_POST[ "depositor"      ];      // 입금할 계좌 예금주
    $account          = $_POST[ "account"        ];      // 입금할 계좌 번호
    $va_date          = $_POST[ "va_date"        ];      // 가상계좌 입금마감시간
    /* = -------------------------------------------------------------------------- = */
    // 포인트
    $add_pnt          = $_POST[ "add_pnt"        ];      // 발생 포인트
    $use_pnt          = $_POST[ "use_pnt"        ];      // 사용가능 포인트
    $rsv_pnt          = $_POST[ "rsv_pnt"        ];      // 총 누적 포인트
    $pnt_app_time     = $_POST[ "pnt_app_time"   ];      // 승인시간
    $pnt_app_no       = $_POST[ "pnt_app_no"     ];      // 승인번호
    $pnt_amount       = $_POST[ "pnt_amount"     ];      // 적립금액 or 사용금액
    /* = -------------------------------------------------------------------------- = */
    //상품권
    $tk_van_code      = $_POST[ "tk_van_code"    ];      // 발급사 코드
    $tk_app_no        = $_POST[ "tk_app_no"      ];      // 승인 번호
    /* = -------------------------------------------------------------------------- = */
    //휴대폰
    $commid           = $_POST[ "commid"         ];      // 통신사 코드
    $mobile_no        = $_POST[ "mobile_no"      ];      // 휴대폰 번호
    /* = -------------------------------------------------------------------------- = */
    // 현금영수증
    $cash_yn          = $_POST[ "cash_yn"        ];      //현금영수증 등록 여부
    $cash_authno      = $_POST[ "cash_authno"    ];      //현금영수증 승인 번호
    $cash_tr_code     = $_POST[ "cash_tr_code"   ];      //현금영수증 발행 구분
    $cash_id_info     = $_POST[ "cash_id_info"   ];      //현금영수증 등록 번호
    $cash_no          = $_POST[ "cash_no"        ];      //현금영수증 거래 번호
    /* = -------------------------------------------------------------------------- = */

    $req_tx_name = "";

    if( $req_tx == "pay" )
    {
        $req_tx_name = "지불";
    }
    else if( $req_tx == "mod" )
    {
        $req_tx_name = "매입/취소";
    }

    /* ============================================================================== */
    /* =   가맹점 측 DB 처리 실패시 상세 결과 메시지 설정                           = */
    /* = -------------------------------------------------------------------------- = */

    if($req_tx == "pay")
    {
        //업체 DB 처리 실패
        if($bSucc == "false")
        {
            if ($res_cd == "0000")
            {
                $res_msg_bsucc = "결제는 정상적으로 이루어졌지만 업체에서 결제 결과를 처리하는 중 오류가 발생하여 시스템에서 자동으로 취소 요청을 하였습니다. <br> 업체로 문의하여 확인하시기 바랍니다.";
            }
            else
            {
                $res_msg_bsucc = "결제는 정상적으로 이루어졌지만 업체에서 결제 결과를 처리하는 중 오류가 발생하여 시스템에서 자동으로 취소 요청을 하였으나, <br> <b>취소가 실패 되었습니다.</b><br> 업체로 문의하여 확인하시기 바랍니다.";
            }
        }
    }

	if($bankname) {
		$_POST['bankname_t'] = 'LGD_FINANCENAME|:|'.$bankname;
		$_POST['account_t'] = 'LGD_ACCOUNTNUM|:|'.$account;
	}

	$arr_name_im = implode(";", $_POST);

	if($ordr_idxx) {
		unset($arr_query);
		$arr_query = array(
			'domain_chk' => $domain_chk,
			'pay_type' => 'K',
			'ot_code' => $ordr_idxx,
			'LGD_TID' => $tno,
			'xl_code' => $res_cd,
			'xl_msg' => $res_msg,
			'xl_content' => $arr_name_im,
			'xl_wdate' => "now()",
		);

		$DB->insert_query('xpay_log_t', $arr_query);
	}

	$_uid = $_SESSION[_uid] = get_uid();

    /* = -------------------------------------------------------------------------- = */
    /* =   가맹점 측 DB 처리 실패시 상세 결과 메시지 설정 끝                        = */
    /* ============================================================================== */

	//다인네 결제완료처리

    /* = -------------------------------------------------------------------------- = */
    /* =   결제 결과 코드 및 메시지 출력 끝                                         = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =  01. 결제 결과 출력                                                        = */
    /* = -------------------------------------------------------------------------- = */
    if ( $req_tx == "pay" )                           // 거래 구분 : 승인
    {
        /* ============================================================================== */
        /* =  01-1. 업체 DB 처리 정상 (bSucc값이 false가 아닌 경우)                     = */
        /* = -------------------------------------------------------------------------- = */
        if ( $bSucc != "false" )                      // 업체 DB 처리 정상
        {
            /* ============================================================================== */
            /* =  01-1-1. 정상 결제시 결제 결과 출력 (res_cd값이 0000인 경우)               = */
            /* = -------------------------------------------------------------------------- = */
            if ( $res_cd == "0000" )                  // 정상 승인
            {
				$query_ot = "select * from ".$db_order_t." where ot_code = '".$ordr_idxx."'";
				$row_ot = $DB->fetch_query($query_ot);

				if($use_pay_method=="001000000000") {
					ot_status_update('12', $ordr_idxx);
				} else {
					ot_status_update('2', $ordr_idxx);
				}

				if($row_ot[ot_code]=="") {
					p_alert("결제가 실패했습니다. 결제를 다시 시도해주시기 바랍니다.[KE01]", "../../");
				} else {
					if($_SESSION[_mt_idx]) {
						p_alert("주문내역을 확인바랍니다. [주문번호 : ".$row_ot[ot_code]."]", "../../myorder.php");
					} else {
						$_SESSION[_nomem_ot_code] = $row_ot[ot_code];
						$_SESSION[_nomem_ot_pwd] = $row_ot[ot_pwd];

						p_gotourl("../../nomem_order_view.php");
					}
				}
			} else {
				ot_status_update('9', $ordr_idxx);

				p_alert("결제가 실패했습니다. 결제를 다시 시도해주시기 바랍니다.[KE02]", "../../");
			}
		} else {
			ot_status_update('9', $ordr_idxx);

			p_alert("결제가 실패했습니다. 결제를 다시 시도해주시기 바랍니다.[KE03]", "../../");
		}
	} else {
		ot_status_update('9', $ordr_idxx);

		p_alert("결제가 실패했습니다. 결제를 다시 시도해주시기 바랍니다.[KE04]", "../../");
	}

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>