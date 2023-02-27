<?php
//////////////////////////////////////////////////////////////////////////////
// BarCode JAN(Japan Article Number)Code Create                             //
// Useage                                                                   //
//  in HTML                                                                 //
// <img src=barcodeJAN_create_png.php?data={$data}                          //
//    $data = Barcode�Υǡ���    �����å��ǥ��åȤμ�ư�׻������å���ǽ��   //
//                             modo=black�����ȿž�ؼ�(���ץ����)��Ƥ�� //
// GET �᥽�å����Ѥ���դ����                                             //
// 2004/02/18 Copyright(C) 2004 K.Kobayashi tnksys@nitto-kohki.co.jp        //
// �ѹ�����                                                                 //
// 2004/02/18 ��������  barcodeJAN_create_png.php                           //
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
if (isset($_GET['data'])) {
    $jan  = $_GET['data'];
    if (!is_numeric($jan)) {
        exit(); // �����ʳ���������϶�����λ
    }
} else {
    exit(); // GET �ѥ�᡼����̵����ж�����λ
}
///// image/png �Υإå�������
Header("Content-type: image/png");
header("cache-control: no-cache"); 

// $jan = '4901772851615';  // �Ȥˤ��ä��Ҷ��Τ������ξ��ʥ����� check digit�ǥ��顼�ˤʤ�
// $jan = '4901772851615';  // ������Υ���ץ��OK

/* ���Ϥ��줿����Υ����å� */
$len_chk = strlen($jan);
if ( $len_chk <13 || $len_chk >13 ):    // ��˰㤦��ʸ�ˤ��Ƥߤ�
    // ���Ϥ��줿������������ޤ���
    exit();
endif;

/* ʸ�����1�Ĥ��ġ�$jancd[] �˳�Ǽ���롣 */
for ($i=0; $i <= 13; $i++) {
    $jancd[$i] = substr($jan,$i,1);
}

/* Check   13���� �����å��ǥ��å� �η׻����ȹ� */

$chk1   = 3*($jancd[1]+$jancd[3]+$jancd[5]+$jancd[7]+$jancd[9]+$jancd[11]); 
$chk2   = $jancd[0]+$jancd[2]+$jancd[4]+$jancd[6]+$jancd[8]+$jancd[10]; 
$chkt   = $chk1+$chk2;
$chk3   = (int) ($chkt/10);
$chkdgt = ($chk3+1)*10 - $chkt;

if ($chkdgt > $jancd[12] || $chkdgt < $jancd[12]):
    // ���Ϥ��줿13����(�Ǹ�)�ο����㤤�ޤ���<p>
    exit();
endif;


/* OEOOEE(=4) �α��������ɤ�ɬ�ѤǤ���*/
/* $LE �� $LO �� ��6�� */
/* Left Odd (���) Table*/

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

/* LEFT Even (����) Table */

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

/* $RE �� ��Ⱦʬ�� Table */
/* $LO ��ȿž  */

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


/* �ơ��֥���ͤ�����˳�Ǽ���롣 */

/* ���� $disp[$k][$i] ��        */
/* $k (JAN�����ɤ� �ǽ��4 �������1--12ʸ���ν� ) */
/* $i �� �ͤ��б����� 7�Ĥ����� 0 or 1*/
/* 0 --> ��   1--> ��  */

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
/*  �ǥХå��ѡ��Ǥ���
for ( $k=1; $k < 13; $k++) {
   echo "($k:) $jancd[$k] --> ";
   for ( $i=1;$i< 8 ; $i++) {
      echo "{$disp[$k][$i]}";
   }
   echo "<br>";
}
*/

/* �������顡GD �饤�֥���Ȥ�������ץ� */
header("content-type: image/png "); 
header("cache-control: no-cache"); 

$im= imagecreate(400,300);

/* �ǽ�����ꤷ�������طʿ��ˤʤ롣= $white */
$white  = imagecolorallocate($im,255,255,255); 
$yellow = imagecolorallocate($im,255,204,000);
$black=   imagecolorallocate($im,000,000,000);
$red =    imagecolorallocate($im,255,000,000);
$green=   imagecolorallocate($im,000,255,000);


imagestring($im, 20, 25, 10, "Test of gd-2.0.17 for  JANCODE",$red);
imagestring($im, 20, 25, 30, "Apache2.0.48 + PHP4.3.5RC2",$red);
imagestring($im, 20, 25, 50, "OS:Red-Hat7 Linux-2.4.18",$red);
imagestring($im, 20, 25, 70, "       19.Feb.2004 K.Kobayashi",$red);

$bw=3;  /* �С������ɤζ� */
$bh=80; /* �С������ɤι⤵ */
$sx=30; /* ��������X��ɸ    */
$sy=240;/* ��������Y��ɸ    */


/* ���Υ����ɥС�  �С�2�� ���ڡ��� 1��  */

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

/* ���󥿡��С�  �С�2�ܡ� ���ڡ���3��  */
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

/* ���Υ����ɥС�  �С�2�� ���ڡ��� 1��  */

imagefilledrectangle($im,$leftendx,     $sy-$bh,$leftendx+$bw-1,  $sy+14,$black);
imagefilledrectangle($im,$leftendx+$bw,  $sy-$bh,$leftendx+$bw*2-1,$sy+14,$white);
imagefilledrectangle($im,$leftendx+$bw*2,$sy-$bh,$leftendx+$bw*3-1,$sy+14,$black); 


// i18n_http_output("pass");    php3 �λ���


imagepng($im);
imagedestroy($im);

?>

