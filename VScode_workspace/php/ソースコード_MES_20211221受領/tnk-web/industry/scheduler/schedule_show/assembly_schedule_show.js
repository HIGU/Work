//////////////////////////////////////////////////////////////////////////////
// ��Ω�����ײ�(�������塼��)�Ȳ� �����ײ�     MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/24 Created    assembly_schedule_show.js                          //
// 2006/01/31 �᥽�åɤ�function()̵̾�ؿ�(�����ؿ�)�ε��ҥ���������ѹ�    //
// 2006/02/15 �ȥ��륹���å����μ�ư����ON/OFF����᥽�åɤ��ɲ�            //
// 2006/03/03 switchComplete()�᥽�åɤ��ɲ� (����ʬ��̤����ʬ������ɽ��)   //
// 2006/03/15 win_open()�᥽�åɤ� window̾�ʤ� resizable=yes ���ѹ�        //
// 2006/04/11 �ȥ��륹���å�ɽ����̤�����ȴ����� �� ͽ���ʤȴ�λ�� ���ѹ�   //
// 2006/06/16 ����ȥ��㡼�ȤΤߤ��̥�����ɥ��ǳ�����ǽ���ɲ� zoomGantt()  //
// 2006/06/22 ������ǳ���pageParameter�ɲä�ȼ��zoomGantt()��?��&���ѹ�    //
// 2006/10/16 �饤������������ɲ� �ץ�ѥƥ�lineMethod, setLineMethod()�ɲ�//
// 2006/11/09 meta��Refresh�����setInterval()��zoomGanttReload()��ƽФ� //
// 2007/03/23 win_open()�᥽�åɤ� menubar=yes ���ѹ� (�����ץ�ӥ塼�б�)  //
// 2007/08/21 win_open()�᥽�åɤ�̾����֥�󥯤���'schedule'���ѹ�        //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_schedule_show class �ƥ�ץ졼�Ȥγ�ĥ���饹�����          *
/****************************************************************************
class assembly_schedule_show extends base_class
*/
///// �����ѡ����饹�ηѾ�
assembly_schedule_show.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function assembly_schedule_show()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag     = 1;                            // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg      = "";                           //      ��
    this.AutoReLoad     = "";                           // ��ư�����ե饰�ν����
    this.AutoReLoadID   = "";                           //    ��   setInterval��ID(�����)
    this.CompleteStatus = "";                           // ̤����ʬ��������ʬ���ξ��� ����ͤϥ����С������ɤǷ���
    this.Parameter      = "";                           // Ajax��GET�ѥ�᡼����
    this.lineMethod     = "1";                          // �饤��λ�����ˡ(1=��������, 2=ʣ������)
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    assembly_schedule_show.prototype.set_focus = function (obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
        // document.body.focus();   // F2/F12������ͭ���������б�
        // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ��ᡢ��������ѹ���NN�б�
        // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
        // document.form_name.element_name.select();
    }
    
    /***** ����ɽ���᥽�å� *****/
    /***** blink_flg Private property �������0.5��������� *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\", \"��å�����\")", 500)'> *****/
    assembly_schedule_show.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    assembly_schedule_show.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    assembly_schedule_show.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, 'schedule', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=yes,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    assembly_schedule_show.prototype.winActiveChk = function ()
    {
        if (document.all) {     // IE�ʤ�
            if (document.hasFocus() == false) {     // IE5.5�ʾ�ǻȤ���
                window.focus();
                return;
            }
            return;
        } else {                // NN �ʤ�ȥ�ꥭ�å�
            window.focus();
            return;
        }
    }
    
    /***** ������礭���Υ⡼�������������ɽ������ *****/
    /***** IE ���ѤʤΤ� Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф� *****/
    /***** ����������ǥꥯ�����Ȥ�Ф����ϥե졼����ڤäƹԤ� *****/
    assembly_schedule_show.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ����ȥ��㡼�ȤΤߤ��̥�����ɥ��ǳ������Ф�����ꤷ�ƥ�������  *****/
    assembly_schedule_show.prototype.zoomGantt = function (url)
    {
        url += "&showMenu=ZoomGantt";
        this.win_open(url, 1024, 768);
    }
    
    /***** �饤��λ�����ˡ������å����� get�ѥ�᡼���������ؤ��� *****/
    assembly_schedule_show.prototype.targetLineExecute = function (url)
    {
        if (this.lineMethod == "1") {
            location.replace(url + "&targetLineMethod=1");
        } else {
            location.replace(url + "&targetLineMethod=2");
        }
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // Ajax����Ѥ��������ײ�������ϥ���ȥ��㡼�� ������Ѽ¹ԥ᥽�å�
    // parameter : ListTable=�����ײ����, GanttTable=����ȥ��㡼��
    assembly_schedule_show.prototype.AjaxLoadTable = function (showMenu)
    {
        var parm = "?";
        parm += "showMenu=" + showMenu  // table�Τ����
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        parm += this.Parameter;
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("�����ѤΥ֥饦������̤�б��Ǥ���\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
            }
        }
        try {
            xmlhttp.open("GET", "assembly_schedule_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** �桼�����Υޥ������ˤ��Ajax�ꥯ�����ȥ᥽�å� *****/
    // �������å��������б���
    // parameter : ListTable=�����ײ����, GanttTable=����ȥ��㡼��
    assembly_schedule_show.prototype.AjaxLoadTableMsg = function (showMenu, status)
    {
        var parm = "?";
        parm += "showMenu=" + showMenu  // table�Τ����
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        parm += this.Parameter;
        if (status == "page_keep") parm += "&page_keep=on"; // ����褫��������
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("�����ѤΥ֥饦������̤�б��Ǥ���\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ����ޤ�WaitMessage����ϡ�
                document.getElementById("showAjax").innerHTML = "<br><br><br><br><br><br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "assembly_schedule_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** �ȥ��륹���å�����̤�����������Ѥ�ɽ������ �᥽�å� *****/
    assembly_schedule_show.prototype.switchComplete = function (status)
    {
        if (this.CompleteStatus == "yes") {
            this.CompleteStatus = "no";
            this.Parameter = "&targetCompleteFlag=no";
            if (status == "Gantt") {
                document.getElementById("CompleteFlag").innerHTML = "ͽ����";
                this.AjaxLoadTableMsg("GanttTable");
            } else {
                // document.getElementById("CompleteFlag").innerHTML = "�ײ�Ŀ�";
                this.AjaxLoadTable("ListTable");
            }
        } else {
            this.CompleteStatus = "yes";
            this.Parameter = "&targetCompleteFlag=yes";
            if (status == "Gantt") {
                document.getElementById("CompleteFlag").innerHTML = "��λ��";
                this.AjaxLoadTableMsg("GanttTable");
            } else {
                // document.getElementById("CompleteFlag").innerHTML = "������";
                this.AjaxLoadTable("ListTable");
            }
        }
    }
    
    /***** �ȥ��륹���å����μ�ư����ON/OFF����᥽�å� *****/
    assembly_schedule_show.prototype.switchAutoReLoad = function (targetFunction, mSec)
    {
        if (this.AutoReLoad == 'ON') {      // ON �� OFF
            if (this.AutoReLoadID) {
                clearInterval(this.AutoReLoadID);
                this.AutoReLoad = "OFF";
                document.getElementById("toggleView").innerHTML = "MAN";
                alert("\n���� ���� �� MAN(��ư) �ˤ��ޤ�����\n");
            }
        } else {                            // OFF �� ON
            if (mSec >= 15000 && mSec <= 300000) {  // 15�ðʾ��300��(5ʬ)�ʲ�
                this.AutoReLoadID = setInterval(targetFunction, mSec);
                document.getElementById("toggleView").innerHTML = "AUT";
                if (this.AutoReLoad != "") {        // ���ξ���Message��ɽ�����ʤ�
                    alert("\n���� ���� �� AUT(��ư) �ˤ��ޤ�����\n");
                }
                this.AutoReLoad = "ON";
            }
        }
    }
    
    /***** �ȥ��륹���å����Υ饤���������ˡ���åȥ᥽�å� *****/
    assembly_schedule_show.prototype.setLineMethod = function (flag)
    {
        if (flag != "") {
            this.lineMethod = flag;
            if (this.lineMethod == "1") {
                document.getElementById("lineMethod1").style.color = "red";
                document.getElementById("lineMethod2").style.color = "black";
            } else {
                document.getElementById("lineMethod2").style.color = "blue";
                document.getElementById("lineMethod1").style.color = "black";
            }
            return;
        }
        if (this.lineMethod == "1") {
            this.lineMethod = "2";
            document.getElementById("lineMethod2").style.color = "blue";
            document.getElementById("lineMethod1").style.color = "black";
        } else {
            this.lineMethod = "1";
            document.getElementById("lineMethod1").style.color = "red";
            document.getElementById("lineMethod2").style.color = "black";
        }
        return;
    }
    
    /***** �����६��ȥ��㡼�ȤΥ���ɥ᥽�å� *****/
    assembly_schedule_show.prototype.zoomGanttReload = function (url)
    {
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("�����ѤΥ֥饦������̤�б��Ǥ���\n\n" + e);
            }
        }
        // var urlHeader = "assembly_schedule_show_ViewZoomGanttHeader.php?" + Date.parse();
        // var urlBody   = "assembly_schedule_show_ViewZoomGanttBody.php?" + Date.parse();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    // ����ǹ��������褿�����̤�����ĥ��Τ���� Header.php��Body.php������setAttribute()����Ѥ����н�
                // window.header.location.reload(true);
                // window.list.location.reload(true);
                     // ����Ϲ������ʤ�(�ʤ���)
                // document.getElementById("frameHeader").setAttribute("src", urlHeader);
                // document.getElementById("frameBody").setAttribute("src", urlBody);
                      // ����⹹�����ʤ�
                // document.getElementById("frameHeader").src = urlHeader;
                // document.getElementById("frameBody").src = urlBody;
            }
        }
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    return this;    // Object Return
    
}   /* class assembly_schedule_show END  */


///// ���󥹥��󥹤�����
var AssemblyScheduleShow = new assembly_schedule_show();

