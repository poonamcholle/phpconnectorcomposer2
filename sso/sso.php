<?php

namespace MiniOrange;

include_once 'autoload.php';

use MiniOrange\Classes\Actions\ProcessResponseAction;
use MiniOrange\Classes\Actions\ProcessUserAction;
use MiniOrange\Classes\Actions\ReadResponseAction;
use MiniOrange\Classes\Actions\TestResultActions;
use MiniOrange\Helper\Constants;
use MiniOrange\Helper\Messages;
use MiniOrange\Helper\Utilities;
use MiniOrange\Helper\PluginSettings;

final class SSO
{

    public function __construct()
    {   

        $pluginSettings = PluginSettings::getPluginSettings();
        if(array_key_exists('SAMLResponse', $_REQUEST) && !empty($_REQUEST['SAMLResponse'])) {
            try{
                $relayStateUrl   = array_key_exists('RelayState', $_REQUEST) ? $_REQUEST['RelayState'] : '/';
                $samlResponseObj = ReadResponseAction::execute(); //read the samlResponse from IDP
                $responseAction = new ProcessResponseAction($samlResponseObj);
                $responseAction->execute();
                $ssoemail        = current(current($samlResponseObj->getAssertions())->getNameId());
                $attrs           = current($samlResponseObj->getAssertions())->getAttributes();
                $attrs['NameID'] = array("0" => $ssoemail);
                $sessionIndex    = current($samlResponseObj->getAssertions())->getSessionIndex();
                $custom_attribute_mapping = $pluginSettings->getCustomAttributeMapping();
                if(strcasecmp($relayStateUrl,Constants::TEST_RELAYSTATE)==0){
                    $testResultAction = new TestResultActions($attrs);
                    $testResultAction->execute(); 
                } else {
					$ProcessUserAction = new ProcessUserAction($attrs,$relayStateUrl,$sessionIndex);
                    $ProcessUserAction->execute();
                    require_once 'connector.php';
					Utilities::ckiovalidationInstant();
                    if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
                        session_start();
                      }

                    $_SESSION['mo_NameID'] = $attrs['NameID'];
                    $email_attr = $attrs[$pluginSettings->getSamlAmEmail()];
                    $username_attr = $attrs[$pluginSettings->getSamlAmUsername()];

                    $attributes = array(
                        'EMAIL' => $email_attr[0],
                        'Username' => $username_attr[0]
                    );
                    if(is_array($custom_attribute_mapping) && !empty($custom_attribute_mapping))
                        foreach($custom_attribute_mapping as $key => $value){
                            if(array_key_exists($value, $attrs))
                                if(count($attrs[$value]) == 1)
                                    $attributes[$key] = $attrs[$value][0];
                                else
                                    $attributes[$key] = $attrs[$value];
                        }
                    

                    if(is_array($attributes) && !empty($attributes))
                        $_SESSION['mo_attributes'] = serialize($attributes);
  

                    //Redirect to application url
                    $applicationUrl = $pluginSettings->getApplicationUrl();
                    if(!empty($applicationUrl)){
                        header('Location: ' . $applicationUrl);
                        exit();
                    } else {
                        echo '<html>
                        <body>You have been logged in!<br/>
                        If you want to redirect to a different URL after logging in, configure the Application url in Step 5 of <b>How to Setup?</b> tab of the connector.
                        </body>
                        </html>';
                    }
                }
            } catch (\Exception $e) {
                if(strcasecmp($relayStateUrl,Constants::TEST_RELAYSTATE) == 0){
                    $testResultAction = new TestResultActions(array(), $e);
                    $testResultAction->execute();
                }else{
                    Utilities::showErrorMessage($e->getMessage());
                }
            }
        } else {
            Utilities::showErrorMessage(Messages::MISSING_SAML_RESPONSE);
        }
    }
}
new SSO();