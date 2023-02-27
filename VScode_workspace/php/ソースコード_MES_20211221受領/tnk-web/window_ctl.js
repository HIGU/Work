//////////////////////////////////////////////////////////////////////////////
// window_ctl.php �� JavaScript���饹                                       //
// Copyright (C) 2005-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/31 Created    window_ctl.js                                      //
// 2005/09/03 ���֥�����ɥ��κ������ 10 �� 20 ���ѹ�(maxWin)              //
//            Window���֤ν����(���)����¸���� ���Offset�ͤλ��ФΤ���   //
// 2005/09/07 ��λ���ν�����JavaScript�ǥ��饤����Ȥβ��̥���������¸����  //
//            Cookie��winW/winH �����פˤʤä�                              //
// 2005/09/09 setCookie()/getCookie��setArrayCookie()/getArrayCookie()���ѹ�//
// 2006/02/24 ���饹�᥽�åɤε��ҥ�������򵼻�(̾���ʤ�)function()�����ѹ�//
// 2014/09/22 ���ɥ쥹�С���ɽ�����ߤ�����IE7�ʹߤ��Բ�ǽ                 //
//            �ʥ�����¦�Ǥ��Բ�ǽ��������PC������Ǥϲ�ǽ                  //
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*              window_ctl class   base_class�γ�ĥ���饹���               *
/****************************************************************************
class window_ctl extends base_class
*/
///// �����ѡ����饹�ηѾ�
window_ctl.prototype = new base_class();   // base_class �ηѾ�
///// Class & Constructer �����
function window_ctl()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    this.w  = screen.availWidth;        // ���饤����Ȥβ�����
    this.h  = screen.availHeight;       // ���饤����Ȥβ��̹⤵
    this.ws = screen.Width;             // Netscape 6.1 , 7.1 �ǻȤ��ʤ�
    this.hs = screen.Height;            //      ��
    this.win_name = "win";              // ������ɥ��δ���window̾
    // this.maxWin = 15;                // base_class.js����� ������ɥ��κ����(cookie�����¤�20�ޤ�)
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** ��С������Υ��å����ǡ������� *****/
    window_ctl.prototype.dropOldVerCookie = function ()
    {
        if (this.getCookie("offX") != "") {
            this.delCookie("offX");
        }
        if (this.getCookie("offY") != "") {
            this.delCookie("offY");
        }
        if (this.getCookie("winW") != "") {
            this.delCookie("winW");
        }
        if (this.getCookie("winH") != "") {
            this.delCookie("winH");
        }
        var win_name;   // ���������
        var data;
        for (var i=1; i<=this.maxWin; i++) {
            win_name = ("win" + i);
            if ( (data=this.getCookie(win_name)) == "") {
                break;
            } else if (data == '0') {
                this.delCookie(win_name);
            } else if (data == '1') {
                this.delCookie(win_name);
            }
            if ( (data=this.getCookie(win_name+"W")) != "") {
                this.delCookie(win_name+"W");
            }
            if ( (data=this.getCookie(win_name+"H")) != "") {
                this.delCookie(win_name+"H");
            }
            if ( (data=this.getCookie(win_name+"X")) != "") {
                this.delCookie(win_name+"X");
            }
            if ( (data=this.getCookie(win_name+"Y")) != "") {
                this.delCookie(win_name+"Y");
            }
        }
    }
    
    /***** ��˥塼�ѤΥ��֥�����ɥ�̾��������� *****/
    window_ctl.prototype.setWinName = function ()
    {
        var name = "win";
        var open_flg = 0;
        for (var i=1; i<=this.maxWin; i++) {
            name = ("win" + i);
            open_flg = this.getArrayCookie(name, name);
            if (open_flg == "1") continue;
            break;
        }
        this.win_name = name;
        return name;
    }
    
    /***** ��˥塼�ѤΥ��֥�����ɥ��򥪡��ץ󤹤� *****/
    window_ctl.prototype.subWin_open = function ()
    {
        var Cw = this.getArrayCookie(this.win_name, "winW");
        var Ch = this.getArrayCookie(this.win_name, "winH");
        if (Cw != "" && Ch != "") {
            var w = Cw;
            var h = Ch;
        } else {
            ///// ����ͤ�����
            if (this.w > 1024) {
                // 1024 X 768
                var w = 1024;
                //var w = 1280;
                //var h =  768;
                var h = 768;
                var left = (this.w - w) / 2;
                var top  = (this.h - h) / 2;
                w -= 12; h -= 80;   // ��Ĵ����ɬ��
            } else {
                // 1024 ̤��(800X600, 640X480 ��)
                // X:-12  Y:-80 ��Ĵ��
                var w = (this.w - 12);
                var h = (this.h - 80);
                var left = 0;
                var top  = 0;
            }
        }
        this.setArrayCookie(this.win_name, "winW", w);  // Window���礭������¸
        this.setArrayCookie(this.win_name, "winH", h);
        ///// �����Window���֤μ����������å�������
        var xData = this.getArrayCookie(this.win_name, "winX");
        var yData = this.getArrayCookie(this.win_name, "winY");
        if (xData != '' && yData != '') {
            left = xData;
            top  = yData;
        } else {
            ///// ���ξ��Ͻ���ͤ���¸
            this.setArrayCookie(this.win_name, "winX", left);
            this.setArrayCookie(this.win_name, "winY", top);
        }
        window.open("menu_frame.php", this.win_name, "menubar=yes,resizable=yes,width="+w+",height="+h+",left="+left+",top="+top+",screenX="+left+",screenY="+top);
            // fullscreen=yes �ե륹���꡼��⡼�� IE ���� ��˥塼���Фʤ����᥹�饤�ɥ��硼���ǻ���(WinXP�Ǥϵդ˥�˥塼���ФƤ��ޤ�)
            // ���ֻ���� screenX=20,screenY=50 �� NN��  left=20,top=50 �� left,top��IE��
        this.setArrayCookie(this.win_name, this.win_name, "1");
    }
    
    /***** ��ʬ���ȤΥ����������ѹ��ʷ����Ĥ��ʤ��� *****/
    window_ctl.prototype.chgLocation = function (url)
    {
        location.replace(url);
        // location.replace("http://www.tnk.co.jp/authenticate.php?background=on");
    }
    
    /***** �ƥ����ѤΥ�å��������� *****/
    window_ctl.prototype.test_disp = function ()
    {
        var msg = "";
        msg += "���Υѥ�����β��̥�������\n\n";
        msg += " ����" + this.w  + "\n\n";
        msg += " ���" + this.h  + "\n\n";
        msg += " ����" + this.ws + "\n\n";
        msg += " ���" + this.hs + "\n\n";
        alert(msg);
    }
    
    ///// Constructer �μ¹���
    /***** ����� *****/
    this.dropOldVerCookie();    // ���Ū�˵�С������Υǡ�������᥽�åɤ������
    this.setWinName();
    this.subWin_open();
    
    return this;    // Object Return
}   // class window_ctl END


