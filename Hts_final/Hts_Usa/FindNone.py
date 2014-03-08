#Busqueda de Hts no existentes en "Hts_USA" pero si en "name"
import json


def find_none_hts():
    file_name = open('input/name.json').read()
    output_name = json.loads(file_name)

    file_hts = open('input/hts_usa.json').read()
    output_hts = json.loads(file_hts)
    num = 1
    for i in output_name:
        value = 1
        for x in output_hts:
                if len(x)==8:
                    if i == x:
                        value =0
                elif x[:8]==i:
                    value = 0
        if i[:2] == '98' or i[:2] == '99':
            value = 0
        if value == 1:
            print str(num) +")" + i
            num =num+1


def find_quantity():
    f = open ("output/filtro.txt", "a")
    file_name = open('hts.json').read()
    output_hts = json.loads(file_name)
    for i in output_hts:
        try:
            if len(output_hts[i]['quantity']) >= 2:
                f.write(i + ":")
                print i + " : "
                for quan in output_hts[i]['quantity']:
                    f.write(quan)
                    print quan
                f.write("\n")
                print "\n"
        except:
            f.write("Sin unidad: " + i + "\n")
            print "Sin unidad: " + i
    f.close()


print 'Iniciando ...'
# find_quantity()
find_none_hts()





















