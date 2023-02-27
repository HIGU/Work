<?php
//////////////////////////////////////////////////////////////////////////////
// ��˥����ڸ�ľ�� ������������ư��Ͽ��ǧ�� �Ȳ��˥塼                 //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/02/12 Created  materialCheckLinear_ViewBody.php                     //
//                     (materialCheck_ViewBody.php���¤                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');                // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');                // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');              // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class

///// ���å����Υ��󥹥��󥹤����
$session = new Session();
///// �ꥯ�����ȤΥ��󥹥��󥹤����
$request = new Request();
if ($request->get('recNo') != '') {
    $session->add_local('recNo', $request->get('recNo'));
    exit();
}
if ($request->get('page_keep') == '') $session->add_local('recNo', -1);
// access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(-1);                 // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_INDUST, 999);         // site_index=30(������˥塼) site_id=999(̤��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�������θ�ľ����ǧ��');
//////////// ɽ�������
$menu->set_caption('�������θ�ľ����ǧ��');
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��
//////////// ��ʬ��ե졼��������Ѥ���
$menu->set_self(INDUST . 'materialCheck/materialCheckLinear_Main.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��������ɽ��ɽ��',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('��������ɽ��ɽ��',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('����������Ͽ',     INDUST . 'material/material_entry/materialCost_entry_main.php');
$menu->set_action('������������',     INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('�����������',       INDUST . 'material/materialCost_view.php');
//////////// �꥿���󥢥ɥ쥹�ؤ�GET�ǡ��������å�
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = $menu->set_useNotCache('mtcheck');

//////////// ���ǤιԿ�
define('PAGE', '200');      // �Ȥꤢ����

//////////// �оݥǡ����μ���
$query = "
    SELECT
        u.assyno                    AS �����ֹ�
        ,
        trim(substr(m.midsc,1,30))  AS ����̾
        ,
        u.�ײ��ֹ�                  AS �ײ��ֹ�
        ,
        (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.�ײ��ֹ�)
                                    AS �������
        ,
        (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS �������Ͽ��
        ,
        (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno LIMIT 1)
                                    AS �ǿ�����ñ��
        ,
        (SELECT to_char(regdate, 'FM9999/99/99') FROM sales_price_nk WHERE parts_no = u.assyno LIMIT 1)
                                    AS ������Ͽ��
        ,
        CASE
            WHEN to_char(mate.regdate, 'HH24:MI:SS') = '00:00:00' THEN '��ư'
            ELSE '��ư'
        END                                                       AS ��Ͽ
    FROM
          hiuuri AS u
    LEFT OUTER JOIN
          assembly_schedule AS a
    ON (u.�ײ��ֹ� = a.plan_no)
    LEFT OUTER JOIN
          miitem AS m
    ON (u.assyno = m.mipn)
    LEFT OUTER JOIN
          material_old_product AS mate
    ON (u.assyno = mate.assy_no)
    WHERE �׾��� >= 20080101 AND �׾��� <= 20080131 AND ������ = 'L' AND datatype='1'
    ORDER BY u.assyno ASC
    OFFSET 0 LIMIT 5000
";
if ( ($rows=getResult2($query, $res)) <= 0) {
    $session->add('s_sysmsg', '�оݥǡ���������ޤ���');
}

////////// ���ߤδ��ǯ����
define('LIMIT_YMD', '2008/01/01');

////////// ������񤬼�ư�����Ϥ���Ƥ��뤫�����å�
function manualInputCheck($planNo)
{
    $query = "
        SELECT 
            CASE
                WHEN to_char(regdate, 'HH24:MI:SS') = '00:00:00' THEN '��ư'
                ELSE '��ư'
            END
        FROM material_cost_header
        WHERE plan_no = '{$planNo}' LIMIT 1
    ";
    if (getResult2($query, $res) <= 0) {
        return false;
    } else {
        if ($res[0][0] == '��ư') {
            return true;
        } else {
            $query = "
                SELECT 
                    CASE
                        WHEN to_char(last_date, 'YYYY/MM/DD') > '2008/02/11' THEN '��ư'
                        ELSE '��ư'
                    END
                FROM material_cost_header
                WHERE plan_no = '{$planNo}' LIMIT 1
            ";
            if (getResult2($query, $res) <= 0) {
                return false;
            } else {
                if ($res[0][0] == '��ư') {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
}

///////////// HTML Header ����Ϥ��ƥ���å��������
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
<link rel='stylesheet' href='materialCheck.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='materialCheck.js?<?php echo $uniq ?>'></script> -->

<script type='text/javascript'>
<!--
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         scroll;
}
-->
</style>
</head>
<body>
    <center>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            for ($r=0; $r<$rows; $r++) {
                ///// ���֥륯��å����оݳ�
                $oldProduct = "onDblClick='if (confirm(\"�оݳ��ˤ��ޤ���������Ǥ�����\")) { baseJS.Ajax(\"materialCheck_ViewBody.php?recNo={$r}\");location.replace(\"materialCheck_ViewBody.php?assyNo=" . urlencode($res[$r][0]) . "&del=yes&page_keep=on&id={$uniq}#mark\");}'";
                if ($session->get_local('recNo') == $r) {
                    echo "<tr style='background-color:#ffffc6;' {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 5%' nowrap align='right'><a name='mark' style='color:black;'>", ($r + 1), "</a></td>\n";
                } elseif (!manualInputCheck($res[$r][2])) {
                    echo "<tr style='background-color:#e6e6e6;' {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 5%' nowrap align='right'>", ($r + 1), "</td>\n";
                } else {
                    echo "<tr {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 5%' nowrap align='right'>", ($r + 1), "</td>\n";
                }
                echo "<td class='winbox pt11b' width=' 8%' align='center'><a href='JavaScript:baseJS.Ajax(\"materialCheck_ViewBody.php?recNo={$r}\");location.replace(\"{$menu->out_action('������������')}?assy=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][0]}</a></td>\n";
                echo "<td class='winbox pt11 ' width='35%' align='left'  >{$res[$r][1]}</td>\n";
                echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][2]}</td>\n";
                echo "<td class='winbox pt11 ' width='10%' align='right' >", number_format($res[$r][3], 2), "</td>\n";
                if (manualInputCheck($res[$r][2])) {
                    echo "<td class='winbox pt11b' width=' 5%' align='center'>��</td>\n";
                } else {
                    echo "<td class='winbox pt11b' width=' 5%' align='center'>��</td>\n";
                }
                echo "<td class='winbox pt11 ' width='10%' align='right' >", number_format($res[$r][5], 2), "</td>\n";
                echo "<td class='winbox pt11b' width='10%' align='center'>{$res[$r][6]}</td>\n";
                echo "</tr>\n";
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
