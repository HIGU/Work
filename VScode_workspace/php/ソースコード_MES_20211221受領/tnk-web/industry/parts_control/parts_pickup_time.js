//////////////////////////////////////////////////////////////////////////////
// �����������ʽи� ��ꡦ��λ���� ������  MVC View �� (JavaScript���饹) //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/12 Created    parts_pickup_time.js                               //
// 2005/09/28 �С������ɤΤ���� Z �� @ ���Ѵ� ���å���᥽�åɤ��ɲ�     //
// 2005/09/30 set_focus()�᥽�åɤ� status Parameter �ɲ�                   //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*         parts_pickup_time class �ƥ�ץ졼�Ȥγ�ĥ���饹�����           *
/****************************************************************************
class parts_pickup_time extends base_class
{   */
    ///// �����ѡ����饹�ηѾ�
    parts_pickup_time.prototype = new base_class();   // base_class �ηѾ�
    ///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
    var _GDEBUG = false;
    
    ///// Constructer �����
    function parts_pickup_time()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // �ץ�ѥƥ����ν����
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        parts_pickup_time.prototype.set_focus           = set_focus;            // ��������ϥ�����Ȥ˥ե�������
        parts_pickup_time.prototype.blink_disp          = blink_disp;           // ����ɽ���᥽�å�
        parts_pickup_time.prototype.obj_upper           = obj_upper;            // ���֥������ͤ���ʸ���Ѵ�
        parts_pickup_time.prototype.win_open            = win_open;             // ���֥�����ɥ��������ɽ��
        parts_pickup_time.prototype.winActiveChk        = winActiveChk;         // ���֥�����ɥ���Active�����å�
        parts_pickup_time.prototype.win_show            = win_show;             // �⡼�������������ɽ��(IE����)
        parts_pickup_time.prototype.start_formCheck     = start_formCheck;      // start_form �����ϥ����å��᥽�å�
        parts_pickup_time.prototype.user_formCheck      = user_formCheck;       // user_form �����ϥ����å��᥽�å�
        parts_pickup_time.prototype.ControlFormSubmit   = ControlFormSubmit;    // ControlForm �Υ��֥ߥåȥ᥽�å�
        
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
    
    /***** start_form �����ϥ����å��᥽�å�(�и��������) *****/
    function start_formCheck(obj) {
        obj.plan_no.value = obj.plan_no.value.toUpperCase();
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
        if (obj.plan_no.value.length == 0) {
            // alert("�ײ��ֹ椬�֥�󥯤Ǥ���");
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
        return true;
    }
    
    /***** user_form �����ϥ����å��᥽�å�(�и˺�ȼԤ���Ͽ���ѹ�) *****/
    function user_formCheck(obj) {
        obj.user_id.value = obj.user_id.value.toUpperCase();    // ����Τ���
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
        if (obj.user_name.value.length == 0) {
            if (confirm("��̾���֥�󥯤Ǥ���\n\n�ϣˤ򥯥�å������̾���򸡺����ޤ���")) return true;
            obj.user_name.focus();
            obj.user_name.select();
            return false;
        }
        if (obj.user_name.value.length > 8) {
            alert("��̾�η���ϣ���ޤǤǤ���");
            obj.user_name.focus();
            obj.user_name.select();
            return false;
        }
        return true;
    }
    
    /***** ControlForm �� Submit �᥽�å� ��������к� *****/
    function ControlFormSubmit(radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // �����줬���Submit���к�
    }
    
/*
}   // class parts_pickup_time END  */


///// ���󥹥��󥹤�����
var PartsPickupTime = new parts_pickup_time();
// blink_disp()�᥽�å���ǻ��Ѥ��륰���Х��ѿ��Υ��å�
var blink_flag = 1;


