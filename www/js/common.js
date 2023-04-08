

// 검색 버튼
/* $('.sch_btn').on('click', function () {
    $('.nav_my').removeClass('on');
    $('.sch_tog').toggleClass('on');
    $('.hd_r_btn').toggleClass('on');
}) */

// 헤더 마이페이지 버튼
$('.btn_my_on').on('click', function () {
    $('.sch_tog').removeClass('on');
    $('.hd_r_btn').removeClass('on');
    $('.nav_my').toggleClass('on');
})

// 모바일 메뉴
if ($('.mo_fix_nav a').hasClass('on')) {
    let src2 = $('.mo_fix_nav a.on img').data("src2")
    $('.mo_fix_nav a.on img').attr('src', src2)
}

// 스크롤 이벤트 (탑버튼)

$(window).scroll(function () {
    // var scrollBottom = $(document).height() - $(window).height() - $(window).scrollTop();
    //console.log(scrollBottom);
    if ($(window).scrollTop() > 50) {
        $('.top_btn').show();
    } else {
        $('.top_btn').hide();
    }
});

// 탑버튼
$('.top_btn').on('click', function () {
    $('html, body').animate({
        scrollTop: 0
    }, 400);
    return false;
});

// 셀렉트 색상 변경
$('select.form-control').change(function () {
    $('select.form-control').css('color', '#000')
});

// 찜하기 버튼
$('.btn_like').on('click', function () {
    $(this).toggleClass('on')
})

// 플레이어 프로그래스바
$(".progress_ip").on("input", function () {
    var val = $(this).val();
    $(this).css(
        "background", "linear-gradient(to right, var(--main) 0%, var(--main) " + val + "%, #fff " + val + "%, #fff 100%)"
    );
});

// 더보기
$('.rev_li').each(function () {
    // 줄수에 따라서 다름 현재는 2 한줄하려면 1 3줄하려면 3으로 수정
    var content = $(this).children('.review_text');
    var content_txtH = content.innerHeight();
    var content_txtLh = content.css('line-height');
    var contentH = content_txtLh.replace('px', '')
    var btn_more = $('<a href="javascript:void(0)" class="more">더보기...</a>');
    // console.log(content_txtH)
    $(this).append(btn_more);
    if (content_txtH > contentH * 2) {
        content.css({
            'overflow': 'hidden',
            'height': contentH * 2,
            'display': '-webkit-box'
        })
    } else {
        content.css({
            'overflow': 'visible',
            'height': content_txtH,
            'display': 'block'
        })
        btn_more.hide()
    }
    btn_more.click(toggle_content);
    function toggle_content() {
        if ($(this).hasClass('short')) {
            // 접기 상태
            $(this).html('더보기...');
            $(this).removeClass('short');
            content.css({
                'overflow': 'hidden',
                'height': contentH * 2,
                'display': '-webkit-box'
            })
        } else {
            // 더보기 상태
            $(this).html('접기');
            content.css({
                'overflow': 'visible',
                'height': content_txtH,
                'display': 'block'
            })
            $(this).addClass('short');
        }
    }
});

// 찜하기 버튼
$('.item_like').on('click', function () {
    $(this).children('div').toggleClass('on');
})
$('.m_item_like').on('click', function () {
    $(this).children('div').toggleClass('on');
})

// 상품 리스트 찜하기 버튼
$(document).on('click', '.like_heart_btn.like_heart_btn2', function(){
    $(this).toggleClass('on');
});

//찜하기
function wish_ing(idx, table){
	$.ajax({
		type: 'post',
		url: '/models/wish_model.php',
		dataType: 'json',
		data: { act : 'wish', wish_idx : idx, table : table},
		success: function(d,s) {
			if(d.result=='_false'){
				alert('로그인이 필요한 기능입니다.');
				//location.href='/login.php';
				return false;
			}
		},
		cache: false
	});
}

//비밀번호 정책에 맞춰서 비밀번호 사용여부 체크
function chk_pwd(id) {
    var pwd = $("#"+id).val();
    var num = pwd.search(/[0-9]/g);

    var eng = pwd.search(/[a-z]/ig);
    var spe = pwd.search(/[`~!@@#$%^&*|₩₩₩'₩";:₩/?]/gi);
    if(pwd.length < 8 || pwd.length > 20){
        alert("8자리 이상 입력해주세요.");
        $("#"+id).focus();
        return false;
    } else if(pwd.search(/\s/) != -1){
        alert("비밀번호는 공백 없이 입력해주세요.");
        $("#"+id).focus();
        return false;
    } else if(spe < 0 ){
        alert("특수문자(!,@,#,$,% 등) 한 개 이상을 포함하여 입력해주세요.");
        $("#"+id).focus();
        return false;
    } else if(/([a-zA-Z0-9])\1{2,}/g.test(pwd)) {
        alert('같은 문자를 3번 이상 연속으로 사용할 수 없습니다.');
        $("#"+id).focus();
        return false;
    } else {
        // console.log("통과");
        return true;
    }
}

//송장번호 복사 함수
function copy_num() {
    var valOfDIV = document.getElementById("ot_delivery_number").textContent;
    valOfDIV = valOfDIV.split("(");
    valOfDIV = valOfDIV[1].split(")");
    var textArea = document.createElement('textarea');
    document.body.appendChild(textArea);
    textArea.value = valOfDIV[0];

    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);

    alert("복사되었습니다.");
}