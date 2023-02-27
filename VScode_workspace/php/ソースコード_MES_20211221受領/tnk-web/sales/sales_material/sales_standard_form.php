<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ��� ɸ�������Ѣ����θ���Ψʬ�� �Ȳ� �������ե�����     //
// Copyright (C) 2005-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/02 Created   sales_custom_form.php �� sales_standard_form.php    //
//            �����Ѥ�ɸ��Υ��ץ顦��˥��˥������ޥ���                    //
// 2005/06/06 ����maxlength='4'��maxlength='5'���ѹ�  ���ν�����ѹ�  //
// 2005/06/07 ɸ�������Ѥ��ä��Τ�����������Ǥ���褦���ѹ�              //
// 2005/06/09 �����ʤ���о�郎������������¾��ȴ���Ƥ����Τ���  //
//            ���/���β������/����Ψ����ܤ��ɲ�  �����=999.9��9999  //
//            $where ���ʣ������Ƥ����Τ��� $_SESSION['standard_where'] //
//            ���λ�����������0������å����ɲ�                       //
// 2005/06/14 �ͼθ����ι�׻��θ��������å����ɲ� IT�Ѱ���ǻ�Ŧ       //
// 2005/06/16 ���ʥ��롼�פ����Ρ����ץ����Τ��ɲ� �����ȼ�����å����ѹ� //
// 2005/06/27 ̵�������դξ��last_day()����Ѥ��ƺǽ�����ưŪ�˥��åȤ���//
// 2005/08/25 549�����ն�� $tmp2_uri_ritu �η׻�������                   //
// 2005/09/21 ���ե����å��θ����Ѥ�checkdate(month, day, year)�����       //
// 2005/10/13 �ѥ���ɤΥե������ ʸ���������ѹ� pt12b ����            //
// 2006/09/06 ���ʥ��롼�פ��Ѥ������˥���ռ¹ԥܥ����ä����å����ɲ�//
// 2006/10/02 �ǥ��쥯�ȥ�� sales/ �� sales/sales_material/ ���ѹ�         //
// 2007/11/07 ���1/2/3/4�ξ��� Division by zero �����å����ɲ�           //
// 2008/11/12 ����դˣ�������ɽ�����ɲ�                               ��ë //
// 2010/05/21 ��������ۤι�׼��������߷פ��Ƥ���ͼθ������ä��Τ�      //
//            ���̤˻ͼθ������Ƥ����߷פ���褦���ѹ�                 ��ë //
// 2011/05/24 ����պ��������������ݣ�������ä��Τ���ꤷ��ǯ��(��¦)��  //
//            ���˺�������褦���ѹ������١��ե������ɽ�����ѹ�     ��ë //
// 2011/11/10 �ƥ��Ȥ�NKCT��NKT���ɲ� �� �����ɲ� ���θ���             ��ë //
// 2013/01/29 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                   ��ë //
//            �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2013/01/31 ��˥��Τߤ�DPEȴ��SQL������                             ��ë //
// 2013/05/28 2013/05���NKCT/NKT����夲��ȴ���Ф��ʤ��褦�˽���      ��ë //
// 2015/05/20 ����å�����Ѵ�����껻�ȽŤʤ�CSV���ϥ��顼�ˤʤ�ٽ��� ��ë//
// 2015/05/25 �����������б�                                           ��ë //
// 2016/07/26 �ǥե���Ȥ��ѹ�(0��108,108��113,113��)                  ��ë //
// 2021/03/04 �ʤ���ȴ���Ф�������Ω���׻����绻���ä��Τ���������ˡ��      //
//            �ѹ�                                                     ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1, 14);                    // site_index=01(����˥塼) site_id=14(ɸ�������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���θ���Ψʬ�� �����������ñ��Ψ �������');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������', SALES . 'sales_material/sales_standard_view.php');
$menu->set_action('�����',   SALES . 'sales_material/sales_standard_graph.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

/////////////// ���Ϥ��ѿ��ν����
/************ �ѥ���� **************/
if ( isset($_REQUEST['uri_passwd']) ) {
    $uri_passwd = $_REQUEST['uri_passwd'];
    $_SESSION['s_uri_passwd'] = $uri_passwd;    // ���ζ��̥ѥ���ɻ���
} else {
    if ( isset($_SESSION['s_uri_passwd']) ) {
        $uri_passwd = $_SESSION['s_uri_passwd'];
    } else {
        $uri_passwd = '';
    }
}
/************ ���ʥ��롼�� **************/
if ( isset($_REQUEST['div']) ) {
    $div = $_REQUEST['div'];
    $_SESSION['standard_div'] = $div;
} else {
    if ( isset($_SESSION['standard_div']) ) {
        $div = $_SESSION['standard_div'];
    } else {
        $div = 'A';     // �����(�����롼��)
    }
}
/*********** ���ʥ��롼�פξ��ʸ ���� **************/
if ($div == 'A') $where_div = "������!=' '";        // ���롼������
if ($div == 'C') $where_div = "������='{$div}'";    // ������
//if ($div == 'CH') $where_div = "������='C'";   // ��ɸ��
if ($div == 'CH') $where_div = "������='C' and (assyno not like 'NKB%%') and (assyno not like 'SS%%') and (CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END)";   // ��ɸ��
//if ($div == 'CS') $where_div = "������='C'";        // ��������ɲ�
if ($div == 'CS') $where_div = "������='C' and (assyno not like 'NKB%%') and (assyno not like 'SS%%') and CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END";        // ��������ɲ�
if ($div == 'L') $where_div = "������='{$div}'";    // ������
//if ($div == 'LL') $where_div = "������='L' and assyno NOT LIKE 'LC%' and assyno NOT LIKE 'LR%'"; // �̤Τ�
//if ($div == 'LL') $where_div = "������='L' and assyno NOT LIKE 'LC%' and assyno NOT LIKE 'LR%' and CASE WHEN assyno = '' THEN ������='L' ELSE m.midsc not like 'DPE%%' END"; // �̤Τ�
if ($div == 'LL') $where_div = "������='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%') and (assyno not like 'SS%%') and CASE WHEN assyno = '' THEN ������='L' ELSE CASE WHEN m.midsc IS NULL THEN ������='L' ELSE m.midsc not like 'DPE%%' END END and CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='L' END"; // �̤Τ�
//if ($div == 'LB') $where_div = "������='L' and (assyno LIKE 'LC%' OR assyno LIKE 'LR%')";        // �Х����Τ�
//if ($div == 'LB') $where_div = "������='L' and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";        // �Х����Τ�
if ($div == 'LB') $where_div = "������='L' and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%') and CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='L' END";        // �Х����Τ�
//if ($div == 'NKCT') $where_div = "groupm.support_group_code=1";     // NKCT�Τ�
if ($div == 'T') $where_div = "������='{$div}'";    // ������
if ($div == 'NKCT') $where_div = "CASE WHEN �׾���<20130501 THEN groupm.support_group_code=1 END";     // NKCT�Τ�
//if ($div == 'NKT') $where_div = "groupm.support_group_code=2";      // NKT�Τ�
if ($div == 'NKT') $where_div = "CASE WHEN �׾���<20130501 THEN groupm.support_group_code=2 END";      // NKT�Τ�
$_SESSION['standard_div'] = $div;
$_SESSION['standard_where_div'] = $where_div;

/************ ���� **************/
if ( isset($_REQUEST['d_start']) ) {
    $d_start = $_REQUEST['d_start'];
    ///// day �Υ����å�
    if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
    ///// �ǽ���������å����ƥ��åȤ���
    if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
        $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
        }
    }
    $_SESSION['standard_d_start'] = $d_start;
} else {
    if ( isset($_SESSION['standard_d_start']) ) {
        $d_start = $_SESSION['standard_d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_REQUEST['d_end']) ) {
    $d_end = $_REQUEST['d_end'];
    ///// day �Υ����å�
    if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
    ///// �ǽ���������å����ƥ��åȤ���
    if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
        $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
        }
    }
    $_SESSION['standard_d_end'] = $d_end;
} else {
    if ( isset($_SESSION['standard_d_end']) ) {
        $d_end = $_SESSION['standard_d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
/************ �����ֹ� **************/
if ( isset($_REQUEST['assy_no']) ) {
    $assy_no = $_REQUEST['assy_no'];
    $_SESSION['standard_assy_no'] = $assy_no;
    if ($assy_no != '') {
        $where_assy_no = "and assyno='{$assy_no}'";
    } else {
        $where_assy_no = '';
    }
} else {
    if ( isset($_SESSION['standard_assy_no']) ) {
        $assy_no = $_SESSION['standard_assy_no'];
        if ($assy_no != '') {
            $where_assy_no = "and assyno='{$assy_no}'";
        } else {
            $where_assy_no = '';
        }
    } else {
        $assy_no = '';      // �����
        $where_assy_no = '';
    }
}
$_SESSION['standard_where_assy_no'] = $where_assy_no;
/************ ����ʬ **************/
if ( isset($_REQUEST['kubun']) ) {
    $kubun = $_REQUEST['kubun'];
    $_SESSION['standard_kubun'] = $kubun;
} else {
    if ( isset($_SESSION['standard_kubun']) ) {
        $kubun = $_SESSION['standard_kubun'];
    } else {
        $kubun = '1';
    }
}
/************ �� **************/
if ( isset($_REQUEST['lower_uri_ritu']) ) {
    $lower_uri_ritu = $_REQUEST['lower_uri_ritu'];
    $_SESSION['standard_lower_uri_ritu'] = $lower_uri_ritu;
} else {
    if ( isset($_SESSION['standard_lower_uri_ritu']) ) {
        $lower_uri_ritu = $_SESSION['standard_lower_uri_ritu'];
    } else {
        $lower_uri_ritu = '113.0';   // �����
        //$lower_uri_ritu = '116.0';   // �����
    }
}
if ( isset($_REQUEST['upper_uri_ritu']) ) {
    $upper_uri_ritu = $_REQUEST['upper_uri_ritu'];
    $_SESSION['standard_upper_uri_ritu'] = $upper_uri_ritu;
} else {
    if ( isset($_SESSION['standard_upper_uri_ritu']) ) {
        $upper_uri_ritu = $_SESSION['standard_upper_uri_ritu'];
    } else {
        $upper_uri_ritu = '99999';   // �����
    }
}
/************ �� **************/
if ( isset($_REQUEST['lower_mate_ritu']) ) {
    $lower_mate_ritu = $_REQUEST['lower_mate_ritu'];
    $_SESSION['standard_lower_mate_ritu'] = $lower_mate_ritu;
} else {
    if ( isset($_SESSION['standard_lower_mate_ritu']) ) {
        $lower_mate_ritu = $_SESSION['standard_lower_mate_ritu'];
    } else {
        $lower_mate_ritu = '108.0';     // �����
    }
}
if ( isset($_REQUEST['upper_mate_ritu']) ) {
    $upper_mate_ritu = $_REQUEST['upper_mate_ritu'];
    $_SESSION['standard_upper_mate_ritu'] = $upper_mate_ritu;
} else {
    if ( isset($_SESSION['standard_upper_mate_ritu']) ) {
        $upper_mate_ritu = $_SESSION['standard_upper_mate_ritu'];
    } else {
        $upper_mate_ritu = '113.0';     // �����
        //$upper_mate_ritu = '116.0';     // �����
    }
}
/************ �� **************/
if ( isset($_REQUEST['lower_equal_ritu']) ) {
    $lower_equal_ritu = $_REQUEST['lower_equal_ritu'];
    $_SESSION['standard_lower_equal_ritu'] = $lower_equal_ritu;
} else {
    if ( isset($_SESSION['standard_lower_equal_ritu']) ) {
        $lower_equal_ritu = $_SESSION['standard_lower_equal_ritu'];
    } else {
        $lower_equal_ritu = '0.0';     // �����
    }
}
if ( isset($_REQUEST['upper_equal_ritu']) ) {
    $upper_equal_ritu = $_REQUEST['upper_equal_ritu'];
    $_SESSION['standard_upper_equal_ritu'] = $upper_equal_ritu;
} else {
    if ( isset($_SESSION['standard_upper_equal_ritu']) ) {
        $upper_equal_ritu = $_SESSION['standard_upper_equal_ritu'];
    } else {
        $upper_equal_ritu = '108.0';    // �����
    }
}
/************ ����ɽ���Կ� **************/
// $_SESSION['s_rec_No'] = 0;  // ɽ���ѥ쥳���ɭ��0�ˤ��롣
if ( isset($_REQUEST['sales_page']) ) {
    $sales_page = $_REQUEST['sales_page'];
    $_SESSION['standard_sales_page'] = $sales_page;
} else {
    if ( isset($_SESSION['standard_sales_page']) ) {      // ���ڡ���ɽ���Կ�����
        $sales_page = $_SESSION['standard_sales_page'];   // ��� Default 25 �ˤʤ�褦�˥����Ȳ��
    } else {
        $sales_page = 25;             // Default 25
    }
}

/*************** ���ɽ�Ȳ�Υꥯ�����Ȥ�div��sum_exec��Ƚ�� ****************/
while (isset($_REQUEST['sum_exec'])) {
    ////////////// �ѥ���ɥ����å�
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_self());  // ��ʬ�˥�å�����
        exit();
    }
    ////////////// ����̹��ɽ�Υǡ�������
    /******************* �� ***********************/
    $sql = "select
                    count(����)                 as ���,
                    sum(����)                   as ����,
                    sum(Uround(����*u.ñ��,0))    as �����,
                    sum(Uround((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * ����,0))
                                                as �������
              from
                    hiuuri as u
              left outer join
                    assembly_schedule as assem
                on �ײ��ֹ�=assem.plan_no
              left outer join
                    material_cost_header as mate
                on �ײ��ֹ�=mate.plan_no
              left outer join
                    product_support_master AS groupm
              on assyno=groupm.assy_no
              left outer join
                    miitem as m
              on assyno=m.mipn
    ";
    $_SESSION['costom_sql'] = $sql;         // ����դΤ��᥻�å����˴�����ʬ����¸(���̤�����ۤ����)
    if ($div == 'CH') { // ɸ���ʤʤ�
        $where = "
            where
            �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
            and
            (note15 NOT like 'SC%%' OR note15 IS NULL) {$where_assy_no}
        ";
    } elseif ($div == 'CS') { // ������ʤ�
        $where = "
            where
            �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
            and
            note15 like 'SC%%' {$where_assy_no}
        ";
    } else {            // ���Ρ���˥����Ρ���˥��Τߡ��Х����
        $where = "
            where
            �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
            {$where_assy_no}
        ";
    }
    $_SESSION['standard_where'] = $where;    // ����ɽ�Τ��᥻�å�������¸
    /*
    $condition = "and
                    (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) > 0
                and
                    (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) >= {$lower_uri_ritu}
                and
                    (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) <= {$upper_uri_ritu}
    ";
    */
    $condition = "and
                    (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) > 0
                and
                    (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) >= {$lower_uri_ritu}
                and
                    (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) <= {$upper_uri_ritu}
    ";
    $_SESSION['standard_where1'] = ($where . $condition); // ����ɽ�Τ��᥻�å�������¸
    $_SESSION['standard_condition1'] = $condition;        // ����դΤ���ξ����¸
    $query = ($sql . $where . $condition);              // ����
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
    }
    if ($res_sum[0]['���'] <= 0) {
        // $_SESSION['s_sysmsg'] .= '���ꤵ�줿���ǤϾ��˹��פ���ǡ����Ϥ���ޤ���';
        $sum1_ken = 0;
        $sum1_suu = 0;
        $sum1_uri = 0;
        $sum1_sou = 0;
        $sum1_rit = 0;
        $sum1_sik = 0;
    } else {
        $sum1_ken = $res_sum[0]['���'];
        $sum1_suu = $res_sum[0]['����'];
        $sum1_uri = $res_sum[0]['�����'];
        $sum1_sou = $res_sum[0]['�������'];
        if ($sum1_uri == 0) {       // Division by zero �б� 2007/11/07
            $sum1_rit = 0;
        } else {
            $sum1_rit = Uround($sum1_sou / $sum1_uri * 100, 1);
        }
        if ($sum1_sou == 0) {       // Division by zero �б� 2007/11/07
            $sum1_sik = 0;
        } else {
            $sum1_sik = Uround($sum1_uri / $sum1_sou * 100, 1);
        }
    }
    ////////// ��פη׻�
    $sum_ken = $sum1_ken;
    $sum_suu = $sum1_suu;
    $sum_uri = $sum1_uri;
    $sum_sou = $sum1_sou;
    $uri1 = $sum1_uri;  // �����ꤹ�����˿��ͤ���¸
    $sum1_ken = number_format($sum1_ken);
    $sum1_suu = number_format($sum1_suu);
    $sum1_uri = number_format($sum1_uri);
    $sum1_sou = number_format(Uround($sum1_sou, 0));
    if ($sum1_rit > 100.0) {
        $sum1_rit = "<font style='color:red;'>" . number_format($sum1_rit, 1) . '��' . '</font>';
    } else {
        $sum1_rit = number_format($sum1_rit, 1) . '��';
    }
    if ($sum1_sik < 100.0 && $sum1_sik > 0.0) {
        $sum1_sik = "<font style='color:red;'>" . number_format($sum1_sik, 1) . '��' . '</font>';
    } else {
        $sum1_sik = number_format($sum1_sik, 1) . '��';
    }
    /******************* �� ***********************/
    /*
    $condition = "and
                    (
                        ((mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) < {$lower_uri_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) > {$upper_uri_ritu}
                    )
                and
                    (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) > 0
                and
                    (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) >= {$lower_mate_ritu}
                and
                    (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) <= {$upper_mate_ritu}
    ";
    */
    $condition = "and
                    (
                        ((mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_uri_ritu}
                    or
                        (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_uri_ritu}
                    )
                and
                    (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) > 0
                and
                    (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) >= {$lower_mate_ritu}
                and
                    (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) <= {$upper_mate_ritu}
    ";
    $_SESSION['standard_where2'] = ($where . $condition); // ����ɽ�Τ��᥻�å�������¸
    $_SESSION['standard_condition2'] = $condition;        // ����դΤ���ξ����¸
    $query = ($sql . $where . $condition);              // ����
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
    }
    if ($res_sum[0]['���'] <= 0) {
        // $_SESSION['s_sysmsg'] .= '���ꤵ�줿���ǤϾ��˹��פ���ǡ����Ϥ���ޤ���';
        $sum2_ken = 0;
        $sum2_suu = 0;
        $sum2_uri = 0;
        $sum2_sou = 0;
        $sum2_rit = 0;
        $sum2_sik = 0;
    } else {
        $sum2_ken = $res_sum[0]['���'];
        $sum2_suu = $res_sum[0]['����'];
        $sum2_uri = $res_sum[0]['�����'];
        $sum2_sou = $res_sum[0]['�������'];
        if ($sum2_uri == 0) {       // Division by zero �б� 2007/11/07
            $sum2_rit = 0;
        } else {
            $sum2_rit = Uround($sum2_sou / $sum2_uri * 100, 1);
        }
        if ($sum2_sou == 0) {       // Division by zero �б� 2007/11/07
            $sum2_sik = 0;
        } else {
            $sum2_sik = Uround($sum2_uri / $sum2_sou * 100, 1);
        }
    }
    ////////// ��פη׻�
    $sum_ken += $sum2_ken;
    $sum_suu += $sum2_suu;
    $sum_uri += $sum2_uri;
    $sum_sou += $sum2_sou;
    $uri2 = $sum2_uri;  // �����ꤹ�����˿��ͤ���¸
    $sum2_ken = number_format($sum2_ken);
    $sum2_suu = number_format($sum2_suu);
    $sum2_uri = number_format($sum2_uri);
    $sum2_sou = number_format(Uround($sum2_sou, 0));
    if ($sum2_rit > 100.0) {
        $sum2_rit = "<font style='color:red;'>" . number_format($sum2_rit, 1) . '��' . '</font>';
    } else {
        $sum2_rit = number_format($sum2_rit, 1) . '��';
    }
    if ($sum2_sik < 100.0 && $sum2_sik > 0.0) {
        $sum2_sik = "<font style='color:red;'>" . number_format($sum2_sik, 1) . '��' . '</font>';
    } else {
        $sum2_sik = number_format($sum2_sik, 1) . '��';
    }
    /******************* �� ***********************/
    /*
    $condition = "and
                    (
                        ((mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) < {$lower_uri_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) > {$upper_uri_ritu}
                    )
                and
                    (
                        ((mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) < {$lower_mate_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) > {$upper_mate_ritu}
                    )
                and
                    (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) > 0
                and
                    (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) >= {$lower_equal_ritu}
                and
                    (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) <= {$upper_equal_ritu}
    ";
    */
    $condition = "and
                    (
                        ((mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_uri_ritu}
                    or
                        (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_uri_ritu}
                    )
                and
                    (
                        ((mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_mate_ritu}
                    or
                        (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_mate_ritu}
                    )
                and
                    (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) > 0
                and
                    (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) >= {$lower_equal_ritu}
                and
                    (Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) <= {$upper_equal_ritu}
    ";
    $_SESSION['standard_where3'] = ($where . $condition); // ����ɽ�Τ��᥻�å�������¸
    $_SESSION['standard_condition3'] = $condition;        // ����դΤ���ξ����¸
    $query = ($sql . $where . $condition);              // ����
    $res_sum = array();
    if (getResult($query, $res_sum) < 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
    }
    if ($res_sum[0]['���'] <= 0) {
        // $_SESSION['s_sysmsg'] .= '���ꤵ�줿���ǤϾ��˹��פ���ǡ����Ϥ���ޤ���';
        $sum3_ken = 0;
        $sum3_suu = 0;
        $sum3_uri = 0;
        $sum3_sou = 0;
        $sum3_rit = 0;
        $sum3_sik = 0;
    } else {
        $sum3_ken = $res_sum[0]['���'];
        $sum3_suu = $res_sum[0]['����'];
        $sum3_uri = $res_sum[0]['�����'];
        $sum3_sou = $res_sum[0]['�������'];
        if ($sum3_uri == 0) {       // Division by zero �б� 2007/11/07
            $sum3_rit = 0;
        } else {
            $sum3_rit = Uround($sum3_sou / $sum3_uri * 100, 1);
        }
        if ($sum3_sou == 0) {       // Division by zero �б� 2007/11/07
            $sum3_sik = 0;
        } else {
            $sum3_sik = Uround($sum3_uri / $sum3_sou * 100, 1);
        }
    }
    ////////// ��פη׻�
    $sum_ken += $sum3_ken;
    $sum_suu += $sum3_suu;
    $sum_uri += $sum3_uri;
    $sum_sou += $sum3_sou;
    $uri3 = $sum3_uri;  // �����ꤹ�����˿��ͤ���¸
    $sum3_ken = number_format($sum3_ken);
    $sum3_suu = number_format($sum3_suu);
    $sum3_uri = number_format($sum3_uri);
    $sum3_sou = number_format(Uround($sum3_sou, 0));
    if ($sum3_rit > 100.0) {
        $sum3_rit = "<font style='color:red;'>" . number_format($sum3_rit, 1) . '��' . '</font>';
    } else {
        $sum3_rit = number_format($sum3_rit, 1) . '��';
    }
    if ($sum3_sik < 100.0 && $sum3_sik > 0.0) {
        $sum3_sik = "<font style='color:red;'>" . number_format($sum3_sik, 1) . '��' . '</font>';
    } else {
        $sum3_sik = number_format($sum3_sik, 1) . '��';
    }
    /******************* ���嵭�ʳ��λĤ����� ***********************/
    /*
    $condition = "and
                    (
                        ((mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) < {$lower_uri_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) > {$upper_uri_ritu}
                    )
                and
                    (
                        ((mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) < {$lower_mate_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) > {$upper_mate_ritu}
                    )
                and
                    (
                        ((mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) < {$lower_equal_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100) > {$upper_equal_ritu}
                    )
    ";
    */
    $condition = "and
                    (
                        ((mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_uri_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_uri_ritu}
                    )
                and
                    (
                        ((mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_mate_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_mate_ritu}
                    )
                and
                    (
                        ((mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 or mate.sum_price IS NULL)
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_equal_ritu}
                    or
                        (Uround(u.ñ�� / (mate.sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_equal_ritu}
                    )
    ";
    $_SESSION['standard_where4'] = ($where . $condition); // ����ɽ�Τ��᥻�å�������¸
    $_SESSION['standard_condition4'] = $condition;        // ����դΤ���ξ����¸
    $query = ($sql . $where . $condition);              // ����
    $res_sum = array();
    if (getResult($query, $res_sum) < 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
    }
    if ($res_sum[0]['���'] <= 0) {
        $sum4_ken = 0;
        $sum4_suu = 0;
        $sum4_uri = 0;
        $sum4_sou = 0;
        $sum4_rit = 0;
        $sum4_sik = 0;
    } else {
        $sum4_ken = $res_sum[0]['���'];
        $sum4_suu = $res_sum[0]['����'];
        $sum4_uri = $res_sum[0]['�����'];
        $sum4_sou = $res_sum[0]['�������'];
        if ($sum4_uri == 0) {       // Division by zero �б� 2007/11/07
            $sum4_rit = 0;
        } else {
            $sum4_rit = Uround($sum4_sou / $sum4_uri * 100, 1);
        }
        if ($sum4_sou > 0) {    // 0������å�
            $sum4_sik = Uround($sum4_uri / $sum4_sou * 100, 1);
        } else {
            $sum4_sik = 0;
        }
    }
    ////////// ��פη׻�
    $sum_ken += $sum4_ken;
    $sum_suu += $sum4_suu;
    $sum_uri += $sum4_uri;
    $sum_sou += $sum4_sou;
    ////////// ���ϺǸ�ʤΤ����Τη׻��⤹��
    if ($sum_uri <= 0) {
        $sum_rit = 0;
    } else {
        $sum_rit = Uround($sum_sou / $sum_uri * 100, 1);
    }
    if ($sum_sou <= 0) {
        $sum_sik = 0;
    } else {
        $sum_sik = Uround($sum_uri / $sum_sou * 100, 1);
    }
    $uri4 = $sum4_uri;  // �����ꤹ�����˿��ͤ���¸
    $sum4_ken = number_format($sum4_ken);
    $sum4_suu = number_format($sum4_suu);
    $sum4_uri = number_format($sum4_uri);
    $sum4_sou = number_format(Uround($sum4_sou, 0));
    if ($sum4_rit > 100.0) {
        $sum4_rit = "<font style='color:red;'>" . number_format($sum4_rit, 1) . '��' . '</font>';
    } else {
        $sum4_rit = number_format($sum4_rit, 1) . '��';
    }
    if ($sum4_sik < 100.0 && $sum4_sik > 0.0) {
        $sum4_sik = "<font style='color:red;'>" . number_format($sum4_sik, 1) . '��' . '</font>';
    } else {
        $sum4_sik = number_format($sum4_sik, 1) . '��';
    }
    /***************** ���פν����� *******************/
    $uri = $sum_uri;    // �����ꤹ�����˿��ͤ���¸
    $sum_ken = number_format($sum_ken);
    $sum_suu = number_format($sum_suu);
    $sum_uri = number_format($sum_uri);
    $sum_sou = number_format(Uround($sum_sou, 0));
    if ($sum_rit > 100.0) {
        $sum_rit = "<font style='color:red;'>" . number_format($sum_rit, 1) . '��' . '</font>';
    } else {
        $sum_rit = number_format($sum_rit, 1) . '��';
    }
    if ($sum_sik < 100.0 && $sum_sik > 0.0) {
        $sum_sik = "<font style='color:red;'>" . number_format($sum_sik, 1) . '��' . '</font>';
    } else {
        $sum_sik = number_format($sum_sik, 1) . '��';
    }
    /***************** �����/�������*100 %��׻� *******************/
    if ($uri > 0) {
        $tmp1_uri_ritu = Uround($uri1 / $uri * 100, 1);
        /*************** �ͼθ����ι�׻��θ��������å� *****************/
        if ($uri2 == 0) {
            $tmp2_uri_ritu = 0;
        } elseif ($uri3 == 0 && $uri4 == 0)  {
            $tmp2_uri_ritu = (100.0 - ($tmp1_uri_ritu));
        } else {
            $tmp2_uri_ritu = Uround($uri2 / $uri * 100, 1);
        }
        if ($uri3 == 0) {
            $tmp3_uri_ritu = 0;
        } elseif ($uri4 == 0)  {
            $tmp3_uri_ritu = (100.0 - ($tmp1_uri_ritu + $tmp2_uri_ritu));
        } else {
            $tmp3_uri_ritu = Uround($uri3 / $uri * 100, 1);
        }
        if ($uri4 == 0) {
            $tmp4_uri_ritu = 0;
        } else {
            $tmp4_uri_ritu = (100.0 - ($tmp1_uri_ritu + $tmp2_uri_ritu + $tmp3_uri_ritu));
        }
        /*************** ��פǣ�������ˤʤ�Τ��ǧ������å� *****************/
        $tmp_uri_ritu  = ($tmp1_uri_ritu + $tmp2_uri_ritu + $tmp3_uri_ritu + $tmp4_uri_ritu);
        /*************** �ͼθ����������å� END ****************/
        $sum1_uri_ritu = number_format($tmp1_uri_ritu, 1) . '��';   // ��
        $sum2_uri_ritu = number_format($tmp2_uri_ritu, 1) . '��';   // ��
        $sum3_uri_ritu = number_format($tmp3_uri_ritu, 1) . '��';   // ��
        $sum4_uri_ritu = number_format($tmp4_uri_ritu, 1) . '��';   // ����¾
        $sum_uri_ritu  = number_format($tmp_uri_ritu , 1) . '��';   // ����
    } else {
        $sum1_uri_ritu = '0.0��';
        $sum2_uri_ritu = '0.0��';
        $sum3_uri_ritu = '0.0��';
        $sum4_uri_ritu = '0.0��';
        $sum_uri_ritu  = '0.0��';
    }
    ////////// �֥�å���λ
    break;
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>
<!--    �ե��������ξ�� -->
<script language='JavaScript' src='./sales_standard_form.js?<?= $uniq ?>'>
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
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
.pt12br {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     right;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size: 10pt;
}
.sum {
    font-size:          12pt;
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
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>

<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    document.select_form.uri_passwd.focus();      // �ѥ�������ϰ��֤�
    document.select_form.uri_passwd.select();
}
function graph_exec(sel) {
    switch (sel) {
    case 1:
        document.graph_form.graph_exec.value = '1';
        break;
    case 2:
        document.graph_form.graph_exec.value = '2';
        break;
    case 3:
        document.graph_form.graph_exec.value = '3';
        break;
    case 4:
        document.graph_form.graph_exec.value = '4';
        break;
    default:
        return FALSE;
    }
    document.graph_form.submit();
}
// -->
</script>
<form name='graph_form' action='<?=$menu->out_action('�����')?>' method='get'>
    <input type='hidden' name='graph_exec' value=''>
</form>
</head>

<body onLoad='set_focus()' style='overflow:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <form name='select_form' action='<?=$menu->out_self()?>' method='post' onSubmit='return chk_select_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' style='background-color:blue; color:yellow;' colspan='2' align='center'>
                        <div class='caption_font'><?=$menu->out_caption()?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �ѥ���ɤ�����Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='password' name='uri_passwd' size='12' value='<?php echo $uri_passwd ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ʥ��롼�פ����򤷤Ʋ�������
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' class='pt12b' onChange='document.getElementById("graphExecForm").innerHTML = "&nbsp;";'>
                            <option value='A' <?php if ($div=='A') echo 'selected'?>>�������Ρ�</option>
                            <option value='C' <?php if ($div=='C') echo 'selected'?>>���ץ�����</option>
                            <option value='CH' <?php if ($div=='CH') echo 'selected'?>>���ץ�ɸ��</option>
                            <option value='CS' <?php if ($div=='CS') echo 'selected'?>>���ץ�����</option>
                            <option value='L' <?php if ($div=='L') echo 'selected'?>>��˥�����</option>
                            <option value='LL' <?php if ($div=='LL') echo 'selected'?>>��˥��Τ�</option>
                            <option value='LB' <?php if ($div=='LB') echo 'selected'?>>���Υݥ��</option>
                            <option value='T' <?php if ($div=='T') echo 'selected'?>>�ġ���</option>
                            <option value='NKCT' <?php if ($div=='NKCT') echo 'selected'?>>�Σˣã�</option>
                            <option value='NKT' <?php if ($div=='NKT') echo 'selected'?>>�Σˣ�</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���դ���ꤷ�Ʋ�����(ɬ��)<BR>
                        �� �������դ�ǯ�����մ���
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='d_start' size='8' class='pt12b' value='<?php echo $d_start ?>' maxlength='8'>
                        ��
                        <input type='text' name='d_end' size='8' class='pt12b' value='<?php echo $d_end ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���
                        (���ꤷ�ʤ����϶���)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='9' class='pt12b' value='<?= $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ����ʬ =�������� (����Τ߾Ȳ��ǽ)
                    </td>
                    <td class='winbox' align='center'>
                        <select name='kubun'>
                            <option value='1' selected>1����</option>
                        <select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='left' style='font-size:11pt;font-weight:bold;'>
                        ��<br>
                        ���������Ф������ñ����Ψ�ϰϣ�������(�㡧113.0��9999)<br>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lower_uri_ritu' size='4' class='pt12br' value='<?=$lower_uri_ritu?>' maxlength='5'>
                        �� ��
                        <input type='text' name='upper_uri_ritu' size='4' class='pt12br' value='<?=$upper_uri_ritu?>' maxlength='5'>
                        ��
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='left' style='font-size:11pt;font-weight:bold;'>
                        ��<br>
                        ���������Ф������ñ����Ψ�ϰϣ�������(�㡧108.0��113.0)<br>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lower_mate_ritu' size='4' class='pt12br' value='<?=$lower_mate_ritu?>' maxlength='5'>
                        �� ��
                        <input type='text' name='upper_mate_ritu' size='4' class='pt12br' value='<?=$upper_mate_ritu?>' maxlength='5'>
                        ��
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='left' style='font-size:11pt;font-weight:bold;'>
                        ��<br>
                        ���������Ф������ñ����Ψ�ϰ�(������)(�㡧0.0��108.0)<br>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lower_equal_ritu' size='4' class='pt12br' value='<?=$lower_equal_ritu?>' maxlength='5'>
                        �� ��
                        <input type='text' name='upper_equal_ritu' size='4' class='pt12br' value='<?=$upper_equal_ritu?>' maxlength='5'>
                        ��
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ڡ�����ɽ���Կ�����ꤷ�Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='sales_page' size='3' value='<?php echo $sales_page ?>' maxlength='3' style='text-align:center;'>
                        ����͡�25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='1' align='center' id='graphExecForm'>
                        <?php if (isset($_REQUEST['sum_exec'])) { ?>
                        <input type='button' name='graph1' value='��������' onClick='graph_exec(1)'>
                        <input type='button' name='graph2' value='��������' onClick='graph_exec(2)'>
                        <input type='button' name='graph3' value='12������' onClick='graph_exec(3)'>
                        <input type='button' name='graph3' value='18������' onClick='graph_exec(4)'>
                        <?php } ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </td>
                    <td class='winbox' colspan='1' align='center'>
                        <input type='submit' name='sum_exec' value='���ɽ�Ȳ�'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        <?php if (isset($_REQUEST['sum_exec'])) { ?>
        <br>
        <form name='sum_form' action='<?=$menu->out_action('�������')?>' method='get'>
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <th class='winbox' width= '70'>���ɽ</th>
                <th class='winbox'>�Ȳ�</th>
                <th class='winbox' width= '70'>��׷��</th>
                <th class='winbox' width= '90'>��׿���</th>
                <th class='winbox' width='140'>��������(��)</th>
                <th class='winbox' width= '90'>���/����<br>*100</th>
                <th class='winbox' width='140'>����������(��)</th>
                <th class='winbox' width= '90'>���/���<br>*100</th>
                <th class='winbox' width= '90'>���/���<br>*100</th>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>��</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view1' value='����' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_sik?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>��</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view2' value='����' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_sik?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>��</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view3' value='����' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_sik?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>����¾</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view4' value='����' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_sik?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>������</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view' value='����' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_sik?></div>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        <?php } ?>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
