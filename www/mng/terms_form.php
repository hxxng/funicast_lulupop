<?
	include "./head_inc.php";
	$chk_menu = '12';
	$chk_sub_menu = '4';
	$chk_ckeditor = 'Y'; //CKEDITOR
	include "./head_menu_inc.php";

	$query = "
		select * from terms_t
	";
	$row = $DB->fetch_query($query);
?>
<style>
    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
    }

    .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
    }

    .tab button:hover {
        background-color: #ddd;
    }

    .tab button.active {
        background-color: #ccc;
    }
</style>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">약관 관리</h4>
                    <div class="tab" style="width: 302px;">
                        <button class="tablinks active" onclick="openTab(event, 'tab_tt_agree1')" style="width: 150px;">이용약관</button>
                        <button class="tablinks" onclick="openTab(event, 'tab_tt_agree2')" style="width: 150px;">개인정보처리방침</button>
                    </div>
					<form method="post" name="frm_form" id="frm_form" action="terms_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
					<input type="hidden" name="act" id="act" value="update" />
					<div class="form-group row" id="tab_tt_agree1">
                        <div class="col-sm-1"></div>
						<div class="col-sm-10" style="margin-top: 50px;">
							<textarea name="tt_agree1" id="tt_agree1" class="form-control form-control-sm"><?=$row['tt_agree1']?></textarea>
							<script type="text/javascript">
								CKEDITOR.replace('tt_agree1', {
									extraPlugins: 'uploadimage, image2',
									height : '400px',
									filebrowserImageBrowseUrl : '',
									filebrowserImageUploadUrl : './file_upload.php?Type=Images&upload_name=tt_agree1',
									enterMode : CKEDITOR.ENTER_BR,
								});
							</script>
						</div>
                        <div class="col-sm-1"></div>
                    </div>

					<div class="form-group row" id="tab_tt_agree2">
                        <div class="col-sm-1"></div>
						<div class="col-sm-10" style="margin-top: 50px;">
							<textarea name="tt_agree2" id="tt_agree2" class="form-control form-control-sm"><?=$row['tt_agree2']?></textarea>
							<script type="text/javascript">
								CKEDITOR.replace('tt_agree2', {
									extraPlugins: 'uploadimage, image2',
									height : '400px',
									filebrowserImageBrowseUrl : '',
									filebrowserImageUploadUrl : './file_upload.php?Type=Images&upload_name=tt_agree2',
									enterMode : CKEDITOR.ENTER_BR,
								});
							</script>
						</div>
                        <div class="col-sm-1"></div>
					</div>

					<p class="p-3 text-center">
						<input type="submit" value="수정" class="btn btn-outline-primary" />
					</p>
					</form>

                    <script type="text/javascript">
                        $(document).ready(function() {
                            $("#tab_tt_agree2").hide();
                        });
						function frm_form_chk(f) {
							var oEditor1 = CKEDITOR.instances.tt_agree1;
							var oEditor2 = CKEDITOR.instances.tt_agree2;

							if(oEditor1.getData()=="") {
								alert("내용을 입력해주세요.");
								oEditor1.focus();
								return false;
							}
							if(oEditor2.getData()=="") {
								alert("내용을 입력해주세요.");
								oEditor2.focus();
								return false;
							}

							return true;
						}
                        function openTab(evt, tabid) {
                            var i, tabcontent, tablinks;

                            tabcontent = document.getElementsByClassName("form-group");
                            for (i = 0; i < tabcontent.length; i++) {
                                tabcontent[i].style.display = "none";
                            }

                            tablinks = document.getElementsByClassName("tablinks");
                            for (i = 0; i < tablinks.length; i++) {
                                tablinks[i].className = tablinks[i].className.replace(" active", "");
                            }

                            document.getElementById(tabid).style.display = "flex";
                            evt.currentTarget.className += " active";
                        }
					</script>
				</div>
			</div>
		</div>
	</div>
</div>
<?
	include "./foot_inc.php";
?>