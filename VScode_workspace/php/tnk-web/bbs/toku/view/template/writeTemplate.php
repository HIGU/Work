<?php require(APP_TEMPLATE_DIR."parts/header.php");?>

<div class="waku">
書き込みが完了しました。
<hr>
<?php

echo "<a href=\"".APP_FILENAME."?".md5(uniqid(""))."\">掲示板にもどる</a>";

?>
</div>

<?php require(APP_TEMPLATE_DIR."parts/footer.php");?>