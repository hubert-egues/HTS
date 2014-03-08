import json
import os


def ReplaceName():
    print "Name"
    file_hts = open('input/hts_peru.json').read()
    output_file = json.loads(file_hts)
    file_name = open('input/name.json').read()
    name_file = json.loads(file_name)
    hts = []
    Text = [6, 4]
    for output in output_file:
        #Name
        if len(output['code']) == 10:
            for text in Text:
                value = output['code'][:text]
                for name in name_file:
                    if name['code'] == value:
                        output['name'] = name['name']+ " " + output['name']
        hts.append(output)
        print "Code " + output['code']
    write_json('output/hts_peru.json', hts)
    print "Terminate"


def write_json(fileName, structure):
    f = open(fileName, mode='w')
    json.dump(structure, f, indent=2)
    f.close()


ReplaceName()