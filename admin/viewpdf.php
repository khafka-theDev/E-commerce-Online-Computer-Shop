<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receipt_blob'])) {
    $receiptContent = base64_decode($_POST['receipt_blob']);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="receipt.pdf"');
    echo $receiptContent;
    exit();
} else {
    echo "<script>alert('No receipt data available.'); window.close();</script>";
    exit();
}
?>
