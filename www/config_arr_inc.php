<?
	$arr_plt_type = array(
		'P' => '적립',
		'M' => '차감',
	);

	foreach($arr_plt_type as $key => $val) {
		if($key>0) {
			$arr_plt_type_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_mt_level = array(
		1 => '탈퇴',
		2 => '회원',
		5 => '업체',
		9 => '관리자',
	);

	foreach($arr_mt_level as $key => $val) {
		if($key>0) {
			$arr_mt_level_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_pdt_type = array(
		1 => '택배',
		2 => '퀵서비스',
		3 => '방문수령',
//		4 => '직접배송',
	);

	foreach($arr_pdt_type as $key => $val) {
		if($key>0) {
			$arr_pdt_type_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

    $ct_delivery_com = array(
        1 => '대한통운',
        2 => '우체국택배',
        3 => '한진택배',
        4 => '로젠택배',
        5 => '롯데택배',
    );

    foreach($ct_delivery_com as $key => $val) {
        if($key>0) {
            $ct_delivery_com_option .= "<option value='".$val."' >".$val."</option>";
        }
    }

	$arr_ct_status = array(
		1 => '결제대기',
		2 => '결제완료',
		3 => '배송준비중',
		4 => '배송중',
		5 => '배송완료',
		6 => '구매확정',
        7 => '취소요청',
        8 => '취소완료',
		78 => '판매자취소',
		79 => '구매확정후취소',
		80 => '교환요청',
		81 => '교환완료',
		82 => '교환거절',
		90 => '반품요청',
		91 => '반품완료',
	);

	foreach($arr_ct_status as $key => $val) {
		if($key>0) {
			$arr_ct_status_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

    $arr_exchange_status = array(
        1 => '교환접수',
        2 => '상품회수중',
        3 => '회수상품도착',
        4 => '교환상품출고',
    );

    $arr_refund_status = array(
        1 => '환불접수',
        2 => '상품회수중',
        3 => '회수상품도착',
        4 => '환불완료',
    );


    $arr_ct_method = array(
        1 => '가상계좌',
        2 => '카드',
        3 => '간편결제',
        4 => '편의점',
        5 => '휴대폰',
        9 => '랜덤 뽑기 상품',
    );
    foreach($arr_ct_method as $key => $val) {
		if($key>0) {
			$arr_ct_method_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

    $arr_ct_delivery_status = array(
        1 => '입금',
        2 => '배송준비중',
        3 => '배송중',
        4 => '완료',
    );

    foreach($arr_ct_delivery_status as $key => $val) {
        if($key>0) {
            $arr_ct_delivery_status_option .= "<option value='".$key."' >".$val."</option>";
        }
    }


	$arr_cat_status = array(
		1 => '정산완료',
		2 => '정산전',
	);

	foreach($arr_cat_status as $key => $val) {
		if($key>0) {
			$arr_cat_status_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_tv_status = array(
		1 => '발행완료',
		2 => '발행취소',
	);

	foreach($arr_tv_status as $key => $val) {
		if($key>0) {
			$arr_tv_status_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_qt_status = array(
		1 => '답변대기',
		2 => '답변완료',
	);

	foreach($arr_qt_status as $key => $val) {
		if($key>0) {
			$arr_qt_status_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

    $arr_qt_type = array(
        1 => '콘텐츠 관련',
        2 => '구독 관련',
        3 => '기타 문의',
        4 => '신고',
    );

    foreach($arr_qt_type as $key => $val) {
        if($key>0) {
            $arr_qt_type_option .= "<option value='".$key."' >".$val."</option>";
        }
    }

	$arr_mt_login_type = array(
		1 => '일반',
		2 => '네이버',
		3 => '카카오',
		4 => '애플',
	);

	foreach($arr_mt_login_type as $key => $val) {
		if($key>0) {
			$arr_mt_login_type_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_pst_type = array(
		1 => '회원',
		2 => '판매자',
		3 => '전체',
	);

	foreach($arr_pst_type as $key => $val) {
		if($key>0) {
			$arr_pst_type_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_mt_seller = array(
		'Y' => '승인',
		'N' => '미승인',
		'D' => '요청',
	);

	foreach($arr_mt_seller as $key => $val) {
		if($key) {
			$arr_mt_seller_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_mt_status = array(
		'Y' => '가능',
		'N' => '불가능',
	);

	foreach($arr_mt_status as $key => $val) {
		if($key) {
			$arr_mt_status_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_pt_status = array(
		7 => '대기중',
		8 => '답변완료',
		1 => '견적서확인중',
		2 => '계약진행중',
		3 => '프로젝트진행중',
		4 => '프로젝트완료',
		5 => '구매확정',
		9 => '구매확정완료',
		6 => '취소',
		99 => '임시',
	);

	foreach($arr_pt_status as $key => $val) {
		if($key>0) {
			$arr_pt_status_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_ppt_chk = array(
		1=>'의류',
		2=>'구두/신발',
		3=>'가방',
		4=>'패션잡화(모자/벨트/악세사리)',
		5=>'침구류/커튼',
		6=>'가구(침대/소파/싱크대/DIY제품)',
		7=>'영상가전(TV류)',
		8=>'가정용전기제품(냉장고/세탁기/식기세척기/전자렌지)',
		9=>'계절가전(에어콘/온풍기)',
		10=>'사무용기기(컴퓨터/노트북/프린터)',
		11=>'광학기기(디지털카메라/캠코더)',
		12=>'소형전자(MP3/전자사전등)',
		13=>'휴대폰',
		14=>'내비게이션',
		15=>'자동차용품(자동차부품/기타자동차용품)',
		16=>'의료기기',
		17=>'주방용품',
		18=>'화장품',
		19=>'귀금속/보석/시계류',
		20=>'식품(농·축·수산물)',
		21=>'가공식품',
		22=>'건강기능식품',
		23=>'영유아용품',
		24=>'악기',
		25=>'스포츠용품',
		26=>'서적',
		27=>'호텔/펜션예약',
		28=>'여행상품',
		29=>'항공권',
		30=>'자동차대여서비스(렌터카)',
		31=>'물품대여서비스(정수기/비데/공기청정기등)',
		32=>'물품대여서비스(서적/유아용품/행사용품등)',
		33=>'디지털콘텐츠(음원/게임/인터넷강의등)',
		34=>'상품권/쿠폰',
		35=>'기타',
	);

	foreach($arr_ppt_chk as $key => $val) {
		if($key>0) {
			$arr_ppt_chk_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_pdt_price_type = array(
		'1' => '무료',
		'2' => '조건부무료',
		'3' => '유료',
		'4' => '수량별',
		'5' => '구간별',
	);

	foreach($arr_pdt_price_type as $key => $val) {
		if($key) {
			$arr_pdt_price_type_option .= "<option value='".$key."' >".$val."</option>";
		}
	}

	$arr_pat_origin = array(
		'국산' => array(
			'강원도' => array('강릉시','고성군','동해시','삼척시','속초시','양구군','양양군','영월군','원주시','인제군','정선군','철원군','춘천시','태백시','평창군','홍천군','화천군','횡성군'),
			'경기도' => array('가평군','고양시','고양시 덕양구','고양시 일산동구','고양시 일산서구','과천시','광명시','광주시','구리시','군포시','김포시','남양주시','동두천시','부천시','성남시','성남시 분당구','성남시 수정구','성남시 중원구','수원시','수원시 권선구','수원시 영통구','수원시 장안구','수원시 팔달구','시흥시','안산시','안산시 단원구','안산시 상록구','안성시','안양시','안양시 동안구','안양시 만안구','양주시','양평군','여주시','연천군','오산시','용인시','용인시 기흥구','용인시 수지구','용인시 처인구','의왕시','의정부시','이천시','파주시','평택시','포천시','하남시','화성시',),
			'경상남도' => array('거제시','거창군','고성군','김해시','남해군','밀양시','사천시','산청군','양산시','의령군','진주시','창녕군','창원시','창원시 마산합포구','창원시 마산회원구','창원시 성산구','창원시 의창구','창원시 진해구','통영시','하동군','함안군','함양군','합천군'),
			'경상북도' => array('경산시','경주시','고령군','구미시','군위군','김천시','문경시','봉화군','상주시','성주군','안동시','영덕군','영양군','영주시','영천시','예천군','울릉군','울진군','의성군','청도군','청송군','칠곡군','포항시','포항시 남구','포항시 북구'),
			'광주광역시' => array('광산구','남구','동구','북구','서구'),
			'대구광역시' => array('남구','달서구','달성군','동구','북구','서구','수성구','중구'),
			'대전광역시' => array('대덕구','동구','서구','유성구','중구'),
			'부산광역시' => array('강서구','금정구','기장군','남구','동구','동래구','부산진구','북구','사상구','사하구','서구','수영구','연제구','영도구','중구','해운대구'),
			'서울특별시' => array('강남구','강동구','강북구','강서구','관악구','광진구','구로구','금천구','노원구','도봉구','동대문구','동작구','마포구','서대문구','서초구','성동구','성북구','송파구','양천구','영등포구','용산구','은평구','종로구','중구','중랑구'),
			'세종특별자치시' => array(),
			'울산광역시' => array('남구','동구','북구','울주군','중구',),
			'인천광역시' => array('강화군','계양구','남동구','동구','미추홀구','부평구','서구','연수구','옹진군','중구'),
			'전라남도' => array('강진군','고흥군','곡성군','광양시','구례군','나주시','담양군','목포시','무안군','보성군','순천시','신안군','여수시','영광군','영암군','완도군','장성군','장흥군','진도군','함평군','해남군','화순군'),
			'전라북도' => array('고창군','군산시','김제시','남원시','무주군','부안군','순창군','완주군','익산시','임실군','장수군','전주시','전주시 덕진구','전주시 완산구','정읍시','진안군'),
			'제주특별자치도' => array('서귀포시','제주시'),
			'충청남도' => array('계룡시','공주시','금산군','논산시','당진시','보령시','부여군','서산시','서천군','아산시','예산군','천안시','천안시 동남구','천안시 서북구','청양군','태안군','홍성군'),
			'충청북도' => array('괴산군','단양군','보은군','영동군','옥천군','음성군','제천시','증평군','진천군','청주시','청주시 상당구','청주시 서원구','청주시 청원구','청주시 흥덕구','충주시'),
		),
		'수입산' => array(
			'라틴아메리카(남미)' => array('과델루페','과테말라','그레나다','네비스','니카라과','도미니카','도미니카공화국','멕시코','몬체라트','바바도스','바하마','버뮤다','베네수엘라','벨리제','볼리비아','브라질','브리티시 버진아일랜드','세인트루시아','수리남','아루바','아르헨티나','아센션 이스난드','안티구아','앙길라','에콰도르','엘살바도르','온두라스','우루과이','자메이카','칠레','코스타리카','콜롬비아','쿠바','트리니다드토바고','파나마','파라과이','페루','포크랜드','푸에르토리코','프랑스령 기아나','하이티'),
			'북아메리카(북미)' => array('미국','바베이도스','세인트 빈센트 그레나딘','아이티','앤티가 바부다','유에스버진아일랜드','캐나다'),
			'아시아' => array('그루지야','네팔','대만','동티모르','라오스','레바논','리비아','마카오','말레이시아','몰디브','몽골','미얀마','바레인','방글라데시','베트남','부탄','북한','브루나이','사우디아라비아','스리랑카','시리아','싱가포르','아랍에미리트','아르메니아','아프가니스탄','예멘','오만','요르단','우즈베키스탄','이라크','이란','이스라엘','인도','인도네시아','인도양식민지','일본','중국','카자흐스탄','카타르','캄보디아','쿠웨이트','키리기스스탄','타지키스탄','태국','투르크메니스탄','티베트','파키스탄','필리핀','홍콩'),
			'아프리카' => array('가나','가봉','가이아나','감비아','기니','기니비사우','나미비아','나이지리아','남아프리카공화국','니제르','라이베리아','레소토','르완다','마다가스카르','마르티니크','말라위','말리','모로코','모리셔스','모리타니','모잠비크','베냉','보츠와나','부룬디','부르키나파소','사오토메프린시페','상투메 프린시페','서사하라','세네갈','세이셀','소말리아','수단','스와질랜드','시에라리온','알제리','앙골라','에디오피아','에리트리아','우간다','이집트','잠비아','적도기니공화국','중앙아프리카공화국','지부티','지브롤터','짐바브웨','차드','카메룬','카보베르데','케냐','코모로','코모로스','코트디브와르','콩고','콩고민주공화국','탄자니아','터크스앤카이코스제도','토고','튀니지'),
			'오세아니아' => array('괌','나우루','노퍽아일랜드','뉴질랜드','뉴칼레도니아','마리아나군도','마셜군도','마이크로네시아','미크로네시아','바누아투','서사모아','세인트빈센트','솔로몬군도','아메리칸사모아','코코스섬','쿡아일랜드','크리스마스섬','키리바시','통가','투발루','파푸아뉴기니','팔라우','폴리네시아(프랑스령)','피지','호주','후투나'),
			'유럽' => array('그리스','그린란드','네덜란드','노르웨이','덴마크','독일','라트비아','러시아연방','루마니아','룩셈부르크','리투아니아','리히텐슈타인','마케도니아','말타','모나코','몰도바공화국','몰타','바티칸','벨기에','벨라루스','벨로루시','보스니아-헤르체고비나','불가리아','사이프러스','세르비아','스웨덴','스위스','스페인','슬로바키아','슬로베니아','아이슬란드','아일랜드공화국','아제르바이잔','안도라','알메니아','알바니아','에스토니아','영국','오스트리아','우크라이나','유고','이탈리아','조지아','체코','크로아티아','터키','페로스제도','포르투갈','폴란드','프랑스','핀란드','헝가리'),
		),
		'기타' => array()
	);

	foreach($arr_pat_origin as $key => $val) {
		if($key) {
			$arr_pat_origin1_option .= "<option value='".$key."' >".$key."</option>";
		}
	}

	$arr_sel_ct_level = array(
		'0' => '대분류',
		'1' => '중분류',
		'2' => '소분류',
		'3' => '세분류',
	);

    $arr_rt_type = array(
        1 => '상품과 관련없는 리뷰',
        2 => '비매너/욕설/언어폭력 행위',
        3 => '권리침해 또는 괴롭힘',
        4 => '개인정보 유출 위험',
        5 => '음란물 포함',
    );

    foreach($arr_rt_type as $key => $val) {
        if($key>0) {
            $arr_rt_type_option .= "<option value='".$key."' >".$val."</option>";
        }
    }

    $arr_review_status = array(
        1 => '삭제',
        2 => '숨기기',
        3 => '신고처리 거절',
    );

    foreach($arr_review_status as $key => $val) {
        if($key>0) {
            $arr_review_status_option .= "<option value='".$key."' >".$val."</option>";
        }
    }
    
    $arr_return_payment = array(
        1 => '신용카드',
        2 => '적립금',
        3 => '환불계좌'
    );

    $arr_bank = array(
		'경남' => '경남',
		'광주' => '광주',
		'국민' => '국민',
		'기업' => '기업',
		'농협' => '농협',
		'단위농협' => '단위농협',
		'대구' => '대구',
		'부산' => '부산',
		'산업' => '산업',
		'새마을' => '새마을',
		'산림' => '산림',
		'수협' => '수협',
		'신한' => '신한',
		'신협' => '신협',
		'씨티' => '씨티',
		'우리' => '우리',
		'우체국' => '우체국',
		'저축' => '저축',
		'전북' => '전북',
		'제주' => '제주',
		'카카오' => '카카오',
		'케이' => '케이',
		'토스' => '토스',
		'하나' => '하나',
		'SC제일' => 'SC제일'
    );

    foreach($arr_bank as $key => $val) {
        if($key>0) {
            $arr_bank_option .= "<option value='".$key."' >".$val."</option>";
        }
    }

    $arr_member_grade = array(
        1 => '일반회원',
        2 => '멤버십회원',
        3 => '프리미엄회원',
    );
?>