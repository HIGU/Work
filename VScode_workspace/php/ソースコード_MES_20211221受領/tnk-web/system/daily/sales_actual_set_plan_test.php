<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ�  new version   sales_view.php                             //
// Copyright (C) 2001-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
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

if( isset($_REQUEST['start_date']) ) {
    $d_start = $_REQUEST['start_date'];
} else {
    $d_start = 20201201;    // �ƥ��ȸ���
}

if( isset($_REQUEST['end_date']) ) {
    $d_end = $_REQUEST['end_date'];
} else {
    $d_end = 20201231;      // �ƥ��ȸ���
}

access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

$err_flg = false;

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

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("select
                        a.kanryou                     AS ��λͽ����,  -- 0
                        CASE
                            WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                            ELSE a.plan_no
                        END                           AS �ײ��ֹ�,    -- 1
                        CASE
                            WHEN trim(a.parts_no) = '' THEN '---'
                            ELSE a.parts_no
                        END                           AS �����ֹ�,    -- 2
                        CASE
                            WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                            WHEN m.midsc IS NULL THEN '&nbsp;'
                            ELSE substr(m.midsc,1,38)
                        END                           AS ����̾,      -- 3
                        a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                        (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                      AS ����ñ��,    -- 5
                        Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                      AS ���,        -- 6
                        sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                      AS �������,    -- 7
                        CASE
                            WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                            ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                        END                           AS Ψ��,        -- 8
                        (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                      AS �������2,   -- 9
                        (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                      AS Ψ��,        --10
                        (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                      AS �ײ��ֹ�2,   --11
                        (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                      AS ���ʺ�����,  --12
                        (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                      AS ñ����Ͽ�ֹ�, --13
                        CASE
                            WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                            ELSE substr(a.plan_no,4,5)
                        END                           AS �ײ��ֹ�3    -- 14
                  FROM
                        assembly_schedule as a
                  left outer join
                        miitem as m
                  on a.parts_no=m.mipn
                  left outer join
                        material_cost_header as mate
                  on a.plan_no=mate.plan_no
                  LEFT OUTER JOIN
                        sales_parts_material_history AS pmate
                  ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                  left outer join
                        product_support_master AS groupm
                  on a.parts_no=groupm.assy_no
                  WHERE a.kanryou>=%d AND a.kanryou<=%d AND (a.plan -a.cut_plan) > 0 AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F' AND (a.plan -a.cut_plan - kansei) > 0
                  order by a.kanryou, �ײ��ֹ�3
                  ", $d_start, $d_end);
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>���ͽ��Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    for ($r=0; $r<$rows; $r++) {
        $res[$r][3] = mb_convert_kana($res[$r][3], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
    }
}

for ($r=0; $r<$rows; $r++) {
    $query = "SELECT * FROM month_sales_plan WHERE plan_no='{$res[$r][1]}' AND parts_no='{$res[$r][2]}'";
    if( getResult2($query, $res_chk) > 0 ) {
//        continue;
        $_SESSION['s_sysmsg'] .= "���ͽ��ϴ�����Ͽ����Ƥ����ǽ��������ޤ���";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }

    $set_arr = "";  // ��Ͽ���������
    for ($i=0; $i<$num; $i++) {    // �쥳���ɿ�ʬ���֤�
        if ($i >= 9) break;
        switch ($i) {
        case 7:     // �������
            if ($res[$r][$i] == 0) {
                if ($res[$r][9]) {
                    $set_arr[$i] = $res[$r][9];
                } elseif ($res[$r][12]) {   // ���ʤκ����������å�����ɽ������
                    $set_arr[$i] = $res[$r][12];
                }
            } else {
                $set_arr[$i] = $res[$r][$i];
            }
            break;
        case 8:    // Ψ(�������)
            if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                $set_arr[$i] = $res[$r][$i];
            } elseif ($res[$r][$i] <= 0) {
                if ($res[$r][10]) {
                    $set_arr[$i] = $res[$r][10];
                } elseif ($res[$r][12]) {
                    $set_arr[$i] = number_format($res[$r][5]/$res[$r][12]*100);
                }
            } else {
                $set_arr[$i] = $res[$r][$i];
            }
            break;
        default:    // ����¾
            $set_arr[$i] = $res[$r][$i];
        }
    }

    if( $set_arr[5] == 0 ) {
        if( empty($set_arr[7]) ) {
            $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan ) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}');";
        } else {
            $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan, materials_price ) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}', '{$set_arr[7]}');";
        }
    } else {
        $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan, partition_price, price, materials_price, rate) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}', '{$set_arr[5]}', '{$set_arr[6]}', '{$set_arr[7]}', '{$set_arr[8]}');";
    }
    if( query_affected($insert_qry) <= 0 ) {
        $err_flg = true;
//        $_SESSION['s_sysmsg'] .= "���ͽ����Ͽ���ԡ�({$r}){$set_arr[5]}";
//        $_SESSION['s_sysmsg'] .= $insert_qry;
    }

}
if( $err_flg ) {
    $_SESSION['s_sysmsg'] .= "���ͽ�����Ͽ�˼��Ԥ��Ƥ���쥳���ɤ�����ޤ���";
} else {
    $_SESSION['s_sysmsg'] .= "���ͽ�����Ͽ���������ޤ�����";
}

header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
exit();

