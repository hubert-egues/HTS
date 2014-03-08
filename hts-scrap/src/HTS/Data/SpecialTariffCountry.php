<?php

namespace Hts\Data;

class SpecialTariffCountry
{
    private $alliances;

    public function __construct()
    {
        $this->alliances = array(
            'EU' => array('PE' => 'Peru',
                          'SG' => 'China')
        );
    }



}
