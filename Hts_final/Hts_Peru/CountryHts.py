import json
import os


def CountryHts():
    """
        Set country into Peru tariffs json
        work in output
    """
    print "Country"
    file_hts = open('output/hts_peru.json').read()
    output_file = json.loads(file_hts)
    hts = []
    for output in output_file:
        output['country'] = 'PE' 
        hts.append(output)

    write_json('output/hts_peru.json', hts)
    print "Terminate"


def write_json(fileName, structure):
    f = open(fileName, mode='w')
    json.dump(structure, f, indent=2)
    f.close()


CountryHts()