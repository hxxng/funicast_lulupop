<script src="https://js.tosspayments.com/v1"></script>
<script>
    function toss (){
        var tossPayments = TossPayments("test_key");
        // var tossPayments = TossPayments("live_key");     //실결제 API키

        let method = "";
        if(<?=$_GET['ct_pay_type']?> == 2) {
            method = "카드";
        } else {
            method = "가상계좌";
        }

        var paymentData = {
            amount: <?=$_GET['ct_price']?>,
            orderId: "<?=$_GET['ct_code']?>",
            orderName: "Coin <?=$_GET['ct_amount']?>개",
            customerName: "<?=$_GET['mt_id']?>",
            successUrl: window.location.origin + "/api/success.php",
            failUrl: window.location.origin + "/api/fail.php",
            appScheme:'appScheme://'
        };

        if (method === '가상계좌') {
            paymentData.virtualAccountCallbackUrl = window.location.origin + '/api/virtual_account_coin_callback.php';
        }

        tossPayments.requestPayment(method, paymentData);
    }

</script>

<?php
if($_GET['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($_GET['ct_price']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_price', '');
    exit;
}
if($_GET['ct_pay_type']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_pay_type', '');
    exit;
}
if($_GET['ct_code']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_code', '');
    exit;
}
if($_GET['ct_amount']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_amount', '');
    exit;
}

echo '<script type="text/javascript"> toss() </script>';
?>

