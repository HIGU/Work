//////////////////////////////////////////////////////////////////////////////
// ������夲�κ�����(������)�� �Ȳ�  (�١���) MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/15 Created    parts_material_show.js                             //
//            �᥽�åɤ�function()̵̾�ؿ�(�����ؿ�)�ε��ҥ���������ѹ�    //
// 2006/02/20 ���ɽ���ΰ�Υ��ꥢ���᥽�åɤ��ɲ�                          //
// 2006/02/21 WaitMessage��᥽�åɤˤ����� �� ����AjaxLoadTable()���ɲ�  //
// 2006/03/02 checkANDexecute()�᥽�åɤ� return false �ΰ��֤��ѹ�         //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     parts_material_show class base_class �γ�ĥ���饹�����              *
/****************************************************************************
class parts_material_show extends base_class
*/
///// �����ѡ����饹�ηѾ�
parts_material_show.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function parts_material_show()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "";                               // ��
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    parts_material_show.prototype.set_focus = function (obj, status)
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
    parts_material_show.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    parts_material_show.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    parts_material_show.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    parts_material_show.prototype.winActiveChk = function ()
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
    parts_material_show.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å�(����������λ���������ֹ�) *****/
    parts_material_show.prototype.checkConditionForm = function (obj)
    {
        obj.targetItemNo.value = obj.targetItemNo.value.toUpperCase();
        if (!obj.targetDateStr.value.length) {
            alert("�����������Ϥ���Ƥ��ޤ���");
            obj.targetDateStr.focus();
            return false;
        }
        if (!this.isDigit(obj.targetDateStr.value)) {
            alert("�������˿����ʳ������Ϥ���Ƥ��ޤ���");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.targetDateStr.value.length != 8) {
            alert("������������Ǥ���ޤ���");
            obj.targetDateStr.focus();
            return false;
        }
        if (!obj.targetDateEnd.value.length) {
            alert("��λ�������Ϥ���Ƥ��ޤ���");
            obj.targetDateEnd.focus();
            return false;
        }
        if (!this.isDigit(obj.targetDateEnd.value)) {
            alert("��λ���˿����ʳ������Ϥ���Ƥ��ޤ���");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        if (obj.targetDateEnd.value.length != 8) {
            alert("��λ��������Ǥ���ޤ���");
            obj.targetDateEnd.focus();
            return false;
        }
        if (obj.targetItemNo.value.length != 0) {
            if (obj.targetItemNo.value.length != 9) {
                alert("�����ֹ�η���ϣ���Ǥ���");
                obj.targetItemNo.focus();
                obj.targetItemNo.select();
                return false;
            }
        }
        this.parameter  = "&showDiv=" + obj.showDiv.value;
        this.parameter += "&targetDateStr=" + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd=" + obj.targetDateEnd.value;
        this.parameter += "&targetSalesSegment=" + obj.targetSalesSegment.value;
        if (obj.targetItemNo) this.parameter += "&targetItemNo=" + obj.targetItemNo.value;
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    parts_material_show.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            // AjaxLoadTable��WaitMessage���ɲä�������ʲ��򥳥��Ȥˤ�����
            // this.WaitMessage();
            // this.AjaxLoadTable("WaitMsg");
            this.AjaxLoadTable("ListTable");
        }
        return false;   // �ºݤˤ�SUBMIT�����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    parts_material_show.prototype.AjaxLoadTable = function (showMenu)
    {
        var parm = "?";
        parm += "&showMenu=" + showMenu // table�Τ����
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
                document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
                document.getElementById("showAjax").innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "parts_material_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    parts_material_show.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    parts_material_show.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class parts_material_show END  */


///// ���󥹥��󥹤�����
var PartsMaterialShow = new parts_material_show();

