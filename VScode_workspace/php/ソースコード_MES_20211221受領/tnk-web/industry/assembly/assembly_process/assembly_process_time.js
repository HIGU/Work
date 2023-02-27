//////////////////////////////////////////////////////////////////////////////
// ��Ω�ؼ���˥塼�� ��ꡦ��λ���� ������  MVC View �� (JavaScript���饹) //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/17 Created    assembly_process_time.js                           //
// 2005/11/18 ���å�����group_no��¾�Υ�˥塼���Զ�礬�Ф뤿��DSgroup_no��//
//            �ܹԤ�����å����ѹ��� Ʊ��������assembly_process_Main.php  //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
// 2006/05/19 �᥽�åɤ򵼻�function()���ѹ� win_open()��windowName�����   //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_process_time class base_class �γ�ĥ���饹�����            *
/****************************************************************************
class assembly_process_time extends base_class
*/
///// �����ѡ����饹�ηѾ�
assembly_process_time.prototype = new base_class();   // base_class �ηѾ�
///// Constructer �����
function assembly_process_time()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "";                               //     ��      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax�̿����Υѥ�᡼����
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    assembly_process_time.prototype.set_focus = function (obj, status)
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
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    assembly_process_time.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    assembly_process_time.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    assembly_process_time.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, 'regTime_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);        
        
        /*
        w -= 15; h -= 25;   // ��Ĵ����ɬ��
        window.open(url, 'regClame_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);        
        */
    }
    
    /***** ���������Ŭ�������� *****/
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    assembly_process_time.prototype.win_openc = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 100; h -= 300;   // ��Ĵ����ɬ��
        window.open(url, 'regClame_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    assembly_process_time.prototype.winActiveChk = function ()
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
    assembly_process_time.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** user_form �����ϥ����å��᥽�å�(��Ω��� ��ȼ�����) *****/
    assembly_process_time.prototype.user_formCheck = function (obj)
    {
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
    
    /***** start_form �����ϥ����å��᥽�å�(��Ω��� �ײ��ֹ�����) *****/
    assembly_process_time.prototype.start_formCheck = function start_formCheck(obj)
    {
        obj.plan_no.value = obj.plan_no.value.toUpperCase();
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
    
    /***** group_form �����ϥ����å��᥽�å�(��Ω���롼�׺�ȶ����Ͽ���ѹ�) *****/
    assembly_process_time.prototype.group_formCheck = function (obj)
    {
        // obj.group_no.value = obj.group_no.value.toUpperCase();    // ����Τ���
        if (obj.Ggroup_no.value.length == 0) {
            alert("���롼���ֹ椬�֥�󥯤Ǥ���");
            obj.Ggroup_no.focus();
            obj.Ggroup_no.select();
            return false;
        }
        if (obj.Ggroup_no.value.length > 3) {
            alert("���롼���ֹ�η���ϣ���ޤǤǤ���");
            obj.Ggroup_no.focus();
            obj.Ggroup_no.select();
            return false;
        }
        if (!this.isDigit(obj.Ggroup_no.value)) {
            alert("���롼���ֹ�Ͽ��������Ϥ��Ʋ�������");
            obj.Ggroup_no.focus();
            obj.Ggroup_no.select();
            return false;
        }
        if (obj.group_name.value.length == 0) {
            alert("���롼��(��ȶ�)̾�Τ��֥�󥯤Ǥ���");
            obj.group_name.focus();
            obj.group_name.select();
            return false;
        }
        if (obj.group_name.value.length > 10) {
            alert("���롼��(��ȶ�)̾�Τϣ���ʸ���ޤǤǤ���");
            obj.group_name.focus();
            obj.group_name.select();
            return false;
        }
        return true;
    }
    
    /***** group_no ���ѹ�����Cookie����¸�塢���̹��� *****/
    assembly_process_time.prototype.groupChange = function (group_no, url)
    {
        if (this.isDigit(group_no)) {
            if (group_no.length <= 3) {
                this.setCookie('DSgroup_no', group_no);
                location.replace(url);
            }
        }
        return false;
    }
    
    /***** ControlForm �� Submit �᥽�å� ��������к� *****/
    assembly_process_time.prototype.ControlFormSubmit = function (radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // �����줬���Submit���к�
    }
    
    return this;    // Object Return
    
}   /* class assembly_process_time END  */


///// ���󥹥��󥹤�����
var AssemblyProcessTime = new assembly_process_time();

