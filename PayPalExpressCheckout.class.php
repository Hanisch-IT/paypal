<?php
namespace HanischIT\PayPal;

/**
 * Klasse stellt alle nötigen Funktionen zur Verfügung um ein Express-Checkout durchzuführen
 *
 * User: Fabian
 * Date: 19.01.2015
 */
class PayPalExpressCheckout
{

    /**
     * URL welche nach erfolgreicher Transaktion aufgerufen werden soll
     *
     * @var string
     */
    private $sReturnUrl;

    /**
     * URL welche nach abgebrochender Transaktion aufgerufen werden soll
     *
     * @var string
     */
    private $sCancelUrl;

    /**
     * PayPal API version
     *
     * @var string
     */
    private $sVersion = "93";

    /**
     * PayPal API-Passwort
     *
     * @var string
     */
    private $sApiPwd;

    /**
     * API User
     *
     * @var string
     */
    private $sApiUser;

    /**
     * API Signatur
     *
     * @var string
     */
    private $sApiSignature;

    /**
     * URL zur Sandbox API
     *
     * @var string
     */
    private $sSandboxApi = "https://api-3t.sandbox.paypal.com/nvp";

    /**
     * URL zur Live API
     *
     * @var string
     */
    private $sLiveApi = "https://api-3t.paypal.com/nvp";

    /**
     * URL zur Sandbox-Oberfläche
     *
     * @var string
     */
    private $sSandboxUrl = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";

    /**
     * URL zur Live-Obefläche
     *
     * @var string
     */
    private $sLiveUrl = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";

    /**
     * Prüfvariable ob Sandbox oder Live verwendet werden soll
     *
     * @var bool
     */
    private $bIsSandbox = true;



    /**
     * @param string $sReturnUrl
     * @param string $sCancelUrl
     * @param bool $bIsSandbox
     */
    function __construct($sReturnUrl, $sCancelUrl, $bIsSandbox = true)
    {
        $this->sReturnUrl = $sReturnUrl;
        $this->sCancelUrl = $sCancelUrl;
        $this->bIsSandbox = $bIsSandbox;
    }

    /**
     * Setze API Passwort
     *
     * @param string $sApiPwd
     * @return $this
     */
    public function setApiPwd($sApiPwd)
    {
        $this->sApiPwd = $sApiPwd;

        return $this;
    }

    /**
     * Setze API-User
     *
     * @param string $sApiUser
     * @return $this
     */
    public function setApiUser($sApiUser)
    {
        $this->sApiUser = $sApiUser;

        return $this;
    }

    /**
     * Setze API-Signatur
     *
     * @param string $sApiSignature
     * @return $this
     */
    public function setApiSignature($sApiSignature)
    {
        $this->sApiSignature = $sApiSignature;

        return $this;
    }

    /**
     * Standardwerte für jeden call zusammensetzen
     *
     * @return string
     */
    private function getStandardParams()
    {
        $aAdditionalData = array();
        $aAdditionalData["RETURNURL"] = $this->sReturnUrl;
        $aAdditionalData["CANCELURL"] = $this->sCancelUrl;
        $aAdditionalData["VERSION"] = $this->sVersion;
        $aAdditionalData["PWD"] = $this->sApiPwd;
        $aAdditionalData["USER"] = $this->sApiUser;
        $aAdditionalData["SIGNATURE"] = $this->sApiSignature;

        $sUrlStr = "";

        foreach($aAdditionalData AS $sKey => $sValue)
        {
            $sUrlStr .= "&".$sKey."=".$sValue;
        }

        return $sUrlStr;
    }


    /**
     * Setze ExpressCheckout und erfasse Tooken
     *
     * @param PayPalSetExpressCheckout $oExpressCheckout
     * @return string Token
     * @throws \Exception
     */
    public function setExpressCheckout(PayPalSetExpressCheckout $oExpressCheckout)
    {
        $aAdditionalData = array();
        $aAdditionalData["METHOD"] = "SetExpressCheckout";
        $aAdditionalData["ADDROVERRIDE"] = 0;
        $aAdditionalData["NOSHIPPING"] = 1;

        $sUrlStr = $this->getStandardParams();

        foreach($aAdditionalData AS $sKey => $sValue)
        {
            $sUrlStr .= "&".$sKey."=".$sValue;
        }
        $aResponse = $this->doCall($sUrlStr.$oExpressCheckout->asUrlString());

        if(true === $this->bIsSandbox)
        {
            return $this->sSandboxUrl.$aResponse["TOKEN"];
        }

        return $this->sLiveUrl.$aResponse["TOKEN"];
    }

    /**
     * Details zu einer Transaktion anhand des Tokens abrufen
     *
     * @param $sToken
     * @return array
     * @throws \Exception
     */
    public function getExpressCheckoutDetails($sToken)
    {
        $sUrl = $this->getStandardParams() . "&METHOD=GetExpressCheckoutDetails&TOKEN=$sToken";
        return $this->doCall($sUrl);
    }

    /**
     * Zahlung durchführen
     *
     * @param string $sToken
     * @param string $sPayerId
     * @param $dSumAmt
     * @param $sCurrency
     * @return array
     * @throws \Exception
     */
    public function doExpressCheckout($sToken, $sPayerId, $dSumAmt, $sCurrency)
    {
        $sUrl = $this->getStandardParams() . "&METHOD=DoExpressCheckoutPayment&TOKEN=$sToken&PAYERID=$sPayerId&PAYMENTREQUEST_0_AMT=$dSumAmt&PAYMENTREQUEST_0_CURRENCYCODE=$sCurrency";
        return $this->doCall($sUrl);
    }

    /**
     * Einen Call ausführen
     *
     * @param string $sUrlStr
     * @return array
     * @throws \Exception
     */
    private function doCall($sUrlStr)
    {
        //setting the curl parameters.
        $oCh = curl_init();

        if(true === $this->bIsSandbox)
        {
            curl_setopt($oCh, CURLOPT_URL, $this->sSandboxApi);
        }
        else
        {
            curl_setopt($oCh, CURLOPT_URL, $this->sLiveApi);
        }

        curl_setopt($oCh, CURLOPT_VERBOSE, 1);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($oCh, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCh, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($oCh, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($oCh, CURLOPT_POST, 1);

        //setting the nvpreq as POST FIELD to curl
        curl_setopt($oCh, CURLOPT_POSTFIELDS, $sUrlStr);

        //getting response from server
        $sResponse = curl_exec($oCh);

        //convrting NVPResponse to an Associative Array

        if (curl_errno($oCh))
        {
            throw new \Exception(curl_error($oCh));
        }
        else
        {
            //closing the curl
            curl_close($oCh);
        }

        $aResponse  = $this->extractResponse($sResponse);

        if(isset($aResponse["ACK"]) && $aResponse["ACK"] == "Failure")
        {
            throw new \Exception($aResponse["L_SHORTMESSAGE0"].": ".$aResponse["L_LONGMESSAGE0"]);
        }

        return $aResponse;
    }

    /**
     * Response aufbereiten und als Array zurückgeben
     *
     * @param string $sResponse
     * @return array
     */
    private function extractResponse($sResponse)
    {
        $aReturn = array();

        $aResponse = explode("&", $sResponse);

        foreach($aResponse AS $sSingleLine)
        {
            $aResponseVal = explode("=", $sSingleLine);

            $aReturn[$aResponseVal[0]] = $aResponseVal[1];
        }

        return $aReturn;
    }
}