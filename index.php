<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
setlocale(LC_TIME, 'french');
ob_start('ob_gzhandler');
session_cache_limiter('public, must-revalidate');
date_default_timezone_set('Africa/Abidjan');
//header('Cache-Control: max-age=31536000, must-revalidate');
require_once('admin/config.php');
require_once('vendor/autoload.php');
//$Session->setFlash('Message envoyé avec succès','success');

/********** AUTH ***********/
require_once('class/PHPAuth/languages/fr_FR.php');
require_once('class/PHPAuth/Config.php');
require_once('class/PHPAuth/Auth.php');
require_once('class/swiftmailer/lib/swift_required.php'); 
require_once('class/DomParser/simple_html_dom.php'); 
/**********************/

$config = new PHPAuth\Config($DB);
$auth   = new PHPAuth\Auth($DB, $config, 'fr_FR');

$__LANGUES_TAB__    =   array('fr','en','es');
$__DEFAULT_LANG__   =   'fr';

if(isset($_GET['url']) && !empty($_GET['url'])){
    $sql = "SELECT id, slug_fr FROM categories_articles WHERE valid = 1 AND statut = 1";
    $req = $DB->prepare($sql); 
    $req->execute();
    $categories = array();
    while($row = $req->fetch()){
        $categories[$row['id']] = strtolower($row['slug_fr']);
    }

    $_URL_PART = explode('/', strtolower($_GET['url']));

    if(!empty($_URL_PART)){

        if(in_array($_URL_PART[0],$__LANGUES_TAB__)){
            $_GET['lang'] = $_URL_PART[0];
            if(isset($_URL_PART[1])){

                if(in_array($_URL_PART[1],$categories)){

                    $_GET['categorie_slug'] = $_URL_PART[1];
                    if(isset($_URL_PART[2]) && $_URL_PART[2] != 'p' && !is_numeric($_URL_PART[2])){
                        $_GET['article_slug'] = $_URL_PART[2];
                        if(isset($_URL_PART[3]) && is_numeric($_URL_PART[3])){
                            $_GET['id_article'] = $_URL_PART[3];
                        }elseif(isset($_URL_PART[3]) && !is_numeric($_URL_PART[3])){
                            $_GET['sous_page'] = $_URL_PART[3];
                        }
                    }elseif($_URL_PART[2] = 'p' && isset($_URL_PART[3]) && is_numeric($_URL_PART[3])){
                        $_GET['p'] = $_URL_PART[3];
                    }else{
                       $_GET['p'] = 1; 
                    }
               }
                $_GET['page'] = $_URL_PART[1];
                
            }else{
               $_GET['page'] = $_URL_PART[1]; 
            }
            array_shift($_URL_PART);
        }else{
            if(in_array($_URL_PART[0],$categories)){
                $_GET['categorie_slug'] = $_URL_PART[0];
                if(isset($_URL_PART[1]) && $_URL_PART[1] != 'p' && !is_numeric($_URL_PART[1])){
                    $_GET['article_slug'] = $_URL_PART[1];
                    if(isset($_URL_PART[2]) && is_numeric($_URL_PART[2])){
                        $_GET['id_article'] = $_URL_PART[2];
                    }elseif(isset($_URL_PART[2]) && !is_numeric($_URL_PART[2])){
                        $_GET['sous_page'] = $_URL_PART[2];
                    }
                }elseif($_URL_PART[1] = 'p' && isset($_URL_PART[2]) && is_numeric($_URL_PART[2])){
                    $_GET['p'] = $_URL_PART[2];
                }else{
                   $_GET['p'] = 1; 
                }
            }
            $_GET['lang'] = $__DEFAULT_LANG__;
            $_GET['page'] = $_URL_PART[0];
        }

    }else{
        $_GET['lang'] = $__DEFAULT_LANG__;
        $_GET['page'] = 'home';
    }

    array_shift($_URL_PART);
    $_GET['params'] = $_URL_PART;
    // Dans le cas ou on a un appel ajax, page commençant par 'ajax_'
    $ajax = explode('_', strtolower($_GET['url']));
    if(isset($ajax[0]) && !empty($ajax[0]) && $ajax[0]== 'ajax'){
        $_GET['xhr'] = true;
    }else{
        $_GET['xhr'] = false;
    }

}else{
    $_GET['lang'] = $__DEFAULT_LANG__;
    $_GET['page'] = 'home';
}

/*
// Create the message
$message = Swift_Message::newInstance();
$message->setTo(array(
   'didier.mambo@gmail.com' => 'didier.mambo@gmail.com'
));
//$message->setCc(array("another@fake.com" => "Aurelio De Rosa"));
//$message->setBcc(array("boss@bank.com" => "Bank Boss"));
$message->setContentType("text/html");
$message->setSubject('test email');
$message->setBody('test');
$message->setFrom('GICOP@gicop.ci');
//$message->attach(Swift_Attachment::fromPath(""));

// Send the email
$mailer = Swift_Mailer::newInstance($transport);
$mailer->send($message, $failedRecipients);
*/
//var_dump($_GET);
$to_email = array('test@test.fr'=>'test@test.fr');
sendEmail($to_email,'didier.mambo@gmail.com','test');

require_once('controllers/baseCtrl.php');

file_exists('controllers/'.$_GET['page'].'Ctrl.php') ? include_once('controllers/'.$_GET['page'].'Ctrl.php') : null; 
isset($_GET['xhr']) && $_GET['xhr'] ? null : include_once(WEBROOT.'header.tpl');
isset($view) && file_exists(WEBROOT.$view) ? include_once(WEBROOT.$view) : include_once(WEBROOT.'content.tpl');
isset($_GET['xhr']) && $_GET['xhr'] ? null : include_once(WEBROOT.'footer.tpl');

