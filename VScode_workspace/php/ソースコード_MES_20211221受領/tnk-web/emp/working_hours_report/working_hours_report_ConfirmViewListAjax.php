<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ƚ���ν��� ��ǧ�Ѱ���      ɽ��(Ajax)                    MVC View��  //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/09/22 Created   working_hours_report_ViewConfirmListAjax.php        //
// 2017/06/02 ����Ĺ���� �ܳʲ�ư                                           //
// 2017/06/29 ���顼�ս���������                                            //
//////////////////////////////////////////////////////////////////////////////
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='98%' height='60' title='����'>\n";
// echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
// echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewConfirmList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='99%' height='78%' title='����'>\n";
echo "    ������ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='98%' height='35' title='�եå���'>\n";
// echo "    �եå�����ɽ�����Ƥ��ޤ���\n";
// echo "</iframe>\n";
?>
