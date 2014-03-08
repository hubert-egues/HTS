#Combinacion de nombres HTS
import json


def find_name(fileName, value):
    file = open(fileName).read()
    output = json.loads(file)
    for i in output:
        if len(value) == 8:
            if i == value:
                return output[i]['name']
        else:
            value = value[:8]
            if i == value:
                return output[i]['name']
    return 'find: '+i+' ==> Not available'


def read_hts(fileName):
    file = open(fileName).read()
    output = json.loads(file)
    for i in output:
        if output[i]['name'] == 'Not available' or len(i) == 10:
            print i+' ==> '+output[i]['name']
            name = find_name('input/name.json', i)
            if len(i) == 10 and output[i]['name'] != 'Not available':
                name = name + " " + output[i]['name']
            output[i]['name'] = name.strip()
    return output;


def write_json(fileName, structure):
    f = open(fileName, mode='w')
    json.dump(structure, f, indent=2)
    f.close()

output = read_hts('input/hts_usa.json')
#output = read_json('name.json')
write_json('output/output.json',output)