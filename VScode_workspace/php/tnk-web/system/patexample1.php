<?PHP
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // define.php と pgsql.php を require_once している
access_log();                           // Script Name は自動取得
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