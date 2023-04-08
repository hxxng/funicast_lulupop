<?
include "./head_inc.php";
?>
<div class="container-scroller">
	<div class="container-fluid page-body-wrapper full-page-wrapper">
		<div class="content-wrapper d-flex align-items-center auth px-0">
			<div class="row w-100 mx-0">
				<div class="col-lg-4 mx-auto">
					<div class="auth-form-light text-left py-5 px-4 px-sm-5">
						<div class="brand-logo">
							<img src="<?=STATIC_HTTP?>/images/logo.png" alt="logo">
						</div>
						<h4>반갑습니다.</h4>
						<p class="text-muted">* 이 페이지는 접근과 동시에 IP주소가 자동저장됩니다.<br/>관계자 이외에 접근시도는 해킹시도로 의심, 추적되어 불이익을 당할 수 도 있습니다.</p>
						<form class="pt-3" role="form" method="post" name="frm_login" id="frm_login" action="./login_update.php" target="hidden_ifrm">
							<div class="form-group">
								<input type="text" name="mt_id" id="mt_id" class="form-control form-control-lg" placeholder="아이디" required autofocus>
							</div>
							<div class="form-group">
								<input type="password" name="mt_pass" id="mt_pass" class="form-control form-control-lg" placeholder="비밀번호" required>
							</div>
							<div class="mt-3"><button class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn" type="submit">로그인</button></div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?
	include "./foot_inc.php";
?>