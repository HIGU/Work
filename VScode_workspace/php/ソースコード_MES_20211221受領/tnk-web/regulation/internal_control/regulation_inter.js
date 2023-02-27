//////////////////////////////////////////////////////////////////////////////
// ���⵬�� site �� JavaScript���饹                                        //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created    regulation.js                                      //
// 2006/02/22 win_open()�᥽�åɤΥ�����ɥ��ν�����å����ѹ�            //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*            regulation class base_class�γ�ĥ���饹�����                 *
/****************************************************************************
class regulation extends base_class
*/
///// �����ѡ����饹�ηѾ�
regulation.prototype = new base_class();   // base_class �ηѾ�
///// Constructer �����
function regulation()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // �ץ�ѥƥ����ν����
    this.blink_flag = 1;                                // blink_disp()�᥽�å���ǻ��Ѥ���
    this.blink_msg  = "";                               // ��
    this.winObj     = new Array();                      // win_open()�᥽�å���ǻ��Ѥ���
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** ����ɽ����HTML�ɥ������ *****/
    /***** blink_flg �ϥ����Х��ѿ������ �������0.5��������� *****/
    /***** <body onLoad='setInterval("templ.blink_disp(\"caption\")", 500)'> *****/
    regulation.prototype.blink_disp = function (id_name)
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
    
    /***** �ѥ�᡼�����ǻ��ꤵ�줿���֥������ȤΥ�����Ȥ˥ե������������� *****/
    regulation.prototype.set_focus = function (obj, status)
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
    
    /***** ���֥������Ȥ��ͤ���ʸ���Ѵ����� *****/
    regulation.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** ������礭���Υ��֥�����ɥ��������ɽ������ *****/
    /***** Windows XP SP2 �Ǥϥ������ƥ��ηٹ𤬽Ф�  *****/
    regulation.prototype.win_open = function (url, winName, w, h)
    {
        if (!w) w = 964;     // �����
        if (!h) h = 708;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        for (var i=0; i<20; i++) {
            if (!this.winObj[i]) {
                left = (left + (20 * i));
                top  = (top  + (20 * i));
                if (!winName) winName = "ReguWin" + i;
                this.winObj[i] = window.open(url, winName, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
                break;
            } else if (this.winObj[i].closed) {
                // �����Ĥ���줿������ɥ�����������
                this.winObj[i] = "";
            }
        }
        /*****      �ǥХå���
        this.winObj[i].document.title = winName;
        var msg = "";
        for (var j in this.winObj[i]) {
            msg += j + " => " + this.winObj[i][j] + "\n";
        }
        alert(msg);
        *****/
    }
    
    /***** ���֥�����ɥ�¦��Window��Active�����å���Ԥ� *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    regulation.prototype.winActiveChk = function ()
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
    regulation.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // �����
        if (!h) h = 600;     // �����
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    
    return this;    // Object Return
}   // class regulation END


///// ���󥹥��󥹤�����
var Regu = new regulation();

