<?php
//////////////////////////////////////////////////////////////////////////////
// �ԣΣ�(����Ū����ʬ�������ȼ�����ʬ��ȴ�Ф�) �ե��󥯥å���� �ե����� //
// Copyright (C) 2001-2011 Kazuhiro Kobayashi all rights reserved.          //
//                                                              2001/10/01  //
// Changed history                                                          //
// 2001/10/01 Created  tnk_func.php                                         //
// 2001/10/01 function corrc_round($var) round() ���������б��Ǻ���         //
// 2001/11/15 function Uround($var,������) ����б��� ����                  //
// 2002/02/15 ���������ط����ɲ�                                            //
// 2002/03/07 �����ط����������ѤΥե�����ˤޤȤ᤿��equip_function.php    //
// 2002/09/24 day_off() �� CASE ʸ�˴ؿ����Ȥ��ʤ����� day_off2()�����     //
// 2002/12/24 menu_bar()���ɲ� ��˥塼�ѥ�������(����)��ư����             //
// 2003/01/16 account_group_check() ��̳(»��)�ط��ε��ĥ桼���������å�    //
// 2003/01/21 Ym_to_tnk() �����ǯ����������칩����оݴ����֤�          //
// 2003/06/02 menu_bar()�ǥե����뤬���ˤ�����ϲ��⤷�ʤ�(����û��)      //
//                      ���Τ��ᡢ�ѹ���������ɬ���ե�����������뤳�ȡ�  //
// 2003/06/28 session_start() 4.3.3RC1 ��Notice �ˤʤ뤿�ᥳ����(������)//
// 2003/07/16 �����Υ����ॹ����פ���������������֤� mb_day_name() ���ɲ� //
// 2003/08/05 account_group_check2() �»�׾Ȳ�(�Ρ��Ĥ���)�桼����ǧ��   //
// 2003/11/27 format_date()����� ������ 0 �ξ��� ----/--/-- ���֤�       //
// 2003/11/29 menu_bar()�� $create_flg �������ɲ� ���᡼���ζ��������ե饰  //
// 2003/12/19 day_off()��date_offset()�����(������offset�оݤ��鳰����)    //
// 2004/04/05 account_group_check()�˥桼�����ɲ�(���ݼ��ס���ë���)       //
// 2005/04/26 �裶���Υ����������ɲ� function day_off($timestamp)         //
// 2005/06/05 last_day()����ǯ��κǸ�������֤��ؿ����ɲ�                  //
// 2006/01/25 �Ķ��������դ�����(+-)��Offset������workingDayOffset()���ɲ�  //
// 2006/02/16 workingDayOffset()�Υޥ��ʥ����ե��åȤΥ��å���������      //
// 2006/02/23 �裷���Υ����������ɲ�                                      //
// 2006/04/03 last_day()�κǽ�����дؿ��˺ǽ������Ķ����������å���ǽ�ɲ�  //
// 2006/06/08 2006/07/20�򳤤�������Ͽ����Ƥ����Τ�2006/07/17������        //
// 2006/06/24 workingDayOffset()php-5.1.4��'+0'��'-0'��switchʸ��0�����б�//
//            workingDayOffset('-0')���̸ߴ� workingDayOffset(0, '-')����ʸ //
// 2006/06/26 �����ȣ����εٲˤ� day_off($timestamp) ���ɲ�                 //
// 2006/07/21 menu_bar()�˥ե�����������Υѡ��ߥå�����ѹ��ɲ�            //
// 2006/09/28 ��Ω�饤���Ѥ� day_off_line() �� �ɲ�                         //
// 2006/09/29 �Ķ����ۤʤ�masterst��masterst2��BAR_MBTTF_F����å�������  //
// 2006/10/05 account_group_check(), account_group_check2() �Υ��ƥʥ�  //
// 2006/12/28 day_off()�򿷵��˽� company_calendar�ơ��֥�����          //
// 2007/01/09 account_group_check(),account_group_check2()���̸���function��//
// 2007/02/06 day_off()�ǡ�����̵�����϶���Ū�˵����ˤ���򢪱Ķ������ѹ� //
// 2007/11/22 ��ȥ饤��ftpGetCheckAndExecute(),ftpPutCheckAndExecute()�ɲ� //
// 2010/01/19 account_group_check��������Ĺ�������ɲ�                       //
//            getCheckAuthority(4)�ǰ��óݤ��Ƥ����ΤǤ��ä����ɲ�     ��ë //
// 2011/06/15 �Ƽ�6������դΥե����ޥåȴؿ����ɲ�                    ��ë //
//////////////////////////////////////////////////////////////////////////////


/********** �����ǻ��ꤵ�줿�����ॹ����פ���������������֤� **********/
function mb_day_name($time_stamp)
{
    $day = date("w", $time_stamp);
    switch ($day) {
    case 0:
        return "������";
        break;
    case 1:
        return "������";
        break;
    case 2:
        return "������";
        break;
    case 3:
        return "������";
        break;
    case 4:
        return "������";
        break;
    case 5:
        return "������";
        break;
    case 6:
        return "������";
        break;
    default:
        return "���顼";
    }
}

/********* �����ǯ����������칩����оݴ����֤� ***********/
// default �� ����δ����֤�(��������ꤷ�ʤ����)
function Ym_to_tnk( $ym )
{
    if ($ym == NULL) {
        $ym = $date("Ym");   // ������ default �ͤ� function()���������뤬�ͤ�ؿ�����������뤳�ȤϽ���ʤ�����
    }
    $tmp = $ym - 200003;     // �裱��������
    $tmp = $tmp / 100;       // ǯ����ʬ����Ф�
    $ki  = ceil($tmp);       // roundup ��Ʊ��
    return $ki;              // �����֤�
}


/********* »�״ط��Υ����� ���ĥ桼���� ***********/
/********* ��Ω�����»�׾Ȳ��˥塼�ǻ��Ѥ��롣�桼����ǧ�ڤ�IP ADDRES�Τ� *******/
function account_group_check2()
{
    require_once ('/home/www/html/tnk-web/function.php');
    if (getCheckAuthority(5)) {
        return true;
    } else {
        return false;
    }
    
    $addr = $_SERVER["REMOTE_ADDR"];
    switch ($addr) {
        case '10.1.3.136'; return TRUE; break;  // ���Ӱ칰
        case '10.1.3.121'; return TRUE; break;  // ��ƣ����
        case '10.1.3.105'; return TRUE; break;  // �����
        case '10.1.3.163'; return TRUE; break;  // ��������
        case '10.1.3.126'; return TRUE; break;  // ����������
        case '10.1.1.246'; return TRUE; break;  // ���ں�͵
        case '10.1.3.123'; return TRUE; break;  // ���ں�͵ �� �����Ӱ�
        case '10.1.3.57' ; return TRUE; break;  // ���ĸ���ͺ �� �����Ӱ�
        case '10.1.3.164'; return TRUE; break;  // ��ë���         2004/04/05�ɲ�
        case '10.1.3.152'; return TRUE; break;  // ��������
        // case '10.1.3.107'; return TRUE; break;  // ���ĸ���ͺ
        // case '10.1.3.113'; return TRUE; break;  // ��ë����
        // case '10.1.3.187'; return TRUE; break;  // ���ݼ���         2004/04/05�ɲ�
        default;       return FALSE;
    }
    return FALSE;
}


/********* »�״ط��Υ����� ���ĥ桼���� ***********/
function account_group_check()
{
    require_once ('/home/www/html/tnk-web/function.php');
    if (getCheckAuthority(4)) {
        return true;
    } else {
        return false;
    }
    
    // session_start();             // 4.3.3RC1 ��Notice �ˤʤ뤿�ᥳ����(������)
    if ( isset($_SESSION["User_ID"]) || isset($_SESSION["Password"]) || isset($_SESSION["Auth"]) ) {
        $chk_usr = $_SESSION['User_ID'];
        switch ($chk_usr) {
            case '010561'; return TRUE; break;  // ���Ӱ칰
            case '300055'; return TRUE; break;  // ��ƣ����
            case '017850'; return TRUE; break;  // �����
            case '300071'; return TRUE; break;  // ��������
            // case '008699'; return TRUE; break;  // ���ܽ���
            // case '001406'; return TRUE; break;  // ��ë����
            case '010189'; return TRUE; break;  // ����������
            case '004154'; return TRUE; break;  // ���ĸ���ͺ
            case '007340'; return TRUE; break;  // ���ն�
            // case '001899'; return TRUE; break;  // ���ں�͵
            // case '005487'; return TRUE; break;  // ���ݼ���         2004/04/05�ɲ�
            case '300101'; return TRUE; break;  // ��ë���         2004/04/05�ɲ�
            case '009504'; return TRUE; break;  // ��������
            case '019321'; return TRUE; break;  // �����Ӱ�
            default;       return FALSE;
        }
    }
    return FALSE;
}


/********* ��˥塼�������� ��ư���� ����͡�ե�����̾ *********/
// �饤�֥��Ȥ��� GD & freetype ����� Free �� True Type Font ��ɬ�ס�
// ����Ǥ���ե���ȥ������� 1��8 14��(gothic) 14,17��(mincho)
// php-4.3.0 ����ΥХ�ɥ���GD����Ѥ�����ˤ�ꥵ�����ϼ�ͳ������Ǥ���褦�ˤʤä�������ͤ� 14
// Default �Υե�����̾�� temp.png
// Default �� Title(Menu Name) �� Blank
// Default �� Background Color �� Sky Blue
// Default �� String Color �� Black
function menu_bar($file="temp.png", $title="", $font_size=14, $create_flg=0,
                  $r_bg=198, $g_bg=219, $b_bg=247, $r_str=0, $g_str=0, $b_str=0)
{
    if (file_exists($file)) {   ////// ���˥ե����뤬������ϲ��⤷�ʤ� �������֤�û�̤Τ���
        if ($create_flg == 0) {     // ���˥ե����뤬��������ե饰�� 0 �ʤ鲿�⤷�ʤ�
            return $file;
        }
    }
    $file_masterst  = '/usr/share/fonts/ja/TrueType/kochi-mincho.ttf';
    $file_masterst2 = '/usr/share/fonts/japanese/TrueType/kochi-mincho.ttf';
    if (file_exists($file_masterst)) {
        $BAR_MBTTF_F = $file_masterst;
    } else {
        $BAR_MBTTF_F = $file_masterst2;
    }
    $im = imagecreate (200, 32);
    $bg_color = ImageColorAllocate ($im, $r_bg, $g_bg, $b_bg);    // �Хå������ɤ����
    $white = ImageColorAllocate ($im, 255, 255, 255);
    $gray  = ImageColorAllocate ($im, 132, 130, 132);
    $black = ImageColorAllocate ($im, 66, 65, 66);
    $win_gray = ImageColorAllocate ($im, 214, 211, 206);     // Windows Gray color
    ImageLine($im, 0, 0, 199, 0, $black);               // X ���� �ͳѷ��ξ���
    ImageLine($im, 1, 1, 199, 1, $white);
    ImageLine($im, 0, 0, 0, 31, $black);                // Y ���� �ͳѷ��κ���
    ImageLine($im, 1, 1, 1, 30, $white);
    ImageLine     ($im,   1, 29, 197, 29, $gray);       // X ����
    ImageRectangle($im,   1, 30, 199, 31, $black);      //  2�ԥ�����ñ�̤�û���κ���
    ImageLine     ($im, 197, 1,  197, 28, $gray);       // Y ����
    ImageRectangle($im, 198, 0,  199, 31, $black);      //  2�ԥ�����ñ�̤�û���κ���
    if ($title != "") {
        $str_color = ImageColorAllocate ($im, $r_str, $g_str, $b_str);  // ʸ���������
        $menu_title = mb_convert_encoding($title, "UTF-8");   //// ʸ���������Ѵ�
        ImageTTFText ($im, $font_size, 0, 6, 22, $str_color, $BAR_MBTTF_F, $menu_title);  // X��=6 Y��=22 (ʸ���κ�����)
    } else {
        ImageFill($im, 10, 20, $win_gray);
    }
    ImagePng ($im, $file);
    ImageDestroy ($im);
    chmod($file, 0666); // 2006/07/21 ADD
    return $file;
}


// �����Ǥ�դ����դ�'/'�ե����ޥåȤ����֤���
// �����褦��ʪ�� number_format() �����롣
// ������Υ���ޤ� number_format() �Ǥ��� default number_format($num)
// ¾�ΰ����� number_format($num,'�������η��','formatʸ��','3���formatʸ��')���ͤˤ��롣
// number_format() ������ͤ���� printf �� sprintf ���Ǥ� %d �Ǥʤ� %s ����Ѥ��뎡
function format_date($date8)
{
    if (0 == $date8) {
        $date8 = '--------';    // ----/--/-- ���ͤ��֤����� 2003/11/27 �ɲ�
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8,0,4);
        $tsuki = substr($date8,4,2);
        $hi    = substr($date8,6,2);
        return $nen . "/" . $tsuki . "/" . $hi;
    } else {
        return FALSE;
    }
}

// �����Ǥ�դ����դ�'/'�ե����ޥåȤ����֤���
function format_date6($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        return $nen . "/" . $tsuki;
    } else {
        return FALSE;
    }
}

// �����Ǥ�դ����դ�'ǯ��'�ե����ޥåȤ����֤���
function format_date6_kan($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        return $nen . "ǯ" . $tsuki . "��";
    } else {
        return FALSE;
    }
}
// �����Ǥ�դ����դ�'����'�ե����ޥåȤ����֤���
function format_date6_ki($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if ($date6 < 200000) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        $tsuki = $tsuki + 1 - 1;
        $ki    = substr($nen, 2, 2);
        if (0 < $tsuki && $tsuki < 4) {
            return "��" . $ki . "��" . $tsuki . "��";
        } else {
            $ki = $ki + 1;
            return "��" . $ki . "��" . $tsuki . "��";
        }
    } else {
        return FALSE;
    }
}
// �����Ǥ�դ����դ�'�����or����'�ե����ޥåȤ����֤���
function format_date6_term($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        if (3 < $tsuki && $tsuki < 10) {
            $term = '���';
        } else {
            $term = '����';
        }
    }
    if (6 == strlen($date6)) {
        $ki    = substr($nen, 2, 2);
        if (0 < $tsuki && $tsuki < 4) {
            return "��" . $ki . "��" . $term;
        } else {
            $ki = $ki + 1;
            return "��" . $ki . "��" . $term;
        }
    } else {
        return FALSE;
    }
}
// TNK�αĶ���(��ư��)�����դ�����(+-)��Offset�����롣
// $offset string���Ǥ����
// �㡧+0=�������Ķ����Ǥʤ����+¦(̤��)��offset�����롣-0����0=�������Ķ����Ǥʤ����-¦(���)��offset�����롣
//     -3=���Ķ���������offset�����롣+2 OR 2=���Ķ�����̤���offset�����롣
// php-5.1.4�ǡ�'+0'��'-0'��switchʸ�Ǥ϶���0�Ȳ�ᤵ��뤿�� $optionFlg���ɲä����б�2006/06/24
function workingDayOffset($offset, $optionFlg='-')
{
    $year  = date('Y');
    $mon   = date('m');
    $day   = date('d');
    $timestamp = mktime(0, 0, 0, $mon, $day, $year);
    if (substr($offset, 0, 1) == '+') $optionFlg = '+';     // ���θߴ����Τ����ɲ�
    if (substr($offset, 0, 1) == '-') $optionFlg = '-';     // ���θߴ����Τ����ɲ�
    if ($offset != 0) $optionFlg = ' ';                     // 0�ʳ����������������ץ�����̵���ˤ���
    switch ($optionFlg) {
    case '+':
        if (day_off($timestamp)) {
            $timestamp += 86400;            // ������ˤ���
            while (day_off($timestamp)) {   // �Ķ����ˤʤ�ޤǷ��֤�
                $timestamp += 86400;        // ������ˤ���
            }
            return date('Ymd',$timestamp);  // ľ���̤��αĶ������֤�
        } else {
            return date('Ymd');             // �������Ķ���
        }
        break;
    case '-':
        if (day_off($timestamp)) {
            $timestamp -= 86400;            // �������ˤ���
            while (day_off($timestamp)) {   // �Ķ����ˤʤ�ޤǷ��֤�
                $timestamp -= 86400;        // �������ˤ���
            }
            return date('Ymd',$timestamp);  // ľ��β��αĶ������֤�
        } else {
            return date('Ymd');             // �������Ķ���
        }
        break;
    default:
        if ($offset <= 0) {              // ���ؤ�offset
            while ($offset < 0) {
                $timestamp -= 86400;    // �������ˤ���
                if (day_off($timestamp)) {
                    continue;           // �٤ߤʤ鷫���֤�
                } else {
                    $offset++;          // �Ķ����ʤ�offset1������Ƚ�λ
                }
            }
        } else {
            while ($offset > 0) {       // ̤��ؤ�offset
                $timestamp += 86400;    // ������ˤ���
                if (day_off($timestamp)) {
                    continue;           // �٤ߤʤ鷫���֤�
                } else {
                    $offset--;          // �Ķ����ʤ�offset1������Ƚ�λ
                }
            }
        }
        return date('Ymd',$timestamp);
    }
}

// �����ǻ��ꤵ�줿��ʬ���դ���ˤ��餹�������������������
// ��������ε٤ߤ������switch case ʸ�Υ��ƥʥ󥹤�ɬ�ס�
function date_offset($offset)
{
    // $today = date('Ymd');
    $year  = date('Y');
    $mon   = date('m');
    $day   = date('d');
    $timestamp = mktime(0, 0, 0, $mon, $day, $year);
    while ($offset > 0) {
        $timestamp -= 86400;    // �������ˤ���
        if (day_off($timestamp)) {
            continue;       // �٤ߤʤ鷫���֤�
        } else {
            $offset--;      // �Ķ����ʤ饫����ȥ�����
        }
    }
    return date('Ymd',$timestamp);
}

/*** day_off()�ε��� ***/
function day_off_old($timestamp)
{
    if (date('w',$timestamp) == 0) return TRUE; // ������
    if (date('w',$timestamp) == 6) return TRUE; // ������
    switch ( date('Ymd',$timestamp) ) {
        /*** �裱�� ***/
        case '20000814'; return TRUE; break;    // �ƴ��ٲ�
        case '20000815'; return TRUE; break;    // �ƴ��ٲ�
        case '20000816'; return TRUE; break;    // �ƴ��ٲ�
        case '20000817'; return TRUE; break;    // �ƴ��ٲ�
        case '20000818'; return TRUE; break;    // �ƴ��ٲ�
        case '20010102'; return TRUE; break;    // ǯ�ϵٲ�
        case '20010103'; return TRUE; break;    // ǯ�ϵٲ�
        case '20010104'; return TRUE; break;    // ǯ�ϵٲ�
        case '20010105'; return TRUE; break;    // ǯ�ϵٲ�
        /*** �裲�� ***/
        case '20010813'; return TRUE; break;    // �ƴ��ٲ�
        case '20010814'; return TRUE; break;    // �ƴ��ٲ�
        case '20010815'; return TRUE; break;    // �ƴ��ٲ�
        case '20010816'; return TRUE; break;    // �ƴ��ٲ�
        case '20010817'; return TRUE; break;    // �ƴ��ٲ�
        case '20011231'; return TRUE; break;    // ǯ���ٲ�
        case '20020102'; return TRUE; break;    // ǯ�ϵٲ�
        case '20020103'; return TRUE; break;    // ǯ�ϵٲ�
        case '20020104'; return TRUE; break;    // ǯ�ϵٲ�
        case '20020321'; return TRUE; break;    // ��ʬ����
        /*** �裳�� ***/
        case '20020429'; return TRUE; break;    // �ߤɤ����
        case '20020503'; return TRUE; break;    // ��ˡ��ǰ��
        case '20020504'; return TRUE; break;    // ��̱�ε���
        case '20020506'; return TRUE; break;    // ���ص���
        case '20020720'; return TRUE; break;    // ������
        case '20020812'; return TRUE; break;    // �ƴ��ٲ�
        case '20020813'; return TRUE; break;    // �ƴ��ٲ�
        case '20020814'; return TRUE; break;    // �ƴ��ٲ�
        case '20020815'; return TRUE; break;    // �ƴ��ٲ�
        case '20020816'; return TRUE; break;    // �ƴ��ٲ�
        case '20020915'; return TRUE; break;    // ��Ϸ����
        case '20020916'; return TRUE; break;    // ���ص���
        case '20020923'; return TRUE; break;    // ��ʬ����
        case '20021014'; return TRUE; break;    // �ΰ����
        case '20021103'; return TRUE; break;    // ʸ������
        case '20021104'; return TRUE; break;    // ���ص���
        case '20021123'; return TRUE; break;    // ��ϫ���դ���
        case '20021223'; return TRUE; break;    // ŷ��������
        case '20021230'; return TRUE; break;    // ǯ���ٲ�
        case '20021231'; return TRUE; break;    // ǯ���ٲ�
        case '20030101'; return TRUE; break;    // ǯ�ϵٲ�
        case '20030102'; return TRUE; break;    // ǯ�ϵٲ�
        case '20030103'; return TRUE; break;    // ǯ�ϵٲ�
        case '20030113'; return TRUE; break;    // ���ͤ���
        case '20030211'; return TRUE; break;    // ����ǰ��
        case '20030321'; return TRUE; break;    // ��ʬ����
        /*** �裴�� ***/
        case '20030429'; return TRUE; break;    // �ߤɤ���
        case '20030503'; return TRUE; break;    // ��ˡ��ǰ��
        case '20030505'; return TRUE; break;    // �Ҷ�����
        case '20030721'; return TRUE; break;    // ������
        case '20030811'; return TRUE; break;    // �ƴ��ٲ�
        case '20030812'; return TRUE; break;    // �ƴ��ٲ�
        case '20030813'; return TRUE; break;    // �ƴ��ٲ�
        case '20030814'; return TRUE; break;    // �ƴ��ٲ�
        case '20030815'; return TRUE; break;    // �ƴ��ٲ�
        case '20030915'; return TRUE; break;    // ��Ϸ����
        case '20030923'; return TRUE; break;    // ��ʬ����
        case '20031013'; return TRUE; break;    // �ΰ����
        case '20031103'; return TRUE; break;    // ʸ������
        case '20031123'; return TRUE; break;    // ��ϫ���դ���
        case '20031124'; return TRUE; break;    // ���ص���
        case '20031223'; return TRUE; break;    // ŷ��������
        case '20031226'; return TRUE; break;    // ǯ���ٲ�(�ݽ�����)
        case '20031229'; return TRUE; break;    // ǯ���ٲ�
        case '20031230'; return TRUE; break;    // ǯ���ٲ�
        case '20031231'; return TRUE; break;    // ǯ���ٲ�
        case '20040101'; return TRUE; break;    // ǯ�ϵٲ�
        case '20040102'; return TRUE; break;    // ǯ�ϵٲ�
        case '20040112'; return TRUE; break;    // ���ͤ���
        case '20040211'; return TRUE; break;    // ����ǰ��
        case '20040320'; return TRUE; break;    // ��ʬ����
        /*** �裵�� ***/
        case '20040429'; return TRUE; break;    // �ߤɤ���
        case '20040503'; return TRUE; break;    // ��ˡ��ǰ��
        case '20040504'; return TRUE; break;    // ��̱�ε���
        case '20040505'; return TRUE; break;    // �Ҷ�����
        case '20040719'; return TRUE; break;    // ������
        case '20040809'; return TRUE; break;    // �ƴ��ٲ�
        case '20040810'; return TRUE; break;    // �ƴ��ٲ�
        case '20040811'; return TRUE; break;    // �ƴ��ٲ�
        case '20040812'; return TRUE; break;    // �ƴ��ٲ�
        case '20040813'; return TRUE; break;    // �ƴ��ٲ�
        case '20040816'; return TRUE; break;    // �ƴ��ٲ�
        case '20040920'; return TRUE; break;    // ��Ϸ����
        case '20040923'; return TRUE; break;    // ��ʬ����
        case '20041011'; return TRUE; break;    // �ΰ����
        case '20041103'; return TRUE; break;    // ʸ������
        case '20041123'; return TRUE; break;    // ��ϫ���դ���
        case '20041223'; return TRUE; break;    // ŷ��������
        case '20041229'; return TRUE; break;    // ǯ���ٲ�(�ݽ�����)
        case '20041230'; return TRUE; break;    // ǯ���ٲ�
        case '20041231'; return TRUE; break;    // ǯ���ٲ�
        case '20050103'; return TRUE; break;    // ǯ�ϵٲ�
        case '20050104'; return TRUE; break;    // ǯ�ϵٲ�
        case '20050105'; return TRUE; break;    // ǯ�ϵٲ�
        case '20050110'; return TRUE; break;    // ���ͤ���
        case '20050211'; return TRUE; break;    // ����ǰ��
        case '20050321'; return TRUE; break;    // ��ʬ����
        /*** �裶�� ***/
        case '20050429'; return TRUE; break;    // �ߤɤ���
        case '20050502'; return TRUE; break;    // NK�����롼�׵���
        case '20050503'; return TRUE; break;    // ��ˡ��ǰ��
        case '20050504'; return TRUE; break;    // ��̱�ε���
        case '20050505'; return TRUE; break;    // �Ҷ�����
        case '20050718'; return TRUE; break;    // ������
        case '20050810'; return TRUE; break;    // �ƴ��ٲ�
        case '20050811'; return TRUE; break;    // �ƴ��ٲ�
        case '20050812'; return TRUE; break;    // �ƴ��ٲ�
        case '20050815'; return TRUE; break;    // �ƴ��ٲ�
        case '20050816'; return TRUE; break;    // �ƴ��ٲ�
        case '20050919'; return TRUE; break;    // ��Ϸ����
        case '20050923'; return TRUE; break;    // ��ʬ����
        case '20051010'; return TRUE; break;    // �ΰ����
        case '20051103'; return TRUE; break;    // ʸ������
        case '20051123'; return TRUE; break;    // ��ϫ���դ���
        case '20051223'; return TRUE; break;    // ŷ��������
        case '20051229'; return TRUE; break;    // ǯ���ٲ�(�ݽ�����)
        case '20051230'; return TRUE; break;    // ǯ���ٲ�
        case '20060102'; return TRUE; break;    // ǯ�ϵٲ�
        case '20060103'; return TRUE; break;    // ǯ�ϵٲ�
        case '20060104'; return TRUE; break;    // ǯ�ϵٲ�
        case '20060109'; return TRUE; break;    // ���ͤ���
        case '20060211'; return TRUE; break;    // ����ǰ��
        case '20060321'; return TRUE; break;    // ��ʬ����
        /*** �裷�� ***/
        case '20060429'; return TRUE; break;    // �ߤɤ���
        case '20060503'; return TRUE; break;    // ��ˡ��ǰ��
        case '20060504'; return TRUE; break;    // ��̱�ε���
        case '20060505'; return TRUE; break;    // �Ҷ�����
        case '20060717'; return TRUE; break;    // ������
        case '20060814'; return TRUE; break;    // �ƴ��ٲ�
        case '20060815'; return TRUE; break;    // �ƴ��ٲ�
        case '20060816'; return TRUE; break;    // �ƴ��ٲ�
        case '20060817'; return TRUE; break;    // �ƴ��ٲ�
        case '20060818'; return TRUE; break;    // �ƴ��ٲ�
        case '20060918'; return TRUE; break;    // ��Ϸ����
        case '20060923'; return TRUE; break;    // ��ʬ����
        case '20061009'; return TRUE; break;    // �ΰ����
        case '20061103'; return TRUE; break;    // ʸ������
        case '20061123'; return TRUE; break;    // ��ϫ���դ���
        case '20061223'; return TRUE; break;    // ŷ��������
        case '20061229'; return TRUE; break;    // ǯ���ٲ�
        case '20070102'; return TRUE; break;    // ǯ�ϵٲ�
        case '20070103'; return TRUE; break;    // ǯ�ϵٲ�
        case '20070108'; return TRUE; break;    // ���ͤ���
        case '20070211'; return TRUE; break;    // ����ǰ��
        case '20070212'; return TRUE; break;    // ���ص���
        case '20070321'; return TRUE; break;    // ��ʬ����
        default; return FALSE;
    }
}

/*** day_off()�ο��� ***/
/*** company_calendar �ơ��֥����� ***/
function day_off($timestamp)
{
    require_once ('/home/www/html/tnk-web/function.php');
    $date = date('Y-m-d',$timestamp);
    $query = "
        SELECT bd_flg FROM company_calendar WHERE tdate='{$date}'
    ";
    if (getUniResult($query, $check) <= 0) {    // ��ҥ��������ǥ����å�
        return false;           // �ǡ�����̵�����϶���Ū�˱Ķ����ˤ���
    } else {
        if ($check == 't') return false; else return true;  // �����ͤ��դʤΤ����
    }
}

/*** day_off()����Ω�饤���� ***/
/*** assembly_calendar �ơ��֥����� ***/
function day_off_line($timestamp, $targetLine)
{
    require_once ('/home/www/html/tnk-web/function.php');
    $date = date('Y-m-d',$timestamp);
    $query = "
        SELECT bd_flg FROM assembly_calendar WHERE line='{$targetLine}' AND tdate='{$date}'
    ";
    if (getUniResult($query, $check) <= 0) {    // ����饤��ǥ����å�
        $query = "
            SELECT bd_flg FROM assembly_calendar WHERE line='0000' AND tdate='{$date}'
        ";
        if (getUniResult($query, $check) <= 0) {    // ���̥饤��ǥ����å�
            $query = "
                SELECT bd_flg FROM company_calendar WHERE tdate='{$date}'
            ";
            if (getUniResult($query, $check) <= 0) {    // ��ҥ��������ǥ����å�
                return true;    // �ǽ�Ū�˥ǡ�����̵�����϶���Ū�˵����ˤ���
            } else {
                if ($check == 't') return false; else return true;  // �����ͤ��դʤΤ����
            }
        } else {
            if ($check == 't') return false; else return true;
        }
    } else {
        if ($check == 't') return false; else return true;
    }
}

// �ͼθ����δؿ�round()�Σ��ʿ��ȣ����ʿ��κ��ۤ��������뤿���
// �ѿ�����Ͽ��
// ����ϻ���Ū���н�Ǥ��������ͤ�Ȥä����˳�Ψ���㤤�Ǥ���
// ���꤬�Ф��礬����ޤ����㡧1.49999999 ���ξ��� 2.0 �ˤʤ롣
// �ޤ�������������꤬�ޥ��ʥ��ξ����θ����ɬ�פ����롣
// ��
// if($var < 0) //�ͼθ����λ�����������
//  $var = var - $corrc_var; <---- �ޥ��ʥ��λ��������ͤ�ޥ��ʥ���
// else
//  $var = $var + $corrc_var; <--- �̾�λ� �����ͥץ饹��
// return round($var); <------------ �����ͤ��̣�����ƻͼθ����򤹤롣

function corrc_round($var)
{
    $corrc_var = 0.00000001;
    if($var < 0)
        $var = $var - $corrc_var;
    else
        $var = $var + $corrc_var;
    return round($var);
}

/********* �����˲ä�����б��� *********/
    // �������ʲ��� default �ͤ� 0
function Uround($var, $num = 0)
{
    $corrc_var = 0.00000001;
    if($var < 0)
        $var = $var - $corrc_var;
    else
        $var = $var + $corrc_var;
    return round($var,$num);
}

/********* ����ǯ��κǽ������֤� **********/
function last_day($year = 0, $month = 0)
{
    if ($year <= 0 || $year >= 2038) {
        $year = date('Y');      // ���꤬�ʤ�����ǯ�ν����
    }
    if ($month <= 0 || $month >= 13) {
        $month = date('m');     // ���꤬�ʤ����η�ν����
    }
    $targetYear = $year;
    $targetMonth = $month;
    if ($month <= 11) {
        $month += 1;
    } else {
        $month = 1;
        $year += 1;
    }
    ///// ����Σ����˥��å�
    $day = date('d', mktime(0, 0, 0, $month, 1, $year) - 1);    // 1�����ˤ��������ˤ���
    ///// ����ǯ��κǽ����Υ����ॹ�����
    $timestamp = mktime(0, 0, 0, $targetMonth, $day, $targetYear);
    ///// �ǽ������Ķ����������å�
    while (day_off($timestamp)) {
        $timestamp -= 86400;    // �������ˤ���
    }
    return date('d',$timestamp);
}

/********* AS/400�Ȥ�FTP DOWNLOAD ��ȥ饤������� ���ե�������Ѥ���������� **********/
function ftpGetCheckAndExecute($stream, $local_file, $as400_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}

/********* AS/400�Ȥ�FTP UPLOAD ��ȥ饤������� ���ե�������Ѥ���������� **********/
function ftpPutCheckAndExecute($stream, $as400_file, $local_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}
?>
