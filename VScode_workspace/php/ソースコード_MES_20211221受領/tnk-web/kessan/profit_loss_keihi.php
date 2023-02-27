<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � �����������ɽ                                         //
// Copyright(C) 2003-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/01/17 Created   profit_loss_keihi.php                               //
// 2003/01/24 ɽ���ѥǡ����� view_data[][] ������ ñ�̡������Ѥˤ���      //
// 2003/01/27 �ǡ�����ƥ����ȥե�����(FTP)����ǡ����١������ѹ�           //
// 2003/01/28 �ǡ����١������߷פ��ѹ������Υե�����ɤ��ߤ�������׻�      //
// 2003/02/21 Font �� monospace (���ֳ�font) ���ѹ�                         //
// 2003/02/23 date("Y/m/d H:m:s") �� H:i:s �Υߥ�����                       //
// 2003/03/06 title_font today_font ������ �����ʲ��η��������ɲ�         //
// 2003/03/11 Location: http �� Location $url_referer ���ѹ�                //
//            ��å���������Ϥ��뤿�� site_index site_id �򥳥��Ȥˤ�    //
//                                          parent.menu_site.��ͭ�����ѹ�   //
// 2003/05/01 ����Ĺ����λؼ���ǧ�ڤ�Account_group�����̾���ѹ�           //
// 2004/05/06 ����ɸ����Ǥ��б��Τ���������β����ɲ�(7520)D36 $r=35       //
//            kin1=��¤���� kin2=�δ��� �ʤΤ� kin3��kin9��ɬ�פʤ��ΤǺ�� //
// 2004/05/11 ��¦�Υ����ȥ�˥塼�Υ��󡦥��� �ܥ�����ɲ�                 //
// 2004/06/04 $rec_keihi = 27��28���ѹ� (����ɸ����Ǥλ������ɲäˤ��)    //
// 2005/10/27 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2009/10/15 �ƾ��ס���פ��������ѹ�                                 ��ë //
// 2010/10/09 �߷ץǡ����μ�������˴����Τ��ͤˤʤäƤ����Τ򡢴��餫��    //
//            �Ȳ񤷤���ޤǤ��߷פ��������褦���ѹ�                 ��ë //
// 2012/01/26 ����ǡ�������Ͽ���ɲáʣ�������ѡ�                     ��ë //
// 2012/02/28 2012ǯ1�� ��̳������ Ĵ�� ��˥���¤���� +1,156,130��    ��ë //
//             �� ʿ�в����ɸ��� 2��˵�Ĵ����Ԥ�����                      //
// 2012/03/05 2012ǯ1�� ��̳������ Ĵ�� ��˥���¤���� +1,156,130�� �� ��ë //
// 2013/11/07 2013ǯ10�� ���ɶ�̳������ Ĵ�� +1,245,035��              ��ë //
//             �� �����ɸ��� 11��˵�Ĵ����Ԥ�����                         //
// 2013/11/07 2013ǯ11�� ���ɶ�̳������ Ĵ�� -1,245,035�� �ᤷ����     ��ë //
// 2015/02/20 ���졼���б���λ������β����ɲ�(7550)D37 $r=36               //
//            kin1=��¤���� kin2=�δ��� �ʤΤ� kin3��kin9��ɬ�פʤ��ΤǺ�� //
//            $rec_keihi = 28��29���ѹ� (���졼���б����ɲäˤ��)          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

////////////// ����������
// $menu->set_site(10, 7);                     // site_index=10(»�ץ�˥塼) site_id=7(�»��)
//////////// ɽ�������
$menu->set_caption('�������칩��(��)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݲ�̾',   PL . 'address.php');

$url_referer     = $_SESSION['pl_referer'];     // �ƽФ�Ȥ� URL �����

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$tuki} ���١��� �� �� �� �� �� ɽ");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о�������
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// ����ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // ����ǯ��

///// ɽ��ñ�̤��������
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;        // ����� ɽ��ñ�� ���
    $_SESSION['keihi_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;          // ����� �������ʲ����
    $_SESSION['keihi_keta'] = $keta;
}
//////////// �ͷ��񡦷���Υ쥳���ɿ� �ե�����ɿ�
$rec_jin   =  8;    // �ͷ���λ��Ѳ��ܿ�
$rec_keihi = 29;    // ����λ��Ѳ��ܿ�  ���졼���б��� �ɲäˤ�� 28��29��
$f_mei     = 13;    // ����(ɽ)�Υե�����ɿ�

////// D���ͤ�ʪ���쥳���ɿ�
define('D_REC', 38);    // 2015/02/20 ���졼���б�����ɲäˤ��37��38

////// �ǡ����١������ǡ���������
$res = array();     /*** ����Υǡ������� ***/      // kin1=��¤����  kin2=�δ���  D���ͤξ��
$query = sprintf("select kin1, kin2 from pl_bs_summary where pl_bs_ym=%d and t_id='D' order by t_id, t_row ASC", $yyyymm);
if (($rows=getResult($query,$res)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    for ($i=0; $i<$rows; $i++) {
        // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
        if ($yyyymm == 201201) {
            if ($i == 17) {
                $res[$i][0] += 1156130;
            }
        }
        if ($yyyymm == 201202) {
            if ($i == 17) {
                $res[$i][0] -= 1156130;
            }
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ���ɶ�̳������ʲ����ɸ�����Ĵ��
        if ($yyyymm == 201310) {
            if ($i == 17) {
                $res[$i][0] += 1245035;
            }
        }
        if ($yyyymm == 201311) {
            if ($i == 17) {
                $res[$i][0] -= 1245035;
            }
        }
        if ($res[$i][0] != 0)
            $res[$i][0] = ($res[$i][0] / $tani);
        if ($res[$i][1] != 0)
            $res[$i][1] = ($res[$i][1] / $tani);
    }
    if ($rows == (D_REC-1)) {    // ���졼���б����D37��̵�����(�쥳���ɿ�=37)��
        $res[D_REC-1][0] = $res[$rows-1][0];  // D99 ��38�쥳�����ܤ˥��ԡ�
        $res[D_REC-1][1] = $res[$rows-1][1];  // D99 ��38�쥳�����ܤ˥��ԡ�
        $res[$rows-1][0] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
        $res[$rows-1][1] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
    }
    $res_p1 = array();     /*** ����Υǡ������� ***/
    $query_p1 = sprintf("select kin1, kin2 from pl_bs_summary where pl_bs_ym=%d and t_id='D' order by t_id, t_row ASC", $p1_ym);
    if (($rows_p1=getResult($query_p1,$res_p1)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        for ($i=0; $i<$rows_p1; $i++) {
            // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
            if ($p1_ym == 201201) {
                if ($i == 17) {
                    $res_p1[$i][0] += 1156130;
                }
            }
            if ($p1_ym == 201202) {
                if ($i == 17) {
                    $res_p1[$i][0] -= 1156130;
                }
            }
            // 2013/11/07 �ɲ� 2013ǯ10���� ���ɶ�̳������ʲ����ɸ�����Ĵ��
            if ($p1_ym == 201310) {
                if ($i == 17) {
                    $res_p[$i][0] += 1245035;
                }
            }
            if ($p1_ym == 201311) {
                if ($i == 17) {
                    $res_p[$i][0] -= 1245035;
                }
            }
            if ($res_p1[$i][0] != 0)
                $res_p1[$i][0] = ($res_p1[$i][0] / $tani);
            if ($res_p1[$i][1] != 0)
                $res_p1[$i][1] = ($res_p1[$i][1] / $tani);
        }
        if ($rows_p1 == (D_REC-1)) {    // ���졼���б����D37��̵�����(�쥳���ɿ�=37)��
            $res_p1[D_REC-1][0] = $res_p1[$rows_p1-1][0];  // D99 ��38�쥳�����ܤ˥��ԡ�
            $res_p1[D_REC-1][1] = $res_p1[$rows_p1-1][1];  // D99 ��38�쥳�����ܤ˥��ԡ�
            $res_p1[$rows_p1-1][0] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
            $res_p1[$rows_p1-1][1] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
        }
    } else {        // �ǡ�����̵������ ���ǽ���� ���;壳���쥳���ɤ��뤳�Ȥ����(D1--D37+D99)
        for ($i=0; $i<D_REC; $i++) {
                $res_p1[$i][0] = 0;
                $res_p1[$i][1] = 0;
        }
    }
    $res_p2 = array();     /*** ������Υǡ������� ***/
    $query_p2 = sprintf("select kin1, kin2 from pl_bs_summary where pl_bs_ym=%d and t_id='D' order by t_id, t_row ASC", $p2_ym);
    if (($rows_p2=getResult($query_p2,$res_p2)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        for ($i=0; $i<$rows_p2; $i++) {
            // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
            if ($p2_ym == 201201) {
                if ($i == 17) {
                    $res_p2[$i][0] += 1156130;
                }
            }
            if ($p2_ym == 201202) {
                if ($i == 17) {
                    $res_p2[$i][0] -= 1156130;
                }
            }
            // 2013/11/07 �ɲ� 2013ǯ10���� ���ɶ�̳������ʲ����ɸ�����Ĵ��
            if ($p2_ym == 201310) {
                if ($i == 17) {
                    $res_p2[$i][0] += 1245035;
                }
            }
            if ($p2_ym == 201311) {
                if ($i == 17) {
                    $res_p2[$i][0] -= 1245035;
                }
            }
            if ($res_p2[$i][0] != 0)
                $res_p2[$i][0] = ($res_p2[$i][0] / $tani);
            if ($res_p2[$i][1] != 0)
                $res_p2[$i][1] = ($res_p2[$i][1] / $tani);
        }
        if ($rows_p2 == (D_REC-1)) {    // ���졼���б����D37��̵�����(�쥳���ɿ�=37)��
            $res_p2[D_REC-1][0] = $res_p2[$rows_p2-1][0];  // D99 ��38�쥳�����ܤ˥��ԡ�
            $res_p2[D_REC-1][1] = $res_p2[$rows_p2-1][1];  // D99 ��38�쥳�����ܤ˥��ԡ�
            $res_p2[$rows_p2-1][0] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
            $res_p2[$rows_p2-1][1] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
        }
    } else {        // �ǡ�����̵������ ���ǽ���� ���;壳���쥳���ɤ��뤳�Ȥ����(D1--D37+D99)
        for ($i=0; $i<D_REC; $i++) {
                $res_p2[$i][0] = 0;
                $res_p2[$i][1] = 0;
        }
    }
    $res_rui = array();     /*** �߷פΥǡ������� ***/
    $query_rui = sprintf("select sum(kin1),sum(kin2) from pl_bs_summary where pl_bs_ym>=%d and pl_bs_ym<=%d and t_id='D' group by t_id, t_row order by t_id, t_row ASC", $str_ym, $yyyymm);
    //$query_rui = sprintf("select sum(kin1),sum(kin2) from pl_bs_summary where ki=%d and t_id='D' group by t_id, t_row order by t_id, t_row ASC", $ki);
    if (($rows_rui=getResult($query_rui,$res_rui)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        for ($i=0; $i<$rows_rui; $i++) {
            // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
            if (($yyyymm >= 201201) && ($yyyymm <= 201203)) {
                if ($i == 17) {
                    $res_rui[$i][0] += 1156130;
                }
            }
            if (($yyyymm >= 201202) && ($yyyymm <= 201203)) {
                if ($i == 17) {
                    $res_rui[$i][0] -= 1156130;
                }
            }
            // 2013/11/07 �ɲ� 2013ǯ10���� ���ɶ�̳������ʲ����ɸ�����Ĵ��
            if (($yyyymm >= 201310) && ($yyyymm <= 201403)) {
                if ($i == 17) {
                    $res_rui[$i][0] += 1245035;
                }
            }
            if (($yyyymm >= 201311) && ($yyyymm <= 201403)) {
                if ($i == 17) {
                    $res_rui[$i][0] -= 1245035;
                }
            }
            if ($res_rui[$i][0] != 0)
                $res_rui[$i][0] = ($res_rui[$i][0] / $tani);
            if ($res_rui[$i][1] != 0)
                $res_rui[$i][1] = ($res_rui[$i][1] / $tani);
        }
        if ($rows_rui == (D_REC-1)) {    // ���졼���б����D37��̵�����(�쥳���ɿ�=37)��
            $res_rui[D_REC-1][0] = $res_rui[$rows_rui-1][0];  // D99 ��38�쥳�����ܤ˥��ԡ�
            $res_rui[D_REC-1][1] = $res_rui[$rows_rui-1][1];  // D99 ��38�쥳�����ܤ˥��ԡ�
            $res_rui[$rows_rui-1][0] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
            $res_rui[$rows_rui-1][1] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
        }
    } else {        // �ǡ�����̵������ ���ǽ���� ���;壳���쥳���ɤ��뤳�Ȥ����(D1--D37+D99)
        for ($i=0; $i<D_REC; $i++) {
                $res_rui[$i][0] = 0;
                $res_rui[$i][1] = 0;
        }
    }
    $res_avg = array();     /*** ���� �߷פ���ʿ�ѤΥǡ������� ***/
    $ki_p = $ki - 1;
    if ($ki_p >= 2) {       ///// �����������ʾ�ξ��ϣ����ǳ��
        $query_avg = sprintf("select round((sum(kin1)+sum(kin2))/12) from pl_bs_summary where ki=%d and t_id='D' group by t_id, t_row order by t_id, t_row ASC", $ki_p);
        if (($rows_avg=getResult($query_avg,$res_avg)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            for ($i=0; $i<$rows_avg; $i++) {
                if ($res_avg[$i][0] != 0)
                    $res_avg[$i][0] = ($res_avg[$i][0] / $tani);
            }
            if ($rows_avg == (D_REC-1)) {    // ���졼���б����D37��̵�����(�쥳���ɿ�=37)��
                $res_avg[D_REC-1][0] = $res_avg[$rows_avg-1][0];  // D99 ��38�쥳�����ܤ˥��ԡ�
                $res_avg[$rows_avg-1][0] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
            }
        } else {        // �ǡ�����̵������ ���ǽ���� ���;壳���쥳���ɤ��뤳�Ȥ����(D1--D37+D99)
            for ($i=0; $i<D_REC; $i++) {
                    $res_avg[$i][0] = 0;
            }
        }
    } elseif ($ki_p == 1) { ///// �����������ξ��ˤϣ��ǳ��
        $query_avg = sprintf("select round((sum(kin1)+sum(kin2))/6) from pl_bs_summary where ki=%d and t_id='D' group by t_id, t_row order by t_id, t_row ASC", $ki_p);
        if (($rows_avg=getResult($query_avg,$res_avg)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            for ($i=0; $i<$rows_avg; $i++) {
                if ($res_avg[$i][0] != 0)
                    $res_avg[$i][0] = ($res_avg[$i][0] / $tani);
            }
            if ($rows_avg == (D_REC-1)) {    // ���졼���б����D37��̵�����(�쥳���ɿ�=37)��
                $res_avg[D_REC-1][0] = $res_avg[$rows_avg-1][0];  // D99 ��38�쥳�����ܤ˥��ԡ�
                $res_avg[$rows_avg-1][0] = 0;                    // �ɲäˤʤä�D37��0�ǽ����
            }
        } else {        // �ǡ�����̵������ ���ǽ���� ���;壳���쥳���ɤ��뤳�Ȥ����(D1--D37+D99)
            for ($i=0; $i<D_REC; $i++) {
                    $res_avg[$i][0] = 0;
            }
        }
    } else {        // �ǡ�����̵������ ���ǽ���� ���;壳���쥳���ɤ��뤳�Ȥ����(D1--D37+D99)
        for ($i=0; $i<D_REC; $i++) {
                $res_avg[$i][0] = 0;
        }
    }

    ///////// ɽ���ѥǡ��������� (���̤�ɽ�ǡ������᡼��)
    ///// �ͷ���ȷ����������
    $view_data = array();      // ɽ�����ѿ� ����ǽ����
    for ($r=0; $r<$rows; $r++) {
        for ($c=0; $c<14; $c++) {
            switch ($c) {
            case  0:        // ��¤���� ������
                $view_data[$r][$c] = number_format($res_p2[$r][0],$keta); break;
            case  4:        // �δ���   ������
                $view_data[$r][$c] = number_format($res_p2[$r][1],$keta); break;
            case  8:        // �硡��   ������
                $view_data[$r][$c] = number_format($res_p2[$r][0] + $res_p2[$r][1],$keta); break;
            case  1:        // ��¤���� ����
                $view_data[$r][$c] = number_format($res_p1[$r][0],$keta); break;
            case  5:        // �δ���   ����
                $view_data[$r][$c] = number_format($res_p1[$r][1],$keta); break;
            case  9:        // �硡��   ����
                $view_data[$r][$c] = number_format($res_p1[$r][0] + $res_p1[$r][1],$keta); break;
            case  2:        // ��¤���� ����
                $view_data[$r][$c] = number_format($res[$r][0],$keta); break;
            case  6:        // �δ���   ����
                $view_data[$r][$c] = number_format($res[$r][1],$keta); break;
            case 10:        // �硡��   ����
                $view_data[$r][$c] = number_format($res[$r][0] + $res[$r][1],$keta); break;
            case  3:        // ��¤���� �߷�
                $view_data[$r][$c] = number_format($res_rui[$r][0],$keta); break;
            case  7:        // �δ���   �߷�
                $view_data[$r][$c] = number_format($res_rui[$r][1],$keta); break;
            case 11:        // �硡��   �߷�
                $view_data[$r][$c] = number_format($res_rui[$r][0] + $res_rui[$r][1],$keta); break;
            case 12:        // ������ʿ��
                $view_data[$r][$c] = number_format($res_avg[$r][0],$keta); break;
            case 13:        // ��­����
                $view_data[$r][$c] = "-"; break;
            default:        // ����¾��̵����
                $view_data[$r][$c] = number_format(0,$keta); break;
            }
        }
    }
    ///// ���פη׻� �ͷ���
    $jin_sum = array();
    for ($c=0; $c < $f_mei; $c++) {
        $jin_sum[$c] = 0;       // �ʲ��� += ����Ѥ��뤿������
    }
    for ($i=0; $i < $rec_jin; $i++) {
        for ($c=0; $c < $f_mei; $c++) {         ///// ��­�����ȴ���� �����쥳���ɤˤʤ�
            switch ($c) {
            case  0:        // ��¤���� ������
                $jin_sum[$c] += $res_p2[$i][0]; break;
            case  4:        // �δ���   ������
                $jin_sum[$c] += $res_p2[$i][1]; break;
            case  8:        // �硡��   ������
                $jin_sum[$c] += ($res_p2[$i][0] + $res_p2[$i][1]); break;
            case  1:        // ��¤���� ����
                $jin_sum[$c] += $res_p1[$i][0]; break;
            case  5:        // �δ���   ����
                $jin_sum[$c] += $res_p1[$i][1]; break;
            case  9:        // �硡��   ����
                $jin_sum[$c] += ($res_p1[$i][0] + $res_p1[$i][1]); break;
            case  2:        // ��¤���� ����
                $jin_sum[$c] += $res[$i][0]; break;
            case  6:        // �δ���   ����
                $jin_sum[$c] += $res[$i][1]; break;
            case 10:        // �硡��   ����
                $jin_sum[$c] += ($res[$i][0] + $res[$i][1]); break;
            case  3:        // ��¤���� �߷�
                $jin_sum[$c] += $res_rui[$i][0]; break;
            case  7:        // �δ���   �߷�
                $jin_sum[$c] += $res_rui[$i][1]; break;
            case 11:        // �硡��   �߷�
                $jin_sum[$c] += ($res_rui[$i][0] + $res_rui[$i][1]); break;
            case 12:        // ������ʿ��
                $jin_sum[$c] += $res_avg[$i][0]; break;
            default:        // ����¾��̵����
                $jin_sum[$c] += 0; break;
            }
        }
    }
    ///// ���פη׻� ����
    $kei_sum = array();
    for ($c=0; $c < $f_mei; $c++) {
        $kei_sum[$c] = 0;       // �ʲ��� += ����Ѥ��뤿������
    }
    for ($i=0; $i < $rec_keihi; $i++){
        for ($c=0; $c < $f_mei; $c++) {         ///// ��­�����ȴ���� �����쥳���ɤˤʤ�
            switch ($c) {
            case  0:        // ��¤���� ������
                $kei_sum[$c] += $res_p2[$i+8][0]; break;
            case  4:        // �δ���   ������
                $kei_sum[$c] += $res_p2[$i+8][1]; break;
            case  8:        // �硡��   ������
                $kei_sum[$c] += ($res_p2[$i+8][0] + $res_p2[$i+8][1]); break;
            case  1:        // ��¤���� ����
                $kei_sum[$c] += $res_p1[$i+8][0]; break;
            case  5:        // �δ���   ����
                $kei_sum[$c] += $res_p1[$i+8][1]; break;
            case  9:        // �硡��   ����
                $kei_sum[$c] += ($res_p1[$i+8][0] + $res_p1[$i+8][1]); break;
            case  2:        // ��¤���� ����
                $kei_sum[$c] += $res[$i+8][0]; break;
            case  6:        // �δ���   ����
                $kei_sum[$c] += $res[$i+8][1]; break;
            case 10:        // �硡��   ����
                $kei_sum[$c] += ($res[$i+8][0] + $res[$i+8][1]); break;
            case  3:        // ��¤���� �߷�
                $kei_sum[$c] += $res_rui[$i+8][0]; break;
            case  7:        // �δ���   �߷�
                $kei_sum[$c] += $res_rui[$i+8][1]; break;
            case 11:        // �硡��   �߷�
                $kei_sum[$c] += ($res_rui[$i+8][0] + $res_rui[$i+8][1]); break;
            case 12:        // ������ʿ��
                $kei_sum[$c] += $res_avg[$i+8][0]; break;
            default:        // ����¾��̵����
                $kei_sum[$c] += 0; break;
            }
        }
    }
    ///// ��פη׻�   ///// ���ס���פ�ɽ���ѥǡ�������
    $all_sum = array();
    $view_jin_sum = array();
    $view_kei_sum = array();
    $view_all_sum = array();
    for ($c=0; $c < $f_mei; $c++) {         ///// ��­�����ȴ���� $f_mei=13�ե�����ɤˤʤ�
        $all_sum[$c]  = $jin_sum[$c] + $kei_sum[$c];             // ��פη׻�
        $view_jin_sum[$c] = number_format($jin_sum[$c],$keta);   // ɽ���� �ͷ����
        $view_kei_sum[$c] = number_format($kei_sum[$c],$keta);   // ɽ���� �����
        $view_all_sum[$c] = number_format($all_sum[$c],$keta);   // ɽ���� �硡��
    }
} else {
    $_SESSION["s_sysmsg"] = sprintf("�оݥǡ���������ޤ���<br>��%d��%d��",$ki,$tuki);
    header("Location: $url_referer");
    exit();
}


    //// ����ǡ�������Ͽ
if (isset($_POST['input_data'])) {
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    $item = array();
    $item[0]   = "�����";
    $item[1]   = "��������";
    $item[2]   = "��Ϳ����";
    $item[3]   = "������";
    $item[4]   = "ˡ��ʡ����";
    $item[5]   = "����ʡ����";
    $item[6]   = "��Ϳ�����ⷫ��";
    $item[7]   = "�࿦��������";
    $item[8]   = "�ͷ����";
    $item[9]   = "ι�������";
    $item[10]  = "������ĥ";
    $item[11]  = "�̿���";
    $item[12]  = "�����";
    $item[13]  = "���������";
    $item[14]  = "����������";
    $item[15]  = "�����";
    $item[16]  = "���²�¤��";
    $item[17]  = "�޽񶵰���";
    $item[18]  = "��̳������";
    $item[19]  = "������";
    $item[20]  = "���Ǹ���";
    $item[21]  = "�������";
    $item[22]  = "����";
    $item[23]  = "������";
    $item[24]  = "�ݾڽ�����";
    $item[25]  = "��̳�Ѿ�������";
    $item[26]  = "�����������";
    $item[27]  = "��ξ��";
    $item[28]  = "�ݸ���";
    $item[29]  = "��ƻ��Ǯ��";
    $item[30]  = "������";
    $item[31]  = "��ʧ�����";
    $item[32]  = "�������";
    $item[33]  = "���ն�";
    $item[34]  = "������";
    $item[35]  = "�¼���";
    $item[36]  = "����������";
    $item[37]  = "���졼���б���";
    $item[38]  = "�����";
    $item[39]  = "���";
    ///////// �ƥǡ������ݴ�
    ///// ɽ���ǡ������饫��ޤ�������
    ///// $number = str_replace(',','',$english_format_number);
    for ($i = 0; $i < 3; $i++) {
        if ($i == 0) {
            $c = 2;             // ��¤����
        } elseif ($i == 1) {
            $c = 6;             // �δ���
        } elseif ($i == 2) {
            $c = 10;            // ���
        }
        $input_data = array();
        $input_data[0]   = str_replace(',','',$view_data[0][$c]);   // �����
        $input_data[1]   = str_replace(',','',$view_data[1][$c]);   // ��������
        $input_data[2]   = str_replace(',','',$view_data[2][$c]);   // ��Ϳ����
        $input_data[3]   = str_replace(',','',$view_data[3][$c]);   // ������
        $input_data[4]   = str_replace(',','',$view_data[4][$c]);   // ˡ��ʡ��
        $input_data[5]   = str_replace(',','',$view_data[5][$c]);   // ����ʡ����
        $input_data[6]   = str_replace(',','',$view_data[6][$c]);   // ��Ϳ�����ⷫ��
        $input_data[7]   = str_replace(',','',$view_data[7][$c]);   // �࿦��������
        $input_data[8]   = str_replace(',','',$view_jin_sum[$c]);   // �ͷ����
        $input_data[9]   = str_replace(',','',$view_data[8][$c]);   // ι�������
        $input_data[10]  = str_replace(',','',$view_data[9][$c]);   // ������ĥ
        $input_data[11]  = str_replace(',','',$view_data[10][$c]);   // �̿���
        $input_data[12]  = str_replace(',','',$view_data[11][$c]);   // �����
        $input_data[13]  = str_replace(',','',$view_data[12][$c]);   // ���������
        $input_data[14]  = str_replace(',','',$view_data[13][$c]);   // ����������
        $input_data[15]  = str_replace(',','',$view_data[14][$c]);   // �����
        $input_data[16]  = str_replace(',','',$view_data[15][$c]);   // ���²�¤��
        $input_data[17]  = str_replace(',','',$view_data[16][$c]);   // �޽񶵰���
        $input_data[18]  = str_replace(',','',$view_data[17][$c]);   // ��̳������
        $input_data[19]  = str_replace(',','',$view_data[35][$c]);   // ������
        $input_data[20]  = str_replace(',','',$view_data[18][$c]);   // ���Ǹ���
        $input_data[21]  = str_replace(',','',$view_data[19][$c]);   // �������
        $input_data[22]  = str_replace(',','',$view_data[20][$c]);   // ����
        $input_data[23]  = str_replace(',','',$view_data[21][$c]);   // ������
        $input_data[24]  = str_replace(',','',$view_data[22][$c]);   // �ݾڽ�����
        $input_data[25]  = str_replace(',','',$view_data[23][$c]);   // ��̳�Ѿ�������
        $input_data[26]  = str_replace(',','',$view_data[24][$c]);   // �����������
        $input_data[27]  = str_replace(',','',$view_data[25][$c]);   // ��ξ��
        $input_data[28]  = str_replace(',','',$view_data[26][$c]);   // �ݸ���
        $input_data[29]  = str_replace(',','',$view_data[27][$c]);   // ��ƻ��Ǯ��
        $input_data[30]  = str_replace(',','',$view_data[28][$c]);   // ������
        $input_data[31]  = str_replace(',','',$view_data[29][$c]);   // ��ʧ�����
        $input_data[32]  = str_replace(',','',$view_data[30][$c]);   // �������
        $input_data[33]  = str_replace(',','',$view_data[31][$c]);   // ���ն�
        $input_data[34]  = str_replace(',','',$view_data[32][$c]);   // ������
        $input_data[35]  = str_replace(',','',$view_data[33][$c]);   // �¼���
        $input_data[36]  = str_replace(',','',$view_data[34][$c]);   // ����������
        $input_data[37]  = str_replace(',','',$view_data[36][$c]);   // ���졼���б���
        $input_data[38]  = str_replace(',','',$view_kei_sum[$c]);   // �����
        $input_data[39]  = str_replace(',','',$view_all_sum[$c]);   // ���
        if ($i == 0) {
            $head  = "��¤����";
        } elseif ($i == 1) {
            $head  = "�δ���";
        } elseif ($i == 2) {
            $head  = "���";
        }
        insert_date($item,$head,$yyyymm,$input_data);
    }
}
function insert_date($item,$head,$yyyymm,$input_data) 
{
    for ($i = 0; $i < 40; $i++) {
        //$item_in     = array();
        //$item_in[$i] = $item[$i];
        //$input_data[$i][$sec] = str_replace(',','',$input_data[$i][$sec]);
        $item_in[$i] = $head . $item[$i];
        $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into profit_loss_keihi_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �߼��о�ɽ�ǡ��� ���� ��Ͽ��λ</font>",$yyyymm);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_keihi_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �߼��о�ɽ�ǡ��� �ѹ� ��λ</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "����Υǡ�������Ͽ���ޤ�����";
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='13' bgcolor='#d6d3ce' align='right' class='pt10'>
                        ñ��
                        <select name='keihi_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>�����</option>\n";
                            else
                                echo "<option value='1000'>�����</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>������</option>\n";
                            else
                                echo "<option value='1'>������</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>ɴ����</option>\n";
                            else
                                echo "<option value='1000000'>ɴ����</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>������</option>\n";
                            else
                                echo "<option value='10000'>������</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>������</option>\n";
                            else
                                echo "<option value='100000'>������</option>\n";
                        ?>
                        </select>
                        ������
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>����</option>\n";
                            else
                                echo "<option value='0'>����</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>����</option>\n";
                            else
                                echo "<option value='3'>����</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>����</option>\n";
                            else
                                echo "<option value='6'>����</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>����</option>\n";
                            else
                                echo "<option value='1'>����</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>����</option>\n";
                            else
                                echo "<option value='2'>����</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>����</option>\n";
                            else
                                echo "<option value='4'>����</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>����</option>\n";
                            else
                                echo "<option value='5'>����</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='ñ���ѹ�'>
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                            if ($keta == 0 && $tani == 1) {
                        ?>
                            &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='����ǡ�����Ͽ' onClick='return data_input_click(this)'>
                        <?php
                            } else {
                        ?>
                            <input class='pt10b' type='submit' name='input_data' value='����ǡ�����Ͽ' onClick='return data_input_click(this)' disabled>
                        <?php
                            }
                        }
                        ?>
                    </td>
                </form>
            </tr>
        </table>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <TR>
                    <TD rowspan="2" align="center" nowrap class='pt10b' bgcolor='#ffffc6'>�������</TD>
                    <TD colspan="4" align="center" height="20" class='pt10b' bgcolor='#ffffc6'>��¤����</TD>
                    <TD colspan="4" align="center" height="20" class='pt10b' bgcolor='#ffffc6'>�� �� ��</TD>
                    <TD colspan="4" align="center" height="20" class='pt10b' bgcolor='#ffffc6'>�硡����</TD>
                    <TD rowspan="2" nowrap class='pt8' bgcolor='#ffffc6'>������ʿ��</TD>
                    <TD rowspan="2" nowrap class='pt10b'>��­����</TD>
                </TR>
                <TR>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p2_ym,0,4), substr($p2_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p1_ym,0,4), substr($p1_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffff'><?php printf("%s/%s", substr($yyyymm,0,4), substr($yyyymm,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffc6'>�ߡ�����</TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p2_ym,0,4), substr($p2_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p1_ym,0,4), substr($p1_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffff'><?php printf("%s/%s", substr($yyyymm,0,4), substr($yyyymm,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffc6'>�ߡ�����</TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p2_ym,0,4), substr($p2_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p1_ym,0,4), substr($p1_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffff'><?php printf("%s/%s", substr($yyyymm,0,4), substr($yyyymm,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffc6'>�ߡ�����</TD>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>�����</TD>
                    <?php
                        $r = 0;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)         // �߷�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)     // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��������</TD>
                    <?php
                        $r = 1;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��Ϳ����</TD>
                    <?php
                        $r = 2;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>������</TD>
                    <?php
                        $r = 3;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>ˡ��ʡ����</TD>
                    <?php
                        $r = 4;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>����ʡ����</TD>
                    <?php
                        $r = 5;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��Ϳ�����ⷫ��</TD>
                    <?php
                        $r = 6;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>�࿦��������</TD>
                    <?php
                        $r = 7;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>�ͷ����</TD>
                    <?php
                        for ($c=0;$c<14;$c++) {
                            if ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10b' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_jin_sum[$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <TD nowrap class='pt10'>ι�������</TD>
                    <?php
                        $r = 8;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>������ĥ</TD>
                    <?php
                    $r = 9;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�̡�������</TD>
                    <?php
                    $r = 10;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�񡡵ġ���</TD>
                    <?php
                    $r = 11;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���������</TD>
                    <?php
                    $r = 12;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 13;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ᡡ�͡���</TD>
                    <?php
                    $r = 14;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���²�¤��</TD>
                    <?php
                    $r = 15;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�޽񶵰���</TD>
                    <?php
                    $r = 16;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��̳������</TD>
                    <?php
                    $r = 17;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�����ȡ���</TD>
                    <?php
                    $r = 35;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���Ǹ���</TD>
                    <?php
                    $r = 18;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�������</TD>
                    <?php
                    $r = 19;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 20;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 21;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ݾڽ�����</TD>
                    <?php
                    $r = 22;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��̳�Ѿ�������</TD>
                    <?php
                    $r = 23;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�����������</TD>
                    <?php
                    $r = 24;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�֡�ξ����</TD>
                    <?php
                    $r = 25;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ݡ�������</TD>
                    <?php
                    $r = 26;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��ƻ��Ǯ��</TD>
                    <?php
                    $r = 27;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��������</TD>
                    <?php
                    $r = 28;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��ʧ�����</TD>
                    <?php
                    $r = 29;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�������</TD>
                    <?php
                    $r = 30;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���ա���</TD>
                    <?php
                    $r = 31;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ҡ��ߡ���</TD>
                    <?php
                    $r = 32;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�¡��ڡ���</TD>
                    <?php
                    $r = 33;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 34;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���졼���б���</TD>
                    <?php
                    $r = 36;     // �����쥳����
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>�����</TD>
                    <?php
                        for ($c=0;$c<14;$c++) {
                            if ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10b' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>�硡��</TD>
                    <?php
                        for ($c=0;$c<14;$c++) {
                            if ($c == 13)                           // ��­����
                                printf("<td nowrap class='pt10b' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
