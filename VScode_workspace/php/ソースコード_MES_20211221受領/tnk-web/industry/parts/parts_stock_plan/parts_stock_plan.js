//////////////////////////////////////////////////////////////////////////////
// ���� �߸� ͽ�� �Ȳ� (������ȯ������Ȳ�)     MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/25 Created    parts_stock_plan.js                                //
// 2006/05/30 �����ֹ������ɽ���ˤ��ޥå��󥰥����å��ɲ�                //
// 2006/07/27 �嵭������ɽ�����ѹ� /^[A-Z0-9]{7} �� /^[A-Z]{2}[0-9]{5}      //
// 2007/03/09 �����ֹ�CP2A723-1 /^[A-Z]{2}[0-9]{5} �� /^[A-Z]{2}[A-Z0-9]{5} //
// 2007/05/22 ����ɬ�����ξȲ���ɲ� requireDate�Υꥯ�����ȥ����쥯�Ƚ���  //
// 2007/06/22 checkConditionForm()�᥽�åɤ�noMenu�Υ����å���parameter�ɲ� //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*         parts_stock_plan class base_class �γ�ĥ���饹�����             *
/****************************************************************************
class parts_stock_plan extends base_class
*/
///// �����ѡ����饹�ηѾ�
parts_stock_plan.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function parts_stock_plan()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "�����ֹ�";                       //     ��      , checkANDexecute(), viewClear()
    this.intervalID;                                    // �����Ѥ�intervalID
    this.blink_id_name;                                 // �����оݤ� ID̾ ID='???'
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    parts_stock_plan.prototype.set_focus = function (obj, status)
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
    parts_stock_plan.prototype.blink_disp = function (id_name)
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
    parts_stock_plan.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    parts_stock_plan.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    parts_stock_plan.prototype.win_open = function (url, w, h)
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
    parts_stock_plan.prototype.winActiveChk = function ()
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
    parts_stock_plan.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å� *****/
    parts_stock_plan.prototype.checkConditionForm = function (obj)
    {
        obj.targetPartsNo.value = obj.targetPartsNo.value.toUpperCase();
        if (!obj.targetPartsNo.value) {
            alert("�����ֹ椬���Ϥ���Ƥ��ޤ���");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        if (obj.targetPartsNo.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        // �����ֹ������ɽ���ǥޥå��󥰥����å�
        if (!obj.targetPartsNo.value.match(/^[A-Z]{2}[A-Z0-9]{5}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("�����ֹ椬�ְ�äƤ��ޤ���");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        /************
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
        ************/
        this.parameter  = "&targetPartsNo=" + obj.targetPartsNo.value;
        if (obj.noMenu.value != "") {
            this.parameter += "&noMenu=" + obj.noMenu.value;
        }
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    parts_stock_plan.prototype.checkANDexecute = function (obj, flg)
    {
        if (this.checkConditionForm(obj)) {
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
            } else {
                this.AjaxLoadTable("List", "showAjax");
            }
            // ���ǤΥ�å��������ѹ�����
            // this.blink_msg = "�����ֹ�";
            // this.stop_blink();
        }
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    parts_stock_plan.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        if (showMenu == "ListWin") {    // �̥�����ɥ���ɽ��
            this.win_open("parts_stock_plan_Main.php"+parm, 950, 700);
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
            xmlhttp.open("GET", "parts_stock_plan_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    parts_stock_plan.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        // this.blink_msg = "�����ֹ�";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    parts_stock_plan.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class parts_stock_plan END  */


///// ���󥹥��󥹤�����
var PartsStockPlan = new parts_stock_plan();

