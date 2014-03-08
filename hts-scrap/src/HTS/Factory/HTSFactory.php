<?php

namespace HTS\Factory;

use HTS\Data\HTS;
use HTS\Data\Country;
use HTS\Data\HTSOrigin;
class HTSFactory
{

    public static function createHTS(mixed $htsValues)
    {
        $HTS = new HTS();
        $HTS->setCode($htsValues["code"]);
        $HTS->setName($htsValues["hs"]);
        $HTS->setCountry(new Country('US'));

    }

    private static function assignTypeValue(mixed $htsValues)
    {
        $value = '';
        if (array_key_exists('quantity', $htsValues)) {
            switch ($htsValues["quantity"]) {
                case 'No.':
                    $value = 'FOB';
                    break;
                default:
                    $value = $htsValues["quantity"];
                    break;
            }
            return $value;
        }


    }
    private static function createHTSOrigin(mixed $htsValues)
    {
        //Primero busca si existe una tarifa especial, para aplicar la tarifa
        //segun el pais de origen
        if ($htsValues["special-tariff"] != 'None') {
            foreach ($htsValues["special-tariff"] as $key => $values) {
                $country = new Country($key);

                foreach ($values as $value) {
                    $origin = new HTSOrigin();
                    if ($value["currency"] == '%') {
                        $origin->setCurrency('');
                        $origin->setValue($value["value"]);
                        $origin->setTypeValue(
                          ($value["value"] == 0) 'FOB' ? self::assignTypeValue($htsValues)
                        );

                        //Caso de Free : 0 % FOB
                    }
                    $origin->setCurrency($value["currency"]);
                    $origin->setTypeValue($value["currency"]);
                    //Caso de Free : 0 % FOB
                    if ($value["currency"] == '%') {
                        //Buscar la descripcion de las unidades dentro
                        //de quantity y quantities
                        if (array_key_exists('quantity', $htsValues)) {
                            switch($htsValues["quantity"]) {
                                case 'No.':
                                    $origin->setTargetValue('FOB');
                                    break;
                                default:
                                    //TODO: si no existen en la base de datos, agregarlos
                                    $origin->setTargetValue($htsValues["quantity"]);
                                    break;
                            }
                        }

                        if(array_key_exists(''))
                    }

                    $origin->setTargetValue(
                        ($value["targetValue"] == '%') ? $htsValues["unit"] : $value["targetValue"]
                    );


                }
            }
        }
        $origin = new HtsOrigin($htsValues["hola"]);
    }


}
