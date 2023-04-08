<?
	if($_SESSION['_mt_level'] < 8 && $_SERVER['PHP_SELF'] != "./login.php") {
		alert("관리자만 접근할 수 있습니다.", "./login.php");
	}
?>

<? if($chk_ckeditor=="Y") { ?>
<!--<script src="//cdn.ckeditor.com/4.14.0/standard-all/ckeditor.js"></script>-->
<script src="../lib/ckeditor/ckeditor.js"></script>
<? } ?>

<div class="container-scroller">
	<!-- 상단바 시작 -->
	<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
		<div class="navbar-brand-wrapper d-flex justify-content-center">
			<div class="navbar-brand-inner-wrapper d-flex justify-content-between align-items-center w-100">
				<a class="navbar-brand brand-logo" href="./">
					<img src="<?=STATIC_HTTP?>/images/logo.png" alt="logo" style="width:auto;height:30px;" />
				</a>
				<a class="navbar-brand brand-logo-mini" href="./">
					<img src="<?=CDN_HTTP?>/images/splash.jpg" alt="logo" />
				</a>
				<button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize"> <span class="mdi mdi-sort-variant"></span></button>
			</div>
		</div>
		<div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
			<ul class="navbar-nav navbar-nav-right">
				<li class="nav-item nav-profile dropdown">
					<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown"><span class="nav-profile-name"><?=$_SESSION['_mt_name']?> 님 반갑습니다.</span></a>
					<div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
						<a href="../" class="dropdown-item" target="_blank"> <i class="mdi mdi-home text-primary"></i> 홈페이지</a>
						<a href="./logout.php" class="dropdown-item"> <i class="mdi mdi-logout text-primary"></i> 로그아웃</a>
					</div>
				</li>
			</ul>
			<button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas"> <span class="mdi mdi-menu"></span></button>
		</div>
	</nav>
	<!-- 상단바 끝 -->

	<div class="container-fluid page-body-wrapper">
		<!-- 왼쪽메뉴 시작 -->
		<nav class="sidebar sidebar-offcanvas" id="sidebar">
			<ul class="nav">
				<!-- 회원, 상품, 판매 -->
				<li class="nav-item<? if($chk_menu=='0') { ?> active<? } ?>">
					<a class="nav-link" href="./member_list.php">
						<i class="mdi mdi-account-card-details-outline menu-icon"></i>
						<span class="menu-title">회원관리</span>
					</a>
				</li>
				<li class="nav-item<? if($chk_menu=='1') { ?> active<? } ?>">
					<a class="nav-link" href="./main_list.php">
						<i class="mdi mdi-home-outline menu-icon"></i>
						<span class="menu-title">메인관리</span>
					</a>
				</li>
				<li class="nav-item<? if($chk_menu=='2') { ?> active<? } ?>">
					<a class="nav-link" data-toggle="collapse" href="#menu_product" aria-expanded="<? if($chk_menu=='2') { ?>true<? } else { ?>false<? } ?>" aria-controls="shopping_mall">
						<i class="mdi mdi-file-powerpoint-box-outline menu-icon"></i>
							<span class="menu-title">상품관리</span>
						<i class="menu-arrow"></i>
					</a>
					<div class="collapse<? if($chk_menu=='2') { ?> show<? } ?>" id="menu_product">
						<ul class="nav flex-column sub-menu">
							<li class="nav-item"> <a class="nav-link<? if($chk_menu=='2' && $chk_sub_menu=='1') { ?> active<? } ?>" href="./product_list.php">기본상품관리</a></li>
							<li class="nav-item"> <a class="nav-link<? if($chk_menu=='2' && $chk_sub_menu=='2') { ?> active<? } ?>" href="./product_random_list.php">랜덤상품관리</a></li>
							<li class="nav-item"> <a class="nav-link<? if($chk_menu=='2' && $chk_sub_menu=='3') { ?> active<? } ?>" href="./catalog_list.php">도감상품관리</a></li>
						</ul>
					</div>
				</li>
				<li class="nav-item<? if($chk_menu=='3') { ?> active<? } ?>">
					<a class="nav-link" data-toggle="collapse" href="#menu_order" aria-expanded="<? if($chk_menu=='3') { ?>true<? } else { ?>false<? } ?>" aria-controls="review">
						<i class="mdi mdi-account-edit-outline menu-icon"></i>
							<span class="menu-title">주문관리</span>
						<i class="menu-arrow"></i>
					</a>
					<div class="collapse<? if($chk_menu=='3') { ?> show<? } ?>" id="menu_order">
						<ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='3' && $chk_sub_menu=='1') { ?> active<? } ?>" href="./order_list.php">주문 관리</a></li>
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='3' && $chk_sub_menu=='2') { ?> active<? } ?>" href="./exchange_list.php">교환 관리</a></li>
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='3' && $chk_sub_menu=='3') { ?> active<? } ?>" href="./refund_list.php">반품 관리</a></li>
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='3' && $chk_sub_menu=='4') { ?> active<? } ?>" href="./cancel_list.php">취소 관리</a></li>
						</ul>
					</div>
				</li>
                <li class="nav-item<? if($chk_menu=='4') { ?> active<? } ?>">
                    <a class="nav-link" href="./community_list.php">
                        <i class="mdi mdi-account-multiple-outline menu-icon"></i>
                        <span class="menu-title">커뮤니티관리</span>
                    </a>
                </li>
                <li class="nav-item<? if($chk_menu=='5') { ?> active<? } ?>">
                    <a class="nav-link" href="./trade_list.php">
                        <i class="mdi mdi-account-cash-outline menu-icon"></i>
                        <span class="menu-title">중고거래관리</span>
                    </a>
                </li>
				<li class="nav-item<? if($chk_menu=='6') { ?> active<? } ?>">
					<a class="nav-link" data-toggle="collapse" href="#menu_event" aria-expanded="<? if($chk_menu=='6') { ?>true<? } else { ?>false<? } ?>" aria-controls="customer_service_center">
						<i class="mdi mdi-calendar-check-outline menu-icon"></i>
							<span class="menu-title">이벤트관리</span>
						<i class="menu-arrow"></i>
					</a>
					<div class="collapse<? if($chk_menu=='6') { ?> show<? } ?>" id="menu_event">
						<ul class="nav flex-column sub-menu">
							<li class="nav-item"> <a class="nav-link<? if($chk_menu=='6' && $chk_sub_menu=='1') { ?> active<? } ?>" href="./event_list.php">이벤트 추가</a></li>
							<li class="nav-item"> <a class="nav-link<? if($chk_menu=='6' && $chk_sub_menu=='2') { ?> active<? } ?>" href="./coupon_list.php">쿠폰관리</a></li>
							<li class="nav-item"> <a class="nav-link<? if($chk_menu=='6' && $chk_sub_menu=='3') { ?> active<? } ?>" href="./point_list.php">적립금관리</a></li>
						</ul>
					</div>
				</li>
                <li class="nav-item<? if($chk_menu=='7') { ?> active<? } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu_coin" aria-expanded="<? if($chk_menu=='7') { ?>true<? } else { ?>false<? } ?>" aria-controls="customer_service_center">
                        <i class="mdi mdi-coin-outline menu-icon"></i>
                        <span class="menu-title">코인관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<? if($chk_menu=='7') { ?> show<? } ?>" id="menu_coin">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='7' && $chk_sub_menu=='1') { ?> active<? } ?>" href="./coin_list.php">코인결제</a></li>
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='7' && $chk_sub_menu=='2') { ?> active<? } ?>" href="./coin_distribution_list.php">코인지급내역</a></li>
                        </ul>
                    </div>
                </li>
				<li class="nav-item<? if($chk_menu=='8') { ?> active<? } ?>">
					<a class="nav-link" href="./review_product_list.php">
						<i class="mdi mdi-comment-multiple-outline menu-icon"></i>
						<span class="menu-title">후기관리</span>
					</a>
				</li>
				<li class="nav-item<? if($chk_menu=='9') { ?> active<? } ?>">
					<a class="nav-link" href="report_list.php">
						<i class="mdi mdi-comment-alert-outline menu-icon"></i>
						<span class="menu-title">신고관리</span>
					</a>
				</li>
                <li class="nav-item<? if($chk_menu=='10') { ?> active<? } ?>">
                    <a class="nav-link" href="qna_list.php">
                        <i class="mdi mdi-phone-outline menu-icon"></i>
                        <span class="menu-title">C/S관리</span>
                    </a>
                </li>
                <li class="nav-item<? if($chk_menu=='14') { ?> active<? } ?>">
                    <a class="nav-link" href="notice_list.php">
                        <i class="mdi mdi-bullhorn-outline menu-icon"></i>
                        <span class="menu-title">공지사항</span>
                    </a>
                </li>
                <li class="nav-item<? if($chk_menu=='11') { ?> active<? } ?>">
                    <a class="nav-link" href="index.php">
                        <i class="mdi mdi-chart-bar menu-icon"></i>
                        <span class="menu-title">통계관리</span>
                    </a>
                </li>
                <li class="nav-item<? if($chk_menu=='12') { ?> active<? } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu_setting" aria-expanded="<? if($chk_menu=='12') { ?>true<? } else { ?>false<? } ?>" aria-controls="customer_service_center">
                        <i class="mdi mdi-settings-outline menu-icon"></i>
                        <span class="menu-title">설정</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<? if($chk_menu=='12') { ?> show<? } ?>" id="menu_setting">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='12' && $chk_sub_menu=='1') { ?> active<? } ?>" href="./product_category.php">카테고리관리</a></li>
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='12' && $chk_sub_menu=='2') { ?> active<? } ?>" href="./draw_form.php">영상효과관리</a></li>
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='12' && $chk_sub_menu=='3') { ?> active<? } ?>" href="./delivery_form.php">택배설정관리</a></li>
                            <li class="nav-item"> <a class="nav-link<? if($chk_menu=='12' && $chk_sub_menu=='4') { ?> active<? } ?>" href="./terms_form.php">이용약관관리</a></li>
                        </ul>
                    </div>
                </li>
			</ul>
		</nav>
		<!-- 왼쪽메뉴 끝 -->

		<div class="main-panel">