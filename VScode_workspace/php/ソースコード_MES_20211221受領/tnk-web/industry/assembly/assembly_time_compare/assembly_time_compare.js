//////////////////////////////////////////////////////////////////////////////
// ��Ω�δ������������ӹ�������Ͽ���������  MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created    assembly_time_compare.js                           //
// 2006/03/12 win_open()�᥽�åɤ�resizable=yes�ˤ���̾��̵�����ѹ�         //
// 2006/03/13 ���ʶ�ʬ������Ȥ��� targetDivision ���ɲ�                    //
// 2006/05/10 ���ȡ���ư������������ �̤˾Ȳ񥪥ץ������ɲ�           //
// 2006/08/31 ���ܥ����ȵ�ǽ �ɲäˤ�� highlight() �᥽�åɤ����          //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_time_compare class base_class �γ�ĥ���饹�����            *
/****************************************************************************
class assembly_time_compare extends base_class
*/
///// �����ѡ����饹�ηѾ�
assembly_time_compare.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function assembly_time_compare()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "���������ϰϤ���ꤷ�Ʋ�������"; //     ��      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    assembly_time_compare.prototype.set_focus = function (obj, status)
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
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    assembly_time_compare.prototype.blink_disp = function (id_name)
    {
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
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    assembly_time_compare.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    assembly_time_compare.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, '', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    assembly_time_compare.prototype.winActiveChk = function ()
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
    assembly_time_compare.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å�(����������λ���������ֹ�) *****/
    assembly_time_compare.prototype.checkConditionForm = function (obj)
    {
        // obj.targetDateStr.value = obj.targetDateStr.value.toUpperCase();
        if (!obj.targetDateStr.value) {
            alert("����ǯ����(YYYYMMDD)�����Ϥ���Ƥ��ޤ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.targetDateStr.value.length != 8) {
            alert("����ǯ����(YYYYMMDD)�η���ϣ���Ǥ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateStr.value)) {
            alert("����ǯ����(YYYYMMDD)�Ͽ��������Ϥ��Ʋ�������");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (!obj.targetDateEnd.value) {
            alert("����ǯ����(YYYYMMDD)�����Ϥ���Ƥ��ޤ���");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        if (obj.targetDateEnd.value.length != 8) {
            alert("����ǯ����(YYYYMMDD)�η���ϣ���Ǥ���");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateEnd.value)) {
            alert("����ǯ����(YYYYMMDD)�Ͽ��������Ϥ��Ʋ�������");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        switch (obj.targetDivision.value) {
        case "AL" :
        case "CA" :
        case "CH" :
        case "CS" :
        case "LA" :
        case "LH" :
        case "LB" :
            obj.exec.focus();       // obj.targetDivision �Υե��������򳰤�����
            break;
        default :
            alert("���ʶ�ʬ�������Ǥ���");
            obj.targetDivision.focus();
            return false;
        }
        switch (obj.targetProcess.value) {
        case "H" :
        case "M" :
        case "G" :
        case "A" :
            obj.exec.focus();       // obj.targetProcess �Υե��������򳰤�����
            break;
        default :
            alert("������ʬ�������Ǥ���");
            obj.targetProcess.focus();
            return false;
        }
        this.parameter  = "&targetDateStr="  + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd="  + obj.targetDateEnd.value;
        this.parameter += "&targetAssyNo="   + obj.targetAssyNo.value;
        this.parameter += "&targetDivision=" + obj.targetDivision.value;
        this.parameter += "&targetProcess="  + obj.targetProcess.value;
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    assembly_time_compare.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
        }
        // ���ǤΥ�å��������ѹ�����
        this.blink_msg = "���ӹ�������Ͽ�����򥯥�å���������٤�ɽ�����ޤ���";
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    assembly_time_compare.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
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
            xmlhttp.open("GET", "assembly_time_compare_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    assembly_time_compare.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        this.blink_msg = "���������ϰϤ���ꤷ�Ʋ�������";
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    assembly_time_compare.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** long_holding_parts_ViewHeader.html�ѤΥ����ȹ��� ��Ĵ ɽ�� �᥽�å� *****/
    assembly_time_compare.prototype.highlight = function ()
    {
        if (location.search.substr(1, 9) == "item=plan") {
            document.getElementById("plan").style.color = "#000000";
            document.getElementById("plan").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=assy") {
            document.getElementById("assy").style.color = "#000000";
            document.getElementById("assy").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=name") {
            document.getElementById("name").style.color = "#000000";
            document.getElementById("name").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 8) == "item=pcs") {
            document.getElementById("pcs").style.color = "#000000";
            document.getElementById("pcs").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=date") {
            document.getElementById("date").style.color = "#000000";
            document.getElementById("date").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=in_no") {
            document.getElementById("in_no").style.color = "#000000";
            document.getElementById("in_no").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 8) == "item=res") {
            document.getElementById("res").style.color = "#000000";
            document.getElementById("res").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 8) == "item=reg") {
            document.getElementById("reg").style.color = "#000000";
            document.getElementById("reg").style.backgroundColor = "#ffffc6";
        } else {
            document.getElementById("line").style.color = "#000000";
            document.getElementById("line").style.backgroundColor = "#ffffc6";
        }
    }
    
    return this;    // Object Return
    
}   /* class assembly_time_compare END  */


///// ���󥹥��󥹤�����
var AssemblyTimeCompare = new assembly_time_compare();

