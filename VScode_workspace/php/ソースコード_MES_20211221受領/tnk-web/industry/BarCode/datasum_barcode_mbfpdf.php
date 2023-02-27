<?php
//////////////////////////////////////////////////////////////////////////////
// �ǡ������� �С������ɥ����� PDF����(����) FPDF/MBFPDF����                //
// 2004/02/18 Copyright(C) 2004 K.Kobayashi tnksys@nitto-kohki.co.jp        //
// �ѹ�����                                                                 //
// 2004/02/18 �������� print_emp_history_mbfpdf.php                         //
// 2004/02/19 check digit ���б����Ƥ��ʤ��С������ɤ��Ȥ��Ƥ��뤿�� 0��  //
// 2004/03/01 FPDF_Protection extends MBFPDF���ѹ��������ΤߤΥץ�ƥ��Ȥ�  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('/home/www/html/tnk-web/function.php');   // access_log()��Ȥ�����define��function������
// require_once ('/home/www/html/tnk-web/define.php');
access_log();                           // Script Name �ϼ�ư����

////////////// �꥿���󥢥ɥ쥹����
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
// if ( !(isset($_POST['check_uid']) || isset($_POST['edit'])) ) {
//    $url_referer = $_SERVER['HTTP_REFERER'];    // GET�ξ��� HTTP_REFERER�����åȤ���ʤ�
//    $_SESSION['ret_addr'] = $url_referer;       // ���Ƥ�����ϻ��Ѥ��ʤ�
// } else {
//    $url_referer = $_SESSION['ret_addr'];       // ��ǧ�ե�����λ��ϥ��å���󤫤��ɹ���
// }
$url_referer = 'datasum_barcode.php';     // ����ƽи�

//////////////// ǧ�ڥ����å�
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 1) {        // ���¥�٥뤬���ʲ��ϵ���(���桼�����Τ�)
// if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    $_SESSION['s_sysmsg'] = '�С������ɤ�������븢�¤�����ޤ���';
    // header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

/////////// ���å����Υǡ��������
if (isset($_SESSION['dsum_uid'])) {
    $uid = $_SESSION['dsum_uid'];
} else {
    $_SESSION['s_sysmsg'] = '���å����˼Ұ��ֹ椬����ޤ���';
    exit();
}
if (isset($_SESSION['dsum_name'])) {
    $name = $_SESSION['dsum_name'];
} else {
    $_SESSION['s_sysmsg'] = '���å����˻�̾������ޤ���';
    exit();
}

##### MBFPDF/FPDF �ǻ��Ѥ����ȹ��ե����
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font �Υѥ�
##### ���ܸ�ɽ���ξ��ɬ�ܡ����ʤ����ɬ�����󥯥롼�ɤ���
require_once ('/home/www/html/mbfpdf/mbfpdf.php');          // �ޥ���Х���FPDF


Header('Pragma: public');   // https�����Ѥ���ݤΤ��ޤ��ʤ��Ǥ���

######### FPDF
// $usr_size = array(182.0, 257.0);     // B5 �����
// $pdf = new PDF_j($usr_size);         // ����Ѱդ�����ĥ���饹������
$usr_size = array(65.0, 94.0);          // �����ɥ����������
$pdf = new MBFPDF('P', 'mm', $usr_size);    // ����=L �Ľ�(Default)=P

///// PDFʸ��Υץ�ѥƥ�����
$pdf->SetAuthor('�������칩�������� ���Ӱ칰');    // Tochigi Nitto Kohki Co.,Ltd. k.kobayashi
$pdf->SetCreator("$current_script");
$pdf->SetTitle('DATA SUM Barcode Card');
$pdf->SetSubject('DATA SUM Barcode Card Print');
$pdf->SetDisplayMode('fullpage', 'default');   // �ڡ����Υ쥤������=�裲���������ꤷ�ʤ�����continuous=Ϣ³��������
$pdf->SetCompression(true);         // ���̤�ͭ���ˤ���(default=on)
$pdf->SetProtection(array('print'), '', 'tnkowner');    // �����Τߵ��ĤΥץ�ƥ��� fpdf_protection.php��ɬ��('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32)

///// PDFʸ��λ��ѥե���Ȥ�����
$pdf->AddMBFont(GOTHIC ,'SJIS');
// $pdf->AddMBFont(PGOTHIC,'SJIS');
// $pdf->AddMBFont(MINCHO ,'SJIS');
// $pdf->AddMBFont(PMINCHO,'SJIS');
// $pdf->AddMBFont(KOZMIN ,'SJIS');

///// PDF�򳫻�(��ά��ǽ��AddPage()��OK)
$pdf->Open();
// $pdf->SetLeftMargin(15.0);      // ���Υޡ�����򣱣�.���ߥ���ѹ�
// $pdf->SetRightMargin(5.0);      // ���Υޡ������.���ߥ���ѹ�
$pdf->SetMargins(0, 0, 0);      // �ޡ���������ƣ���
$pdf->SetFont(GOTHIC,'',10);    // �ǥե���ȥե���Ȥ�MS�����å� 10�ݥ���Ȥˤ��Ƥ�����
$pdf->AddPage();
$pdf->SetXY(0, 0);              // X Y ��ɸ�Υ��å�
$pdf->SetLineWidth(0.5);        // ����������0.5mm
// $pdf->Rect(0.5, 0.5, 64, 93);       // Ĺ����������
// $pdf->Rect(1.5, 1.5, 62, 91);       // Ĺ����������
$pdf->SetLineWidth(0.2);        // ����������0.2mm

///// �����ܤα��糫�Ϥ�����
$pdf->SetXY(1.5, 0);
$pdf->Cell(20, 22.75, '�����糫��', 'B', 0, 'L', 0);
$pdf->Cell(42, 22.75, '', 'B', 0, 'C', 0);
$pdf->Image('http://www.tnk.co.jp/barcode/barcode39_create_png.php?data=916&check=0&mode=white', 35, 1.5, 20, 18, 'PNG', '');  // ���糫�� *916* �ΥС�������ɽ��
$pdf->SetFont(GOTHIC,'', 6);    // �С������ɤο����� 6pt
$pdf->Text(35, 21.5, ' *   9   1   6   *');
$pdf->SetFont(GOTHIC,'',10);    // �ǥե���Ȥ��᤹

///// �����ܤμҰ��ֹ������
$pdf->SetXY(1.5, 22.5);
$pdf->SetFont(GOTHIC,'', 7);    // ��̾�Ͼ���������
$pdf->Cell(20, 22.75, '', 'B', 0, 'L', 0);
$pdf->Cell(42, 22.75, '', 'B', 0, 'C', 0);
$pdf->Text(3.5, 25, "��{$name}");
$pdf->Image("http://www.tnk.co.jp/barcode/barcode39_create_png.php?data={$uid}&check=0&mode=white", 20, 24.5, 42, 18, 'PNG', '');  // �Ұ��ֹ� *777001* �ΥС�������ɽ��
$pdf->SetFont(GOTHIC,'', 6);    // �С������ɤο����� 6pt
$pdf->Text(22, 44.5, '*    '.substr($uid,0,1).'    '.substr($uid,1,1).'    '.substr($uid,2,1).'    '.substr($uid,3,1).'    '.substr($uid,4,1).'    '.substr($uid,5,1).'    *');
$pdf->SetFont(GOTHIC,'',10);    // �ǥե���Ȥ��᤹

///// �����ܤΤ���¾�ײ������
$pdf->SetXY(1.5, 46.5);
$pdf->SetFont(GOTHIC,'', 6);    // ����¾�ײ�Ͼ���������
$pdf->Cell(20, 22.75, '', 'B', 0, 'L', 0);
$pdf->Cell(42, 22.75, '', 'B', 0, 'C', 0);
$pdf->Text(3.5, 48, '������¾�ײ�');
$pdf->Image('http://www.tnk.co.jp/barcode/barcode39_create_png.php?data=C9999999&check=0&mode=white', 16, 49, 46, 18, 'PNG', '');  // ����¾�ײ� *C9999999* �ΥС�������ɽ��
$pdf->SetFont(GOTHIC,'', 5);    // �С������ɤο����� 5pt
$pdf->Text(18, 68.5, '*    C    9    9    9    9    9    9    9    *');
$pdf->SetFont(GOTHIC,'',10);    // �ǥե���Ȥ��᤹

///// �����ܤα��糫�Ϥ�����
$pdf->SetXY(1.5, 73);     // �ʤ���73��Ķ�����Cell��Write�Ǥϼ��Ǥ�����Ǥ��ޤ�
// $pdf->Cell(20, 0, '���ܺ��', 'B', 0, 'L', 0);
// $pdf->Cell(42, 18.00, '', 'B', 0, 'C', 0);
// $pdf->Write(0, '���ܺ��');
$pdf->Text(3, 82, '���ܺ��');  // MBFPDF �������� ���ꥸ�ʥ�� Text()��EUC-JP̤�б�
$pdf->Image('http://www.tnk.co.jp/barcode/barcode39_create_png.php?data=910&check=0&mode=white', 35, 71, 20, 18, 'PNG', '');  // �ܺ�� *910* �ΥС�������ɽ��
$pdf->SetFont(GOTHIC,'', 6);    // �С������ɤο����� 6pt
$pdf->Text(35, 91, ' *   9   1   0   *');
$pdf->SetFont(GOTHIC,'',10);    // �ǥե���Ȥ��᤹

$pdf->Output();     // �Ǹ�ˡ��嵭�ǡ�������Ϥ��ޤ���
exit();               // �ʤ�٤������뤹�롣�ޤ����Ǹ��PHP���å��˲��Ԥʤɤ��ޤޤ��ȥ���
?> 