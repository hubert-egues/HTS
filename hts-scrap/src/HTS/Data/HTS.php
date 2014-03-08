<?php

namespace HTS\Data;

use Hts\Data\Country;
use Hts\Data\HtsOrigin;

class HTS
{
    private $name;

    private $code;

    private $country;

    private $hs;

    private $origin;

    private $quantities;


    public function getOrigin()
    {
        return $this->origin;
    }

    public function setHs($hs)
    {
        $this->hs = $hs;
    }

    public function getHs()
    {
        return $this->hs;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(Country $country)
    {
        $this->country = $country;
    }

}
