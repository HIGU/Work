////////////////////////////////////////////////////////////////////////////////
// ������Ư�����ؼ����ƥʥ�                                               //
//                                            MVC View �� (JavaScript���饹)  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring.js                                           //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*     monitoring class �ƥ�ץ졼�Ȥγ�ĥ���饹�����           *
/****************************************************************************
class monitoring extends base_class
{   */
    ///// �����ѡ����饹�ηѾ�
    monitoring.prototype = new base_class();   // base_class �ηѾ�
    ///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
    var _GDEBUG = false;
    
    ///// Constructer �����
    function monitoring()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // �ץ�ѥƥ����ν����
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        monitoring.prototype.set_focus        = set_focus;        // ��������ϥ�����Ȥ˥ե�������
        monitoring.prototype.blink_disp       = blink_disp;       // ����ɽ���᥽�å�
        monitoring.prototype.obj_upper        = obj_upper;        // ���֥������ͤ���ʸ���Ѵ�
        monitoring.prototype.win_open         = win_open;         // ���֥�����ɥ��������ɽ��
        monitoring.prototype.winActiveChk     = winActiveChk;     // ���֥�����ɥ���Active�����å�
        monitoring.prototype.win_show         = win_show;         // �⡼�������������ɽ��(IE����)
        monitoring.prototype.ControlFormSubmit= ControlFormSubmit;// ControlForm �Υ��֥ߥåȥ᥽�å�
        monitoring.prototype.checkANDexecute  = checkANDexecute;  // �Ժ߼ԤΥ�����ɥ�ɽ��
        monitoring.prototype.AjaxLoadTable    = AjaxLoadTable;    // �Ժ߼ԤΥ�����ɥ�ɽ��2
        monitoring.prototype.AjaxLoadPITable  = AjaxLoadPITable;  // PI���������Υ�����ɥ�ɽ��
        
        return this;    // Object Return
    }
    
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    function set_focus(obj, status)
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
    
    /***** ����ɽ����HTML�ɥ������ *****/
    /***** blink_flg �ϥ����Х��ѿ������ �������0.5��������� *****/
    /***** <body onLoad='setInterval("templ.blink_disp(\"caption\")", 500)'> *****/
    function blink_disp(id_name)
    {
        if (blink_flag == 1) {
            document.getElementById(id_name).innerHTML = "";
            blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = "����ץ�ǥ����ƥ�ޥ�������ɽ�����Ƥ��ޤ�";
            blink_flag = 1;
        }
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    function obj_upper(obj) {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    // ��ư�����ʥ���ɡ�����
    function init() 
    {
        setInterval('document.reload_form.submit()', 30000);   // 30��
    }
    
    // ��ư�����ʥ���ɡˤʤ�
    function init2() 
    {
        var obj = document.getElementById('id_plan_no');
        if( obj ) set_focus(obj, "select");
    }
    
    // ���Ϥ����ײ��ֹ�η�������å�
    function planNoCheck() {
        if( document.getElementById('id_plan_no').value.length < 8 ) {
            alert("�ײ��ֹ�ϡ�����ɬ�פǤ���");
            return false;
        } else {
            return true;
        }
    }
    
    function setState(obj) {
        document.getElementById('id_state').value = obj.name;
//alert("setState(" + obj.name + ")");
        return true;
    }

    function setSelectMode(obj) {
        document.getElementById('id_select_mode').value = obj.value;
        document.header_form.submit();
//        obj.submit();
//alert("TEST");
        return true;
    }

    function setViewMode(obj) {
        document.getElementById('id_view_mode').value = obj.id;
        document.header_form.submit();
//        obj.submit();
//alert("TEST");
        return true;
    }
    
    //
    function setSlectInfo(rec){
        document.getElementById('id_m_no').value = document.getElementById('id_m_no'+rec).value;
        document.getElementById('id_m_name').value = document.getElementById('id_m_name'+rec).value;
        document.getElementById('id_plan_no').value = document.getElementById('id_plan_no'+rec).value;
//alert("setSlectInfo(" + rec + ")");
    }

    function chk_break_del(obj, mac_no, name, plan_no, parts_no) {
        var flag = confirm(   "�����ֹ桧" + mac_no + "\n\n"
                        + "�� �� ̾��" + name + "\n\n"
                        + "�ײ��ֹ桧" + plan_no + "\n\n"
                        + "�����ֹ桧" + parts_no + "\n\n"
                        + "����������ޤ����������Ǥ�����");
        if( flag ) {
            obj.value = 'delete';
            return true;
        } else {
            return false;
        }
    }
    
    function chk_break_restart(obj, mac_no, name, plan_no, parts_no) {
        var flag = confirm(   "�����ֹ桧" + mac_no + "\n\n"
                        + "�� �� ̾��" + name + "\n\n"
                        + "�ײ��ֹ桧" + plan_no + "\n\n"
                        + "�����ֹ桧" + parts_no + "\n\n"
                        + "��Ƴ����ޤ����������Ǥ�����");
        if( flag ) {
            obj.value = 'restart';
            return true;
        } else {
            return false;
        }
    }
    
    function chk_end_inst(obj, mac_no, name, plan_no, parts_no) {
        var flag = confirm(   "�����ֹ桧" + mac_no + "\n\n"
                        + "�� �� ̾��" + name + "\n\n"
                        + "�ײ��ֹ桧" + plan_no + "\n\n"
                        + "�����ֹ桧" + parts_no + "\n\n"
                        + "��λ���ޤ����������Ǥ�����");
        if( flag ) {
            obj.value = 'end';
            return true;
        } else {
            return false;
        }
    }
    
    function chk_cut_form(obj, mac_no, name, plan_no, parts_no) {
        var flag = confirm(   "�����ֹ桧" + mac_no + "\n\n"
                        + "�� �� ̾��" + name + "\n\n"
                        + "�ײ��ֹ桧" + plan_no + "\n\n"
                        + "�����ֹ桧" + parts_no + "\n\n"
                        + "�����Ǥ��ޤ����������Ǥ�����");
        if( flag ) {
            obj.value = 'break';
            return true;
        } else {
            return false;
        }
    }

    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    function win_open(url, w, h) {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    function winActiveChk() {
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
    function win_show(url, w, h) {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ControlForm �� Submit �᥽�å� ��������к� *****/
    function ControlFormSubmit(radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // �����줬���Submit���к�
    }
    
    /***** ControlForm �����ϥ����å��򤷤�Ajax�¹� *****/
    function checkANDexecute(flg)
    {
        // confirm("����������˥���������ɲä��ޤ���\n\n�������Ǥ�����");
            if (flg == 1) {
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 2){
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            } else if (flg == 3) {
                this.parameter += "&requireDate=yes"
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 4){
                this.parameter += "&requireDate=yes"
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            } else if (flg == 5){
                this.parameter += "&noMenu=yes";
                this.AjaxLoadPITable("ListWin", "showAjax");
            } else if (flg == 6){        // ��ãȯ�������Ȳ���
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("NotiWin", "showAjax");
            } else if (flg == 7){        // ���������Ȳ���
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("EizWin", "showAjax");
            } else {
                this.AjaxLoadTable("List", "showAjax");
            }
            // ���ǤΥ�å��������ѹ�����
            // this.blink_msg = "�����ֹ�";
            // this.stop_blink();
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    function AjaxLoadTable(showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("monitoring_absence_Main.php"+parm, 500, 400);
            return;
        }
        // ��ãȯ�������Ȳ���
        if (showMenu == "NotiWin") {    // �̥�����ɥ���ɽ��
            this.win_open("notification.php"+parm, 1100, 600);
            return;
        }
        // ���������Ȳ���
        if (showMenu == "EizWin") {    // �̥�����ɥ���ɽ��
            this.win_open("notification_eizen.php"+parm, 1200, 600);
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
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "monitoring_absence_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    function AjaxLoadPITable(showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("monitoring_pi_Main.php"+parm, 1000, 600);
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
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "monitoring_pi_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
/*
}   // class monitoring END  */

///// ���󥹥��󥹤�����
var Monitoring = new monitoring();
// blink_disp()�᥽�å���ǻ��Ѥ��륰���Х��ѿ��Υ��å�
var blink_flag = 1;

