<?
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set( "display_errors", 1 );

	define("APP_AUTHOR", env('APP_AUTHOR', ''));	

	//상단타이틀, URL 설정
	define("APP_TITLE", env('APP_TITLE', ''));
	define("APP_DOMAIN", env('APP_DOMAIN', ''));
	define("STATIC_HTTP", env('STATIC_HTTP', ''));
	define("CDN_HTTP", env('CDN_HTTP', ''));
	define("KEYWORDS", env('KEYWORDS', ''));
	define("DESCRIPTION", env('DESCRIPTION', ''));
	define("DECODEKEY", env('DECODEKEY', ''));
	define("IV", env('IV', ''));
	define("SERVER_NAME", env('SERVER_NAME', ''));
	define("SECRETKEY", env('SECRETKEY', ''));
	define("DEBUG_JWT", env('DEBUG_JWT', ''));	


	//게시판 리스팅수
	$n_limit_num = 10;
	$pt_image_num = 10;

	$arr_file = array(
		'0' => '첨부파일1',
		'1' => '첨부파일2',
		'2' => '첨부파일3',
	);

	//이미지 업로드 가능 확장자
	$ct_image_ext = "jpg;png;gif;jpeg;bmp";

	$ct_no_img_url = STATIC_HTTP."/images/noimg.png";
	$ct_member_no_img_url = STATIC_HTTP."/images/no-image.png";

	//업로드 링크
	$ct_img_dir_r = "./images/uploads";
	$ct_img_dir_a = "../images/uploads";
	$ct_img_url = STATIC_HTTP."/images/uploads";

	//엑셀다운로드 링크
	$ct_excel_dir_r = "./images/excel";
	$ct_excel_dir_a = "../images/excel";
	$ct_excel_url = STATIC_HTTP."/images/excel";

	//오디오 파일
	$ct_audio_dir_r = "./data";
	$ct_audio_dir_a = "../data";
	$ct_audio_url = STATIC_HTTP."/data";

	$imp_key = "imp_key";
	$imp_secret = "imp_secret";

?>