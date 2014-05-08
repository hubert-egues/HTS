#Combinacion de nombres HTS
import json

def get_json(fileName): 
    filep = open(fileName).read()
    output = json.loads(filep)
    return output

def get_name_from_json(output, value):
    parent_name = None 
    for i in output:
        if len(value) == 8:
            if i == value:
                return output[i]['name']
        else:
            value = value[:8]
            if i == value:
                return output[i]['name']
        if value[:6]+'00' == i:
            parent_name = output[i]['name']

    return parent_name


def find_name(fileName, value):
    output = get_json(fileName)
    parent_name = get_name_from_json(output, value)
    return parent_name


def read_hts(fileName):
    file = open(fileName).read()
    output = json.loads(file)
    for i in output:
        if output[i]['name'] == 'Not available' or len(i) == 10:
            name = find_name('input/name.json', i)
            if len(i) == 10 and output[i]['name'] != 'Not available':
                name = name + " " + output[i]['name']
            if name is not None:
                output[i]['name'] = name.strip()
            else:
                print output[i]['code']+' NO TIENE NAME'
    return output;


def write_json(fileName, structure):
    f = open(fileName, mode='w')
    json.dump(structure, f, indent=2)
    f.close()


output = read_hts('input/hts_usa.json')
write_json('output/output.json',output)


# TEST
def test_get_name():
    output = {
        "01012120": {
            "name": "Hts 1 with variant"
        },
        "01012100": {
            "name": "Hts One"
        },
        "85269200": {
            "name": "Radio remote control apparatus"
        },     
        "91010100": {
            "name": "Hts 9"
        },
        "91010150": {
            "name": "Hts 9 with variant"
        },
    }
    results = 0
    parent_name = get_name_from_json(output, '8526925000')
    if parent_name == 'Radio remote control apparatus':
        results+= 1
        
    parent_name = get_name_from_json(output, '01012120')
    if parent_name == 'Hts 1 with variant':
        results+= 1
        
    parent_name = get_name_from_json(output, '9101015000')
    if parent_name == 'Hts 9 with variant':
        results+= 1
        
    if results == 3:
        print 'tests OK'
    else:
        print 'test fail'
        
#test_get_name()
    