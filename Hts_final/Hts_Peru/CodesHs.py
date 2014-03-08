import json
import os


def six_digit():
    print "Hello"
    file_name = open('input/hs_categories.json').read()
    output_file = json.loads(file_name)
    codigos = []
    for output in output_file['values']:
        output[1] = output[1].replace("'", '')
        print output[1] + " - " + str(len(output[1]))
        if len(output[1])==6:
            temp = {'codigo': output[1]}
            codigos.append(temp)
    write_json('output/hs.json', codigos)
    print "Terminate"


def write_json(fileName, structure):
    f = open(fileName, mode='w')
    json.dump(structure, f, indent=2)
    f.close()


six_digit()