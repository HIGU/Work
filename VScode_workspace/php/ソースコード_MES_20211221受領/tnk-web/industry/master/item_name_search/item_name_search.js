//////////////////////////////////////////////////////////////////////////////
// �����ƥ�ޥ���������̾�ˤ����������ʬ���� MVC View��(JavaScript���饹)  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/10 Created    item_name_search.js                                //
// 2006/04/14 �ǽ�ζ�����������ɽ���� /^ +/i /^��+/i ���ѹ�              //
//            viewClear()�᥽�åɼ¹Ի��� targetItemName.value �⥯�ꥢ     //
// 2006/05/22 ����ˤ��ޥ������������ɲ� targetItemMaterial               //
// 2006/05/23 �߸˥����å����ץ������ɲ� targetStockOption �¹Ի����Ǥ�off//
// 2006/07/05 ���С���blink_id_name �ν���ͤ����� ���ꤵ������θƽ��б� //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*          item_name_search class base_class �γ�ĥ���饹�����            *
/****************************************************************************
class item_name_search extends base_class
*/
///// �����ѡ����饹�ηѾ�
item_name_search.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function item_name_search()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "��̾�ޤ��Ϻ���˸���ʸ���������Enter�������¹ԥܥ���򲡤��Ʋ�������";
                                                        //     ��      , checkANDexecute(), viewClear()
    this.intervalID;                                    // �����Ѥ�intervalID
    this.blink_id_name = "blink_item";                  // �����оݤ� ID̾ ID='???' ����ͤ�Default��
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    item_name_search.prototype.set_focus = function (obj, status)
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
    item_name_search.prototype.blink_disp = function (id_name)
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
    item_name_search.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    item_name_search.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    item_name_search.prototype.win_open = function (url, w, h)
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
    item_name_search.prototype.winActiveChk = function ()
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
    item_name_search.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å�(����������λ���������ֹ�) *****/
    item_name_search.prototype.checkConditionForm = function (obj)
    {
        // ��̾��
        obj.targetItemName.value = obj.targetItemName.value.toUpperCase();
        obj.targetItemName.value = obj.targetItemName.value.replace(/^ +/i, "");    // �ǽ��Ⱦ�ѥ��ڡ���
        obj.targetItemName.value = obj.targetItemName.value.replace(/^��+/i, "");   // �ǽ�����ѥ��ڡ���
        // �����
        obj.targetItemMaterial.value = obj.targetItemMaterial.value.toUpperCase();
        obj.targetItemMaterial.value = obj.targetItemMaterial.value.replace(/^ +/i, "");    // �ǽ��Ⱦ�ѥ��ڡ���
        obj.targetItemMaterial.value = obj.targetItemMaterial.value.replace(/^��+/i, "");   // �ǽ�����ѥ��ڡ���
        ///// ���� ��̾�Ⱥ���Υ����å�
        if ( (!obj.targetItemName.value) && (!obj.targetItemMaterial.value) ) {
            alert("��̾������Τɤ��餫������ɬ�����Ϥ��Ʋ�������");
            obj.targetItemName.focus();
            obj.targetItemName.select();
            return false;
        }
        if ( (obj.targetItemName.value) && (obj.targetItemMaterial.value) ) {
            if (confirm("��̾�Ⱥ���Τɤ�������Ϥ���Ƥ��ޤ��Τ�\n\n��̾��ͥ�褵��ޤ����������Ǥ�����")) {
                // return true;
            } else {
                obj.targetItemName.focus();
                obj.targetItemName.select();
                return false;
            }
        }
        ///// ���ʥ��롼�פΥ����å�
        switch (obj.targetDivision.value) {
        case "A" :  // ���٤�
        case "C" :
        case "L" :
        case "T" :
        case "O" :  // OTHER ����¾ ��������ʬ
            obj.exec.focus();       // obj.targetDivision �Υե��������򳰤�����
            break;
        default :
            alert("���ʶ�ʬ�������Ǥ���");
            obj.targetDivision.focus();
            return false;
        }
        ///// �߸˥����å����ץ����Υ����å�
        switch (obj.targetStockOption.value) {
        case "3" :  // �߸ˤ�����ʪ
        case "2" :  // �߸˷��򤬤���ʪ
        case "1" :  // �߸˥ޥ�����������ʪ
        case "0" :  // �߸ˤ�̵�뤹��
            obj.exec.focus();       // obj.targetDivision �Υե��������򳰤�����
            break;
        default :
            alert("�߸˥����å����ץ���������Ǥ���");
            obj.targetStockOption.focus();
            return false;
        }
        // ���Х���ʸ�������Ϥ����Τǥ��󥳡��ɤ�ɬ�פʤΤ���escape()��Ajax�Ǥϻ��Ѥ��ʤ�
        this.parameter  = "&targetItemName="        + obj.targetItemName.value;
        this.parameter += "&targetItemMaterial="    + obj.targetItemMaterial.value;
        this.parameter += "&targetDivision="        + obj.targetDivision.value;
        this.parameter += "&targetStockOption="     + obj.targetStockOption.value;
        this.parameter += "&targetLimit="           + obj.targetLimit.value;
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    item_name_search.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
            // ���ǤΥ�å��������ѹ�����
            this.blink_msg = "�����ֹ�򥯥�å�����к߸˷����ɽ�����ޤ������ܥ���å��ǥ����Ȥ��ޤ���";
            this.stop_blink();
        }
        // ��̾��������˥ե�������
        obj.targetItemName.focus();
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    item_name_search.prototype.AjaxLoadTable = function (showMenu, location)
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
            xmlhttp.open("GET", "item_name_search_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    item_name_search.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        document.ConditionForm.targetItemName.value = "";
        document.ConditionForm.targetItemMaterial.value = "";
        // ���ǤΥ�å������������֤��᤹
        this.blink_msg = "��̾�ޤ��Ϻ���˸���ʸ���������Enter�������¹ԥܥ���򲡤��Ʋ�������";
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    item_name_search.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** item_name_search_ViewHeader.html�ѤΥ����ȹ��� ��Ĵ ɽ�� �᥽�å� *****/
    item_name_search.prototype.highlight = function ()
    {
        if (location.search.substr(1, 10) == "item=parts") {
            // document.getElementById("parts").style.color = "white";
            document.getElementById("parts").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=name") {
            // document.getElementById("name").style.color = "white";
            document.getElementById("name").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 13) == "item=material") {
            // document.getElementById("date").style.color = "white";
            document.getElementById("date").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 11) == "item=parent") {
            // document.getElementById("in_pcs").style.color = "white";
            document.getElementById("in_pcs").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=date") {
            // document.getElementById("stock").style.color = "white";
            document.getElementById("stock").style.backgroundColor = "#ffffc6";
        }
    }
    
    return this;    // Object Return
    
}   /* class item_name_search END  */


///// ���󥹥��󥹤�����
var ItemNameSearch = new item_name_search();

