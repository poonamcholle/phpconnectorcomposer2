<?php

namespace MiniOrange\Classes\Actions;


use MiniOrange\Classes\AuthnRequest;
use MiniOrange\Helper\Constants;
use MiniOrange\Helper\Exception\NoIdentityProviderConfiguredException;
use MiniOrange\Helper\PluginSettings;
use MiniOrange\Helper\Utilities;

class SendAuthnRequest
{
    /**
     * Execute function to execute the classes function.
     * @throws \Exception
     * @throws NoIdentityProviderConfiguredException
     */
    public static function execute()
    {   
        $pluginSettings = PluginSettings::getPluginSettings();

        if(!Utilities::isSPConfigured()) throw new NoIdentityProviderConfiguredException();

        $relayState = isset($_REQUEST['RelayState']) ? $_REQUEST['RelayState'] : '/';

        //generate the saml request
        $authnrequest = new AuthnRequest($pluginSettings->getAcsUrl(), $pluginSettings->getSpEntityId()
        ,$pluginSettings->getSamlLoginUrl(),$pluginSettings->getForceAuthentication(),
        $pluginSettings->getLoginBindingType(), $pluginSettings->getAssertionSigned(),
        $pluginSettings->getResponseSigned()); 
        $samlRequest = $authnrequest->build();

        $bindingType = $pluginSettings->getLoginBindingType();
        // send saml request over
        if(empty($bindingType)
            || $bindingType == Constants::HTTP_REDIRECT){
                $httpaction = new HttpAction();
                return $httpaction->sendHTTPRedirectRequest($samlRequest,$relayState,$pluginSettings->getSamlLoginUrl());
            }
        else{
            $httpaction = new HttpAction();
            return $httpaction->sendHTTPPostRequest($samlRequest,$relayState,$pluginSettings->getSamlLoginUrl());
        }
    }

}