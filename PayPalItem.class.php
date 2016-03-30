<?php
/**
 * Entity-Klasse welche ein PayPal-Item für SetExpressCheckout zusammenfast
 *
 * User: Fabian
 * Date: 19.01.2015
 */
namespace HanischIT\PayPal;

class PayPalItem
{
    /**
     * Name des Artikels
     *
     * @var string
     */
    private $sName;

    /**
     * Beschreibung des Artikels
     *
     * @var string
     */
    private $sDescription;

    /**
     * Wert des Artikels ja Stück
     *
     * @var double
     */
    private $dAmt;

    /**
     * Artikelnummer
     *
     * @var string
     */
    private $sNumber;

    /**
     * Anzahl des Artikels
     *
     * @var int
     */
    private $dQty;


    /**
     * Artikelnamen setzen
     *
     * @param string $sName
     * @return $this
     */
    public function setName($sName)
    {
        $this->sName = $sName;

        return $this;
    }

    /**
     * Artikelbeschreibung setzen
     *
     * @param string $sDescription
     * @return $this
     */
    public function setDescription($sDescription)
    {
        $this->sDescription = $sDescription;

        return $this;
    }


    /**
     * Artikelbetrag je stück setzen
     *
     * @param double $dAmt
     * @return $this
     */
    public function setAmt($dAmt)
    {
        $this->dAmt = $dAmt;

        return $this;
    }

    /**
     * Artikelbetrag auslesen
     *
     * @return double
     */
    public function getAmt()
    {
        return $this->dAmt;
    }


    /**
     * Artikelnummer setzen
     *
     * @param string $sNumber
     * @return $this
     */
    public function setNumber($sNumber)
    {
        $this->sNumber = $sNumber;

        return $this;
    }


    /**
     * Menge setzen
     *
     * @param double $dQty
     * @return $this
     */
    public function setQty($dQty)
    {
        $this->dQty = $dQty;

        return $this;
    }

    /**
     * Daten des Artikels als Array zurückgeben
     *
     * @return array
     */
    public function asArray()
    {
        $aReturn = array();
        $aReturn["NAME"] = $this->sName;
        $aReturn["DESC"] = $this->sDescription;
        $aReturn["AMT"] = $this->dAmt;
        $aReturn["NUMBER"] = $this->sNumber;
        $aReturn["QTY"] = $this->dQty;

        return $aReturn;
    }

    /**
     * Item-URL für Express-Checkout lesen
     *
     * @param int $iPaymentRequest
     * @param int $iCurrentItem
     * @return string
     *
     * @throws \Exception Is something mandatory missing
     */
    public function asUrlString($iPaymentRequest = 0, $iCurrentItem = 0)
    {
        $this->validate();

        $sUrlKey = "";
        $aReturn = $this->asArray();

        foreach($aReturn AS $sKey => $mValue)
        {
            $sUrlKey .= "&L_PAYMENTREQUEST_".$iPaymentRequest."_".$sKey.$iCurrentItem."=".$mValue;
        }

        return $sUrlKey;
    }

    /**
     * Item validieren
     *
     * @return $this
     * @throws \Exception
     */
    public function validate()
    {
        if(empty($this->sName))
        {
            throw new \Exception("Missing mandatory Item-Name.");
        }

        if(strlen($this->dAmt) == 0)
        {
            throw new \Exception("Missing mandatory Item-AMT.");
        }

        return $this;
    }
}