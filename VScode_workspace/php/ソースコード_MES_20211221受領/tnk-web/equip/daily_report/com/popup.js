// CONTEXT_PATH �����ǧ��
if (CONTEXT_PATH == "") {
    alert("CONTEXT_PATH �����ꤵ��Ƥ��ޤ���");
}
//���ѥݥåץ��å�ɽ��
// url      ... URL
// arg      ... �ƤӽФ������ץ����� HttpServletRequest ���̤����Ϥ������ե�����ɡ������
function ShowDialog(url,arg,width,height) {
    var args;
    if((arg != null) && (typeof(arg) != "undefined")) {
        var prmLen = 0;
        for (i=0; i<arg.length; i++) {
            if( typeof(arg[i].length) == "undefined" ) {
                prmLen++;
            } else {
                prmLen+=arg[i].length;
            }
        }
        args = new Array(prmLen+1);
        args[0] = url;
        var k = 0;
        for(i=0; i<arg.length; i++) {
            if( typeof(arg[i].length) == "undefined" ) {
                args[k+1] = arg[i];
                k++;
            } else {
                for (j=0 ; j<arg[i].length ; j++) {
                    args[k+1] = arg[i][j];
                    k++;
                }
            }

        }
    } else {
        args = new Array(1);
        args[0] = url;
    }
    return window.showModalDialog(CONTEXT_PATH + "/com/popup.php",args,"status:no; dialogWidth:" + width + "px; dialogHeight:" + height + "px; center:yes" );
}
//�ݥåץ��å׸����¹�
// pgm      ... �����ץ����̾ ( URL )
// target   ... ������̤�ȿ���о� Field
// btn      ... ������λ��� click() ���٥�Ȥ�ȯ�����������ܥ���ʾ�ά�ġ�
// arg      ... �ƤӽФ������ץ����� HttpServletRequest ���̤����Ϥ������ե�����ɡ������
function SearchClick(pgm,target,arg) {
    var width;
    var height;

    width   = "600";
    height  = "400";

    var retValue = ShowDialog(getUrl(pgm),arg,width,height);
    if(( retValue != null ) && ( typeof(retValue) != "undefined" )) {
        retValue.replace(/ /g,"").match(/(.+)��(.+)/);
        target.value = RegExp.$1;
        if (( btn != null ) && ( typeof(btn) != "undefined" )) {
            btn.click();
        }
    }
}
// ���������УϣХ��å�
// pgm      ... �����ץ����̾ ( URL )
// year     ... ������̤�ȿ���о� Field
// month    ... ������̤�ȿ���о� Field
// day      ... ������̤�ȿ���о� Field
// btn      ... ������λ��� click() ���٥�Ȥ�ȯ�����������ܥ���ʾ�ά�ġ�
// arg      ... �ƤӽФ������ץ����� HttpServletRequest ���̤����Ϥ������ե�����ɡ������
function CalendarPopup(pgm,year,month,day) {
    var width;
    var height;
    
    width   = "600";
    height  = "400";
    
    var QueryString = "";
    if (year.value != "" && month.value != "") {
        var QueryString = "?CalYear=" + year.value + "&CalMonth=" + month.value;
    }
    
    var retValue = ShowDialog(getUrl(pgm + QueryString),null,width,height);
    
    if(( retValue != null ) && ( typeof(retValue) != "undefined" )) {
        
        year.value  = retValue.substring(0,4);
        month.value = retValue.substring(4,6);
        day.value   = retValue.substring(6,8);
        
    }
}

//���ߤδĶ��˹�碌��URL������
function getUrl(target) {
    var host = window.location.host;
    var path = window.location.pathname;

    //�ѥ�̾����ǽ�γ�����ʬ�����
    path.match(/\/(.+)\//);

    return "http://" + host + "/" + RegExp.$1 + "/" + target;
}

