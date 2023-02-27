<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ�  new version   sales_view.php                             //
// Copyright (C) 2001-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   sales_view.php                                      //
// 2002/08/07 ���å����������ɲ�                                          //
// 2002/09/26 selectʸ�� left outer join on u.assyno=m.mipn ���ѹ�          //
// 2003/01/10 substr($res[$r][$n],0,38)��mb_substr($res[$r][$n],0,12)       //
//                   �ޥ���Х��Ȥ��б�������X���β�����˼����            //
// 2003/06/16 ��׶�ۡ���������̤� SQL �Ǽ��� ���٤ϣ��ڡ���ʬ�Τ�        //
//              ������ Logic ���������ѹ�   �������˥Х������ɲ�          //
// 2003/09/05 ����ñ������Ͽ�����ξ����θ�������å����ѹ�              //
//            ����������Ͽ�����ξ���Ʊ��(�����б��Ѥ�)                  //
//            error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲ�       //
// 2003/10/31 ���� �����ֹ� ���� �ɲ�  �������˥��ץ�������ɲ�             //
// 2003/11/26 �ǥ�����ȥ��å���쿷 view_uriage.php �� sales_view.php    //
// 2003/11/28 �������������ʤ��ɲ� left outer��assymbly���Ф���join��     //
//            on���� plan_no�����ǹԤ� index�� plan_no �������ѹ�         //
// 2003/12/11 ������ξ�������̾ width='150' �� width='170' ���ѹ�        //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ���������      //
// 2003/12/17 ��������������Υ����å����å����ɲ� (�������������)     //
// 2003/12/19 �������Ȳ�Υ�󥯥��å������ ���ߤϣ�����Τ�           //
//            $_SESSION['offset']��$_SESSION['sales_offset']��  �����Ǥ����//
// 2003/12/22 ����̾�����ѥ��ʱѿ�����Ⱦ�ѥ��ʱѿ�����testŪ�˥���С���    //
//            ������ʳ����������Ψ �Ȳ�Υ�󥯥��å������           //
// 2003/12/23 ����ñ����Ψ �ڤ� �������Ψ �����ξ��� '-'���Ѵ�����ɽ�� //
// 2003/12/24 ob_gzhandler��� ���Ѥ���ȣ��ǣ�������λ���GET�����ʤ�����//
//            order by �׾��� �� , assyno���ɲ� ���ǤιԿ����ѹ����Ƥ� OK   //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/11/01 ����ʳ�����������ײ��ֹ����Ͽ���ʤ���кǸ����Ͽ��Ȥ�  //
// 2004/11/09 ����������롼�ס����ץ����Ρ�����ɸ�ࡦ��˥���������ʬ����//
// 2005/01/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//              set_focus()�� document.focus()��Ȥ� F2/F12������ͭ���ˤ��� //
// 2005/02/01 ��������mate.sum_price��0��ʪ������ײ��ֹ�=C1261631�����б�//
//             mate.sum_price <= 0    ����Ū�ˤ����ʤϻٵ��ʤ�������Ω��Τ�//
//                     ��                                                   //
//            (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 //
// 2005/05/27 PAGE > 25 �ˤ�� style='overflow:hidden;' ��������ɲ�        //
// 2005/06/03 regdate DESC �� assy_no DESC, regdate DESC ��index�ѹ��ˤ��  //
// 2005/09/06 ���롼��(������)��̵���Τ⤬����Τǥ����å������褦���ɲ�  //
// 2005/09/21 ���ե����å��θ����Ѥ�checkdate(month, day, year)�����       //
// 2006/01/24 WHEN m.midsc IS NULL THEN '&nbsp;' ���ɲ�                     //
// 2006/02/01 ����ʳ��ξȲ�������ʤκ������ɽ����Ψ���ɲ� 105̤�����ֻ�  //
//            parts_cost_history ������ ��³�Τߤˤ������kubun=1���ɲ� //
// 2006/02/02 �嵭�Υ�����ñ����Ͽ�Ȳ��ɲ� &reg_no��ʸ��������& reg_no  //
// 2006/02/12 ���ʤκ��������SQLʸ�� SUB��JOIN ���ѹ������ԡ��ɥ��å�      //
// 2006/03/22 ����������Υ�󥯤򥯥�å�������ä����˹ԥޡ������ɲ�      //
// 2006/09/21 sales/details �ǥ��쥯�ȥ�β��˺�����                        //
// 2007/04/18 Ψ2���ײ��ֹ�2 �� AND regdate<=�׾��� ��ȴ���Ƥ����Τ���    //
// 2007/09/28 Uround(assy_time * assy_rate, 2) ��    ��ư����Ψ��׻����ɲ� //
//    Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) //
// 2008/11/11 ���ץ���Ψ�ѹ�25.6��57.00�ѹ����å��ɲ�(�����Ȳ�)    ��ë //
// 2009/04/16 �����ֹ��Ƭ��SS�λ���˥������Ȥ���ȴ�Ф��褦���ɲ�     ��ë //
// 2009/08/04 �����ֹ��Ƭ��NKB�λ�ʪή�Ȥ���ȴ�Ф��褦���ɲ�          ��ë //
// 2009/08/19 ʪή���ʴ�����̾���ѹ�                                 ��ë //
// 2009/09/16 ��˥�ɸ��ξ����������ȴ���褦���ѹ�               ��ë //
// 2009/10/01 ���ץ����Τξ�羦�ʴ�����ȴ���褦���ѹ�                 ��ë //
// 2009/11/10 ��������ɽ���������Ψ�ȼ�����Ψ���ڴ�������褦          //
//            �ե饰�����֡ʽ���ͤϷ�����Ψ�ˢ���������form���ȹ���   ��ë //
// 2009/11/13 $shanai_flg�ΰ��֤��ѹ� �����1�ˤ���м�����Ψɽ��           //
//            ����ͤϣ�                                               ��ë //
// 2009/11/25 ���ʺ�����μ�����sum_price=NULL�λ��������Ƥ�����            //
//            ���ޤ����ʤ����ʤ����ä�������򳰤���               ��ë //
// 2009/12/02 ���ץ顦��˥������ȴ�Ф����б������ߤϥǡ���̵��     ��ë //
// 2010/05/21 CSV���Ϥ򤷤褦�Ȥ�������ľǼ��Ĵ����������Τ���α      ��ë //
// 2010/12/14 ���(00222 TRI)���ɲá��������Ρ����ץ����Ρ�ɸ��           //
//            ����ӻ��ǽ��פ����                                   ��ë //
// 2010/12/20 ����CSV�����ѥե�����̾�����ꤵ��Ƥ��ʤ��ä��Τ���  ��ë //
// 2010/12/24 ľǼ��Ĵ����ʸ���������б� �ܳʥ�꡼��                  ��ë //
// 2011/03/11 ������ξ���datatype='7'���ɲ�                            //
//            3�Ǽ�ư�׾�ʬ��ȴ�Ф���7�Ǽ�ư����ȴ�Ф�               ��ë //
// 2011/05/19 �����滳�������ˤ�ꡢ�ꥹ�Ȳ����ˤ������Ǥ��ɲ�       ��ë //
// 2011/11/10 �ƥ��Ȥ�NKCT��NKT���ɲ� �� �����ɲ� ���θ���             ��ë //
// 2011/11/21 CSV�ե�����̾����������.csv���ѹ��ΰ�Ĵ��              ��ë //
// 2011/11/30 ���ץ�ɸ��ȥ��ץ�����ˤ�NKCT��ޤޤʤ��褦���ѹ�            //
//            �����������ץ����Τˤϴޤࡣ�ޤ���˥��ΤߤȥХ�����        //
//            Ʊ�ͤ�NKT��ޤޤʤ��褦�ѹ�����������˥����Τˤϴޤ�    ��ë //
// 2013/01/29 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                   ��ë //
//            �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2013/01/31 ��˥��Τߤ�DPEȴ��SQL������                             ��ë //
// 2013/05/28 2013/05���NKCT/NKT����夲��ȴ���Ф��ʤ��褦�˽���      ��ë //
// 2013/05/28 ������λ�����ɲ�                                       ��ë //
// 2014/11/19 ����ξ��Ϲ����ֹ����Ϥ���褦���ѹ�                      //
// 2016/08/08 mouseover���ɲ�                                          ��ë //
// 2016/11/15 ������λ���򤹤���������򤬸����ʤ��ʤ�Τ�����       ��ë //
// 2018/03/29 ���ײ���ɽ�����ɲ�                                       ��ë //
// 2018/03/30 ���٤Υڡ������ܤǽ��פ˰�ư���Ƥ��ޤ��Х�����         ��ë //
// 2019/10/09 ��ɥƥå��ȥ�ɡ����ȤΥե�����̾�Ѵ�����äƤ����Τǽ�����ë//
// 2020/02/04 SS��NKB�ο�ʬ�����祳���ɤʤ��ǤǤ���褦���ѹ�          ��ë //
// 2020/03/12 NKCT/NKT������2011/11���ȴ���Ф��褦���ѹ�            ��ë //
// 2020/12/07 ���ͽ��Ȳ��ã��Ψ�ɲäˤ���ѹ�                       ���� //
// 2021/04/21  style='overflow:hidden;'���������������ǽ��        ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�����������',   INDUST . 'material/materialCost_view_assy.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $_SESSION['s_uri_ritu']   = $_REQUEST['uri_ritu'];
    $_SESSION['s_sales_page'] = $_REQUEST['sales_page'];
    $_SESSION['uri_assy_no']  = $_REQUEST['assy_no'];
    $_SESSION['uri_customer']  = $_REQUEST['customer'];
    $_SESSION['s_syukei']       = $_REQUEST['syukei'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $kubun      = $_SESSION['s_kubun'];
    $uri_ritu   = $_SESSION['s_uri_ritu'];
    $syukei     = $_SESSION['s_syukei'];
    $assy_no    = $_SESSION['uri_assy_no'];
    $customer   = $_SESSION['uri_customer'];
        ///// day �Υ����å�
        if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            }
        }
        ///// day �Υ����å�
        if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
            $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            }
        }
    $_SESSION['s_d_start'] = $d_start;
    $_SESSION['s_d_end']   = $d_end  ;
    
    ////////////// �ѥ���ɥ����å�
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
    ///////////// ��׶�ۡ�����������
    if ( ($div != 'S') && ($div != 'D') ) {      // �������ɸ�� �ʳ��ʤ�
        $query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
    } else {
        $query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
                  //left outer join
                  //      aden_master as aden
                  //on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)";
    }
    //////////// SQL where ��� ���Ѥ���
    $search = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($assy_no != '') {       // �����ֹ椬���ꤵ�줿���
        $search .= " and assyno like '{$assy_no}%%'";
    }
    if ($customer != ' ') {    // �����褬���ꤵ�줿���
        $search .= " and ������='{$customer}'";
    }
    if ($div == 'S') {    // ������ʤ�
        $search .= " and ������='C' and note15 like 'SC%%'";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
        $search .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search .= " and ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
        $search .= " and (CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END)";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "N") {    // ��˥��ΥХ���롦���������� assyno �ǥ����å�
        $search .= " and ������='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
        $search .= " and (assyno not like 'SS%%')";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and CASE WHEN assyno = '' THEN ������='L' ELSE CASE WHEN m.midsc IS NULL THEN ������='L' ELSE m.midsc not like 'DPE%%' END END";
        $search .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='L' END";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "B") {    // �Х����ξ��� assyno �ǥ����å�
        //$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
        $search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
        $search .= " and (assyno not like 'SS%%')";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='L' END";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "SSC") {   // ���ץ��������ξ��� assyno �ǥ����å�
        $search .= " and ������='C' and (assyno like 'SS%%')";
    } elseif ($div == "SSL") {   // ��˥���������ξ��� assyno �ǥ����å�
        // ���ץ����ʤ��ʤä��Τǻ�����L�Ͼʤ�
        //$search .= " and ������='L' and (assyno like 'SS%%')";
        $search .= " and (assyno like 'SS%%')";
    } elseif ($div == "NKB") {  // ���ʴ����ξ��� assyno �ǥ����å�
        $search .= " and (assyno like 'NKB%%')";
    } elseif ($div == "TRI") {  // ���ξ��ϻ�����������ʬ����ɼ�ֹ�ǥ����å�
        $search .= " and ������='C'";
        $search .= " and ( datatype='3' or datatype='7' )";
        $search .= " and ��ɼ�ֹ�='00222'";
    } elseif ($div == "NKCT") { // NKCT�ξ��ϻٱ��襳����(1)�ǥ����å�
        $search .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code=1 END";
        //$search .= " and groupm.support_group_code=1";
    } elseif ($div == "NKT") {  // NKT�ξ��ϻٱ��襳����(2)�ǥ����å�
        $search .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code=2 END";
        //$search .= " and groupm.support_group_code=2";
    } elseif ($div == "_") {    // �������ʤ�
        $search .= " and ������=' '";
    } elseif ($div == "C") {
        $search .= " and ������='$div'";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div == "L") {
        $search .= " and ������='$div'";
        $search .= " and (assyno not like 'SS%%')";
        $search .= " and (assyno not like 'NKB%%')";
    } elseif ($div != " ") {
        $search .= " and ������='$div'";
    }
    if ($syukei == 'meisai') {
        if ($kubun != " ") {
            $search .= " and datatype='$kubun'";
        }
    }
    $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $_SESSION['sales_search'] = $search;        // SQL��where�����¸
    $query_s = $query;                          // ��׾Ȳ�SQL query ʸ����¸
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] = '��׶�ۤμ����˼��Ԥ��ޤ�����';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $t_ken     = $res_sum[0]['t_ken'];
        $t_kazu    = $res_sum[0]['t_kazu'];
        $t_kingaku = $res_sum[0]['t_kingaku'];
        $_SESSION['u_t_ken']  = $t_ken;
        $_SESSION['u_t_kazu'] = $t_kazu;
        $_SESSION['u_t_kin']  = $t_kingaku;
    }

    // 2020/12/07 add. ------------------------------------------------------->
    if( isset($_REQUEST['yotei']) ) {
        $_SESSION['s_yotei']       = $_REQUEST['yotei'];
    } else {
        $_SESSION['s_yotei']       = "";
    }
    // <-----------------------------------------------------------------------
} else {                                                // �ڡ������ؤʤ�
    $t_ken     = $_SESSION['u_t_ken'];
    $t_kazu    = $_SESSION['u_t_kazu'];
    $t_kingaku = $_SESSION['u_t_kin'];
    $syukei    = $_SESSION['s_syukei'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = $_SESSION['s_kubun'];
$uri_ritu   = $_SESSION['s_uri_ritu'];
$assy_no    = $_SESSION['uri_assy_no'];
$customer   = $_SESSION['uri_customer'];
$search     = $_SESSION['sales_search'];

// 2020/12/07 add. ----------------------------------------------------------->
if( $_SESSION['s_yotei'] == "on" ) {
    $menu->set_RetUrl(SALES . "sales_plan/sales_plan_view.php?uri_passwd={$uri_passwd}&div={$div}&d_start={$d_start}&d_end={$d_end}&uri_ritu={$uri_ritu}&shikiri=&sales_page={$_SESSION['s_sales_page']}&assy_no=&tassei=tassei&yotei=");
} else {
    $menu->set_RetUrl($menu->out_RetUrl());
}
// <---------------------------------------------------------------------------

///// ���ʥ��롼��(������)̾������
if ($div == " ") $div_name = "�����롼��";
if ($div == "C") $div_name = "���ץ�����";
if ($div == "D") $div_name = "���ץ�ɸ��";
if ($div == "S") $div_name = "���ץ�����";
if ($div == "L") $div_name = "��˥�����";
if ($div == "N") $div_name = "��˥��Τ�";
if ($div == "B") $div_name = "���Υݥ��";
if ($div == "SSC") $div_name = "���ץ�";
if ($div == "SSL") $div_name = "��˥��";
if ($div == "NKB") $div_name = "���ʴ���";
if ($div == "T") $div_name = "�ġ���";
if ($div == "TRI") $div_name = "���";
if ($div == "NKCT") $div_name = "�Σˣã�";
if ($div == "NKT") $div_name = "�Σˣ�";
if ($div == "_") $div_name = "�ʤ�";
///// ������̾������
if ($customer == " ") $customer_name = "����";
if ($customer == "00001") $customer_name = "���칩��";
if ($customer == "00002") $customer_name = "��ɡ�����";
if ($customer == "00003") $customer_name = "�Σˣ�";
if ($customer == "00004") $customer_name = "��ɥƥå�";
if ($customer == "00005") $customer_name = "������칩��";
if ($customer == "00101") $customer_name = "�Σˣã�";
if ($customer == "00102") $customer_name = "�£ңţã�";
if ($customer == "99999") $customer_name = "����";

//////////// ɽ�������
$ft_kingaku = number_format($t_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
$menu->set_caption("<u>����=<font color='red'>{$div_name}</font>��������=<font color='red'>{$customer_name}</font>��{$f_d_start}��{$f_d_end}<u>");
$menu->set_caption2("<u>��׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");

//////////// ���ǤιԿ�
if (isset($_SESSION['s_sales_page'])) {
    define('PAGE', $_SESSION['s_sales_page']);
} else {
    define('PAGE', 25);
}

//////////// ��ץ쥳���ɿ�����     (�оݥơ��֥�κ������ڡ�������˻���)
$maxrows = $t_ken;

//////////// �ڡ������ե��å�����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['sales_offset'] += PAGE;
    if ($_SESSION['sales_offset'] >= $maxrows) {
        $_SESSION['sales_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['sales_offset'] -= PAGE;
    if ($_SESSION['sales_offset'] < 0) {
        $_SESSION['sales_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $_SESSION['sales_offset'];
} elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['sales_offset'];
} else {
    $_SESSION['sales_offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['sales_offset'];

// �ʲ��ϼ�������Ψɽ���򤷤����Ȥ���Ŭ�Ѥ�����
// �����Ȥˤ���Τ����ѤʤΤǥե饰��Ω�Ƥ�
$shanai_flg = 0;

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
if ($syukei == 'meisai') {
    if ($div != 'S') {      // ������ �ʳ��ʤ�
        $query = sprintf("select
                                u.�׾���        as �׾���,                  -- 0
                                CASE
                                    WHEN u.datatype=1 THEN '����'
                                    WHEN u.datatype=2 THEN '����'
                                    WHEN u.datatype=3 THEN '����'
                                    WHEN u.datatype=4 THEN 'Ĵ��'
                                    WHEN u.datatype=5 THEN '��ư'
                                    WHEN u.datatype=6 THEN 'ľǼ'
                                    WHEN u.datatype=7 THEN '���'
                                    WHEN u.datatype=8 THEN '����'
                                    WHEN u.datatype=9 THEN '����'
                                    ELSE u.datatype
                                END             as ��ʬ,                    -- 1
                                CASE
                                    WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.�ײ��ֹ�
                                END                     as �ײ��ֹ�,        -- 2
                                CASE
                                    WHEN trim(u.assyno) = '' THEN '---'
                                    ELSE u.assyno
                                END                     as �����ֹ�,        -- 3
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END             as ����̾,                  -- 4
                                CASE
                                    WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.���˾��
                                END                     as ����,            -- 5
                                u.����          as ����,                    -- 6
                                u.ñ��          as ����ñ��,                -- 7
                                Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                        as �������,        -- 9
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                     as Ψ��,            --10
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS �������2,       --11
                                (select Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS Ψ��,            --12
                                (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS �ײ��ֹ�2,       --13
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                        AS ���ʺ�����,      --14
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                        AS ñ����Ͽ�ֹ�     --15
                          from
                                hiuuri as u
                          left outer join
                                assembly_schedule as a
                          on u.�ײ��ֹ�=a.plan_no
                          left outer join
                                miitem as m
                          on u.assyno=m.mipn
                          left outer join
                                material_cost_header as mate
                          on u.�ײ��ֹ�=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on u.assyno=groupm.assy_no
                          %s
                          order by �׾���, assyno
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // ���� $search �Ǹ���
        // �ʲ��ϼ�������Ψɽ���򤷤����Ȥ���Ŭ�Ѥ�����
        // �����Ȥˤ���Τ����ѤʤΤǥե饰��Ω�Ƥ�
        if ($shanai_flg == 1) {
            $query = sprintf("select
                                    u.�׾���        as �׾���,                  -- 0
                                    CASE
                                        WHEN u.datatype=1 THEN '����'
                                        WHEN u.datatype=2 THEN '����'
                                        WHEN u.datatype=3 THEN '����'
                                        WHEN u.datatype=4 THEN 'Ĵ��'
                                        WHEN u.datatype=5 THEN '��ư'
                                        WHEN u.datatype=6 THEN 'ľǼ'
                                        WHEN u.datatype=7 THEN '���'
                                        WHEN u.datatype=8 THEN '����'
                                        WHEN u.datatype=9 THEN '����'
                                        ELSE u.datatype
                                    END             as ��ʬ,                    -- 1
                                    CASE
                                        WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                        ELSE u.�ײ��ֹ�
                                    END                     as �ײ��ֹ�,        -- 2
                                    CASE
                                        WHEN trim(u.assyno) = '' THEN '---'
                                        ELSE u.assyno
                                    END                     as �����ֹ�,        -- 3
                                    CASE
                                        WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                        WHEN m.midsc IS NULL THEN '&nbsp;'
                                        ELSE substr(m.midsc,1,38)
                                    END             as ����̾,                  -- 4
                                    CASE
                                        WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                        ELSE u.���˾��
                                    END                     as ����,            -- 5
                                    u.����          as ����,                    -- 6
                                    u.ñ��          as ����ñ��,                -- 7
                                    Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                    sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)
                                                            as �������,        -- 9
                                    CASE
                                        WHEN (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                        ELSE Uround(u.ñ�� / (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                    END                     as Ψ��,            --10
                                    (select sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                            AS �������2,       --11
                                    (select Uround(u.ñ�� / (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                            AS Ψ��,            --12
                                    (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                            AS �ײ��ֹ�2,       --13
                                    (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                            AS ���ʺ�����,      --14
                                    (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                            AS ñ����Ͽ�ֹ�     --15
                              from
                                    hiuuri as u
                              left outer join
                                    assembly_schedule as a
                              on u.�ײ��ֹ�=a.plan_no
                              left outer join
                                    miitem as m
                              on u.assyno=m.mipn
                              left outer join
                                    material_cost_header as mate
                              on u.�ײ��ֹ�=mate.plan_no
                              LEFT OUTER JOIN
                                    sales_parts_material_history AS pmate
                              ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                              left outer join
                                    product_support_master AS groupm
                              on u.assyno=groupm.assy_no
                              %s
                              order by �׾���, assyno
                              offset %d limit %d
                              ", $search, $offset, PAGE);   // ���� $search �Ǹ���
        }
    } else {    ////////////////////////////////////////// ������ξ��
        $query = sprintf("select
                                u.�׾���        as �׾���,                  -- 0
                                CASE
                                    WHEN u.datatype=1 THEN '����'
                                    WHEN u.datatype=2 THEN '����'
                                    WHEN u.datatype=3 THEN '����'
                                    WHEN u.datatype=4 THEN 'Ĵ��'
                                    WHEN u.datatype=5 THEN '��ư'
                                    WHEN u.datatype=6 THEN 'ľǼ'
                                    WHEN u.datatype=7 THEN '���'
                                    WHEN u.datatype=8 THEN '����'
                                    WHEN u.datatype=9 THEN '����'
                                    ELSE u.datatype
                                END             as ��ʬ,                    -- 1
                                CASE
                                    WHEN trim(u.�ײ��ֹ�)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.�ײ��ֹ�
                                END                     as �ײ��ֹ�,        -- 2
                                u.assyno        as �����ֹ�,                -- 3
                                CASE
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,18)
                                END                     as ����̾,          -- 4
                                CASE
                                    WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.���˾��
                                END                     as ����,            -- 5
                                u.����          as ����,                    -- 6
                                u.ñ��          as ����ñ��,                -- 7
                                Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                trim(a.note15)  as �����ֹ�,                -- 9
                                aden.order_price  as ����ñ��,              --10
                                CASE
                                    WHEN aden.order_price <= 0 THEN '0'
                                    ELSE Uround(u.ñ�� / aden.order_price, 3) * 100
                                END                     as Ψ��,            --11
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                        as �������,        --12
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                     as Ψ��             --13
                          from
                                (hiuuri as u left outer join miitem as m on u.assyno=m.mipn)
                          left outer join
                                assembly_schedule as a
                          on u.�ײ��ֹ�=a.plan_no
                          left outer join
                                aden_master as aden
                          -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                          on (a.plan_no=aden.plan_no)
                          left outer join
                                material_cost_header as mate
                          on u.�ײ��ֹ�=mate.plan_no
                          left outer join
                                product_support_master AS groupm
                          on u.assyno=groupm.assy_no
                          %s
                          order by �׾���, assyno
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // ���� $search �Ǹ���
        // �ʲ��ϼ�������Ψɽ���򤷤����Ȥ���Ŭ�Ѥ�����
        // �����Ȥˤ���Τ����ѤʤΤǥե饰��Ω�Ƥ�
        if ($shanai_flg == 1) {
            $query = sprintf("select
                                    u.�׾���        as �׾���,                  -- 0
                                    CASE
                                        WHEN u.datatype=1 THEN '����'
                                        WHEN u.datatype=2 THEN '����'
                                        WHEN u.datatype=3 THEN '����'
                                        WHEN u.datatype=4 THEN 'Ĵ��'
                                        WHEN u.datatype=5 THEN '��ư'
                                        WHEN u.datatype=6 THEN 'ľǼ'
                                        WHEN u.datatype=7 THEN '���'
                                        WHEN u.datatype=8 THEN '����'
                                        WHEN u.datatype=9 THEN '����'
                                        ELSE u.datatype
                                    END             as ��ʬ,                    -- 1
                                    CASE
                                        WHEN trim(u.�ײ��ֹ�)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                        ELSE u.�ײ��ֹ�
                                    END                     as �ײ��ֹ�,        -- 2
                                    u.assyno        as �����ֹ�,                -- 3
                                    CASE
                                        WHEN m.midsc IS NULL THEN '&nbsp;'
                                        ELSE substr(m.midsc,1,18)
                                    END                     as ����̾,          -- 4
                                    CASE
                                        WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                        ELSE u.���˾��
                                    END                     as ����,            -- 5
                                    u.����          as ����,                    -- 6
                                    u.ñ��          as ����ñ��,                -- 7
                                    Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                    trim(a.note15)  as �����ֹ�,                -- 9
                                    aden.order_price  as ����ñ��,              --10
                                    CASE
                                        WHEN aden.order_price <= 0 THEN '0'
                                        ELSE Uround(u.ñ�� / aden.order_price, 3) * 100
                                    END                     as Ψ��,            --11
                                    sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)
                                                            as �������,        --12
                                    CASE
                                        WHEN (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                        ELSE Uround(u.ñ�� / (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                    END                     as Ψ��             --13
                              from
                                    (hiuuri as u left outer join miitem as m on u.assyno=m.mipn)
                              left outer join
                                    assembly_schedule as a
                              on u.�ײ��ֹ�=a.plan_no
                              left outer join
                                    aden_master as aden
                              -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                              on (a.plan_no=aden.plan_no)
                              left outer join
                                    material_cost_header as mate
                              on u.�ײ��ֹ�=mate.plan_no
                              left outer join
                                product_support_master AS groupm
                              on u.assyno=groupm.assy_no
                              %s
                              order by �׾���, assyno
                              offset %d limit %d
                              ", $search, $offset, PAGE);   // ���� $search �Ǹ���
        }
    }
    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $num = count($field);       // �ե�����ɿ�����
        for ($r=0; $r<$rows; $r++) {
            $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        }
        $_SESSION['SALES_TEST'] = sprintf("order by �׾��� offset %d limit %d", $offset, PAGE);
    }
} else {
    // ���׶�ۤμ���
    $s_ken       = array();
    $s_kazu      = array();
    $s_kingaku   = array();
    $s_ken_t     = 0;
    $s_kazu_t    = 0;
    $s_kingaku_t = 0;
    for ($r=1; $r<10; $r++) {   // ����ʬ�������ޤǤ����
        $search_s  = " and datatype='$r'";
        $query_sk  = sprintf("$query_s %s", $search_s);     // SQL query ʸ�δ���
        $res_syu   = array();
        if (getResult($query_sk, $res_syu) <= 0) {
            $s_ken[$r]     = 0;
            $s_kazu[$r]    = 0;
            $s_kingaku[$r] = 0;
        } else {
            $s_ken[$r]     = $res_syu[0]['t_ken'];
            $s_kazu[$r]    = $res_syu[0]['t_kazu'];
            $s_kingaku[$r] = $res_syu[0]['t_kingaku'];
            $s_ken_t      += $s_ken[$r];
            $s_kazu_t     += $s_kazu[$r];
            $s_kingaku_t  += $s_kingaku[$r];
        }
    }
    if ($div != 'S') {      // ������ �ʳ��ʤ�
        $query = sprintf("select
                                u.�׾���        as �׾���,                  -- 0
                                CASE
                                    WHEN u.datatype=1 THEN '����'
                                    WHEN u.datatype=2 THEN '����'
                                    WHEN u.datatype=3 THEN '����'
                                    WHEN u.datatype=4 THEN 'Ĵ��'
                                    WHEN u.datatype=5 THEN '��ư'
                                    WHEN u.datatype=6 THEN 'ľǼ'
                                    WHEN u.datatype=7 THEN '���'
                                    WHEN u.datatype=8 THEN '����'
                                    WHEN u.datatype=9 THEN '����'
                                    ELSE u.datatype
                                END             as ��ʬ,                    -- 1
                                CASE
                                    WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.�ײ��ֹ�
                                END                     as �ײ��ֹ�,        -- 2
                                CASE
                                    WHEN trim(u.assyno) = '' THEN '---'
                                    ELSE u.assyno
                                END                     as �����ֹ�,        -- 3
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END             as ����̾,                  -- 4
                                CASE
                                    WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.���˾��
                                END                     as ����,            -- 5
                                u.����          as ����,                    -- 6
                                u.ñ��          as ����ñ��,                -- 7
                                Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                        as �������,        -- 9
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                     as Ψ��,            --10
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS �������2,       --11
                                (select Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS Ψ��,            --12
                                (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                        AS �ײ��ֹ�2,       --13
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                        AS ���ʺ�����,      --14
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                        AS ñ����Ͽ�ֹ�     --15
                          from
                                hiuuri as u
                          left outer join
                                assembly_schedule as a
                          on u.�ײ��ֹ�=a.plan_no
                          left outer join
                                miitem as m
                          on u.assyno=m.mipn
                          left outer join
                                material_cost_header as mate
                          on u.�ײ��ֹ�=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on u.assyno=groupm.assy_no
                          %s
                          order by �׾���, assyno
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // ���� $search �Ǹ���
        // �ʲ��ϼ�������Ψɽ���򤷤����Ȥ���Ŭ�Ѥ�����
        // �����Ȥˤ���Τ����ѤʤΤǥե饰��Ω�Ƥ�
        if ($shanai_flg == 1) {
            $query = sprintf("select
                                    u.�׾���        as �׾���,                  -- 0
                                    CASE
                                        WHEN u.datatype=1 THEN '����'
                                        WHEN u.datatype=2 THEN '����'
                                        WHEN u.datatype=3 THEN '����'
                                        WHEN u.datatype=4 THEN 'Ĵ��'
                                        WHEN u.datatype=5 THEN '��ư'
                                        WHEN u.datatype=6 THEN 'ľǼ'
                                        WHEN u.datatype=7 THEN '���'
                                        WHEN u.datatype=8 THEN '����'
                                        WHEN u.datatype=9 THEN '����'
                                        ELSE u.datatype
                                    END             as ��ʬ,                    -- 1
                                    CASE
                                        WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                        ELSE u.�ײ��ֹ�
                                    END                     as �ײ��ֹ�,        -- 2
                                    CASE
                                        WHEN trim(u.assyno) = '' THEN '---'
                                        ELSE u.assyno
                                    END                     as �����ֹ�,        -- 3
                                    CASE
                                        WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                        WHEN m.midsc IS NULL THEN '&nbsp;'
                                        ELSE substr(m.midsc,1,38)
                                    END             as ����̾,                  -- 4
                                    CASE
                                        WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                        ELSE u.���˾��
                                    END                     as ����,            -- 5
                                    u.����          as ����,                    -- 6
                                    u.ñ��          as ����ñ��,                -- 7
                                    Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                    sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)
                                                            as �������,        -- 9
                                    CASE
                                        WHEN (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                        ELSE Uround(u.ñ�� / (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                    END                     as Ψ��,            --10
                                    (select sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                            AS �������2,       --11
                                    (select Uround(u.ñ�� / (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                            AS Ψ��,            --12
                                    (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                            AS �ײ��ֹ�2,       --13
                                    (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                            AS ���ʺ�����,      --14
                                    (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                            AS ñ����Ͽ�ֹ�     --15
                              from
                                    hiuuri as u
                              left outer join
                                    assembly_schedule as a
                              on u.�ײ��ֹ�=a.plan_no
                              left outer join
                                    miitem as m
                              on u.assyno=m.mipn
                              left outer join
                                    material_cost_header as mate
                              on u.�ײ��ֹ�=mate.plan_no
                              LEFT OUTER JOIN
                                    sales_parts_material_history AS pmate
                              ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                              left outer join
                                    product_support_master AS groupm
                              on u.assyno=groupm.assy_no
                              %s
                              order by �׾���, assyno
                              offset %d limit %d
                              ", $search, $offset, PAGE);   // ���� $search �Ǹ���
        }
    } else {    ////////////////////////////////////////// ������ξ��
        $query = sprintf("select
                                u.�׾���        as �׾���,                  -- 0
                                CASE
                                    WHEN u.datatype=1 THEN '����'
                                    WHEN u.datatype=2 THEN '����'
                                    WHEN u.datatype=3 THEN '����'
                                    WHEN u.datatype=4 THEN 'Ĵ��'
                                    WHEN u.datatype=5 THEN '��ư'
                                    WHEN u.datatype=6 THEN 'ľǼ'
                                    WHEN u.datatype=7 THEN '���'
                                    WHEN u.datatype=8 THEN '����'
                                    WHEN u.datatype=9 THEN '����'
                                    ELSE u.datatype
                                END             as ��ʬ,                    -- 1
                                CASE
                                    WHEN trim(u.�ײ��ֹ�)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.�ײ��ֹ�
                                END                     as �ײ��ֹ�,        -- 2
                                u.assyno        as �����ֹ�,                -- 3
                                CASE
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,18)
                                END                     as ����̾,          -- 4
                                CASE
                                    WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE u.���˾��
                                END                     as ����,            -- 5
                                u.����          as ����,                    -- 6
                                u.ñ��          as ����ñ��,                -- 7
                                Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                trim(a.note15)  as �����ֹ�,                -- 9
                                aden.order_price  as ����ñ��,              --10
                                CASE
                                    WHEN aden.order_price <= 0 THEN '0'
                                    ELSE Uround(u.ñ�� / aden.order_price, 3) * 100
                                END                     as Ψ��,            --11
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                        as �������,        --12
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                     as Ψ��             --13
                          from
                                (hiuuri as u left outer join miitem as m on u.assyno=m.mipn)
                          left outer join
                                assembly_schedule as a
                          on u.�ײ��ֹ�=a.plan_no
                          left outer join
                                aden_master as aden
                          -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                          on (a.plan_no=aden.plan_no)
                          left outer join
                                material_cost_header as mate
                          on u.�ײ��ֹ�=mate.plan_no
                          left outer join
                                product_support_master AS groupm
                          on u.assyno=groupm.assy_no
                          %s
                          order by �׾���, assyno
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // ���� $search �Ǹ���
        // �ʲ��ϼ�������Ψɽ���򤷤����Ȥ���Ŭ�Ѥ�����
        // �����Ȥˤ���Τ����ѤʤΤǥե饰��Ω�Ƥ�
        if ($shanai_flg == 1) {
            $query = sprintf("select
                                    u.�׾���        as �׾���,                  -- 0
                                    CASE
                                        WHEN u.datatype=1 THEN '����'
                                        WHEN u.datatype=2 THEN '����'
                                        WHEN u.datatype=3 THEN '����'
                                        WHEN u.datatype=4 THEN 'Ĵ��'
                                        WHEN u.datatype=5 THEN '��ư'
                                        WHEN u.datatype=6 THEN 'ľǼ'
                                        WHEN u.datatype=7 THEN '���'
                                        WHEN u.datatype=8 THEN '����'
                                        WHEN u.datatype=9 THEN '����'
                                        ELSE u.datatype
                                    END             as ��ʬ,                    -- 1
                                    CASE
                                        WHEN trim(u.�ײ��ֹ�)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                        ELSE u.�ײ��ֹ�
                                    END                     as �ײ��ֹ�,        -- 2
                                    u.assyno        as �����ֹ�,                -- 3
                                    CASE
                                        WHEN m.midsc IS NULL THEN '&nbsp;'
                                        ELSE substr(m.midsc,1,18)
                                    END                     as ����̾,          -- 4
                                    CASE
                                        WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                        ELSE u.���˾��
                                    END                     as ����,            -- 5
                                    u.����          as ����,                    -- 6
                                    u.ñ��          as ����ñ��,                -- 7
                                    Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                                    trim(a.note15)  as �����ֹ�,                -- 9
                                    aden.order_price  as ����ñ��,              --10
                                    CASE
                                        WHEN aden.order_price <= 0 THEN '0'
                                        ELSE Uround(u.ñ�� / aden.order_price, 3) * 100
                                    END                     as Ψ��,            --11
                                    sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)
                                                            as �������,        --12
                                    CASE
                                        WHEN (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                        ELSE Uround(u.ñ�� / (sum_price + Uround(m_time * m_rate, 2) + Uround(g_time * g_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                    END                     as Ψ��             --13
                              from
                                    (hiuuri as u left outer join miitem as m on u.assyno=m.mipn)
                              left outer join
                                    assembly_schedule as a
                              on u.�ײ��ֹ�=a.plan_no
                              left outer join
                                    aden_master as aden
                              -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                              on (a.plan_no=aden.plan_no)
                              left outer join
                                    material_cost_header as mate
                              on u.�ײ��ֹ�=mate.plan_no
                              left outer join
                                product_support_master AS groupm
                              on u.assyno=groupm.assy_no
                              %s
                              order by �׾���, assyno
                              offset %d limit %d
                              ", $search, $offset, PAGE);   // ���� $search �Ǹ���
        }
    }
    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $num = count($field);       // �ե�����ɿ�����
        for ($r=0; $r<$rows; $r++) {
            $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        }
        $_SESSION['SALES_TEST'] = sprintf("order by �׾��� offset %d limit %d", $offset, PAGE);
    }
}

//////////////////// ������񥫥ץ�ɸ����Ψ57���ִ���
//$query_i = sprintf("select
//                            CASE
//                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
//                                ELSE u.�ײ��ֹ�
//                            END                     as �ײ��ֹ�        -- 0
//                      from
//                            hiuuri as u
//                      left outer join
//                            assembly_schedule as a
//                      on u.�ײ��ֹ�=a.plan_no
//                      left outer join
//                            miitem as m
//                      on u.assyno=m.mipn
//                      left outer join
//                            material_cost_header as mate
//                      on u.�ײ��ֹ�=mate.plan_no
//                      LEFT OUTER JOIN
//                            sales_parts_material_history AS pmate
//                      ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
//                      WHERE �׾���>=20071001 and �׾���<=20080331
//                      AND ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)
//                      AND datatype=1
//                      order by �ײ��ֹ�
//                        ");   // ���� $search �Ǹ���
//$res_i   = array();
//$field_i = array();
//if (($rows_i = getResultWithField3($query_i, $field_i, $res_i)) <= 0) {
//    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
//    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
//    exit();
//} else {
//    for ($r=0; $r<$rows_i; $r++) {
//        $query_c = sprintf("UPDATE material_cost_header SET assy_rate = 57.00 WHERE plan_no='{$res_i[$r][0]}'");
//        $res_c   = array();
//        if (getResult($query_c, $res_c) <= 0) {
//        } else {
//        }
//    }
//}

// ��������CSV�����Ѥν������
// �ե�����̾�����ܸ��Ĥ���ȼ����Ϥ��ǥ��顼�ˤʤ�Τǰ���ѻ����ѹ�
if ($div == " ") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "D") $act_name = "C-hyou";
if ($div == "S") $act_name = "C-toku";
if ($div == "L") $act_name = "L-all";
if ($div == "N") $act_name = "L-hyou";
if ($div == "B") $act_name = "L-bimor";
if ($div == "SSC") $act_name = "C-shuri";
if ($div == "SSL") $act_name = "L-shuri";
if ($div == "NKB") $act_name = "NKB";
if ($div == "T") $act_name = "TOOL";
if ($div == "TRI") $act_name = "SHISAKU";
if ($div == "NKCT") $act_name = "NKCT";
if ($div == "NKT") $act_name = "NKT";
if ($div == "_") $act_name = "NONE";
///// ������̾��CSV������
if ($customer == " ") $c_name = "T-ALL";
if ($customer == "00001") $c_name = "T-NK";
if ($customer == "00002") $c_name = "T-MEDOS";
if ($customer == "00003") $c_name = "T-NKT";
if ($customer == "00004") $c_name = "T-MEDOTEC";
if ($customer == "00005") $c_name = "T-SNK";
if ($customer == "00101") $c_name = "T-NKCT";
if ($customer == "00102") $c_name = "T-BRECO";
if ($customer == "99999") $c_name = "T-SHO";

// SQL�Υ������������ܸ��ѻ����ѹ���'�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
$csv_search = str_replace('�׾���','keidate',$search);
$csv_search = str_replace('������','jigyou',$csv_search);
$csv_search = str_replace('��ɼ�ֹ�','denban',$csv_search);
$csv_search = str_replace('������','tokui',$csv_search);
$csv_search = str_replace('\'','/',$csv_search);

// CSV�ե�����̾������ʳ���ǯ��-��λǯ��-��������
$outputFile = $d_start . '-' . $d_end . '-' . $act_name . '-' . $c_name;

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();                          // F2/F12��������뤿����б�
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:   yellow;
    color:              blue;
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<?php if (PAGE > 25) { ?>
<body onLoad='set_focus()'>
<?php } else { ?>
<body onLoad='set_focus()'>
<?php } ?>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <?php
                    if ($syukei == 'meisai') {
                    ?>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <?php
                    }
                    ?>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                        <BR>
                        <?php echo $menu->out_caption2(), "\n" ?>
                    </td>
                    <?php
                    if ($syukei == 'meisai') {
                    ?>
                    <a href='sales_csv.php?csvname=<?php echo $outputFile ?>&actname=<?php echo $act_name ?>&csvsearch=<?php echo $csv_search ?>'>
                        CSV����
                    </a>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                    <?php
                    }
                    ?>
                </tr>
            </form>
        </table>
        <BR>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            if ($syukei == 'meisai') {
            ?>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    if ($i >= 11) if ($div != 'S') break;
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + $offset + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        if ($i >= 11) if ($div != 'S') break;
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        if ($div != 'S') { // ������ �ʳ��ʤ�
                            switch ($i) {
                            case 0:     // �׾���
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 3:
                                if ($res[$r][1] == '����') {
                                    echo "<td class='winbox' nowrap align='center'><a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"{$menu->out_action('�����������')}?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][2]), "\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                                }
                                break;
                            case 4:     // ����̾
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 6:     // ����
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // ����ñ��
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                break;
                            case 8:     // ���
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 9:     // �������
                                if ($res[$r][$i] == 0) {
                                    if ($res[$r][11]) {
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                                , number_format($res[$r][11], 2), "</a></td>\n";
                                    } elseif ($res[$r][14]) {   // ���ʤκ����������å�����ɽ������
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('ñ����Ͽ�Ȳ�'), "?parts_no=", urlencode($res[$r][3]), "& reg_no={$res[$r][15]}\")' target='application' style='text-decoration:none;'>"
                                                , number_format($res[$r][14], 2), "</a></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 10:    // Ψ(�������)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>", number_format($res[$r][$i], 1), "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    if ($res[$r][12]) {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][12], 1), "</div></td>\n";
                                    } elseif ($res[$r][14]) {
                                        if ( ($res[$r][7]/$res[$r][14]) < 1.049 ) {   // �ֻ�ɽ����ʬ��
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9' style='color:red;'>", number_format($res[$r][7]/$res[$r][14]*100, 1), "</div></td>\n";
                                        } else {
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][7]/$res[$r][14]*100, 1), "</div></td>\n";
                                        }
                                    } else {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][$i], 1), "</div></td>\n";
                                }
                                break;
                            default:    // ����¾
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                            }
                        } else {        // ������ʤ�
                            switch ($i) {
                            case 0:     // �׾���
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 4:     // ����̾
                                echo "<td class='winbox' nowrap width='130' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 6:     // ����
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // ����ñ��
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                break;
                            case 8:     // ���
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 10:    // ����ñ��
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='55' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='55' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                }
                                break;
                            case 11:    // Ψ
                                if ($res[$r][$i] > 0 && $res[$r][$i] < $uri_ritu) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 1) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 1) . "</div></td>\n";
                                }
                                break;
                            case 12:    // �������
                                if ($res[$r][$i] == 0) {
                                    // echo "<td nowrap width='60' align='right' class='pt9'>" . number_format($res[$r][$i], 2) . "</td>\n";
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 13:    // Ψ(�������)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 1) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 1) . "</div></td>\n";
                                }
                                break;
                            default:    // ����¾
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            }
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
            <?php
            } else {        // ����ɽ��
            ?>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>����ʬ</th>
                    <th class='winbox' nowrap>���</th>
                    <th class='winbox' nowrap>����</th>
                    <th class='winbox' nowrap>���</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>Ĵ��</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>��ư</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>ľǼ</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[6], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[7], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[8], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_ken[9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kazu[9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[9], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <td class='winboxy' nowrap align='center'>���</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_ken_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kazu_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t, 0) . "</td>\n";
                    ?>
                </tr>
            </tbody>
            <?php
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <?php
        if ($syukei == 'meisai') {
        ?>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        <table style='border: 2px solid #0A0;'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>���������Ŀ�ɽ����Ʊ�ײ��ֹ����Ͽ������ʪ�ǡ��㿧��Ʊ�ײ�Ǥ�̵��������������Ǻǿ�����Ͽ��ɽ��</td></tr>
        </table>
        <?php
        }
        ?>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
