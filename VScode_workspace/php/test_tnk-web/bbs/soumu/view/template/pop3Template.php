<?php require(APP_TEMPLATE_DIR."parts/header.php");?>
<div class="waku">
<a href="<?php echo APP_FILENAME; ?>">掲示板にもどる</a>
</div><br>
<div class="waku">
<?php

$SELF = APP_FILENAME;

if ($mailcount == 0) {
    echo <<<EOM
メールによる投稿はありませんでした。<br>
メールの到着まで数分かかることもあります。
EOM;
} else {
    echo <<<EOM
${mailcount}件の投稿を書き込みました。
EOM;
}

?>
<hr>
<span style="font-weight:bold;color:blue"><?php echo APP_MAIL_ADDR; ?></span> に携帯メールを送信すると、
この掲示板に投稿できます。<br>
メールの題名が名前になり、<br>
メールのメッセージがそのままメッセージになります。<br>
<hr>
<a href="javascript:history.go(-1)">もどる</a>
</div>

<?php require(APP_TEMPLATE_DIR."parts/footer.php");?>