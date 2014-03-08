import json
import os


def ReplaceTariff():
    print "Tariff"
    file_hts = open('input/hts_peru.json').read()
    output_file = json.loads(file_hts)
    file_tariff = open('input/tariff.json').read()
    tariff_file = json.loads(file_tariff)
    hts = []
    for output in output_file:
        #Tariff
        for tariff in tariff_file:
            if tariff['code'] == output['code']:
                temp = tariff['tariff_all']
                output['tariff_all'] = ""
                output['tariff_all'] = temp
        hts.append(output)
        print "Code " + output['code']
    write_json('output/hts_peru.json', hts)
    print "Terminate"


def write_json(fileName, structure):
    f = open(fileName, mode='w')
    json.dump(structure, f, indent=2)
    f.close()


ReplaceTariff()