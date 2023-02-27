<?php
//////////////////////////////////////////////////////////////////////////////
// »�״ط��Υ���պ�����˥塼  ���� Function Include File                 //
// Copyright (C) 2007 - 2013  Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp   //
// Changed history                                                          //
// 2007/10/04 Created   graphCreate_Function.php                            //
// 2007/10/07 ����դ���ɽ������ɽ���ɲá�Y������(����)������(�̡�)���ɲ�   //
// 2007/10/09 ����Υޡ����β�����Ĺ����Ĺ������ SetMarkAbsHSize(12)        //
// 2007/10/10 ����������(Cɸ�ࡦC����L���ʡ��ʎގ��ӎ�)��»�׷׻���data���ɲ�//
//            getGraphData()������ͤ�����å����ƥ���պ�����������ɲ�    //
//            preg_match()����Ѥ���̤����ȥ�������̾�򲫿�ɽ��          //
// 2007/10/13 X����ǯ���prot1��prot2�̡�������Ǥ��륪�ץ������ɲ�       //
// 2007/10/16 compositionXaxis()��isset($p2[$i])�Υ����å����ɲ�            //
//            getGraphStrYM($end_ym)�򿷵����ɲä�X�������������ɽ����     //
// 2007/10/17 SetLabelAlign('center', 'top') SetLabelMargin(1)��X�����ɲ�   //
// 2013/01/29 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                   ��ë //
//            �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
//////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���

//////////// ����դ�������ܤΥ�ƥ����
function getGraphItemArray()
{
    return array(
        '̤����',
        '--�ʲ�������--',
        '��������',                       '���κ�����(������)',             '����ϫ̳��',
        '������¤����',                     '���δ��������ų���ê����',       '������帶��',
        '�������������',                   '���οͷ���',                     '���η���',
        '�����δ���ڤӰ��̴������',       '���αĶ�����',                   '���ηо�����',
        '--�ʲ��ϥ��ץ�--',
        '���ץ�����',                     '���ץ������(������)',           '���ץ�ϫ̳��',
        '���ץ���¤����',                   '���ץ���������ų���ê����',     '���ץ���帶��',
        '���ץ����������',                 '���ץ�ͷ���',                   '���ץ����',
        '���ץ��δ���ڤӰ��̴������',     '���ץ�Ķ�����',                 '���ץ�о�����',
        '--�ʲ��ϥ�˥�--',
        '��˥�����',                     '��˥�������(������)',           '��˥�ϫ̳��',
        '��˥���¤����',                   '��˥����������ų���ê����',     '��˥���帶��',
        '��˥����������',                 '��˥��ͷ���',                   '��˥�����',
        '��˥��δ���ڤӰ��̴������',     '��˥��Ķ�����',                 '��˥��о�����',
        '--�ʲ��ϻ����--',
        '���������',                   '�����������(������)',         '�����ϫ̳��',
        '�������¤����',                 '��������������ų���ê����',   '�������帶��',
        '��������������',               '������ͷ���',                 '���������',
        '������δ���ڤӰ��̴������',   '������Ķ�����',               '������о�����',
        '--�ʲ��Ͼ��ʴ���--',
        '���ʴ�������',                   '���ʴ���������(������)',         '���ʴ���ϫ̳��',
        '���ʴ�����¤����',                 '���ʴ������������ų���ê����',   '���ʴ�����帶��',
        '���ʴ������������',               '���ʴ����ͷ���',                 '���ʴ�������',
        '���ʴ����δ���ڤӰ��̴������',   '���ʴ����Ķ�����',               '���ʴ����о�����',
        '--�ʲ���Cɸ��--',
        '���ץ�ɸ������',                 '���ץ�ɸ��������(������)',     '���ץ�ɸ��ϫ̳��',
        '���ץ�ɸ����¤����',               '���ץ�ɸ����������ų���ê����', '���ץ�ɸ����帶��',
        '���ץ�ɸ�����������',             '���ץ�ɸ��ͷ���',               '���ץ�ɸ�����',
        '���ץ�ɸ���δ���ڤӰ��̴������', '���ץ�ɸ��Ķ�����',             '���ץ�ɸ��о�����',
        '--�ʲ���C����--',
        '���ץ���������',                 '���ץ����������(������)',       '���ץ�����ϫ̳��',
        '���ץ�������¤����',               '���ץ�������������ų���ê����', '���ץ�������帶��',
        '���ץ��������������',             '���ץ�����ͷ���',               '���ץ��������',
        '���ץ������δ���ڤӰ��̴������', '���ץ�����Ķ�����',             '���ץ�����о�����',
        '--�ʲ���Lɸ��--',
        '��˥�ɸ������',                 '��˥�ɸ�������(������)',       '��˥�ɸ��ϫ̳��',
        '��˥�ɸ����¤����',               '��˥�ɸ����������ų���ê����', '��˥�ɸ����帶��',
        '��˥�ɸ�����������',             '��˥�ɸ��ͷ���',               '��˥�ɸ�����',
        '��˥�ɸ���δ���ڤӰ��̴������', '��˥�ɸ��Ķ�����',             '��˥�ɸ��о�����',
        '--�ʲ��ϱ��Υݥ��--',
        '���Υݥ������',                 '���Υݥ�׺�����(������)',       '���Υݥ��ϫ̳��',
        '���Υݥ����¤����',               '���Υݥ�״��������ų���ê����', '���Υݥ����帶��',
        '���Υݥ�����������',             '���Υݥ�׿ͷ���',               '���Υݥ�׷���',
        '���Υݥ���δ���ڤӰ��̴������', '���Υݥ�ױĶ�����',             '���Υݥ�׷о�����'
    );
}

//////////// ɽ������ǯ�������ե���������
function ymFormCreate($dataxFlg, $yyyymm, $name='yyyymm1', $event='')
{
    // $ym_form = "<select name='yyyymm' onChange='document.ym_form.submit()'>\n";
    if ($name == 'yyyymm2' && $dataxFlg == 'on') {
        $ym_form = "<select name='{$name}' {$event} disabled>\n";
    } else {
        $ym_form = "<select name='{$name}' {$event}>\n";
    }
            ///// ǯ����ϰϤ���ꤷ�Ƥ���ΤϥХå����å��ѤΥǡ����˰��¿����Τ����뤿��
    $query = "
        SELECT pl_bs_ym FROM profit_loss_pl_history WHERE pl_bs_ym >= 200010 AND pl_bs_ym <= 203003 GROUP BY pl_bs_ym ORDER BY pl_bs_ym DESC
    ";
    $res = array();
    if ( ($rows=getResult2($query, $res)) > 0 ) {
        for ($i=0; $i<$rows; $i++) {
            if ($yyyymm == $res[$i][0]) {
                $ym_form .= "    <option value='{$res[$i][0]}' selected>{$res[$i][0]}</option>\n";
            } else {
                $ym_form .= "    <option value='{$res[$i][0]}'>{$res[$i][0]}</option>\n";
            }
        }
    }
    $ym_form .= "    </select>\n";
    return $ym_form;
}

//////////// ����դΥץ�åȹ��� ����ե���������
function graphSelectForm($name, $plotItem)
{
    $select = "<select name='{$name}'>\n";
    // $query = "SELECT note FROM act_pl_history WHERE pl_bs_ym >= 200010 AND pl_bs_ym <= 203003 GROUP BY note ORDER BY note DESC";
    // ���ܤ�¿������ΤǾ嵭�Ϥ��롣�������������Ѥ��롣
    $res = getGraphItemArray();     // ��ƥ�������ͤǼ���
    foreach ($res as $value) {
        if ($plotItem == $value) {
            if (preg_match('/^̤����/', $value) || preg_match('/^--/', $value)) {
                $select .= "    <option value='{$value}' style='color:yellow;' selected>{$value}</option>\n";
            } else {
                $select .= "    <option value='{$value}' selected>{$value}</option>\n";
            }
        } elseif (preg_match('/^̤����/', $value) || preg_match('/^--/', $value)) {
            $select .= "    <option value='{$value}' style='color:yellow;'>{$value}</option>\n";
        } else {
            $select .= "    <option value='{$value}'>{$value}</option>\n";
        }
    }
    $select .= "</select>\n";
    return $select;
}

//////////// �ᥤ�󥳥�ȥ���
function mainController($menu, $request, $session)
{
    //////////// ����դΥץ�åȹ��ܤμ���
    if ($request->get('g1plot1') != '') $session->add_local('g1plot1', $request->get('g1plot1'));
    if ($request->get('g1plot2') != '') $session->add_local('g1plot2', $request->get('g1plot2'));
    if ($request->get('g2plot1') != '') $session->add_local('g2plot1', $request->get('g2plot1'));
    if ($request->get('g2plot2') != '') $session->add_local('g2plot2', $request->get('g2plot2'));
    if ($request->get('g3plot1') != '') $session->add_local('g3plot1', $request->get('g3plot1'));
    if ($request->get('g3plot2') != '') $session->add_local('g3plot2', $request->get('g3plot2'));
    //////////// ����դ���ɽ������ɽ���μ���
    if ($request->get('plot1_value') != '') $session->add_local('plot1_value', $request->get('plot1_value'));
    if ($request->get('plot2_value') != '') $session->add_local('plot2_value', $request->get('plot2_value'));
    //////////// ����� �����ܤΥץ�å�Y������(����)�����Ĥμ���
    if ($request->get('yaxis') != '') $session->add_local('yaxis', $request->get('yaxis'));
    //////////// ����դ�X��(ǯ��)���Ѥ��뤫�̡��ˤ��뤫
    if ($request->get('dataxFlg') != '') $session->add_local('dataxFlg', $request->get('dataxFlg'));
    //////////// ����ǯ��μ���
    if ($request->get('yyyymm1') != '' || $request->get('yyyymm2') != '') {
        $session->add_local('yyyymm1', $request->get('yyyymm1'));
        $session->add_local('yyyymm2', $request->get('yyyymm2'));
        // header("Location: http:" . WEB_HOST . "processing_msg.php?script=". SALES ."uriage_graph_all_niti.php");
        header('Location: ' . H_WEB_HOST . '/processing_msg.php?script=' . $menu->out_self());
        exit(); ////////// ���줬�ʤ��ȥ�����ץȤ�Ǹ�ޤǥ����å�����Τǻ��֤������롣
    }
    
}

//////////// Y������(����)�����Ĥμ����򤷤ƥ饸���ܥ���Υ����å����֤�
function getRadioChecked($request, $name, $value)
{
    if ($request->get($name) == $value) {
        return ' checked';
    } elseif ($request->get($name) == '' && $value == 1) {
        return ' checked';
    } else {
        return '';
    }
}

//////////// ������ե�������᤹�ǡ�������
function setReturnData($menu, $session)
{
    $menu->set_retPOST('yyyymm1', $session->get_local('yyyymm1'));
    $menu->set_retPOST('yyyymm2', $session->get_local('yyyymm2'));
    $menu->set_retPOST('dataxFlg', $session->get_local('dataxFlg'));
    $menu->set_retPOST('g1plot1', $session->get_local('g1plot1'));
    $menu->set_retPOST('g1plot2', $session->get_local('g1plot2'));
    $menu->set_retPOST('g2plot1', $session->get_local('g2plot1'));
    $menu->set_retPOST('g2plot2', $session->get_local('g2plot2'));
    $menu->set_retPOST('g3plot1', $session->get_local('g3plot1'));
    $menu->set_retPOST('g3plot2', $session->get_local('g3plot2'));
    $menu->set_retPOST('plot1_value', $session->get_local('plot1_value'));
    $menu->set_retPOST('plot2_value', $session->get_local('plot2_value'));
    $menu->set_retPOST('yaxis', $session->get_local('yaxis'));
}

//////////// ����դΥץ�åȹ��� ����
function graphCreate($session, $result)
{
    $graph_name1 = "graph/graphCreate1.png";  
    $graph_name2 = "graph/graphCreate2.png";
    $graph_name3 = "graph/graphCreate3.png";
    $result->add('graph_name1', $graph_name1);
    $result->add('graph_name2', $graph_name2);
    $result->add('graph_name3', $graph_name3);
    if ($session->get_local('g1plot1') != '̤����' && $session->get_local('g1plot2') != '̤����') {
        $rows = getGraphData($session, $result, 'g1plot1');
        getGraphData($session, $result, 'g1plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name1, 'g1plot1', 'g1plot2');
    } elseif ($session->get_local('g1plot1') != '̤����') {
        $rows = getGraphData($session, $result, 'g1plot1');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name1, 'g1plot1');
    }
    if ($session->get_local('g2plot1') != '̤����' && $session->get_local('g2plot2') != '̤����') {
        $rows = getGraphData($session, $result, 'g2plot1');
        getGraphData($session, $result, 'g2plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name2, 'g2plot1', 'g2plot2');
    } elseif ($session->get_local('g2plot1') != '̤����') {
        $rows = getGraphData($session, $result, 'g2plot1');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name2, 'g2plot1');
    }
    if ($session->get_local('g3plot1') != '̤����' && $session->get_local('g3plot2') != '̤����') {
        $rows = getGraphData($session, $result, 'g3plot1');
        getGraphData($session, $result, 'g3plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name3, 'g3plot1', 'g3plot2');
    } elseif ($session->get_local('g3plot1') != '̤����') {
        $rows = getGraphData($session, $result, 'g3plot1');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name3, 'g3plot1');
    }
}

//////////// ����դγ���ǯ��μ��� ���ܤ�ǯ�٤�X���Ŀ����� �쥿����
function getGraphStrYM_old($end_ym)
{
    $yyyy   = substr($end_ym, 0, 4);
    $mm     = substr($end_ym, 4, 2);
    if ($mm >= 4 && $mm <= 10) {
        $mm = '01';
    } elseif ($mm >= 1 && $mm <= 3) {
        $mm = '04';
        $yyyy--;
    } else {
        $mm = '04';
    }
    $str_ym = $yyyy . $mm;
    return $str_ym;
}

//////////// ����դγ���ǯ��μ��� ����������� ��������
function getGraphStrYM($end_ym)
{
    $yyyy   = substr($end_ym, 0, 4);
    $mm     = substr($end_ym, 4, 2);
    $month = $mm - 23;
    if ($month <= 0) {
        $yyyy -= 1;
        $mm = sprintf('%02d', $month + 12);
    } else {
        $mm = sprintf('%02d', $month);
    }
    $str_ym = $yyyy . $mm;
    return $str_ym;
}

//////////// ����եǡ����μ���
function getGraphData($session, $result, $plot)
{
    ///// �ꥯ������ǯ��򥰥�դν�λǯ��ˤ��ơ�����ǯ��򻻽�
    if (substr($plot, 2, 5) == 'plot1') {
        $end_ym = $session->get_local('yyyymm1');
    } else {
        $end_ym = $session->get_local('yyyymm2');
    }
    $str_ym = getGraphStrYM($end_ym);
    $plot_change = str_replace('���Υݥ��','�Х����', $session->get_local($plot));
    ///// �ץ�åȹ��ܼ���
    $query = "
        SELECT kin, pl_bs_ym FROM profit_loss_pl_history WHERE pl_bs_ym >= {$str_ym} AND pl_bs_ym <= {$end_ym}
        AND note = '{$plot_change}' ORDER BY pl_bs_ym ASC
    ";
    $res = array();
    if ( ($rows=getResult2($query, $res)) > 0) {
        $data  = array();
        $datax = array();   // X������̾(ǯ��)
        for ($i=0; $i<$rows; $i++) {
            $data[$i]  = Uround($res[$i][0] / 1000000, 1);   // ñ�̤�ɴ���ߤ�
            $datax[$i] = $res[$i][1];
        }
        $result->add($plot.'_data', $data);     // �ץ�åȥǡ������å�
        $result->add($plot.'_datax', $datax);   // X�����ܥ��å�
    }
    $result->add($plot.'_rows', $rows);  // �ץ�åȥǡ����Υ쥳���ɿ��򥻥å� ���ʲ��ʤ�����褷�ʤ�
    return $rows;
}

//////////// ����պ����¹�
function graphCreateExecute($session, $result, $graph_name, $plot1, $plot2='')
{
    require_once ('../../../jpgraph.php'); 
    require_once ('../../../jpgraph_line.php'); 
    // A nice graph with anti-aliasing 
    $graph = new Graph(1200, 350, 'auto');       // ����դ��礭�� X/Y
    if ($session->get_local('dataxFlg') == 'on') {
        $graph->img->SetMargin(40, 50, 30, 85);    // ����հ��֤Υޡ����� �����岼
    } else {
        $graph->img->SetMargin(40, 50, 30, 95);    // ����հ��֤Υޡ����� �����岼
    }
    $graph->SetScale('textlin'); 
    $graph->SetShadow(); 
    // Slightly adjust the legend from it's default position in the 
    // top right corner. 
    // $graph->legend->Pos(0.015, 0.5, 'right', 'center'); // ����ΰ��ֻ���(�����ޡ�����,�岼�ޡ�����,"right","center")
    $graph->legend->Pos(0.5, 0.97, 'center', 'bottom');
    $graph->legend->SetLayout(LEGEND_HOR);
    $graph->legend->SetFont(FF_GOTHIC, FS_NORMAL, 14);  // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�
    $graph->legend->SetMarkAbsHSize(12);                // �ޡ����β�����Ĺ����Ĺ������
    
    // $graph->title->Set("Line plot with null values"); 
    $graph->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
    $graph->yaxis->SetColor('blue');
    $graph->yaxis->SetWeight(2);
    
    // ����դΥ����ȥ�����
    if ($session->get_local($plot1) != '' && $session->get_local($plot2) != '') {
        $title = $session->get_local($plot1) . '��' . $session->get_local($plot2);
    } else {
        $title = $session->get_local($plot1);
    }
    $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
    $graph->title->Set(mb_convert_encoding("{$title} ��ܥ����", 'UTF-8')); 
    $text = new Text(mb_convert_encoding('ñ�̡�ɴ����', 'UTF-8'));
    $text->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $text->SetPos(880, 10);
    $text->SetColor('teal');
    $graph->AddText($text);
    // Setup X-scale
    if ($session->get_local('dataxFlg') == 'on') {
        $graph->xaxis->SetTickLabels($result->get($plot1.'_datax'));
    } else {
        $xaxis = compositionXaxis($result, $plot1, $plot2); // ʣ�緿��X������Ѥ���
        $graph->xaxis->SetTickLabels($xaxis, $result->get('xaxis_color'));
        $graph->xaxis->SetLabelAlign('center', 'top');  // ����ͤ�right��center���ѹ�
    }
    $graph->xaxis->SetLabelMargin(1);   // X����ǯ���ĥޡ��������� 7��1 ���ѹ�
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 9);   // �ե���Ȥϥܡ���ɤ����Ǥ��롣
    $graph->xaxis->SetLabelAngle(35);
    $graph->xaxis->SetPos('min');   // ���X���򥰥�դκǲ�����ɽ������
    // �ץ�åȣ��ȥץ�åȣ��Υǡ���������å��������褹��
    if ($result->get($plot1.'_rows') > 0) {
        // Create the first line
        $p1 = new LinePlot($result->get($plot1.'_data'));
        $p1->mark->SetType(MARK_FILLEDCIRCLE);
        $p1->mark->SetFillColor('blue');
        $p1->mark->SetWidth(3);
        $p1->mark->Show();              // �ޡ���ɽ��
        $p1->SetColor('blue');
        $p1->SetCenter(); 
        $p1->SetWeight(1);              // �ץ�å���������(2�ɥåȢ�1��)
        $p1->SetLegend(mb_convert_encoding($session->get_local($plot1), 'UTF-8'));
        // $p1->value->SetFormat('%01.1f'); // ��������̵������0����������������
        $p1->value->SetFormatCallback('userFormat');    // �嵭�Ǥϣ���Υ���ޤ��б��Ǥ��ʤ�����
        $p1->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
        if ($session->get_local('plot1_value') == 'on') {
            $p1->value->Show();
        }
        $graph->Add($p1); 
    }
    if ($result->get($plot2.'_rows') > 0) {
        // ... and the second 
        if ($session->get_local('yaxis') == '2') {
            $graph->SetY2Scale('lin');      // Y2����������ɲ�
            $graph->y2axis->SetColor('red');// Y2��������ο�
            $graph->y2axis->SetWeight(2);   // Y2�������������(���ɥå�)
            $graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
        }
        $p2 = new LinePlot($result->get($plot2.'_data'));  // ����ܤΥ饤��ץ�åȥ��饹�����
        $p2->mark->SetType(MARK_IMG_STAR, 'red', 0.7);  // �ץ�åȥޡ����η�, ��, �礭��
        $p2->mark->SetFillColor('red'); // �ץ�åȥޡ����ο�
        $p2->mark->SetWidth(4);         // �ץ�åȥޡ������礭��
        $p2->mark->Show();              // �ޡ���ɽ��
        $p2->SetColor('red');           // �ץ�å����ο�
        $p2->SetCenter();               // �ץ�åȤ������
        $p2->SetWeight(1);              // �ץ�å���������(2�ɥåȢ�1��)
        $p2->SetLegend(mb_convert_encoding($session->get_local($plot2), 'UTF-8')); 
        // $p2->value->SetFormat('%01.1f'); // ��������̵������0����������������
        $p2->value->SetFormatCallback('userFormat');    // �嵭�Ǥϣ���Υ���ޤ��б��Ǥ��ʤ�����
        $p2->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
        $p2->value->SetColor('red');                    // �ͤΤο�
        if ($session->get_local('plot2_value') == 'on') {
            $p2->value->Show();
        }
        if ($session->get_local('yaxis') == '2') {
            $graph->AddY2($p2);             // Y2���������ѤΥץ�åȣ����ɲ�
        } else {
            $graph->Add($p2);           // Ʊ��Y���ǤΥץ�å�
        }
    }
    // Output line 
    $graph->Stroke($graph_name); 
    // echo $graph->GetHTMLImageMap("myimagemap"); 
    // echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 
}
////////// ����դΥ�����Хå��ؿ�(���奫��ޤ˾�������)
function userFormat($aLabel)
{
    return number_format($aLabel, 1);
}
////////// ����դ�X����prot1��prot2��ʣ��ǯ���������֤�
function compositionXaxis($result, $plot1, $plot2)
{
    $p1 = $result->get($plot1.'_datax');
    $p2 = $result->get($plot2.'_datax');
    $xaxis = array();
    $color = array();
    for ($i=0; $i<count($result->get($plot1.'_datax')); $i++) {
        if (isset($p2[$i])) {
            $xaxis[$i] = "{$p1[$i]}\n{$p2[$i]}";
        } else {
            $xaxis[$i] = $p1[$i];
        }
        $color[$i] = 'darkred';
    }
    $result->add('xaxis_color', $color);
    return $xaxis;
}
//////////// ����դ���ɽ��ON/OFF���󥫡��Υ���������
function getPlotValueOnOff($session, $menu, $uniq)
{
    ///// ����������
    $anchor = '';
    if ($session->get_local('plot1_value') == 'on') {
        $anchor .= "<a href='{$menu->out_self()}?yyyymm1={$session->get_local('yyyymm1')}&yyyymm2={$session->get_local('yyyymm2')}&plot1_value=off&{$uniq}'>�ץ�å�1�����ɽ��</a>��\n";
    } else {
        $anchor .= "<a href='{$menu->out_self()}?yyyymm1={$session->get_local('yyyymm1')}&yyyymm2={$session->get_local('yyyymm2')}&plot1_value=on&{$uniq}'>�ץ�å�1���ɽ��</a>��\n";
    }
    if ($session->get_local('plot2_value') == 'on') {
        $anchor .= "<a href='{$menu->out_self()}?yyyymm2={$session->get_local('yyyymm2')}&yyyymm1={$session->get_local('yyyymm1')}&plot2_value=off&{$uniq}' style='color:red;'>�ץ�å�2�����ɽ��</a>��\n";
    } else {
        $anchor .= "<a href='{$menu->out_self()}?yyyymm2={$session->get_local('yyyymm2')}&yyyymm1={$session->get_local('yyyymm1')}&plot2_value=on&{$uniq}' style='color:red;'>�ץ�å�2���ɽ��</a>��\n";
    }
    return $anchor;
}

//////////// �������Υڡ������� �ǡ�������
function setPageData($yyyymm, $name, $result)
{
    $yyyy = substr($yyyymm, 0, 4);
    $mm   = substr($yyyymm, 4, 2);
    if ($mm == 1) {
        $next_yyyymm = $yyyy . '02';
        $yyyy--;
        $pre_yyyymm = $yyyy . '12';
    } elseif ($mm == 12) {
        $pre_yyyymm = $yyyy . '11';
        $yyyy++;
        $next_yyyymm = $yyyy . '01';
    } else {
        $pre_yyyymm = $yyyymm - 1;
        $next_yyyymm = $yyyymm + 1;
    }
    $query = "SELECT pl_bs_ym FROM profit_loss_pl_history WHERE pl_bs_ym = {$pre_yyyymm}";
    if (getUniResult($query, $check) <= 0) {
        $result->add('backward', ' disabled');
    }
    $query = "SELECT pl_bs_ym FROM profit_loss_pl_history WHERE pl_bs_ym = {$next_yyyymm}";
    if (getUniResult($query, $check) <= 0) {
        $result->add('forward', ' disabled');
    }
    if ($name == 'yyyymm1') {
        $result->add('pre_yyyymm1', $pre_yyyymm);
        $result->add('next_yyyymm1', $next_yyyymm);
    } else {
        $result->add('pre_yyyymm2', $pre_yyyymm);
        $result->add('next_yyyymm2', $next_yyyymm);
    }
}

?>
