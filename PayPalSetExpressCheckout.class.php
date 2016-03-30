<?php
namespace HanischIT\PayPal;

/**
 * Klasse für das Sammeln der Daten um den Call SetExpressCheckout aufzurufen
 *
 * User: Fabian
 * Date: 19.01.2015
 */
class PayPalSetExpressCheckout {

    /**
     * Währung
     *
     * @var string
     */
    private $sCurrency = "EUR";

    /**
     * Währung setzen
     *
     * @param $sCurrency
     */
    public function setCurrency($sCurrency)
    {
        $this->sCurrency = $sCurrency;
    }

    /**
     * Enthält alle hinzugefügten Items
     *
     * @var PayPalItem[]
     */
    private $aItems = array();

    /**
     * Artikel-Objekt hinzufpgen
     *
     * @param PayPalItem $oItem
     * @return $this
     * @throws \Exception
     */
    public function addItem(PayPalItem $oItem)
    {
        $oItem->validate();
        $aItems[] = $oItem;

        return $this;
    }

    /**
     * Artikel hinzufügen
     *
     * @param string $sNumber
     * @param string $sName
     * @param string $sDescription
     * @param double $dAmt
     * @param double $dQty
     * @return $this
     * @throws \Exception
     */
    public function addNewItem($sNumber, $sName, $sDescription, $dAmt, $dQty)
    {
        $this->aItems[] = (new PayPalItem())
            ->setNumber($sNumber)
            ->setName($sName)
            ->setDescription($sDescription)
            ->setAmt($dAmt)
            ->setQty($dQty)
            ->validate();

        return $this;
    }

    /**
     * Daten des Objekts als Array zurückgeben
     *
     * @return array
     */
    public function asArray()
    {
        $aReturn = array();
        $aReturn["AMT"] = $this->getAmtSum();
        $aReturn["ITEMAMT"] = $this->getAmtSum();
        $aReturn["PAYMENTACTION"] = "Sale";
        $aReturn["CURRENCYCODE"] = $this->sCurrency;

        return $aReturn;
    }

    /**
     * Objektdaten als URL übergben
     *
     * @param int $iPaymentRequest
     * @return string
     */
    public function asUrlString($iPaymentRequest = 0)
    {
        $sUrlKey = "";
        $aReturn = $this->asArray();

        $i = 0;
        foreach($aReturn AS $sKey => $mValue)
        {
            $sUrlKey .= ($i == 0) ? "&" : "&";
            $sUrlKey .= "PAYMENTREQUEST_".$iPaymentRequest."_".$sKey."=".$mValue;

            $i++;
        }


        // Add Items
        $i=0;
        foreach($this->aItems AS $oItem)
        {
            $sUrlKey .= $oItem->asUrlString($iPaymentRequest, $i);

            $i++;
        }

        return $sUrlKey;
    }

    /**
     * Summe aller Artikel zurückgeben
     *
     * @return double
     */
    private function getAmtSum()
    {
        $dAmt = 0.0;

        foreach($this->aItems AS $oItem)
        {
            $dAmt += $oItem->getAmt();
        }

        return $dAmt;
    }
}