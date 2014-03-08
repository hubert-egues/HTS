import json
import os


def order_jason():
    print "Hello"
    file_name = open('input/hts_china.json').read()
    output_file = json.loads(file_name)
    write_json('output/order.json', output_file)
    print "Terminate"


def write_json(fileName, structure):
    f = open(fileName, mode='w')
    json.dump(structure, f, indent=2)
    f.close()


#print os.path.isfile(r"C:\Users\Desktop\kk.fasta")
order_jason()