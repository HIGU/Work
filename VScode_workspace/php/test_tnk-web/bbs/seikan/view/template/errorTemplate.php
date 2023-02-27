<?php require(APP_TEMPLATE_DIR."parts/header.php");?>

<div class="waku">
エラー発生
<hr size=1>
<?php
print_r( $context->get("errstr") );
?>
</div>

<?php require(APP_TEMPLATE_DIR."parts/footer.php");?>