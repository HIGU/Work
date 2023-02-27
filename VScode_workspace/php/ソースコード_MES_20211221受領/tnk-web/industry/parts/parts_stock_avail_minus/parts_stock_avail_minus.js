//////////////////////////////////////////////////////////////////////////////
// ���� �߸ˡ�ͭ�����ѿ��ޥ��ʥ��ꥹ��         MVC View ��(JavaScript���饹)//
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/02 Created    parts_stock_avail_minus.js                         //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*       parts_stock_avail_minus class base_class �γ�ĥ���饹�����        *
/****************************************************************************
class parts_stock_avail_minus extends base_class
*/
///// �����ѡ����饹�ηѾ�
parts_stock_avail_minus.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function parts_stock_avail_minus()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "���ʥ��롼�פ����򤷤Ʋ�������";
                                                        //     ��      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    parts_stock_avail_minus.prototype.set_focus = function (obj, status)
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
    parts_stock_avail_minus.prototype.blink_disp = function (id_name)
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
    parts_stock_avail_minus.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    parts_stock_avail_minus.prototype.win_open = function (url, w, h)
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
    parts_stock_avail_minus.prototype.winActiveChk = function ()
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
    parts_stock_avail_minus.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å�(���ʥ��롼��) *****/
    parts_stock_avail_minus.prototype.checkConditionForm = function (obj)
    {
        // �����ֹ������ɽ���ǥޥå��󥰥����å�
        /***************
        if (!obj.targetPartsNo.value.match(/^[A-Z]{2}[A-Z0-9]{5}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("�����ֹ椬�ְ�äƤ��ޤ���");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        ***************/
        switch (obj.targetDivision.value) {
        case "AL" :
        case "CA" :
        case "CH" :
        case "CS" :
        case "LA" :
        case "LL" :
        case "LB" :
            obj.exec.focus();       // obj.targetDivision �Υե��������򳰤�����
            break;
        default :
            alert("���ʥ��롼�פ�����ǲ�������");
            obj.targetDivision.focus();
            return false;
        }
        this.parameter = "&targetDivision=" + obj.targetDivision.value;
        this.parameter += "&targetMinusItem=" + obj.targetMinusItem.value;
        this.parameter += "&searchPartsNo=" + obj.searchPartsNo.value;
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    parts_stock_avail_minus.prototype.sortClear = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.parameter += "&Action=ClearSort";
            this.AjaxLoadTable("List", "showAjax");
        }
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    parts_stock_avail_minus.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
        }
        // ���ǤΥ�å��������ѹ�����
        this.blink_msg = "�����Ȥ��������ܤ򥯥�å����Ʋ�������";
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    parts_stock_avail_minus.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default�ͤ�����
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframe�Τ����
        parm += this.parameter;
        this.parameter = "";    // �����(����)
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
            xmlhttp.open("GET", "parts_stock_avail_minus_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    parts_stock_avail_minus.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        this.blink_msg = "���ʥ��롼�פ����򤷤Ʋ�������";
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    parts_stock_avail_minus.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** EditFactorForm �����ϥ����å��᥽�å�(�װ����ܡ��װ�����) *****/
    parts_stock_avail_minus.prototype.checkEditFactorForm = function (obj)
    {
        obj.targetFactorName.value = obj.targetFactorName.value.substr(0, 5);
        if (obj.targetFactorName.value.replace(/[ ��]+/g, "") == "") {
            alert("�װ����ܤ����Ϥ���Ƥ��ޤ���");
            obj.targetFactorName.focus();
            obj.targetFactorName.select();
            return false;
        } else if (obj.targetFactorExplanation.value.replace(/[ ��]+/g, "") == "") {
            alert("�װ����������Ϥ���Ƥ��ޤ���");
            obj.targetFactorExplanation.focus();
            obj.targetFactorExplanation.select();
            return false;
        } else if (!obj.targetFactor) {
            alert("�װ��ֹ椬¸�ߤ��ޤ���");
            return false;
        }
        this.parameter = "&Action=EditFactor";
        this.parameter += "&targetFactor=" + obj.targetFactor.value;
        this.parameter += "&targetFactorName=" + obj.targetFactorName.value;
        this.parameter += "&targetFactorExplanation=" + obj.targetFactorExplanation.value;
        this.AjaxLoadTable("FactorMnt", "showAjax");
        return false;
    }
    
    /***** �װ��ޥ������κ�� ��ǧ �� �¹� �᥽�å� *****/
    parts_stock_avail_minus.prototype.deleteFactor = function (factor, factor_name)
    {
        if (confirm(factor_name + "\n\n�������ޤ����������Ǥ�����")) {
            // Ajax�ѥѥ�᡼�����򥻥å�
            this.parameter = "&Action=DeleteFactor";
            this.parameter += "&targetFactor="   + factor;
            // Ajax�̿�
        this.AjaxLoadTable("FactorMnt", "showAjax");
        }
    }
    
    /***** �װ��ޥ�������ͭ����̵�������� �᥽�å� *****/
    parts_stock_avail_minus.prototype.activeFactor = function (factor, factor_name, active)
    {
        if (confirm(factor_name + "\n\n�� ��" + active + "�� �ˤ��ޤ����������Ǥ�����")) {
            // Ajax�ѥѥ�᡼�����򥻥å�
            this.parameter = "&Action=ActiveFactor";
            this.parameter += "&targetFactor="   + factor;
            // Ajax�̿�
            this.AjaxLoadTable("FactorMnt", "showAjax");
        }
    }
    
    /***** �װ��ޥ������ν����Τ�����Ͽ�ե�����˥��ԡ� �᥽�å� *****/
    parts_stock_avail_minus.prototype.copyFactor = function (factor, name, explanation)
    {
        window.form.document.EditFactorForm.targetFactor.value = factor;
        window.form.document.EditFactorForm.targetFactorName.value = name;
        window.form.document.EditFactorForm.targetFactorExplanation.value = explanation;
        window.form.document.EditFactorForm.cancelButton.style.visibility = "visible";
        window.form.document.EditFactorForm.targetFactorName.focus();
        window.form.document.EditFactorForm.targetFactorName.select();
        alert("��Ͽ�ե�����˥��ԡ����ޤ�����\n\n����������Ͽ�ܥ���򲡤��Ʋ�������");
    }
    
    /***** �װ��ޥ�������OPTIONS�� FactorName��FactorExplanation�˥��ԡ� �᥽�å� *****/
    parts_stock_avail_minus.prototype.selectOptionsLink = function (obj, obj2)
    {
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                obj2.options[i].selected = true;
            } else {
                obj2.options[i].selected = false;
            }
        }
    }
    
    return this;    // Object Return
    
}   /* class parts_stock_avail_minus END  */


///// ���󥹥��󥹤�����
var PartsStockAvailMinus = new parts_stock_avail_minus();

