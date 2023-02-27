//////////////////////////////////////////////////////////////////////////////
// ��Ω�Υ饤���̹��� �Ƽ殺���               MVC View��(JavaScript���饹) //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/12 Created    assembly_time_graph.js                             //
// 2006/05/24 stop_blink()�᥽�åɤ��ɲä��������ɽ���������Ǥ�ߤ��      //
// 2006/07/08 ���С���blink_id_name �ν���ͤ����� ���ꤵ������θƽ��б� //
// 2006/09/27 ����ե�����(�����׻���ˡ)�Υ��ץ����(���������׻�)�ɲ�    //
// 2006/11/02 ����ղ�������Ψ������ɲ� targetScale                        //
// 2007/01/16 ������ɽ��ON/OFF�ɲ� targetPastData checkConditionForm()  //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_time_graph class base_class �γ�ĥ���饹�����            *
/****************************************************************************
class assembly_time_graph extends base_class
*/
///// �����ѡ����饹�ηѾ�
assembly_time_graph.prototype = new base_class();    // base_class �ηѾ�
///// Constructer �����
function assembly_time_graph()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "����դ�������������ꤷ�Ʋ�������"; //     ��      , checkANDexecute(), viewClear()
    this.intervalID;                                    // �����Ѥ�intervalID
    this.blink_id_name = "blink_item";                  // �����оݤ� ID̾ ID='???' ����ͤ�Default��
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    assembly_time_graph.prototype.set_focus = function (obj, status)
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
    assembly_time_graph.prototype.blink_disp = function (id_name)
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
    assembly_time_graph.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    assembly_time_graph.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    assembly_time_graph.prototype.win_open = function (url, w, h)
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
    assembly_time_graph.prototype.winActiveChk = function ()
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
    assembly_time_graph.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm �����ϥ����å��᥽�å�(����������λ���������ֹ�) *****/
    assembly_time_graph.prototype.checkConditionForm = function (obj)
    {
        if (!obj.targetDateYM.value) {
            alert("����ǯ����(YYYYMM)�����Ϥ���Ƥ��ޤ���");
            obj.targetDateYM.focus();
            // obj.targetDateYM.select();
            return false;
        }
        if (obj.targetDateYM.value.length != 6) {
            alert("����ǯ����(YYYYMM)�η���ϣ���Ǥ���");
            obj.targetDateYM.focus();
            // obj.targetDateYM.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateYM.value)) {
            alert("����ǯ����(YYYYMM)�Ͽ��������Ϥ��Ʋ�������");
            obj.targetDateYM.focus();
            // obj.targetDateYM.select();
            return false;
        }
        // obj.targetLine.value = obj.targetLine.value.toUpperCase();
        // if (obj.targetLine.value.length != 4) {
        if (obj.lineView.value.length <= 0) {
            alert("�饤���ֹ椬���ꤵ��Ƥ��ޤ���");
            obj.elements["targetLine[]"].focus();
            // obj.targetLine.focus();
            // obj.targetLine.select();
            return false;
        }
        if (!this.isDigit(obj.targetSupportTime.value)) {
            alert("���������Ͽ��������Ϥ��Ʋ�������");
            obj.targetSupportTime.focus();
            // obj.targetSupportTime.select();
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
        if ( (obj.targetScale.value >= 0.3) && (obj.targetScale.value <= 1.7) ) {
            obj.exec.focus();       // obj.targetProcess �Υե��������򳰤�����
        } else {
            alert("��Ψ���꤬�����Ǥ���");
            obj.targetScale.focus();
            return false;
        }
        this.parameter  = "&targetDateYM=" + obj.targetDateYM.value;
        this.parameter += "&targetSupportTime=" + obj.targetSupportTime.value;
        this.parameter += "&targetGraphType=" + obj.targetGraphType.value;
        this.parameter += "&targetScale=" + obj.targetScale.value;
        // this.parameter += "&targetProcess=" + obj.targetProcess.value;
        // this.parameter += "&targetLine=" + obj.targetLine.value;
        this.setTargetLineArray(obj.elements["targetLine[]"]);
        if (obj.targetPastData.checked) {
            this.parameter += "&targetPastData=1";
        }
        return true;
    }
    
    /***** ConditionForm �����ϥ����å��򤷤�Ajax�¹� *****/
    assembly_time_graph.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("Graph", "showAjax");
            // ���ǤΥ�å��������ѹ�����
            this.blink_msg = "����դΥС��򥯥�å���������٤�ɽ�����ޤ���";
            this.stop_blink();
        }
        return false;   // �ºݤ�submit�Ϥ����ʤ�
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    // onReadyStateChange���٥�Ȥ�Ȥäƽ�������λ���Ƥ��ʤ�����WaitMessage����ϡ�
    // parameter : ListTable=���ɽ��, WaitMsg=������Ǥ������Ԥ���������
    assembly_time_graph.prototype.AjaxLoadTable = function (showMenu, location)
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
            xmlhttp.open("GET", "assembly_time_graph_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
    /***** ���ɽ���ΰ�Υ��ꥢ���᥽�å� *****/
    assembly_time_graph.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // ���ǤΥ�å������������֤��᤹
        this.blink_msg = "����դ�������������ꤷ�Ʋ�������";
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** �᥽�åɼ����ˤ��WaitMessageɽ�� *****/
    assembly_time_graph.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>������Ǥ������Ԥ���������<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** lineView ��ɽ���ѥ��ԡ��᥽�å� *****/
    assembly_time_graph.prototype.lineViewCopy = function (obj)
    {
        document.ConditionForm.lineView.value = "";
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                if (document.ConditionForm.lineView.value == "") {
                    document.ConditionForm.lineView.value += obj.options[i].text;
                } else {
                    document.ConditionForm.lineView.value += (", " + obj.options[i].text);
                }
            }
        }
    }
    
    /***** ConditionForm.targetLine[] ������ǡ�����GET�ѥ�᡼�����˥��å� *****/
    assembly_time_graph.prototype.setTargetLineArray = function (obj)
    {
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                                // URL���󥳡��ɽ��� 2006/11/06 �ɲ�            �ʲ���value��text�ˤ�����
                this.parameter += "&targetLine" + escape("[]") + "=" + obj.options[i].value;
            }
        }
    }
    
    return this;    // Object Return
    
}   /* class assembly_time_graph END  */


///// ���󥹥��󥹤�����
var AssemblyTimeGraph = new assembly_time_graph();

