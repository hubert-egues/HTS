<?php

namespace HTS\Data;

class Country
{
    private $name;

    private $code;

    public function __construct($name)
    {
        switch ($name) {
            case 'US':
                $this->name = 'United States';
                $this->code = $name;
                break;
            case 'SG':
                $this->name = 'Singapore';
                $this->code = $name;
                break;
            case 'PE':
                $this->name = 'Perú';
                $this->code = $name;
        }
    }
}

