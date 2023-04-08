<?php
$paymentKey = $_GET['paymentKey'];
$orderId = $_GET['orderId'];
$amount = $_GET['amount'];

$secretKey = 'test_key';

$url = 'https://api.tosspayments.com/v1/payments/' . $paymentKey;

$data = ['orderId' => $orderId, 'amount' => $amount];

$credential = base64_encode($secretKey . ':');

$curlHandle = curl_init($url);

curl_setopt_array($curlHandle, [
    CURLOPT_POST => TRUE,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . $credential,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($curlHandle);

$httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
$isSuccess = $httpCode == 200;
$responseJson = json_decode($response);

$result = new stdClass();
$result->result = true;
$result->data = $responseJson->orderId;
$result->method = $responseJson->method;
$result->paymentKey = $responseJson->paymentKey;
$result->amount = $responseJson->totalAmount;
$result->virtualAccount->accountNumber = $responseJson->virtualAccount->accountNumber;
$result->virtualAccount->bank = $responseJson->virtualAccount->bank;
$result->virtualAccount->dueDate = date("Y-m-d H:i:s", strtotime($responseJson->virtualAccount->dueDate));
?>
<script>
    window.onload=function(){
        window.ReactNativeWebView.postMessage(`<?php echo json_encode($result); ?>`);
    }
</script>
<!DOCTYPE html>
<html lang="ko">
<head>
    <title>결제 성공</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
</head>
<body style="margin: 0 auto;">
<section>
    <?php
    if ($isSuccess) {
        ?>
<!--        <h1>결제 완료</h1>-->
<!--        <p>결과 데이터: --><?php //echo json_encode($responseJson, JSON_UNESCAPED_UNICODE); ?><!--</p>-->
        <?php
    } else { ?>
<!--        <h1>결제 실패</h1>-->
<!--        <p>--><?php //echo $responseJson->message ?><!--</p>-->
<!--        <span>에러코드: --><?php //echo $responseJson->code ?><!--</span>-->
        <?php
    }
    ?>

</section>
</body>
</html>

