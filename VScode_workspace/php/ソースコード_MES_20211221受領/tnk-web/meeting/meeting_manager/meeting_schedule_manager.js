//////////////////////////////////////////////////////////////////////////////
// ����Ĺ�Ѳ�ĥ������塼��Ȳ�                MVC View��(JavaScript���饹) //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created    meeting_schedule_manager.js                        //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     meeting_schedule_manager_show class �ƥ�ץ졼�Ȥγ�ĥ���饹�����          *
/****************************************************************************
class meeting_schedule_manager_show extends base_class
*/
///// �����ѡ����饹�ηѾ�
meeting_schedule_manager_show.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function meeting_schedule_manager_show()
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
    meeting_schedule_manager_show.prototype.set_focus = function (obj, status)
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
    meeting_schedule_manager_show.prototype.blink_disp = function (id_name)
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
    meeting_schedule_manager_show.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    meeting_schedule_manager_show.prototype.win_open = function (url, w, h)
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
    meeting_schedule_manager_show.prototype.winActiveChk = function ()
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
    meeting_schedule_manager_show.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ����ȥ��㡼�ȤΤߤ��̥�����ɥ��ǳ������Ф�����ꤷ�ƥ�������  *****/
    meeting_schedule_manager_show.prototype.zoomGantt = function (url)
    {
        url += "&showMenu=ZoomGantt";
        this.win_open(url, 1024, 768);
    }
    
    /***** �饤��λ�����ˡ������å����� get�ѥ�᡼���������ؤ��� *****/
    meeting_schedule_manager_show.prototype.targetLineExecute = function (url)
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
    meeting_schedule_manager_show.prototype.AjaxLoadTable = function (showMenu)
    {
        var parm = "?";
        parm += "showMenu=" + showMenu  // table�Τ����
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        parm += "&year="                + document.ControlForm.year.value;
        parm += "&month="               + document.ControlForm.month.value;
        parm += "&day="                 + document.ControlForm.day.value;
        parm += "&my_flg="              + document.ControlForm.my_flg.value;
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
            xmlhttp.open("GET", "meeting_schedule_manager_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** �桼�����Υޥ������ˤ��Ajax�ꥯ�����ȥ᥽�å� *****/
    // �������å��������б���
    // parameter : ListTable=�����ײ����, GanttTable=����ȥ��㡼��
    meeting_schedule_manager_show.prototype.AjaxLoadTableMsg = function (showMenu, status)
    {
        var parm = "?";
        parm += "showMenu=" + showMenu  // table�Τ����
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        parm += "&year="                + document.ControlForm.year.value;
        parm += "&month="               + document.ControlForm.month.value;
        parm += "&day="                 + document.ControlForm.day.value;
        parm += "&my_flg="              + document.ControlForm.my_flg.value;
        
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
            xmlhttp.open("GET", "meeting_schedule_manager_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** �ȥ��륹���å�����̤�����������Ѥ�ɽ������ �᥽�å� *****/
    meeting_schedule_manager_show.prototype.switchComplete = function (status)
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
    meeting_schedule_manager_show.prototype.switchAutoReLoad = function (targetFunction, mSec)
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
    meeting_schedule_manager_show.prototype.setLineMethod = function (flag)
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
    meeting_schedule_manager_show.prototype.zoomGanttReload = function (url)
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
    
    /***** ControlForm �� Submit �᥽�å� ��������к� *****/
    meeting_schedule_manager_show.prototype.ControlFormSubmit = function (radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // �����줬���Submit���к�
    }
    
    /***** ����������˥���������ɲä��� ��Ū�ϥǥ����ȥåפ˥��������Ž���դ���� *****/
    meeting_schedule_manager_show.prototype.addFavoriteIcon = function (url, uid)
    {
        if (!confirm("����������˥���������ɲä��ޤ���\n\n�������Ǥ�����")) return false;
        if (document.all && !window.opera) {
            if (uid >= 100 && uid <= 999999) {
                window.external.AddFavorite(url + "?calUid=" + uid, "����Ĺ�������塼��");
            } else {
                window.external.AddFavorite(url, "����Ĺ�������塼��");
            }
        }
        return false;       // ���������� �¹Ԥ��к�
    }
    
    return this;    // Object Return
    
}   /* class assembly_schedule_show END  */


///// ���󥹥��󥹤�����
var MeetingScheduleManager = new meeting_schedule_manager_show();

