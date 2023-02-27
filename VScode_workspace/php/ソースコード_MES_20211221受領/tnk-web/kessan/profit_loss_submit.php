<?php
//////////////////////////////////////////////////////////////////////////////
// �»�׽�����SUBMIT Branch(ʬ��)����                                    //
// Copyright (C) 2003-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2003/01/17 Created   profit_loss_submit.php                              //
// 2003/01/27 AS/400 �Ȥθ��̥ǡ�����󥯥�˥塼�ɲä��б�                 //
// 2003/02/07 �ǡ�������������ȥե������ɲä��б�                          //
// 2003/02/22 ê����Ĵ����������(��������ݥǡ���)Ĵ���ץ�����ɲ�        //
// 2003/02/24 ��帶��Ĵ�����ϤΥ�˥塼���ɲ�                              //
// 2003/02/26 ��̳�������������Ϥ��˥塼���ɲ�                            //
// 2003/03/07 �Ȳ��˥塼���̤�URL���֤�������ƽФ�Ȥ�URL����¸          //
// 2003/03/10 �����Ĵ�����ϥ�˥塼���ɲ�                                //
// 2003/05/01 ����Ĺ����λؼ���ǧ�ڤ�Account_group�����̾���ѹ�           //
// 2003/08/05 ����Ĺ����λؼ���ǧ�ڤ��̾狼��Account_group���᤹           //
//              ����Ĺ�οͷ���ʬ���뤿��                                  //
// 2003/09/27 � ���ê��ɽ(�ǡ�������ߤȾȲ�)���ɲ�                     //
// 2003/10/10 � �ǡ����κ�������ɲ� profit_loss_clear.php?pl_table=     //
// 2003/10/15 ������ץ��̤˥桼�������¤Υ����å����ѹ� ���ê��ɽ�Ȳ�     //
// 2003/11/27 tnk-turbine.gif �Υ��˥᡼�������ɲ�                        //
// 2004/05/06 ��ƥ��ǻ��ꤷ�Ƥ���'kessan/'�� define���줿 PL ���ѹ�      //
// 2007/10/10 ���������� »�׷׻���Υǡ��������˥塼���ɲ�             //
// 2008/10/07 CL���񺹳����ɽ���ɲ�                                   ��ë //
// 2009/08/18 ʪή���»����Ͽ���ɲá�11��                                //
//            ���������̼�����ѹ�(11��12)                           ��ë //
// 2009/08/19 ʪή�򾦴ɤ��ѹ�                                         ��ë //
//            ��ã̾�����»�׾Ȳ���ɲ�                               ��ë //
// 2009/08/20 ��CL����������ɽ�Ȳ���ɲ�                                  //
//            ��˥塼�ɲäΰ١��쥤�����Ȥ�Ĵ��                       ��ë //
// 2009/08/21 �£̡������ ������»�׾Ȳ���ɲ�                      ��ë //
// 2009/12/09 ������ʣã̡˾�����»�׾Ȳ���ɲ�                     ��ë //
// 2010/01/15 »�����������ɽ���ɲ�                                   ��ë //
// 2010/01/19 �����ȾȲ�Ǵ������¤�ʬ����褦���ѹ� ��������˥塼��  ��ë //
//            �ɲä���ݤ�ǧ�ڤ�ʬ��ˤ��ɲä��뤳��                   ��ë //
// 2012/01/16 �������ɽ�ξȲ���ɲ�                                   ��ë //
// 2015/06/04 BL��LT���ѹ�                                             ��ë //
// 2015/06/15 LT»�פ�2015ǯ4��餷�������ʤ��褦���ѹ�              ��ë //
// 2016/07/13 CLT������»�פ��ɲ�                                      ��ë //
// 2016/07/25 �����������»�פ�ã̤����ѵס��������ѹ�             ��ë //
// 2017/09/08 ��¤�����׻����ɲ�                                       ��ë //
// 2017/11/09 ����»�׽�����10��ǰ��ǹԤä�»�׾Ȳ���ɲ�           ��ë //
// 2018/05/29 �軻������ɲáʼ�ʬ�Τߡ�                             ��ë //
// 2018/06/12 �����������ɽ���ɲáʼ�ʬ�Τߡ�                         ��ë //
// 2020/01/27 ��������������ɽ���ɲ�                                   ��ë //
// 2020/06/12 ��������������ٽ���ɲ�                                 ��ë //
// 2021/05/31 2021/04��꾦����»�פ�ʬ�� 2021/04�ʹ߾Ȳ�ϥġ���ʤ���     //
//            2021/03�����ξȲ�ǥġ���ɽ��                            ��ë //
// 2021/08/02 $_SESSION['2ki_ym']�Υ��顼���б�                        ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name �ϼ�ư����
if (isset($_POST['pl_ym'])) {
    $_SESSION['pl_ym'] = $_POST['pl_ym'];                   // �о�ǯ��򥻥å�������¸
}
if (isset($_POST['2ki_ym'])) {
    $_SESSION['2ki_ym'] = $_POST['2ki_ym'];                   // �о�ǯ��򥻥å�������¸
}

$_SESSION['pl_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸

////////// ʬ����Υ�����ץ�̾�����
if ($_SESSION['2ki_ym'] >= 202104) {
    if ($_SESSION['pl_ym'] >= 202104) {
        switch ($_POST['pl_name']) {
            case '1 AS/400��TNK'            : $script_name = 'profit_loss_ftp_to_db_all.php'; break;
            case '2 CL����Ψ�׻�'           : $script_name = 'profit_loss_cl_allocation.php'; break;
            case '3 ��������¹�'           : $script_name = 'profit_loss_cl_keihi_save.php'; break;
            case '4 ê��������'             : $script_name = 'profit_loss_inventory_put.php'; break;
            case '5 ê����Ĵ��'             : $script_name = 'profit_loss_adjust_invent.php'; break;
            case '6 ������Ĵ��'             : $script_name = 'profit_loss_adjust_shiire.php'; break;
            case '7 ��帶��Ĵ��'           : $script_name = 'profit_loss_adjust_ugenka.php'; break;
            case '8 ��̳��������'           : $script_name = 'profit_loss_gyoumu_put.php'   ; break;
            case '9 ����Ĵ��'             : $script_name = 'profit_loss_adjust_uriage.php'; break;
            case '10 �ã�»�׷׻�'          : $script_name = 'profit_loss_pl_act_save.php'  ; break;
            case '11 ���ɡ��»����Ͽ'    : $script_name = 'profit_loss_nkb_input.php'    ; break;
            //case '12 ���������̼��'    : $script_name = 'pl_segment/pl_segment_get_form.php'   ; break;
            case '12 �̿Ͱ���Ψ�׻�'        : $script_name = 'profit_loss_bls_input.php'    ; break;
            case '13 �ÿͰ���Ψ�׻�'        : $script_name = 'profit_loss_ctoku_input.php'  ; break;
            case '14 ���ҿͰ���Ψ�׻�'      : $script_name = 'profit_loss_staff_input.php'  ; break;
            
            case '�����������'                     : $script_name = 'profit_loss_keihi.php'            ; break;
            case '�ã̡����� �������ɽ'            : $script_name = 'profit_loss_cl_keihi.php'         ; break;
            case '����������»��'                 : $script_name = 'profit_loss_pl_act.php'           ; break;
            case '����������»��10����'         : $script_name = 'profit_loss_pl_act10.php'         ; break;
            case '����������»�׵���'             : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '�߼��о�ɽ'                       : $script_name = 'profit_loss_bs_act.php'           ; break;
            case '�ã�ͽ����»��'                   : $script_name = 'profit_loss_select.php'           ; break;
            case '����Ψ�׻�ɽ'                     : $script_name = 'profit_loss_cost_rate.php'        ; break;
            case '��������ɽ'                       : $script_name = 'profit_loss_select.php'           ; break;
            case '�ã̷��񺹳����ɽ'               : $script_name = 'profit_loss_cl_keihi_compare.php' ; break;
            case '�£̡�� ������»��'            : $script_name = 'profit_loss_pl_act_bls.php'       ; break;
            case '����ɸ�� ������»��'            : $script_name = 'profit_loss_pl_act_ctoku.php'     ; break;
            case '������� ������»��'            : $script_name = 'profit_loss_pl_act_ss.php'        ; break;
            case '��ã̾�����»��'                 : $script_name = 'profit_loss_pl_act_old.php'       ; break;
            case '��ã̷������ɽ'                 : $script_name = 'profit_loss_cl_keihi_old.php'     ; break;
            case '�£� ������»��'                  : $script_name = 'profit_loss_pl_act_bl.php'        ; break;
            case '»�����������ɽ'                 : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '�����̥ƥ���'                     : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '�̣� ������»��'                  : $script_name = 'profit_loss_pl_act_lt.php'        ; break;
            case '�ạ̃ԡ�������� ������»��'    : $script_name = 'profit_loss_pl_act_all.php'       ; break;
            case '�������Ȳ�'                     : $script_name = 'profit_loss_sales_view.php'       ; break;
            
            case '���������ǡ���' : $script_name = 'profit_loss_ftp_to_db_D.php'    ; break;
            case '�ã̷���ǡ���' : $script_name = 'profit_loss_ftp_to_db_B.php'    ; break;
            case '�������������' : $script_name = 'profit_loss_ftp_to_db_B1.php'   ; break;
            case '������ݥǡ���' : $script_name = 'profit_loss_ftp_to_db_E.php'    ; break;
            case '�ã�»�ץǡ���' : $script_name = 'profit_loss_ftp_to_db_AC.php'   ; break;
            case '�߼��оȥǡ���' : $script_name = 'profit_loss_ftp_to_db_F.php'    ; break;
            
            case '��ʿ��ê������' : $script_name = 'profit_loss_invent_gross_average.php'   ; break;
            
            case 'CL����Clear'    : $script_name = 'profit_loss_clear.php?pl_table=allo_history'; break;
            case '��������Clear'  : $script_name = 'profit_loss_clear.php?pl_table=cl_history'  ; break;
            case 'CL»��Clear'    : $script_name = 'profit_loss_clear.php?pl_table=pl_history'  ; break;
            
            case '���ê��ɽ'   : $script_name = 'invent_comp/invent_comp_view.php'     ; break;
            case '�ǡ��������' : $script_name = 'invent_comp/invent_comp_get_form.php' ; break;
            
            case '��������������ɽ'   : $script_name = 'depreciation_statement/depreciation_statement_view.php'     ; break;
            case '��������������ɽ���' : $script_name = 'depreciation_statement/depreciation_statement_get_form.php' ; break;
            
            // �������ɽ
            case '���� �ܷ軻»��ɽ'    : $script_name = 'profit_loss_pl_act_2ki.php'   ; break;
            case '���� �߼��о�ɽ'      : $script_name = 'profit_loss_bs_act_2ki.php'   ; break;
            case '���� �ã̾�����»��'  : $script_name = 'profit_loss_pl_act_2ki_cl.php'; break;
            case '���� �����������'    : $script_name = 'profit_loss_keihi_2ki.php'    ; break;
            case '��¤�����׻�'         : $script_name = 'manufacture_cost_total.php'   ; break;
            case '�軻����'           : $script_name = 'financial_report_view.php'    ; break;
            case '�����������ɽ'       : $script_name = 'account_transfer_view.php'    ; break;
            case '���������'         : $script_name = 'machine_production_view.php'  ; break;
            case '��������������ٽ�'   : $script_name = 'account_statement_view.php'    ; break;
            
            // �����ǿ����
            case '̤ʧ��׾������'    : $script_name = 'sales_tax_miharai_view.php'   ; break;
            case '���Ǽ�ճ�ǧ'        : $script_name = 'sales_tax_chukan_view.php'   ; break;
            case '�����ǽ���ɽ'        : $script_name = 'sales_tax_zeishukei_view.php'   ; break;
            case '�����ǳ۷׻�ɽ'      : $script_name = 'sales_tax_koujyo_view.php'   ; break;
            case '���������׻�ɽ'      : $script_name = 'sales_tax_syozei_allo_view.php'   ; break;
            case '�����ǿ������'      : $script_name = 'sales_tax_syozei_shinkoku_view.php'   ; break;
            case '���꿽�����1ɽ'     : $script_name = 'print/sales_tax_kakutei_shinkoku1_pdf.php'   ; break;
            case '��2ɽ'               : $script_name = 'print/sales_tax_kakutei_shinkoku2_pdf.php'   ; break;
            case '��ɽ1-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-1_pdf.php'   ; break;
            case '��ɽ1-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-2_pdf.php'   ; break;
            case '��ɽ2-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-1_pdf.php'   ; break;
            case '��ɽ2-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-2_pdf.php'   ; break;
            
            default: $script_name = 'profit_loss_select.php';       // �ƽФ�Ȥص���
                 $url_name    = $_SESSION['pl_referer'];        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
        }
    } else {
        switch ($_POST['pl_name']) {
            case '1 AS/400��TNK'            : $script_name = 'profit_loss_ftp_to_db_all.php'; break;
            case '2 CL����Ψ�׻�'           : $script_name = 'profit_loss_cl_allocation.php'; break;
            case '3 ��������¹�'           : $script_name = 'profit_loss_cl_keihi_save.php'; break;
            case '4 ê��������'             : $script_name = 'profit_loss_inventory_put.php'; break;
            case '5 ê����Ĵ��'             : $script_name = 'profit_loss_adjust_invent.php'; break;
            case '6 ������Ĵ��'             : $script_name = 'profit_loss_adjust_shiire.php'; break;
            case '7 ��帶��Ĵ��'           : $script_name = 'profit_loss_adjust_ugenka.php'; break;
            case '8 ��̳��������'           : $script_name = 'profit_loss_gyoumu_put.php'   ; break;
            case '9 ����Ĵ��'             : $script_name = 'profit_loss_adjust_uriage.php'; break;
            case '10 �ã�»�׷׻�'          : $script_name = 'profit_loss_pl_act_save.php'  ; break;
            case '11 ���ɡ��»����Ͽ'    : $script_name = 'profit_loss_nkb_input.php'    ; break;
            //case '12 ���������̼��'    : $script_name = 'pl_segment/pl_segment_get_form.php'   ; break;
            case '12 �̿Ͱ���Ψ�׻�'        : $script_name = 'profit_loss_bls_input.php'    ; break;
            case '13 �ÿͰ���Ψ�׻�'        : $script_name = 'profit_loss_ctoku_input.php'  ; break;
            case '14 ���ҿͰ���Ψ�׻�'      : $script_name = 'profit_loss_staff_input.php'  ; break;
            
            case '�����������'                     : $script_name = 'profit_loss_keihi.php'            ; break;
            case '�ã̡����� �������ɽ'            : $script_name = 'profit_loss_cl_keihi.php'         ; break;
            case '����������»��'                 : $script_name = 'profit_loss_pl_act_t-bk.php'       ; break;
            case '����������»��10����'         : $script_name = 'profit_loss_pl_act10.php'         ; break;
            case '����������»�׵���'             : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '�߼��о�ɽ'                       : $script_name = 'profit_loss_bs_act.php'           ; break;
            case '�ã�ͽ����»��'                   : $script_name = 'profit_loss_select.php'           ; break;
            case '����Ψ�׻�ɽ'                     : $script_name = 'profit_loss_cost_rate.php'        ; break;
            case '��������ɽ'                       : $script_name = 'profit_loss_select.php'           ; break;
            case '�ã̷��񺹳����ɽ'               : $script_name = 'profit_loss_cl_keihi_compare.php' ; break;
            case '�£̡�� ������»��'            : $script_name = 'profit_loss_pl_act_bls.php'       ; break;
            case '����ɸ�� ������»��'            : $script_name = 'profit_loss_pl_act_ctoku.php'     ; break;
            case '������� ������»��'            : $script_name = 'profit_loss_pl_act_ss.php'        ; break;
            case '��ã̾�����»��'                 : $script_name = 'profit_loss_pl_act_old.php'       ; break;
            case '��ã̷������ɽ'                 : $script_name = 'profit_loss_cl_keihi_old.php'     ; break;
            case '�£� ������»��'                  : $script_name = 'profit_loss_pl_act_bl.php'        ; break;
            case '»�����������ɽ'                 : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '�����̥ƥ���'                     : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '�̣� ������»��'                  : $script_name = 'profit_loss_pl_act_lt.php'        ; break;
            case '�ạ̃ԡ�������� ������»��'    : $script_name = 'profit_loss_pl_act_all.php'       ; break;
            case '�������Ȳ�'                     : $script_name = 'profit_loss_sales_view.php'       ; break;
            
            case '���������ǡ���' : $script_name = 'profit_loss_ftp_to_db_D.php'    ; break;
            case '�ã̷���ǡ���' : $script_name = 'profit_loss_ftp_to_db_B.php'    ; break;
            case '�������������' : $script_name = 'profit_loss_ftp_to_db_B1.php'   ; break;
            case '������ݥǡ���' : $script_name = 'profit_loss_ftp_to_db_E.php'    ; break;
            case '�ã�»�ץǡ���' : $script_name = 'profit_loss_ftp_to_db_AC.php'   ; break;
            case '�߼��оȥǡ���' : $script_name = 'profit_loss_ftp_to_db_F.php'    ; break;
            
            case '��ʿ��ê������' : $script_name = 'profit_loss_invent_gross_average.php'   ; break;
            
            case 'CL����Clear'    : $script_name = 'profit_loss_clear.php?pl_table=allo_history'; break;
            case '��������Clear'  : $script_name = 'profit_loss_clear.php?pl_table=cl_history'  ; break;
            case 'CL»��Clear'    : $script_name = 'profit_loss_clear.php?pl_table=pl_history'  ; break;
            
            case '���ê��ɽ'   : $script_name = 'invent_comp/invent_comp_view.php'     ; break;
            case '�ǡ��������' : $script_name = 'invent_comp/invent_comp_get_form.php' ; break;
            
            case '��������������ɽ'   : $script_name = 'depreciation_statement/depreciation_statement_view.php'     ; break;
            case '��������������ɽ���' : $script_name = 'depreciation_statement/depreciation_statement_get_form.php' ; break;
            
            // �������ɽ
            case '���� �ܷ軻»��ɽ'    : $script_name = 'profit_loss_pl_act_2ki.php'   ; break;
            case '���� �߼��о�ɽ'      : $script_name = 'profit_loss_bs_act_2ki.php'   ; break;
            case '���� �ã̾�����»��'  : $script_name = 'profit_loss_pl_act_2ki_cl.php'; break;
            case '���� �����������'    : $script_name = 'profit_loss_keihi_2ki.php'    ; break;
            case '��¤�����׻�'         : $script_name = 'manufacture_cost_total.php'   ; break;
            case '�軻����'           : $script_name = 'financial_report_view.php'    ; break;
            case '�����������ɽ'       : $script_name = 'account_transfer_view.php'    ; break;
            case '���������'         : $script_name = 'machine_production_view.php'  ; break;
            case '��������������ٽ�'   : $script_name = 'account_statement_view.php'    ; break;
            
            // �����ǿ����
            case '̤ʧ��׾������'    : $script_name = 'sales_tax_miharai_view.php'   ; break;
            case '���Ǽ�ճ�ǧ'        : $script_name = 'sales_tax_chukan_view.php'   ; break;
            case '�����ǽ���ɽ'        : $script_name = 'sales_tax_zeishukei_view.php'   ; break;
            case '�����ǳ۷׻�ɽ'      : $script_name = 'sales_tax_koujyo_view.php'   ; break;
            case '���������׻�ɽ'      : $script_name = 'sales_tax_syozei_allo_view.php'   ; break;
            case '�����ǿ������'      : $script_name = 'sales_tax_syozei_shinkoku_view.php'   ; break;
            case '���꿽�����1ɽ'     : $script_name = 'print/sales_tax_kakutei_shinkoku1_pdf.php'   ; break;
            case '��2ɽ'               : $script_name = 'print/sales_tax_kakutei_shinkoku2_pdf.php'   ; break;
            case '��ɽ1-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-1_pdf.php'   ; break;
            case '��ɽ1-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-2_pdf.php'   ; break;
            case '��ɽ2-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-1_pdf.php'   ; break;
            case '��ɽ2-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-2_pdf.php'   ; break;
            
            default: $script_name = 'profit_loss_select.php';       // �ƽФ�Ȥص���
                 $url_name    = $_SESSION['pl_referer'];        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
        }
    }
} else {
    if ($_SESSION['pl_ym'] >= 202104) {
        switch ($_POST['pl_name']) {
            case '1 AS/400��TNK'            : $script_name = 'profit_loss_ftp_to_db_all.php'; break;
            case '2 CL����Ψ�׻�'           : $script_name = 'profit_loss_cl_allocation.php'; break;
            case '3 ��������¹�'           : $script_name = 'profit_loss_cl_keihi_save.php'; break;
            case '4 ê��������'             : $script_name = 'profit_loss_inventory_put.php'; break;
            case '5 ê����Ĵ��'             : $script_name = 'profit_loss_adjust_invent.php'; break;
            case '6 ������Ĵ��'             : $script_name = 'profit_loss_adjust_shiire.php'; break;
            case '7 ��帶��Ĵ��'           : $script_name = 'profit_loss_adjust_ugenka.php'; break;
            case '8 ��̳��������'           : $script_name = 'profit_loss_gyoumu_put.php'   ; break;
            case '9 ����Ĵ��'             : $script_name = 'profit_loss_adjust_uriage.php'; break;
            case '10 �ã�»�׷׻�'          : $script_name = 'profit_loss_pl_act_save.php'  ; break;
            case '11 ���ɡ��»����Ͽ'    : $script_name = 'profit_loss_nkb_input.php'    ; break;
            //case '12 ���������̼��'    : $script_name = 'pl_segment/pl_segment_get_form.php'   ; break;
            case '12 �̿Ͱ���Ψ�׻�'        : $script_name = 'profit_loss_bls_input.php'    ; break;
            case '13 �ÿͰ���Ψ�׻�'        : $script_name = 'profit_loss_ctoku_input.php'  ; break;
            case '14 ���ҿͰ���Ψ�׻�'      : $script_name = 'profit_loss_staff_input.php'  ; break;
            
            case '�����������'                     : $script_name = 'profit_loss_keihi.php'            ; break;
            case '�ã̡����� �������ɽ'            : $script_name = 'profit_loss_cl_keihi.php'         ; break;
            case '����������»��'                 : $script_name = 'profit_loss_pl_act.php'           ; break;
            case '����������»��10����'         : $script_name = 'profit_loss_pl_act10.php'         ; break;
            case '����������»�׵���'             : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '�߼��о�ɽ'                       : $script_name = 'profit_loss_bs_act.php'           ; break;
            case '�ã�ͽ����»��'                   : $script_name = 'profit_loss_select.php'           ; break;
            case '����Ψ�׻�ɽ'                     : $script_name = 'profit_loss_cost_rate.php'        ; break;
            case '��������ɽ'                       : $script_name = 'profit_loss_select.php'           ; break;
            case '�ã̷��񺹳����ɽ'               : $script_name = 'profit_loss_cl_keihi_compare.php' ; break;
            case '�£̡�� ������»��'            : $script_name = 'profit_loss_pl_act_bls.php'       ; break;
            case '����ɸ�� ������»��'            : $script_name = 'profit_loss_pl_act_ctoku.php'     ; break;
            case '������� ������»��'            : $script_name = 'profit_loss_pl_act_ss.php'        ; break;
            case '��ã̾�����»��'                 : $script_name = 'profit_loss_pl_act_old.php'       ; break;
            case '��ã̷������ɽ'                 : $script_name = 'profit_loss_cl_keihi_old.php'     ; break;
            case '�£� ������»��'                  : $script_name = 'profit_loss_pl_act_bl.php'        ; break;
            case '»�����������ɽ'                 : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '�����̥ƥ���'                     : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '�̣� ������»��'                  : $script_name = 'profit_loss_pl_act_lt.php'        ; break;
            case '�ạ̃ԡ�������� ������»��'    : $script_name = 'profit_loss_pl_act_all.php'       ; break;
            case '�������Ȳ�'                     : $script_name = 'profit_loss_sales_view.php'       ; break;
            
            case '���������ǡ���' : $script_name = 'profit_loss_ftp_to_db_D.php'    ; break;
            case '�ã̷���ǡ���' : $script_name = 'profit_loss_ftp_to_db_B.php'    ; break;
            case '�������������' : $script_name = 'profit_loss_ftp_to_db_B1.php'   ; break;
            case '������ݥǡ���' : $script_name = 'profit_loss_ftp_to_db_E.php'    ; break;
            case '�ã�»�ץǡ���' : $script_name = 'profit_loss_ftp_to_db_AC.php'   ; break;
            case '�߼��оȥǡ���' : $script_name = 'profit_loss_ftp_to_db_F.php'    ; break;
            
            case '��ʿ��ê������' : $script_name = 'profit_loss_invent_gross_average.php'   ; break;
            
            case 'CL����Clear'    : $script_name = 'profit_loss_clear.php?pl_table=allo_history'; break;
            case '��������Clear'  : $script_name = 'profit_loss_clear.php?pl_table=cl_history'  ; break;
            case 'CL»��Clear'    : $script_name = 'profit_loss_clear.php?pl_table=pl_history'  ; break;
            
            case '���ê��ɽ'   : $script_name = 'invent_comp/invent_comp_view.php'     ; break;
            case '�ǡ��������' : $script_name = 'invent_comp/invent_comp_get_form.php' ; break;
            
            case '��������������ɽ'   : $script_name = 'depreciation_statement/depreciation_statement_view.php'     ; break;
            case '��������������ɽ���' : $script_name = 'depreciation_statement/depreciation_statement_get_form.php' ; break;
            
            // �������ɽ
            case '���� �ܷ軻»��ɽ'    : $script_name = 'profit_loss_pl_act_2ki.php'   ; break;
            case '���� �߼��о�ɽ'      : $script_name = 'profit_loss_bs_act_2ki.php'   ; break;
            case '���� �ã̾�����»��'  : $script_name = 'profit_loss_pl_act_2ki_cl.php'; break;
            case '���� �����������'    : $script_name = 'profit_loss_keihi_2ki.php'    ; break;
            case '��¤�����׻�'         : $script_name = 'manufacture_cost_total.php'   ; break;
            case '�軻����'           : $script_name = 'financial_report_view.php'    ; break;
            case '�����������ɽ'       : $script_name = 'account_transfer_view.php'    ; break;
            case '���������'         : $script_name = 'machine_production_view.php'  ; break;
            case '��������������ٽ�'   : $script_name = 'account_statement_view.php'    ; break;
            
            // �����ǿ����
            case '̤ʧ��׾������'    : $script_name = 'sales_tax_miharai_view.php'   ; break;
            case '���Ǽ�ճ�ǧ'        : $script_name = 'sales_tax_chukan_view.php'   ; break;
            case '�����ǽ���ɽ'        : $script_name = 'sales_tax_zeishukei_view.php'   ; break;
            case '�����ǳ۷׻�ɽ'      : $script_name = 'sales_tax_koujyo_view.php'   ; break;
            case '���������׻�ɽ'      : $script_name = 'sales_tax_syozei_allo_view.php'   ; break;
            case '�����ǿ������'      : $script_name = 'sales_tax_syozei_shinkoku_view.php'   ; break;
            case '���꿽�����1ɽ'     : $script_name = 'print/sales_tax_kakutei_shinkoku1_pdf.php'   ; break;
            case '��2ɽ'               : $script_name = 'print/sales_tax_kakutei_shinkoku2_pdf.php'   ; break;
            case '��ɽ1-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-1_pdf.php'   ; break;
            case '��ɽ1-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-2_pdf.php'   ; break;
            case '��ɽ2-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-1_pdf.php'   ; break;
            case '��ɽ2-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-2_pdf.php'   ; break;
            
            default: $script_name = 'profit_loss_select.php';       // �ƽФ�Ȥص���
                     $url_name    = $_SESSION['pl_referer'];        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
        }
    } else {
        switch ($_POST['pl_name']) {
            case '1 AS/400��TNK'            : $script_name = 'profit_loss_ftp_to_db_all.php'; break;
            case '2 CL����Ψ�׻�'           : $script_name = 'profit_loss_cl_allocation.php'; break;
            case '3 ��������¹�'           : $script_name = 'profit_loss_cl_keihi_save.php'; break;
            case '4 ê��������'             : $script_name = 'profit_loss_inventory_put.php'; break;
            case '5 ê����Ĵ��'             : $script_name = 'profit_loss_adjust_invent.php'; break;
            case '6 ������Ĵ��'             : $script_name = 'profit_loss_adjust_shiire.php'; break;
            case '7 ��帶��Ĵ��'           : $script_name = 'profit_loss_adjust_ugenka.php'; break;
            case '8 ��̳��������'           : $script_name = 'profit_loss_gyoumu_put.php'   ; break;
            case '9 ����Ĵ��'             : $script_name = 'profit_loss_adjust_uriage.php'; break;
            case '10 �ã�»�׷׻�'          : $script_name = 'profit_loss_pl_act_save.php'  ; break;
            case '11 ���ɡ��»����Ͽ'    : $script_name = 'profit_loss_nkb_input.php'    ; break;
            //case '12 ���������̼��'    : $script_name = 'pl_segment/pl_segment_get_form.php'   ; break;
            case '12 �̿Ͱ���Ψ�׻�'        : $script_name = 'profit_loss_bls_input.php'    ; break;
            case '13 �ÿͰ���Ψ�׻�'        : $script_name = 'profit_loss_ctoku_input.php'  ; break;
            case '14 ���ҿͰ���Ψ�׻�'      : $script_name = 'profit_loss_staff_input.php'  ; break;
            
            case '�����������'                     : $script_name = 'profit_loss_keihi.php'            ; break;
            case '�ã̡����� �������ɽ'            : $script_name = 'profit_loss_cl_keihi.php'         ; break;
            case '����������»��'                 : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '����������»��10����'         : $script_name = 'profit_loss_pl_act10.php'         ; break;
            case '����������»�׵���'             : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '�߼��о�ɽ'                       : $script_name = 'profit_loss_bs_act.php'           ; break;
            case '�ã�ͽ����»��'                   : $script_name = 'profit_loss_select.php'           ; break;
            case '����Ψ�׻�ɽ'                     : $script_name = 'profit_loss_cost_rate.php'        ; break;
            case '��������ɽ'                       : $script_name = 'profit_loss_select.php'           ; break;
            case '�ã̷��񺹳����ɽ'               : $script_name = 'profit_loss_cl_keihi_compare.php' ; break;
            case '�£̡�� ������»��'            : $script_name = 'profit_loss_pl_act_bls.php'       ; break;
            case '����ɸ�� ������»��'            : $script_name = 'profit_loss_pl_act_ctoku.php'     ; break;
            case '������� ������»��'            : $script_name = 'profit_loss_pl_act_ss.php'        ; break;
            case '��ã̾�����»��'                 : $script_name = 'profit_loss_pl_act_old.php'       ; break;
            case '��ã̷������ɽ'                 : $script_name = 'profit_loss_cl_keihi_old.php'     ; break;
            case '�£� ������»��'                  : $script_name = 'profit_loss_pl_act_bl.php'        ; break;
            case '»�����������ɽ'                 : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '�����̥ƥ���'                     : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '�̣� ������»��'                  : $script_name = 'profit_loss_pl_act_lt.php'        ; break;
            case '�ạ̃ԡ�������� ������»��'    : $script_name = 'profit_loss_pl_act_all.php'       ; break;
            case '�������Ȳ�'                     : $script_name = 'profit_loss_sales_view.php'       ; break;
            
            case '���������ǡ���' : $script_name = 'profit_loss_ftp_to_db_D.php'    ; break;
            case '�ã̷���ǡ���' : $script_name = 'profit_loss_ftp_to_db_B.php'    ; break;
            case '�������������' : $script_name = 'profit_loss_ftp_to_db_B1.php'   ; break;
            case '������ݥǡ���' : $script_name = 'profit_loss_ftp_to_db_E.php'    ; break;
            case '�ã�»�ץǡ���' : $script_name = 'profit_loss_ftp_to_db_AC.php'   ; break;
            case '�߼��оȥǡ���' : $script_name = 'profit_loss_ftp_to_db_F.php'    ; break;
            
            case '��ʿ��ê������' : $script_name = 'profit_loss_invent_gross_average.php'   ; break;
            
            case 'CL����Clear'    : $script_name = 'profit_loss_clear.php?pl_table=allo_history'; break;
            case '��������Clear'  : $script_name = 'profit_loss_clear.php?pl_table=cl_history'  ; break;
            case 'CL»��Clear'    : $script_name = 'profit_loss_clear.php?pl_table=pl_history'  ; break;
            
            case '���ê��ɽ'   : $script_name = 'invent_comp/invent_comp_view.php'     ; break;
            case '�ǡ��������' : $script_name = 'invent_comp/invent_comp_get_form.php' ; break;
            
            case '��������������ɽ'   : $script_name = 'depreciation_statement/depreciation_statement_view.php'     ; break;
            case '��������������ɽ���' : $script_name = 'depreciation_statement/depreciation_statement_get_form.php' ; break;
            
            // �������ɽ
            case '���� �ܷ軻»��ɽ'    : $script_name = 'profit_loss_pl_act_2ki.php'   ; break;
            case '���� �߼��о�ɽ'      : $script_name = 'profit_loss_bs_act_2ki.php'   ; break;
            case '���� �ã̾�����»��'  : $script_name = 'profit_loss_pl_act_2ki_cl.php'; break;
            case '���� �����������'    : $script_name = 'profit_loss_keihi_2ki.php'    ; break;
            case '��¤�����׻�'         : $script_name = 'manufacture_cost_total.php'   ; break;
            case '�軻����'           : $script_name = 'financial_report_view.php'    ; break;
            case '�����������ɽ'       : $script_name = 'account_transfer_view.php'    ; break;
            case '���������'         : $script_name = 'machine_production_view.php'  ; break;
            case '��������������ٽ�'   : $script_name = 'account_statement_view.php'    ; break;
            
            // �����ǿ����
            case '̤ʧ��׾������'    : $script_name = 'sales_tax_miharai_view.php'   ; break;
            case '���Ǽ�ճ�ǧ'        : $script_name = 'sales_tax_chukan_view.php'   ; break;
            case '�����ǽ���ɽ'        : $script_name = 'sales_tax_zeishukei_view.php'   ; break;
            case '�����ǳ۷׻�ɽ'      : $script_name = 'sales_tax_koujyo_view.php'   ; break;
            case '���������׻�ɽ'      : $script_name = 'sales_tax_syozei_allo_view.php'   ; break;
            case '�����ǿ������'      : $script_name = 'sales_tax_syozei_shinkoku_view.php'   ; break;
            case '���꿽�����1ɽ'     : $script_name = 'print/sales_tax_kakutei_shinkoku1_pdf.php'   ; break;
            case '��2ɽ'               : $script_name = 'print/sales_tax_kakutei_shinkoku2_pdf.php'   ; break;
            case '��ɽ1-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-1_pdf.php'   ; break;
            case '��ɽ1-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-2_pdf.php'   ; break;
            case '��ɽ2-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-1_pdf.php'   ; break;
            case '��ɽ2-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-2_pdf.php'   ; break;
            
            default: $script_name = 'profit_loss_select.php';       // �ƽФ�Ȥص���
                     $url_name    = $_SESSION['pl_referer'];        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
        }
    }
}
// �����ϤΥץ�����ǧ�ڤ�ʬ����
$auth = 0;  // �����å����ѿ������ 1=�Ȳ� 2=����
switch ($_POST['pl_name']) {
    case '1 AS/400��TNK'            : $auth = 2 ; break;
    case '2 CL����Ψ�׻�'           : $auth = 2 ; break;
    case '3 ��������¹�'           : $auth = 2 ; break;
    case '4 ê��������'             : $auth = 2 ; break;
    case '5 ê����Ĵ��'             : $auth = 2 ; break;
    case '6 ������Ĵ��'             : $auth = 2 ; break;
    case '7 ��帶��Ĵ��'           : $auth = 2 ; break;
    case '8 ��̳��������'           : $auth = 2 ; break;
    case '9 ����Ĵ��'             : $auth = 2 ; break;
    case '10 �ã�»�׷׻�'          : $auth = 2 ; break;
    case '11 ���ɡ��»����Ͽ'    : $auth = 2 ; break;
    //case '12 ���������̼��'    : $auth = 2 ; break;
    case '12 �̿Ͱ���Ψ�׻�'        : $auth = 2 ; break;
    case '13 �ÿͰ���Ψ�׻�'        : $auth = 2 ; break;
    case '14 ���ҿͰ���Ψ�׻�'      : $auth = 2 ; break;
    
    case '�����������'             : $auth = 1 ; break;
    case '�ã̡����� �������ɽ'    : $auth = 1 ; break;
    case '����������»��'         : $auth = 1 ; break;
    case '����������»��10����' : $auth = 1 ; break;
    case '����������»�׵���'     : $auth = 1 ; break;
    case '�߼��о�ɽ'               : $auth = 1 ; break;
    case '�ã�ͽ����»��'           : $auth = 1 ; break;
    case '����Ψ�׻�ɽ'             : $auth = 1 ; break;
    case '��������ɽ'               : $auth = 1 ; break;
    case '�ã̷��񺹳����ɽ'       : $auth = 1 ; break;
    case '�£̡�� ������»��'    : $auth = 1 ; break;
    case '����ɸ�� ������»��'    : $auth = 1 ; break;
    case '������� ������»��'    : $auth = 1 ; break;
    case '��ã̾�����»��'         : $auth = 1 ; break;
    case '��ã̷������ɽ'         : $auth = 1 ; break;
    case '�£� ������»��'          : $auth = 1 ; break;
    case '»�����������ɽ'         : $auth = 1 ; break;
    case '�����̥ƥ���'             : $auth = 1 ; break;
    case '�̣� ������»��'          : $auth = 1 ; break;
    case '�ạ̃ԡ�������� ������»��'    : $auth = 1 ; break;
    case '�������Ȳ�'             : $auth = 1 ; break;
    
    case '���������ǡ���'   : $auth = 2 ; break;
    case '�ã̷���ǡ���'   : $auth = 2 ; break;
    case '�������������'   : $auth = 2 ; break;
    case '������ݥǡ���'   : $auth = 2 ; break;
    case '�ã�»�ץǡ���'   : $auth = 2 ; break;
    case '�߼��оȥǡ���'   : $auth = 2 ; break;
    
    case '��ʿ��ê������'   : $auth = 2 ; break;
    
    case 'CL����Clear'      : $auth = 2 ; break;
    case '��������Clear'    : $auth = 2 ; break;
    case 'CL»��Clear'      : $auth = 2 ; break;
    
    case '���ê��ɽ'       : $auth = 1 ; break;
    case '�ǡ��������'     : $auth = 2 ; break;
    
    case '��������������ɽ'       : $auth = 1 ; break;
    case '��������������ɽ���'     : $auth = 2 ; break;
    
    // �������ɽ
    case '���� �ܷ軻»��ɽ'    : $auth = 1 ; break;
    case '���� �߼��о�ɽ'      : $auth = 1 ; break;
    case '���� �ã̾�����»��'  : $auth = 1 ; break;
    case '���� �����������'    : $auth = 1 ; break;
    case '��¤�����׻�'         : $auth = 1 ; break;
    case '�軻����'           : $auth = 1 ; break;
    case '�����������ɽ'       : $auth = 1 ; break;
    case '���������'         : $auth = 1 ; break;
    case '��������������ٽ�'   : $auth = 1 ; break;
    
    // �����ǿ����
    case '̤ʧ��׾������'    : $auth = 1 ; break;
    case '���Ǽ�ճ�ǧ'        : $auth = 1 ; break;
    case '�����ǽ���ɽ'        : $auth = 1 ; break;
    case '�����ǳ۷׻�ɽ'      : $auth = 1 ; break;
    case '���������׻�ɽ'      : $auth = 1 ; break;
    case '�����ǿ������'      : $auth = 1 ; break;
    case '���꿽�����1ɽ'     : $auth = 1 ; break;
    case '��2ɽ'               : $auth = 1 ; break;
    case '��ɽ1-1'             : $auth = 1 ; break;
    case '��ɽ1-2'             : $auth = 1 ; break;
    case '��ɽ2-1'             : $auth = 1 ; break;
    case '��ɽ2-2'             : $auth = 1 ; break;
    
    default: $script_name = 'profit_loss_select.php';       // �ƽФ�Ȥص���
             $url_name    = $_SESSION['pl_referer'];        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
}

////////// ������ץȤˤ�äƥ桼�����θ��¥����å����Ѥ���
//if ($script_name == 'profit_loss_cost_rate.php') {      // ����Ψ�׻�ɽ
//    if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//        header("Location: " . $_SESSION['pl_referer']);
//        exit();
//    }
//} elseif ($script_name == 'profit_loss_bs_act.php') {   // �߼��о�ɽ
//    if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//        header("Location: " . $_SESSION['pl_referer']);
//        exit();
//    }
//} elseif ($script_name == 'getsuji_comp_invent.php') {  // ����ê��ɽ
//    if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//        header("Location: " . $_SESSION['pl_referer']);
//        exit();
//    }
//} else {
//    if (account_group_check() == FALSE) {               // �嵭�ʳ��ϥ�������ȥ��롼��
//        $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
//        // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
//        header("Location: " . $_SESSION['pl_referer']);
//        exit();
//    }
//}
if ($auth == 1) {
    if (account_group_check() == FALSE) {               // �嵭�ʳ��ϥ�������ȥ��롼��
        $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
        // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        header("Location: " . $_SESSION['pl_referer']);
        exit();
    }
} elseif ($auth == 2) {
    if (!getCheckAuthority(31)) {
        $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
        // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        header("Location: " . $_SESSION['pl_referer']);
        exit();
    }
}
if ($_POST['pl_name'] == '�̣� ������»��') {
    if ($_POST['pl_ym'] <= 201503) {               // �嵭�ʳ��ϥ�������ȥ��롼��
        $_SESSION["s_sysmsg"] = "LT������»�פ�2015ǯ4���Ǥ���";
        // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        header("Location: " . $_SESSION['pl_referer']);
        exit();
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>�»��ʬ������</title>
<style type='text/css'>
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</head>
<body>
    <center>
        ������Ǥ������Ԥ���������<br>
        <img src='../img/tnk-turbine.gif' width=68 height=72>
    </center>

    <script language="JavaScript">
    <!--
    <?php
        if (isset($url_name)) {
            echo "location = '$url_name'";
        } else {
            // echo "location = 'http:" . WEB_HOST . "kessan/" . "$script_name'";
            echo "location = '" . H_WEB_HOST . PL . "$script_name'";
        }
    ?>
    // -->
    </script>
</body>
</html>
