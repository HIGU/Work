//////////////////////////////////////////////////////////////////////////////
// ���������λ��֡�����ν��׎�ʬ�� ��� �Ȳ�   MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/08/09 Created   acceptance_inspection_analyze.js                    //
// 2006/11/30 dateCreate()�᥽�åɤ�targetDateYM����������λ�StrEnd�򥯥ꥢ//
// 2006/12/05 win_open()�᥽�åɤ��ѿ�̾�Υߥ����� name �� winName        //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     acceptance_inspection_analyze class base_class �γ�ĥ���饹�����    *
/****************************************************************************
class acceptance_inspection_analyze extends base_class
*/
///// �����ѡ����饹�ηѾ�
acceptance_inspection_analyze.prototype = new base_class();    // base_class �ηѾ�
///// class & Constructer �����
function acceptance_inspection_analyze()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "";                               //     ��      , checkANDexecute(), viewClear()
    this.intervalID;                                    // �����Ѥ�intervalID
    this.blink_id_name;                                 // �����оݤ� ID̾ ID='???'
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    acceptance_inspection_analyze.prototype.set_focus = function (obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
    }
    
    /***** ����ɽ���᥽�å� *****/
    /***** blink_flg Private property �������0.5��������� *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    acceptance_inspection_analyze.prototype.blink_disp = function (id_name)
    {
        this.blink_id_name = id_name;
        if (this.blink_flag == 1) {
            // ����ͤ�ץ�ѥƥ��ǻ��ꤷ������ʲ��򥳥���
            // this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** ���ǥ��ȥåץ᥽�å� *****/
    acceptance_inspection_analyze.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    acceptance_inspection_analyze.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    acceptance_inspection_analyze.prototype.win_open = function (url, w, h, winName)
    {
        if (!winName) winName = "";
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, winName, 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    acceptance_inspection_analyze.prototype.winActiveChk = function ()
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
    acceptance_inspection_analyze.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** DOM ����Ѥ������٥����Ͽ�᥽�å� *****/
    acceptance_inspection_analyze.prototype.addListener = function (obj, eventType, func, cap)
    {
        if (obj.attachEvent) {                          // IE �ξ��
            obj.attachEvent("on" + eventType, func);
        } else if (obj.addEventListener) {              // IE �ʳ�
            obj.addEventListener(eventType, func, cap);
        } else {
            alert("�����ѤΥ֥饦�����ϡ����Υץ�����̤�б��Ǥ���");
            return false;
        }
    }
    
    /***** DOM ����Ѥ������٥������(ʣ����)�᥽�å� *****/
    acceptance_inspection_analyze.prototype.setEventListeners = function (eventType, ID)
    {
        var eSource = document.getElementById(ID);
        this.addListener(eSource, eventType, catchEventListener, false);  // catchEventListener()��ñ�ȴؿ�
        ///// �嵭�� eventType = "click" �� "Click" �Τ褦����ʸ������Ѥ��ƤϤ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    acceptance_inspection_analyze.prototype.AjaxLoadTable = function (Action, showMenu, location)
    {
        if (!location) location = "showAjax1";   // Default�ͤ�����
        var parm = "?";
        parm += "Action=" + Action;
        parm += "&showMenu=" + showMenu;
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("acceptance_inspection_analyze_Main.php"+parm, 700, 350);
            return;
        }
        /***
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        ***/
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
                try {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
                } catch (e) {}
                if (location == "showCateList") {
                    AcceptanceInspectionAnalyze.setEventListeners("change", "targetCategory");
                    AcceptanceInspectionAnalyze.setEventListeners("focus", "targetCategory");
                    document.getElementById("targetCategory").focus();
                }
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                if (location != "showCateList" && location != "showIDName") {
                    try {
                    document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
                    } catch (e) {}
                }
            }
        }
        var url = "acceptance_inspection_analyze_Main.php" + parm;
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** URL���� Ajax�ѥ��ɥ᥽�å� *****/
    acceptance_inspection_analyze.prototype.AjaxLoadUrl = function (url, location)
    {
        if (!location) location = "showAjax1";   // Default�ͤ�����
        if (!url) return;   // URL�����ꤵ��Ƥ��ʤ���н�λ
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
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    acceptance_inspection_analyze.prototype.viewClear = function (showAjax)
    {
        document.getElementById(showAjax).innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        // this.blink_msg = "�����ֹ�";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    acceptance_inspection_analyze.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** �ꥹ�ȥܥå��������򤷤����դγ��ϡ���λ���դ򥻥åȤ��� *****/
    acceptance_inspection_analyze.prototype.dateCreate = function (obj)
    {
        if (!obj) return;     // parameter�Υ����å�
        if (!obj.targetDateYM.value) {      // 2006/11/30 ADD
            obj.targetDateStr.value = "";
            obj.targetDateEnd.value = "";
            return;
        }
        obj.targetDateStr.value = obj.targetDateYM.value + "01";
        var yyyy = obj.targetDateStr.value.substr(0, 4);
        var mm   = obj.targetDateStr.value.substr(4, 2);
        if (mm == 12) {
            yyyy = (yyyy - 0 + 1);      // ʸ�������ͤ��Ѵ����뤿�� - 0���Ƥ��롣
            mm = 0;
        }
        var dateEnd = new Date(yyyy, mm, 1, 0, 0, 0)    // ��������ե��֥������Ȥ����
        dateEnd.setTime(dateEnd.getTime() - 1000);      // �������ˤ����������ˤ���
        yyyy = dateEnd.getYear();
        mm   = dateEnd.getMonth() + 1;
        var dd = dateEnd.getDate();
        if (yyyy < 2000) { yyyy += 1900; }
        if (mm < 10) { mm = "0" + mm; }
        if (dd < 10) { dd = "0" + dd; }
        obj.targetDateEnd.value = (yyyy + "" + mm + dd);
        return;
    }
    
    /***** �������Ƥ� ��ǧ �� �¹� �᥽�å� *****/
    acceptance_inspection_analyze.prototype.checkExecute = function (obj)
    {
        if (!obj) return;     // parameter�Υ����å�
        if (!obj.targetDateStr.value) {
            alert("�������դ����Ϥ���Ƥ��ޤ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        // �������դ�����ɽ���ǥޥå��󥰥����å�
        if (!obj.targetDateStr.value.match(/^[2][0](?:[012][0-9]|[3][0])(?:[0][1-9]|[1][0-2])(?:[0][1-9]|[12][0-9]|[3][01])$/)) {
            alert("�������դ��ְ�äƤ��ޤ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        // �嵭�ϰʲ��Ǥ�OK (?:����)
        // if (!obj.targetDateStr.value.match(/^[2][0]([012][0-9]|[3][0])([0][1-9]|[1][0-2])([0][1-9]|[12][0-9]|[3][01])$/)) {
        // ����ɽ���ǿ��ͣ���Υ����å�
        // if (!obj.targetDateStr.value.match(/^\d{8}$/)) {
        
        if (!obj.targetDateEnd.value) {
            alert("��λ���դ����Ϥ���Ƥ��ޤ���");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        // ��λ���դ�����ɽ���ǥޥå��󥰥����å�
        if (!obj.targetDateEnd.value.match(/^[2][0](?:[012][0-9]|[3][0])(?:[0][1-9]|[1][0-2])(?:[0][1-9]|[12][0-9]|[3][01])$/)) {
            alert("��λ���դ��ְ�äƤ��ޤ���");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        // �������դȽ�λ���դε�ž�����å�
        if (obj.targetDateStr.value > obj.targetDateEnd.value) {
            alert("�������դȽ�λ���դ���ž���Ƥ��ޤ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        // return; // �ƥ�����Ϥ����ǽ�λ
        // Ajax�ѥѥ�᡼�����򥻥å�
        this.parameter  = "&targetDateStr="   + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd="   + obj.targetDateEnd.value;
        return true;
    }
    
    /***** ���¥��С��Υꥹ�Ȼؼ� �᥽�å� *****/
    acceptance_inspection_analyze.prototype.listID = function (division)
    {
        // Ajax�ѥѥ�᡼�����򥻥å�
        this.parameter  = "&targetDivision="   + division;
        // Ajax�̿�
        this.AjaxLoadTable("ListID", "ListID", "showAjax2");
    }
    
    /***** ���¥��С��κ�� ��ǧ �� �¹� �᥽�å� *****/
    acceptance_inspection_analyze.prototype.deleteID = function (id, division)
    {
        if (confirm(id + "\n\n�������ޤ����������Ǥ�����")) {
            // Ajax�ѥѥ�᡼�����򥻥å�
            this.parameter  = "&targetID="   + id;
            this.parameter  += "&targetDivision="   + division;
            // Ajax�̿�
            this.AjaxLoadTable("DeleteID", "ListID", "showAjax2");
        }
    }
    
    ///// Constructer
    this.addListener(self, "load", setInitOnLoad, false);
    
    return this;    // Object Return
    
}   /* class acceptance_inspection_analyze END  */


///// ���󥹥��󥹤�����
var AcceptanceInspectionAnalyze = new acceptance_inspection_analyze();

/***** ������ɡ����٥������ *****/
function setInitOnLoad()
{
    AcceptanceInspectionAnalyze.set_focus(document.ConditionForm.targetDateYM, "noSelect");
    // AcceptanceInspectionAnalyze.intervalID = setInterval("AcceptanceInspectionAnalyze.blink_disp(\"blink_item\")", 1300);
    AcceptanceInspectionAnalyze.setEventListeners("change", "targetDateYM");
    AcceptanceInspectionAnalyze.setEventListeners("submit", "ConditionForm");
    AcceptanceInspectionAnalyze.setEventListeners("click", "ListLeadTime");
    AcceptanceInspectionAnalyze.setEventListeners("click", "showWin");
    AcceptanceInspectionAnalyze.setEventListeners("click", "WinListLeadTime");
    AcceptanceInspectionAnalyze.setEventListeners("click", "clear");
    AcceptanceInspectionAnalyze.setEventListeners("click", "ListInspectionTime");
    AcceptanceInspectionAnalyze.setEventListeners("click", "WinListInspectionTime");
}

function catchEventListener(evtObj)
{
    // �֥饦������Ƚ��
    if (evtObj.srcElement) {        // IE
        var id = evtObj.srcElement.id;
    } else if (evtObj.target) {     // IE �ʳ�
        var id = evtObj.target.id;
    } else {
        alert("�����ѤΥ֥饦������̤�б��Ǥ���");
        return false;
    }
    // id �ˤ������ο�ʬ
    switch (id) {
    case "targetDateYM":
        AcceptanceInspectionAnalyze.dateCreate(document.ConditionForm);
        return false;
    case "ConditionForm":
    case "ListLeadTime":
        if (AcceptanceInspectionAnalyze.checkExecute(document.ConditionForm)) {
            // Ajax�̿�
            AcceptanceInspectionAnalyze.AjaxLoadTable("ListLeadTime", "List", "showAjax");
        }
        return false;
    case "showWin":
    case "WinListLeadTime":
        if (AcceptanceInspectionAnalyze.checkExecute(document.ConditionForm)) {
            var parm = "?";
            parm += "Action=ListLeadTime";
            parm += "&showMenu=ListWin";
            parm += AcceptanceInspectionAnalyze.parameter;
            AcceptanceInspectionAnalyze.win_open("acceptance_inspection_analyze_Main.php"+parm, 750, 500, "AIA_ListLeadTime");
        }
        return false;
        alert("window�ϸ��߽�����Ǥ���");
    case "clear":
        AcceptanceInspectionAnalyze.viewClear("showAjax");
        return false;
    case "ListInspectionTime":
        if (AcceptanceInspectionAnalyze.checkExecute(document.ConditionForm)) {
            // Ajax�̿�
            AcceptanceInspectionAnalyze.AjaxLoadTable("ListInspectionTime", "List", "showAjax");
        }
        return false;
    case "WinListInspectionTime":
        if (AcceptanceInspectionAnalyze.checkExecute(document.ConditionForm)) {
            var parm = "?";
            parm += "Action=ListInspectionTime";
            parm += "&showMenu=ListWin";
            parm += AcceptanceInspectionAnalyze.parameter;
            AcceptanceInspectionAnalyze.win_open("acceptance_inspection_analyze_Main.php"+parm, 750, 500, "AIA_ListInspectionTime");
        }
        return false;
    default:
        alert("�֥饦������NN�ξ�硢ͽ�۳��Υ��٥�Ȥ�ȯ�����ޤ���\n\nID = "+id);
        return false;
    }
}

