<?php
//////////////////////////////////////////////////////////////////////////////
// BarCode Code39 Create                                                    //
// Useage                                                                   //
//  in HTML                                                                 //
// <img src=barcode39_create_png.php?data={$data}&check=[0|1]&[mode=black]  //
//    $data = Barcode�Υǡ���  check = �����å��ǥ��å� �̾��1=ͭ��        //
//                                   modo = black�����ȿž�ؼ�(���ץ����) //
// GET �᥽�å����Ѥ���դ����                                             //
//   code39.php ver 1.10  (c)1999-2000 Y.Swetake �Υ���ץ륳���ɤ򻲹�     //
// 2004/02/18 Copyright(C) 2004 K.Kobayashi tnksys@nitto-kohki.co.jp        //
// �ѹ�����                                                                 //
// 2004/02/18 ��������  barcode39_create_png.php                            //
// 2004/02/19 FPDF ���ϻ��� CGI �Ȥ��ƸƽФ����� SESSION �򳰤�����         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
// ob_start('ob_gzhandler');               // ���ϥХåե���gzip����
// session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

// require_once ('/home/www/html/tnk-web/function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
// require_once ('/home/www/html/tnk-web/tnk_func.php');       // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
// access_log();                           // Script Name �ϼ�ư����

///// GET �ѥ�᡼����ǧ
if (isset($_GET['data']) && isset($_GET['check'])) {
    $data  = $_GET['data'];
    $check = $_GET['check'];
} else {
    exit(); // GET �ѥ�᡼����̵����ж�����λ
}
///// image/png �Υإå�������
Header("Content-type: image/png");
// header("cache-control: no-cache");   // ɬ�פ�����Х����Ȥ򳰤���

///// URL �ǥ����ɤ��� $data��� + �� ���ڡ������ִ���
$qs = rawurldecode(strtr($data, "+", " "));
$lx = strlen($qs);
if ($lx < 1) {
    exit;   // �ǡ�����̵����ж�����λ
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
$im = imagecreate($w, $ht);     // �С������ɥ���������

///// ���ץ����� mode ���꤬���뤫�����å� ����������ȿž���롣
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
