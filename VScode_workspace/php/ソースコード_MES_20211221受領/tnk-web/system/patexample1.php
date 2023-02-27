<?PHP
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
access_log();                           // Script Name �ϼ�ư����
    include( "../../patTemplate/include/patTemplate.php" );

    $tmpl   =   new patTemplate();

    //  In diesem Verzeichnis liegen die Templates
    $tmpl->setBasedir( "templates" );

    $tmpl->readTemplatesFromFile( "patexample1.tmpl.html" );

    $tmpl->addVars( "listeneintrag", array( "CUSTOMER_NAME" => array( "Stephan Schmidt", "Sebastian Mordziol", "Georg Rothweiler" ),
                                    "CUSTOMER_EMAIL" => array( "stephan@metrix.de", "sebastian@metrix.de", "georg@metrix.de" ) ) );

    //  Alle Templates ausgeben
    $tmpl->displayParsedTemplate( );

    //  Debug Infos ausgeben
    echo    "<br><br>----------------------------------------------&lt;DUMP INFOS&gt;----------------------------------------------------<br><br>";
    
    $tmpl->dump();
?>