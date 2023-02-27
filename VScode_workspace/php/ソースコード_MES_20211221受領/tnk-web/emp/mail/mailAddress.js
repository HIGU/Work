//////////////////////////////////////////////////////////////////////////////
// �Ұ��ޥ������Υ᡼�륢�ɥ쥹 �Ȳ񡦥��ƥʥ�                          //
//                                           MVC View �� (JavaScript���饹) //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/15 Created    mailAddress.js                                     //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*           mailAddress class �ƥ�ץ졼�Ȥγ�ĥ���饹�����               *
/****************************************************************************
class mailAddress extends base_class
{   */
    ///// �����ѡ����饹�ηѾ�
    mailAddress.prototype = new base_class();   // base_class �ηѾ�
    ///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
    var _GDEBUG = false;
    
    ///// Constructer �����
    function mailAddress()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // �ץ�ѥƥ����ν����
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        mailAddress.prototype.set_focus        = set_focus;        // ��������ϥ�����Ȥ˥ե�������
        mailAddress.prototype.blink_disp       = blink_disp;       // ����ɽ���᥽�å�
        mailAddress.prototype.obj_upper        = obj_upper;        // ���֥������ͤ���ʸ���Ѵ�
        mailAddress.prototype.win_open         = win_open;         // ���֥�����ɥ��������ɽ��
        mailAddress.prototype.winActiveChk     = winActiveChk;     // ���֥�����ɥ���Active�����å�
        mailAddress.prototype.win_show         = win_show;         // �⡼�������������ɽ��(IE����)
        mailAddress.prototype.mail_formCheck   = mail_formCheck;   // mail_form �����ϥ����å��᥽�å�
        
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
    
    /***** mail_form �����ϥ����å��᥽�å�(�Ұ��ֹ�, �᡼�륢�ɥ쥹) *****/
    function mail_formCheck(obj) {
        // obj.uid.value = obj.uid.value.toUpperCase();
        if (obj.uid.value.length == 0) {
            alert("�Ұ��ֹ椬�֥�󥯤Ǥ���");
            obj.uid.focus();
            obj.uid.select();
            return false;
        }
        if (!this.isDigit(obj.uid.value)) {
            alert("�Ұ��ֹ�Ͽ��������Ϥ��Ʋ�������");
            obj.uid.focus();
            obj.uid.select();
            return false;
        }
        if (obj.uid.value < 1 || obj.uid.value > 999999) {
            alert("�Ұ��ֹ�ϣ��������������飹�����������ޤǤǤ���");
            obj.uid.focus();
            obj.uid.select();
            return false;
        }
        if (obj.mailaddr.value.length == 0) {
            alert("�᡼�륢�ɥ쥹���֥�󥯤Ǥ���");
            obj.mailaddr.focus();
            obj.mailaddr.select();
            return false;
        }
        return true;
    }
    
/*
}   // class mailAddress END  */


///// ���󥹥��󥹤�����
var mailAddress = new mailAddress();
// blink_disp()�᥽�å���ǻ��Ѥ��륰���Х��ѿ��Υ��å�
var blink_flag = 1;


