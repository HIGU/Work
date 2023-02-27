<?php
//////////////////////////////////////////////////////////////////////////////
// BarCode Code39 Create                                                    //
// Useage                                                                   //
//  in HTML                                                                 //
// <img src=barcode39_create_png.php?data={$data}&check=[0|1]&[mode=black]  //
//    $data = Barcodeのデータ  check = チェックデジット 通常は1=有り        //
//                                   modo = blackは白黒反転指示(オプション) //
// GET メソッド専用に注意する事                                             //
//   code39.php ver 1.10  (c)1999-2000 Y.Swetake のサンプルコードを参考     //
// 2004/02/18 Copyright(C) 2004 K.Kobayashi tnksys@nitto-kohki.co.jp        //
// 変更経歴                                                                 //
// 2004/02/18 新規作成  barcode39_create_png.php                            //
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
if (isset($_GET['data']) && isset($_GET['check'])) {
    $data  = $_GET['data'];
    $check = $_GET['check'];
} else {
    exit(); // GET パラメータが無ければ強制終了
}
///// image/png のヘッダー出力
Header("Content-type: image/png");
// header("cache-control: no-cache");   // 必要があればコメントを外す。

///// URL デコードして $data中の + を スペースに置換え
$qs = rawurldecode(strtr($data, "+", " "));
$lx = strlen($qs);
if ($lx < 1) {
    exit;   // データが無ければ強制終了
}

$cc = array(
        "0"=>52, "1"=>289,"2"=>97, "3"=>352,"4"=>49, "5"=>304,"6"=>112,"7"=>37,
        "8"=>292,"9"=>100,"A"=>265,"B"=>73, "C"=>328,"D"=>25, "E"=>280,"F"=>88,
        "G"=>13, "H"=>268,"I"=>76, "J"=>28, "K"=>259,"L"=>67, "M"=>322,"N"=>19,
        "O"=>274,"P"=>82, "Q"=>7,  "R"=>262,"S"=>70, "T"=>22, "U"=>385,"V"=>193,
        "W"=>448,"X"=>145,"Y"=>400,"Z"=>208,"-"=>133,"."=>388," "=>196,"$"=>168,
        "/"=>162,"+"=>138, "%"=>42, "*"=>148
    );

if ($check == 1) {
    $ch="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%";
    $l  = 0;
    $cd = 0;
    while ($l < $lx) {
        $cd += strpos($ch, substr($qs, $l, 1));
        $l++;
    }
    $cd  = ($cd % 43);
    $s   = "*" . $qs . substr($ch, $cd, 1) . "*";
    $lx += 3;
} else {
    $s   = "*" . $qs . "*";
    $lx += 2;
}

$ww = 2;
$nw = 0;
$w  = (($ww + 1) * 4 + ($nw + 1) * 6) * $lx + $ww;
$ht = 50;
$im = imagecreate($w, $ht);     // バーコードサイズ決定

///// オプションの mode 指定があるかチェック あれば白黒を反転する。
if (isset($_GET['mode'])) {
    $mode = $_GET["mode"];
} else {
    $mode = '';
}
if ($mode == 'black') {
    $col[2] = ImageColorAllocate($im, 255, 255, 255);
    $col[0] = ImageColorAllocate($im, 0, 0, 0);
} else {
    $col[2] = ImageColorAllocate($im, 0, 0, 0);
    $col[0] = ImageColorAllocate($im, 255, 255, 255);
}

$x = 0;
$l = 0;

while ($l < $lx) {
    $cs = $cc[substr($s,$l,1)];
    
    if ($cs == '') {
        imagedestroy($im);
        exit();
    }
    ImageFilledRectangle($im, $x, 0, $x+$ww, $ht, $col[0]);
    $x  = ($x+$ww+1);
    $sn = 1;
    $j  = 8;
    while ($j >= 0) {
        $p = (1 << $j);
        if ($cs>=$p) {
            $cs = ($cs - $p);
            $bl = $ww;
        } else {
            $bl=$nw;
        }
        ImageFilledRectangle($im, $x, 0, $x+$bl, $ht, $col[$sn+1]);
        $x  = ($x+$bl+1);
        $sn = -$sn;
        $j--;
    }
    $l++;
}
ImageFilledRectangle($im, $x, 0, $w, $ht, $col[0]);
ImagePng($im);
Imagedestroy($im);
?>
