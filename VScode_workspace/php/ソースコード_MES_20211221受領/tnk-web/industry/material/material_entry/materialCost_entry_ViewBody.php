<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ͽ materialCost_entry_ViewBody.php                           //
// Copyright (C) 2007-2019 Norihisa.Ohya                                    //
// Changed history                                                          //
// 2007/05/23 Created   materialCost_entry_ViewBody.php                     //
// 2007/06/19 ��������ֹ�-1���ֹ�˥ޡ�������Ĥ���褦���ѹ�              //
// 2007/06/21 php���硼�ȥ�����ɸ�ॿ���ء� HTML��;ʬ�ʥ��������� ����   //
//            $menu->out_retF2Script() �ɲ� �ֹ楯��å������Υ���å��ؾ���//
// 2007/06/22 $uniq�Σ����������������                                   //
// 2007/09/18 E_ALL | E_STRICT ���ѹ� ����                                  //
// 2019/05/18 mark�����ޤ�ȿ�����ʤ��ä���tr�ǤϤʤ�No�ˤĤ���褦�ѹ�      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 21);                    // site_index=30(������˥塼) site_id=21(����������Ͽ)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� Ͽ (��������)');
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��
//////////// ��ʬ��ե졼��������Ѥ���
//$menu->set_self(INDUST . 'material/materialCost_entry_main.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����������Ͽ',   INDUST . 'material/materialCost_entry_old.php');
//////////// ����ؤ�GET�ǡ�������
$menu->set_retGET('page_keep', 'On');

$request = new Request;
$session = new Session;
//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// ���ǤιԿ�
define('PAGE', '300');

//////////// ��å��������ϥե饰
$msg_flg = 'site';

//////////// ���顼���ν�����
$error_log_name = '/tmp/materialCost_entry_error.log';

//////////// �ײ��ֹ桦�����ֹ�����
$plan_no = $session->get('plan_no');
$assy_no = $session->get('assy_no');

//////////// �������κǿ���Ͽ������������ֹ����
if (substr($plan_no, 0, 2) == 'ZZ') $menu->set_retGET('assy', $assy_no);

//////////// ����ǡ����Υ��ԡ��ܥ��󤬲����줿���
if ($request->get('pre_copy') != '') {
    $query = "SELECT midsc FROM miitem WHERE mipn='{$assy_no}'";
    if ( getUniResult($query, $assy_name) <= 0) {           // ����̾�μ���
        $_SESSION['s_sysmsg'] .= "����̾�μ����˼���";      // .= ��å��������ɲä���
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
    $query = "SELECT plan_no FROM material_cost_header WHERE assy_no='{$assy_no}'
                order by assy_no DESC, regdate DESC limit 1
    ";
    $chk_sql = "SELECT plan_no FROM material_cost_history
                WHERE
                    plan_no='{$plan_no}' and assy_no='{$assy_no}'
                LIMIT 1
    ";
    if (getUniResult($query, $pre_plan_no) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$assy_name} �Ϸ��򤬤���ޤ���";    // .= �����
    } elseif (getUniResult($chk_sql, $tmp_plan) > 0) {
        $_SESSION['s_sysmsg'] .= "{$assy_name} �ϴ��˹�������Ͽ����Ƥ��ޤ���";    // .= �����
        $msg_flg = 'alert';
    } else {
        $query = "INSERT INTO material_cost_history (
                        plan_no, assy_no, parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, last_date, last_user)
                  SELECT
                        '{$plan_no}', '{$assy_no}', parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}'
                  FROM material_cost_history
                  WHERE plan_no='{$pre_plan_no}' and assy_no='{$assy_no}'
                  ORDER BY par_parts ASC, parts_no ASC, pro_no ASC
        ";
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$assy_name} ��COPY�˼��ԡ� ô���Ԥ�Ϣ���Ʋ�������<br>COPY���ηײ��ֹ桧{$pre_plan_no}";    // .= �����
            $msg_flg = 'alert';
            ///////////////////////////////////// debug ADD 2005/06/01
            $fp_error = fopen($error_log_name, 'a');   // ���顼���ؤν���ߤǥ����ץ�
            $log_msg  = date('Y-m-d H:i:s');
            $log_msg .= " ���顼�λ��� SQL ʸ�ϰʲ� \n";
            fwrite($fp_error, $log_msg);
            fwrite($fp_error, $query);
            fclose($fp_error);
            ///////////////////////////////////// debug END
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$assy_name} ��COPY���ޤ���<br>COPY���ηײ��ֹ桧{$pre_plan_no}</font>";    // .= �����
        }
    }
}

//////////// SQL ʸ�� WHERE ��� ���Ѥ���
$search = sprintf("WHERE plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// ��ץ쥳���ɿ����������μ���     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("SELECT count(*), sum(Uround(pro_price * pro_num, 2)) FROM material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

//////////// �ײ��ֹ�ñ�̤ι������٤κ�ɽ
$query = sprintf("
        SELECT
            mate.last_user  AS \"Level\",                   -- 0
            parts_no        as �����ֹ�,                    -- 1
            midsc           as ����̾,                      -- 2
            pro_num         as ���ѿ�,                      -- 3
            pro_no          as ����,                        -- 4
            pro_mark        as ����̾,                      -- 5
            pro_price       as ����ñ��,                    -- 6
            Uround(pro_num * pro_price, 2)
                            as �������,                    -- 7
            CASE
                WHEN intext = '0' THEN '����'
                WHEN intext = '1' THEN '���'
                ELSE intext
            END             as �⳰��,                      -- 8
            par_parts       as ���ֹ�                       -- 9
        FROM
            -- material_cost_history
            material_cost_level_as('{$plan_no}') AS mate
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        -- %s 
        -- ORDER BY par_parts ASC, parts_no ASC, pro_no ASC
        
    ", $search);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    // exit();
    $num = count($field);       // �ե�����ɿ�����
    $final_flg = 0;             // ��λ�ե饰 0=NG
} else {
    $num = count($field);       // �ե�����ɿ�����
    $final_flg = 1;             // ��λ�ե饰 1=OK
    $query = "SELECT parts_no FROM material_cost_level_as('{$plan_no}')";
    $chk_rows = getResult2($query, $res_chk);
    if ($chk_rows != $maxrows) {
        $_SESSION['s_sysmsg'] .= "��٥�ɽ����{$chk_rows} �ȼ¥ǡ�����{$maxrows} �Υ쥳���ɿ������פ��Ƥ��ޤ��󡪡�ľ�����ϥ�˥塼����Ѥ��Ʋ�������";    // .= �����
        $msg_flg = 'alert';
        $old_menu = 'on';
        $_GET['page_keep'] = '1';   // ���顼�ξ��ϥڡ�����ݻ����뤿�� page_keep�����
    }
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script type='text/javascript'>
<!--
function targetEdit(url, row)
{
    document.targetCopy.action = url;
    document.targetCopy.number.value = row;
    document.targetCopy.submit();
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.mhForm.backwardStack.focus();  // IE/NN ξ�б�
    // document.entry_form.parts_no.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.entry_form.parts_no.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:          0.75em;
    font-family:        monospace;
}
.pt10b {
    font-size:          0.85em;
    font-weight:        bold;
    font-family:        monospace;
}
.pt11b {
    font-size:          0.95em;
    font-weight:        bold;
    font-family:        monospace;
}
a {
    font-size:          0.9em;
    font-weight:        bold;
    color:              blue;
    text-decoration:    none;
}
a:hover {
    background-color:   blue;
    color:              white;
}
.list tr.mouseOver {
    background-color:   #ceffce;
}
.list td.Edit {
    background-color:   white;
    font-size:          1.1em;
}
-->
</style>
<form name='targetCopy' action='' target='footer' action='post'>
<input type='hidden' name='number' value=''>
</form>
</head>
<body onLoad='set_focus()' bgcolor='#d6d3ce'>
    <center>
       <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field list' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <!--  bgcolor='#ffffc6' �������� -->
                    <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<$rows; $r++) {
                if ($request->get('mark') != '') {
                    if ($request->get('parts_no') == $res[$r][1] && $request->get('pro_mark') == $res[$r][5]) {    //��Ͽ�����ֹ�Ȱ��ʤ�ޡ�����
                        if ($request->get('par_parts') != '') {
                            if ($request->get('par_parts') == $res[$r][9]) {
                                echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                            } else {
                                echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                            }
                        } else {
                            echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        }
                    } else {
                        echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                    }
                } else if ($request->get('c_mark') != '') {
                    if ($request->get('c_number') == 1) {
                        if ($request->get('c_number') == $r) {    //1�ξ���������ֹ�˥ޡ��������դ���
                            echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        } else {
                            echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        }
                    } else {
                        if ($request->get('c_number')-1 == $r) {    //��������ֹ�ΰ�ľ�˥ޡ��������դ���
                            echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        } else {
                            echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        }
                    }
                } else if ($request->get('no_del_mark') != '') {
                    if ($request->get('no_del_num') == $r) {    //����˼��Ԥ�������ֹ�˥ޡ��������դ���
                        echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                    } else {
                        echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                    }
                } else {
                    echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                }
                /////////////////////////
                
                if ($request->get('mark') != '') {
                    if ($request->get('parts_no') == $res[$r][1] && $request->get('pro_mark') == $res[$r][5]) {    //��Ͽ�����ֹ�Ȱ��ʤ�ޡ�����
                        if ($request->get('par_parts') != '') {
                            if ($request->get('par_parts') == $res[$r][9]) {
                            ?>
                                <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                    onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                    <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                                </td>    <!-- �ԥʥ�С���ɽ�� -->
                            <?php
                            } else {
                            ?>
                                <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                    onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                    <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                                </td>    <!-- �ԥʥ�С���ɽ�� -->
                            <?php
                            }
                        } else {
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                            </td>    <!-- �ԥʥ�С���ɽ�� -->
                        <?php
                        }
                    } else {
                    ?>
                        <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                            onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                            <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                        </td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    }
                } else if ($request->get('c_mark') != '') {
                    if ($request->get('c_number') == 1) {
                        if ($request->get('c_number') == $r) {    //1�ξ���������ֹ�˥ޡ��������դ���
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                            </td>    <!-- �ԥʥ�С���ɽ�� -->
                        <?php
                        } else {
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                            </td>    <!-- �ԥʥ�С���ɽ�� -->
                        <?php
                        }
                    } else {
                        if ($request->get('c_number')-1 == $r) {    //��������ֹ�ΰ�ľ�˥ޡ��������դ���
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                            </td>    <!-- �ԥʥ�С���ɽ�� -->
                        <?php
                        } else {
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                            </td>    <!-- �ԥʥ�С���ɽ�� -->
                        <?php
                        }
                    }
                } else if ($request->get('no_del_mark') != '') {
                    if ($request->get('no_del_num') == $r) {    //����˼��Ԥ�������ֹ�˥ޡ��������դ���
                    ?>
                        <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                            onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                            <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                        </td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    } else {
                    ?>
                        <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                            onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                            <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                        </td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    }
                } else {
                ?>
                    <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                        onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                        <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                    </td>    <!-- �ԥʥ�С���ɽ�� -->
                <?php
                }
                /////////////////
                ?>
                
                <?php
                for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                    switch ($i) {
                    case 0:
                        echo "<td class='winbox' width='5%' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 1:
                        echo "<td class='winbox' width='8%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 2:
                        echo "<td class='winbox' width='39%' align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 3:
                        echo "<td class='winbox' nowrap width='6%' align='right'><div class='pt9'>", number_format($res[$r][$i], 4), "</div></td>\n";
                        break;
                    case 4:
                        echo "<td class='winbox' width='4%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 5:
                        echo "<td class='winbox' width='6%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 6:
                        echo "<td class='winbox' nowrap width='7%' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                        break;
                    case 7:
                        echo "<td class='winbox' nowrap width='7%' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                        break;
                    case 8    :
                        echo "<td class='winbox' width='6%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    default:
                        if ($res[$r][$i] != '') {
                            echo "<td class='winbox' width='8%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        } else {    // ���ֹ椬�ʤ��������� $i=8
                            echo "<td class='winbox' width='8%' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                        }
                    }
                }
                ?>
                </tr>
            <?php
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <?php echo $menu->out_retF2Script() ?>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
