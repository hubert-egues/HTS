<?php

namespace HTS\Data;

use HTS\Data\Country;

class HTSOrigin
{
    private $country;

    //dollar, etc
    private $currency;

    // $ o %
    private $typeValue;

    // kg, liter, t, FOB
    private $targetValue;

    //valor de moneda o de porcentaje
    private $value;


    public function __construct()
    {

    }
    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setTargetValue($targetValue)
    {
        $this->targetValue = $targetValue;
    }

    public function getTargetValue()
    {
        return $this->targetValue;
    }

    public function setTypeValue($typeValue)
    {
        $this->typeValue = $typeValue;
    }

    public function getTypeValue()
    {
        return $this->typeValue;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

}
