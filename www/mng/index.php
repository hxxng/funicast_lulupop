<?
	include "./head_inc.php";
	$chk_menu = '11';
	include "./head_menu_inc.php";
?>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">하루 구매금액 통계</h4>
                    <div id="chartContainer1" style="height: 300px; width: 100%;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">구매전환율</h4>
                    <div id="chartContainer2" style="height: 300px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
		<div class="col-md-6 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h3 class="card-title">검색어 순위</h3>
<!--					<h4 class="card-title">날짜 선택 <input type="date" id="date" class="datepicker ml-3"/></h4>-->
                    <div class="row" id="search">
                    </div>
				</div>
			</div>
		</div>
		<div class="col-md-6 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h3 class="card-title">관심순위 <small> * 상품의 좋아요수/뷰수/구매수로 측정합니다.</small></h3>
                    <div class="row" id="attention">
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
    $(document).ready(function(){
        init();
    });

    function init(){
        get_search_cnt();
        get_attention();
        chart1();
        chart2();
    }

    function getToday(){
        var now = new Date();
        var day = ("0" + now.getDate()).slice(-2);
        var month = ("0" + (now.getMonth() + 1)).slice(-2);

        var today = now.getFullYear()+"-"+(month)+"-"+(day) ;

        return today;
    }

    function get_search_cnt(){
        $.ajax({
            type: 'post',
            url: './ajax.index.php',
            dataType: 'json',
            data: {type: 'search_cnt', date: $("#date").val()},
            success: function (d, s) {
                if (d['result'] == "_ok") {
                    var html = "";
                    for(var i=0; i<d['data'].length; i++) {
                        html += '<span class="col-md-12 mb-1">'+(i+1)+'. '+d['data'][i]['slt_txt']+'</span>';
                    }
                    $("#search").html(html);
                }
            },
            cache: false
        });
    }

    function get_attention(){
        $.ajax({
            type: 'post',
            url: './ajax.index.php',
            dataType: 'json',
            data: {type: 'get_attention', date: $("#date").val()},
            success: function (d, s) {
                if (d['result'] == "_ok") {
                    let html = "";
                    let obj = [];
                    for (let number in d['data']) {
                        obj.push([number, d['data'][number]]);
                    }
                    obj.sort(function(a, b) {
                        return b[1] - a[1];
                    });
                    for(var i=0; i<10; i++) {
                        html += '<span class="col-md-12 mb-1">'+(i+1)+'. '+obj[i][0]+'</span>';
                    }
                    $("#attention").html(html);
                }
            },
            cache: false
        });
    }

    function get_ot_price(){
        var data = "";
        $.ajax({
            type: 'post',
            url: './ajax.index.php',
            dataType: 'json',
            async: false,
            data: {type: 'get_ot_price'},
            success: function (d, s) {
                if (d['result'] == "_ok") {
                    data = d['data'];
                }
            },
            cache: false
        });
        return data;
    }

    function get_buy_cnt(){
        var data = "";
        $.ajax({
            type: 'post',
            url: './ajax.index.php',
            dataType: 'json',
            async: false,
            data: {type: 'get_buy_cnt'},
            success: function (d, s) {
                if (d['result'] == "_ok") {
                    data = d['data'];
                }
            },
            cache: false
        });
        return data;
    }

    function chart1() {
        var data = get_ot_price();
        var chart1 = new CanvasJS.Chart("chartContainer1", {
            type: "column",
            legend: {
                horizontalAlign: "right", // left, center ,right
                verticalAlign: "top",  // top, center, bottom
                fontSize: 15,
            },
            dataPointWidth: 30,
            axisY:{
                gridThickness: 0,
                includeZero: true,
                valueFormatString: "#,##0.##",
                suffix: "원",
            },
            animationEnabled: true,
            data: [
                {
                    color: "#f2a8c3",
                    type: "column",
                    showInLegend: true,
                    legendText: "구매총액",
                    toolTipContent: "<span style='\"'color: {color};'\"'>{label}</span> : <strong>{y}원</strong>",
                    dataPoints: data
                }
            ]
        });
        chart1.render();
    }

    function chart2() {
        var data = get_buy_cnt();
        var chart2 = new CanvasJS.Chart("chartContainer2", {
            type: "column",
            legend: {
                horizontalAlign: "right", // left, center ,right
                verticalAlign: "top",  // top, center, bottom
                fontSize: 15,
            },
            dataPointWidth: 30,
            axisY:{
                gridThickness: 0,
                interval: 20,
                includeZero: true,
                suffix: "%",
            },
            animationEnabled: true,
            data: [
                {
                    color: "#3fc0f0",
                    type: "column",
                    showInLegend: true,
                    legendText: "구매전환율",
                    toolTipContent: "<span style='\"'color: {color};'\"'>{label}</span> : <strong>{y}%</strong>",
                    dataPoints: data
                }
            ]
        });
        chart2.render();
    }

</script>
<!-- 메인 끝 -->
<?
	include "./foot_inc.php";
?>