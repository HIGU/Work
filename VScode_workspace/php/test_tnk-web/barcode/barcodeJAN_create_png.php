<?php
//////////////////////////////////////////////////////////////////////////////
// BarCode JAN(Japan Article Number)Code Create                             //
// Useage                                                                   //
//  in HTML                                                                 //
// <img src=barcodeJAN_create_png.php?data={$data}                          //
//    $data = Barcodeのデータ    チェックデジットの自動計算チェック機能付   //
//                             modo=blackは白黒反転指示(オプション)を検討中 //
// GET メソッド専用に注意する事                                             //
// 2004/02/18 Copyright(C) 2004 K.Kobayashi tnksys@nitto-kohki.co.jp        //
// 変更経歴                                                                 //
// 2004/02/18 新規作成  barcodeJAN_create_png.php                           //
// 2004/02/19 FPDF 出力時は CGI として呼出すため SESSION を外した。         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
// ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮
// session_start();                        // ini_set()の次に指定すること Script 最上行

// require_once ('/var/www/html/function.php');       // define.php と pgsql.php を require_once している
// require_once ('/var/www/html/tnk_func.php');       // TNK に依存する部分の関数を require_once している
// access_log();                           // Script Name は自動取得

///// GET パラメータ確認
if (isset($_GET['data'])) {
    $jan  = $_GET['data'];
    if (!is_numeric($jan)) {
        exit(); // 数字以外がある場合は強制終了
    }
} else {
    exit(); // GET パラメータが無ければ強制終了
}
///// image/png のヘッダー出力
Header("Content-type: image/png");
header("cache-control: no-cache"); 

// $jan = '4901772851615';  // 家にあった子供のおもちゃの商品コード check digitでエラーになる
// $jan = '4901772851615';  // こちらのサンプルはOK

/* 入力された桁数のチェック */
$len_chk = strlen($jan);
if ( $len_chk <13 || $len_chk >13 ):    // 試しに違う構文にしてみた
    // 入力された桁数がちがいます。
    exit();
endif;

/* 文字列を1つずつ　$jancd[] に格納する。 */
for ($i=0; $i <= 13; $i++) {
    $jancd[$i] = substr($jan,$i,1);
}

/* Check   13桁目 チェックデジット の計算、照合 */

$chk1   = 3*($jancd[1]+$jancd[3]+$jancd[5]+$jancd[7]+$jancd[9]+$jancd[11]); 
$chk2   = $jancd[0]+$jancd[2]+$jancd[4]+$jancd[6]+$jancd[8]+$jancd[10]; 
$chkt   = $chk1+$chk2;
$chk3   = (int) ($chkt/10);
$chkdgt = ($chk3+1)*10 - $chkt;

if ($chkdgt > $jancd[12] || $chkdgt < $jancd[12]):
    // 入力された13桁目(最後)の数が違います。<p>
    exit();
endif;


/* OEOOEE(=4) の隠しコードが必用です。*/
/* $LE と $LO は 左6値 */
/* Left Odd (奇数) Table*/

$LO[0]=array(0,  0,0,0,1,1,0,1);
$LO[1]=array(0,  0,0,1,1,0,0,1);
$LO[2]=array(0,  0,0,1,0,0,1,1);
$LO[3]=array(0,  0,1,1,1,1,0,1);
$LO[4]=array(0,  0,1,0,0,0,1,1);
$LO[5]=array(0,  0,1,1,0,0,0,1); 
$LO[6]=array(0,  0,1,0,1,1,1,1);
$LO[7]=array(0,  0,1,1,1,0,1,1);
$LO[8]=array(0,  0,1,1,0,1,1,1);
$LO[9]=array(0,  0,0,0,1,0,1,1); 

/* LEFT Even (偶数) Table */

$LE[0]=array(0,  0,1,0,0,1,1,1);
$LE[1]=array(0,  0,1,1,0,0,1,1);
$LE[2]=array(0,  0,0,1,1,0,1,1);
$LE[3]=array(0,  0,1,0,0,0,0,1);
$LE[4]=array(0,  0,0,1,1,1,0,1); 
$LE[5]=array(0,  0,1,1,1,0,0,1); 
$LE[6]=array(0,  0,0,0,0,1,0,1);
$LE[7]=array(0,  0,0,1,0,0,0,1);
$LE[8]=array(0,  0,0,0,1,0,0,1);
$LE[9]=array(0,  0,0,1,0,1,1,1);

/* $RE は 右半分の Table */
/* $LO の反転  */

$RE[0]=array(0,  1,1,1,0,0,1,0);
$RE[1]=array(0,  1,1,0,0,1,1,0);
$RE[2]=array(0,  1,1,0,1,1,0,0);
$RE[3]=array(0,  1,0,0,0,0,1,0);
$RE[4]=array(0,  1,0,1,1,1,0,0);
$RE[5]=array(0,  1,0,0,1,1,1,0);
$RE[6]=array(0,  1,0,1,0,0,0,0);
$RE[7]=array(0,  1,0,0,0,1,0,0);
$RE[8]=array(0,  1,0,0,1,0,0,0);
$RE[9]=array(0,  1,1,1,0,1,0,0);


/* テーブルの値を配列に格納する。 */

/* 配列 $disp[$k][$i] は        */
/* $k (JANコードの 最初の4 を除く、1--12文字の順 ) */
/* $i は 値に対応する 7つの要素 0 or 1*/
/* 0 --> 白   1--> 黒  */

for ( $k=1; $k < 13; $k++) {
   for ( $i=1; $i < 8 ; $i++) {
      if( $k > 6 ):
         $disp[$k][$i] = $RE[$jancd[$k]] [$i];
         elseif( $k == 1 || $k == 3 || $k ==4):
            $disp[$k][$i] = $LO[$jancd[$k]] [$i];
            else:
            $disp[$k][$i] = $LE[$jancd[$k]] [$i];
      endif;
   }
 
}
/*  デバッグ用　です。
for ( $k=1; $k < 13; $k++) {
   echo "($k:) $jancd[$k] --> ";
   for ( $i=1;$i< 8 ; $i++) {
      echo "{$disp[$k][$i]}";
   }
   echo "<br>";
}
*/

/* ここから　GD ライブラリを使うスクリプト */
header("content-type: image/png "); 
header("cache-control: no-cache"); 

$im= imagecreate(400,300);

/* 最初に設定した色が背景色になる。= $white */
$white  = imagecolorallocate($im,255,255,255); 
$yellow = imagecolorallocate($im,255,204,000);
$black=   imagecolorallocate($im,000,000,000);
$red =    imagecolorallocate($im,255,000,000);
$green=   imagecolorallocate($im,000,255,000);


imagestring($im, 20, 25, 10, "Test of gd-2.0.17 for  JANCODE",$red);
imagestring($im, 20, 25, 30, "Apache2.0.48 + PHP4.3.5RC2",$red);
imagestring($im, 20, 25, 50, "OS:Red-Hat7 Linux-2.4.18",$red);
imagestring($im, 20, 25, 70, "       19.Feb.2004 K.Kobayashi",$red);

$bw=3;  /* バーコードの巾 */
$bh=80; /* バーコードの高さ */
$sx=30; /* スタートX座標    */
$sy=240;/* スタートY座標    */


/* 左のガードバー  バー2本 スペース 1本  */

imagefilledrectangle($im,$sx,$sy-$bh,$sx+$bw-1,$sy+14,$black);
imagefilledrectangle($im,$sx+$bw,$sy-$bh,$sx+$bw*2-1,$sy+14,$white);
imagefilledrectangle($im,$sx+$bw*2,$sy-$bh,$sx+$bw*3-1,$sy+14,$black);
imagestring($im, 20, $sx-14, $sy+2,"4",$black);

for ($k=1; $k < 7; $k++) {
   $mae =($sx+$bw*3) + $bw*7*($k-1);
    imagestring($im, 20, $mae+3,$sy+2, $jancd[$k],$black);
   for ($i=1; $i < 8; $i++) {
       if ( $disp[$k][$i] == 1):
           imagefilledrectangle($im,$mae+$bw*($i-1),$sy-$bh,$mae+$bw*$i-1,$sy,$black);

           $leftendx = $mae+$bw*$i ;
       else:
           imagefilledrectangle($im,$mae+$bw*($i-1),$sy-$bh,$mae+$bw*$i-1,$sy,$white);

           $leftendx = $mae+$bw*$i ;
       endif;
   }
} 

/* センターバー  バー2本、 スペース3本  */
imagefilledrectangle($im,$leftendx,      $sy-$bh,$leftendx+$bw-1,  $sy,$white);
imagefilledrectangle($im,$leftendx+$bw,  $sy-$bh,$leftendx+$bw*2-1,$sy+14,$black);
imagefilledrectangle($im,$leftendx+$bw*2,$sy-$bh,$leftendx+$bw*3-1,$sy+14,$white);
imagefilledrectangle($im,$leftendx+$bw*3,$sy-$bh,$leftendx+$bw*4-1,$sy+14,$black);
imagefilledrectangle($im,$leftendx+$bw*4,$sy-$bh,$leftendx+$bw*5-1,$sy+14,$white);
$mae1 = $leftendx+$bw*5;

for ($k=7; $k < 13; $k++) {
   $mae =$mae1 + $bw*7*($k-7);
      imagestring($im, 20, $mae+3,$sy+2, $jancd[$k],$black);
   for ($i=1; $i < 8; $i++) {
       if ( $disp[$k][$i] == 1):
           imagefilledrectangle($im,$mae+$bw*($i-1),$sy-$bh,$mae+$bw*$i-1,$sy,$black);
           $leftendx = $mae+$bw*$i ;
       else:
           imagefilledrectangle($im,$mae+$bw*($i-1),$sy-$bh,$mae+$bw*$i-1,$sy,$white);
           $leftendx = $mae+$bw*$i ;
       endif;
   }
} 

/* 右のガードバー  バー2本 スペース 1本  */

imagefilledrectangle($im,$leftendx,     $sy-$bh,$leftendx+$bw-1,  $sy+14,$black);
imagefilledrectangle($im,$leftendx+$bw,  $sy-$bh,$leftendx+$bw*2-1,$sy+14,$white);
imagefilledrectangle($im,$leftendx+$bw*2,$sy-$bh,$leftendx+$bw*3-1,$sy+14,$black); 


// i18n_http_output("pass");    php3 の仕様


imagepng($im);
imagedestroy($im);

?>

