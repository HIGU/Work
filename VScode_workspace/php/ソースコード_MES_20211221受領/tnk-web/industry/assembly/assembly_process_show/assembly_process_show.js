//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ��� ��ꡦ��λ�ǡ��� �Ȳ���  MVC View �� (JavaScript���饹)   //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created    assembly_process_show.js                           //
// 2006/01/20 Ajax�Ѥ�tableɽ���ѥ᥽�åɤ��ɲ� AjaxLoadStart (������)    //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*     assembly_process_show class �ƥ�ץ졼�Ȥγ�ĥ���饹�����           *
/****************************************************************************
class assembly_process_show extends base_class
{   */
    ///// �����ѡ����饹�ηѾ�
    assembly_process_show.prototype = new base_class();   // base_class �ηѾ�
    ///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
    var _GDEBUG = false;
    
    ///// Constructer �����
    function assembly_process_show()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // �ץ�ѥƥ����ν����
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        assembly_process_show.prototype.set_focus           = set_focus;            // ��������ϥ�����Ȥ˥ե�������
        assembly_process_show.prototype.blink_disp          = blink_disp;           // ����ɽ���᥽�å�
        assembly_process_show.prototype.obj_upper           = obj_upper;            // ���֥������ͤ���ʸ���Ѵ�
        assembly_process_show.prototype.win_open            = win_open;             // ���֥�����ɥ��������ɽ��
        assembly_process_show.prototype.winActiveChk        = winActiveChk;         // ���֥�����ɥ���Active�����å�
        assembly_process_show.prototype.win_show            = win_show;             // �⡼�������������ɽ��(IE����)
        assembly_process_show.prototype.checkInputForm      = checkInputForm;       // ���ϥե���������ϥ����å��᥽�å�
        assembly_process_show.prototype.AjaxLoadStart       = AjaxLoadStart;        // Ajax����Ѥ���������������Ѽ¹ԥ᥽�å�
        
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
    
    /***** checkInputForm �����ϥ����å��᥽�å�(�ײ��ֹ�, �Ұ��ֹ�) *****/
    function checkInputForm(obj) {
        obj.plan_no.value = obj.plan_no.value.toUpperCase();
        if (obj.plan_no.value.length == 0) {
            alert("�ײ��ֹ椬�֥�󥯤Ǥ���");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (obj.plan_no.value.length != 8) {
            alert("�ײ��ֹ�η���ϣ���Ǥ���");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (!this.isDigit(obj.plan_no.value.substr(2, 6))) {
            alert("�ײ��ֹ�Σ�ʸ���ܰʹߤϿ��������Ϥ��Ʋ�������");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (obj.plan_no.value.substr(0, 1) == 'Z') {
            // �С������ɤΤ���� Z �� @ ���Ѵ�
            obj.plan_no.value = '@' + obj.plan_no.value.substr(1, 7);
        }
        if (obj.user_id.value.length == 0) {
            alert("��ȼԤμҰ��ֹ椬�֥�󥯤Ǥ���");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        if (obj.user_id.value.length != 6) {
            alert("��ȼԤμҰ��ֹ�η���ϣ���Ǥ���");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        if (!this.isDigit(obj.user_id.value)) {
            alert("��ȼԤμҰ��ֹ�Ͽ��������Ϥ��Ʋ�������");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        return true;
    }
    
    /***** ���̹�����桼�����˰��´�̵��ɽ��������Ajax�ѥ���ɥ᥽�å� *****/
    function AjaxLoadStart()
    {
        var parm = "?";
        parm += "&showMenu=StartTable"  // table�Τ����
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
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
            xmlhttp.open("GET", "assembly_process_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\n�򥪡��ץ����ޤ���\n\n" + e);
        }
    }
    
/*
}   // class assembly_process_show END  */


///// ���󥹥��󥹤�����
var AssemblyProcessShow = new assembly_process_show();
// blink_disp()�᥽�å���ǻ��Ѥ��륰���Х��ѿ��Υ��å�
var blink_flag = 1;


