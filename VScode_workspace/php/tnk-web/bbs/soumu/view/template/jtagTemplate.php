<?php require(APP_TEMPLATE_DIR."parts/header.php");?>
<div class="waku">
<a href="<?php echo APP_FILENAME; ?>">掲示板にもどる</a>
</div><br>
<div class="waku">
<?php

$SELF = APP_FILENAME;

echo <<<EOM
<span style="font-size:14px;font-weight:bold">貼り付けタグ</span>
EOM;

$euc_tag = "<a href=\"${SELF}?m=jtag\">EUC-JPページ用</a>";
$sjis_tag= "<a href=\"${SELF}?m=jtag&c=s\">Shift_JISページ用</a>";
$utf8_tag= "<a href=\"${SELF}?m=jtag&c=u\">UTF-8ページ用</a>";

if ($code == "u") {
    $utf8_tag = "<strong>".strip_tags($utf8_tag)."</strong>";
} elseif ($code == "s") {
    $sjis_tag = "<strong>".strip_tags($sjis_tag)."</strong>";
} else {
    $euc_tag  = "<strong>".strip_tags($euc_tag)."</strong>";
}

echo <<<EOM

<div style="line-height:130%;margin:10px">
テキストボックス内のタグをページに貼り付けると、<a href="#sample">ページ下部</a>のようなコンテンツがページ内に表示されます。<br />
ご自身のページやブログ、ＷＩＫＩ、ＸＯＯＰＳなどに貼り付けることができます。<br />
<span style="font-size:11px;color:gray">※貼り付け先はcgiやphpが動かなくてもHTML内に記述するだけで動作します</span><br />
<!--貼り付けTAG-->
<textarea rows="10" cols="100" wrap="off">
<div align="center" style="font-size:12px">
<script language="javascript" src="${scripturi}?m=jscript&c=$code"></script>
<!--変更不可-->
<div style="text-align:center;font-size:10px;font-family:Tahoma">
<a href="http://rd.phpspot.org/?phpspot">PHP掲示板</a>
</div>
<!--/変更不可-->
</div>
</textarea>
<!--/貼り付けTAG-->
<br />
$euc_tag <span style="color:#e0e0e0">|</span> $sjis_tag <span style="color:#e0e0e0">|</span> $utf8_tag<br />
貼り付け先ページの文字コードによって使い分けてください。<br />
違った文字コードのタグを貼ると文字化けします。分からない場合は３種類ためしてみてください。
<hr>
<span style="color:red">※スクリプト名/著作権表記の変更/削除は出来ません。<br />
　&nbsp;それら以外の部分のフォントのサイズ/色変更はスタイルシート等で自由に行って頂いて構いませんが、<br />
　&nbsp;変更した場合、スクリプト名/著作権表記が見えるよう適切にサイズ/色設定を行ってください。</span><br />
　&nbsp;著作権削除をしたい場合は<a href="mailto:phpedit@hotmail.com">こちら</a>までお問い合わせください。
</div>

<hr>
<a name="sample"></a>
以下設置サンプル(最新の情報から順に表示)
<hr>
<div align="center" style="font-size:12px">
<div style="width:155px;background-color:#f0f0f0;padding:3px;border: 1pt solid #e0e0e0;">
<script language="javascript" src="${scripturi}?m=jscript"></script>
<!--変更不可-->
<div style="text-align:center;font-size:10px;font-family:Tahoma">
<a href="http://rd.phpspot.org/?phpspot">PHP掲示板</a>
</div>
<!--/変更不可-->
</div>
</div>
EOM;

?>
<a href="javascript:history.go(-1)">もどる</a>
</div>

<?php require(APP_TEMPLATE_DIR."parts/footer.php");?>