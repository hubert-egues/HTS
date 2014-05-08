# encoding=utf-8
import json
from scrapy.selector import HtmlXPathSelector
from scrapy.spider import BaseSpider
from scrapy.http import FormRequest, Request
from hts_peru.items import Hts
from hts_peru.items import Hts_name
import sys
from decimal import Decimal
### Kludge to set default encoding to utf-8
#reload(sys)
#sys.setdefaultencoding('utf-8')


class HtsListSpider (BaseSpider):
    name = "hts-list"
    start_urls = [     
        'http://www.aduanet.gob.pe/itarancel/arancelS01Alias?accion=buscarPartida&esframe=1'
    ]
    detail_urls = [
       'http://www.aduanet.gob.pe/itarancel/JSPListadoPartidaArancel.jsp',
       'http://www.aduanet.gob.pe/itarancel/JSPDetallePartidaArancel.jsp',
       'http://www.aduanet.gob.pe/itarancel/arancelS01Alias?accion=consultarUnidadMedida&cod_partida={0}',
       'http://www.aduanet.gob.pe/itarancel/arancelS01Alias?accion=consultarConvenio&cod_partida={0}'
    ]
    
    taxations = {
        'adv': u'Ad / Valorem',
        'base': u'Arancel Base',
        'excise': u'Excise Tax',
        'general': u'General Sales Tax',
        'municipal': u'Municipal Promotion Tax',
        'insurance': u'Insurance',
        'surcharge': u'Surcharge',
        'freeadv': u'Free Ad / Valorem',
    }
    
    country_iso = 'PE'
    
    log = {'uknowTariff': []}

    def parse(self, response):
        # DEBUG
        ejecute_for_two_first_codes = False
        debug_count = 0

        parser = HtmlXPathSelector(response)
        rows = parser.xpath('//form/table/tr[position()>1]')
        form_requests = []
        self.detail_requests = []
        i = 0
        self.codigos = []
        
        for row in rows:
            code = row.xpath('.//td[1]/a/font/text()').extract()
            name = row.xpath('.//td[2]/font/text()').extract()
            
            # DEBUG
            if ejecute_for_two_first_codes:
                debug_count+=1
                if debug_count > 2:
                    return form_requests
            # END DEBUG

            if len(code) > 0 and len(name) > 0:
                hts = Hts()
                hts['code'] = self.parse_hts_data(code[0])
                hts['name'] = self.parse_hts_name(name[0])
                hts['country'] = self.country_iso

                item_form_request = FormRequest(url=self.start_urls[0],
                                            formdata={'cod_partida':hts["code"]},
                                            callback=self.call_details_urls,
                                            meta={'hts': hts})
                form_requests.append(item_form_request)

        return form_requests

    def call_details_urls(self, response):
        hts = response.meta['hts']
        updated_hts = FormRequest(method='GET',
                                    url=self.detail_urls[1],
                                    formdata={'cod_partida': hts['code']},
                                    callback=self.get_generic_hts_tariffs,
                                    meta={'hts': hts})
        self.detail_requests.append(updated_hts)
        return updated_hts

    def get_generic_hts_tariffs(self, response):
        hts = response.meta['hts']
        ley = {}
        parser = HtmlXPathSelector(response)
        table = parser.xpath('//div/center')
        title = parser.xpath('//div/table/tr[1]/td[2]/font/text()').extract()
        code = parser.xpath('//center/font/b/text()').extract()

        i = 1
        ley = {i: "temp"}
        for title in title:
            ley = {i: title}
            i = i + 1

        value_detail = {}
        title = {}
        i = 1
        try :
            for table in table:
                temp = {}
                rows = parser.xpath('//div/center/table['+str(i)+']/tr[position()>1]')
                value = []
                for row in rows:
                    description = row.xpath('.//td[1]/font/text()').extract()
                    percentage  = row.xpath('.//td[2]/font/text()').extract()
                    if len(description) > 0 and len(percentage) > 0:
                        description = self.parse_hts_data(description[0])
                        description = self.get_taxation(description)
                        tariff_value = self.parse_hts_tariff(percentage[0])
                        if self.validate_description(description) and self.validate_tariff_value(tariff_value):
                            temp = {
                            'description': description,
                            'value': tariff_value,
                            'currency': '%',
                            'targetValue': 'CIF'}
                            if self.is_valid_tariff(temp):
                                value.append(temp)

                temp = { ley[i]: value }
                title.update(temp)
                i = i + 1
            value_detail.update(title)

            save_with_law = False
            if save_with_law:
                # old code
                hts['tariff_all'] = value_detail
            else:
                all_law = value_detail.values()
                hts['tariff_all'] = []
                for law_tariffs in all_law:
                    hts['tariff_all']+= law_tariffs

        except Exception, e:
            self.print_console(e.message)

        return FormRequest(method='GET',
                            url=self.detail_urls[2].format(hts['code']),
                            callback=self.get_units,
                            meta={'hts': hts})

    def get_units(self, response):
        '''
        Peticion usada para capturar las unidades metricas
        que maneja cada HTS (ejemplo: KG, LT, MTS)
        '''
        hts = response.meta['hts']
        parser = HtmlXPathSelector(response)
        count = len(parser.xpath('//center[2]/table/tr'))

        father_code = hts['code']
        hts['hs'] = father_code[0:6]
        rows = parser.xpath('//center[3]/table/tbody/tr[1]')

        hts['quantity'] = []
        for row in rows:
            hts['quantity'].append(self.parse_hts_data(row.xpath('td[3]/div/font/text()').extract()[0]).replace(' ',''))

        return Request(url=self.detail_urls[3].format(hts['code']),
                       callback=self.hts_member_detail,
                       meta={ 'hts':hts })

    def parse_detail(self, response):
        hts = response.meta['hts']
        detail_request = FormRequest(method='GET',
                                     url=self.detail_urls[0],
                                     formdata={'cod_partida': hts['code']},
                                     callback=self.hts_percent_detail,
                                     meta={'hts': hts})
        self.detail_requests.append(detail_request)
        return detail_request

    def parse_hts_data(self, data):
        data = data.replace('\t','').replace('\n','').replace('\r','')
        data = data.replace('.','').replace(' - ','')
        data = data.strip()
        return data
    
    def parse_hts_name(self, data):
        data = data.replace('\t','').replace('\n','').replace('\r','')
        data = data.strip()
        return data

    def parse_hts_tariff(self,data):
        data = data.replace('\t','').replace('\n','').replace('\r','')
        data = data.replace(' ','').replace('%','')
        data = data.strip()
        return data

    def validate_description(self, description):
        if description == u'Derecho Específicos '\
        or description == u'Derecho Antidumping'\
        or description == u'Unidad de Medida':
            return False
        return True
    
    def validate_tariff_value(self, value):
        if value == u'N.A.':
            return False
        return True
    
    def is_valid_tariff(self, tariff):
        try:
            value = Decimal(tariff['value'])
        except Exception, e:
            self.print_console(e.message)
            value = 0

        if value != 0:
            return True
        elif tariff['description'] == self.taxations['adv']:
            return True
            
        return False
    
    def get_taxation(self, description):
        if description == u'Ad / Valorem':
            return self.taxations['adv']
        elif description == u'Impuesto Selectivo al Consumo':
            return self.taxations['excise']
        elif description == u'Impuesto de Promoción Municipal':
            return self.taxations['municipal']
        elif description == u'Impuesto General a las Ventas':
            return self.taxations['general']
        elif description == u'Seguro':
            return self.taxations['insurance']
        elif description == u'Sobretasa':
            return self.taxations['surcharge']
        else:
            if description not in self.log['uknowTariff']:
                self.log['uknowTariff'] = description
        # 'Derecho Específicos', 'Derecho Antidumping'

        return description

    def hts_member_detail(self, response):
        '''
        Peticion usada para capturar las tarifas que se manejan
        con los paises a considerar en la aplicacion, dentro del HTS (EEUU, China, Singapur, Colombia)
        '''
        hts = response.meta['hts']
        tariffs = {}
        detail_tariff={}
        parser = HtmlXPathSelector(response)
        rows = parser.xpath('//center[3]/table/tr[position()>1]')
        for row in rows:
            country = self.parse_hts_data(row.xpath('td[1]/center/text()').extract()[0])
            if self.is_valid_country(country):
                tariff = {self.get_country_alias(country): [
                    {
                        'value': self.parse_hts_data(row.xpath('td[7]/center/text()').extract()[0]).replace('%',''),
                        'currency': '%',
                        'targetValue': 'ADV',
                        'description': self.taxations['freeadv']
                    }
                ]}

                freeadv_value = self.parse_hts_data(row.xpath('td[6]/center/text()').extract()[0]).replace('%','')
                try:
                    freeadv_value = Decimal(freeadv_value)
                    tariff[self.get_country_alias(country)].append({
                        'value': freeadv_value,
                        'currency': '%',
                        'targetValue': 'CIF',
                        'description': self.taxations['adv']
                    })
                except:
                    pass

                detail_tariff.update(tariff)

        hts['tariff'] = detail_tariff
        return hts

    def is_valid_country(self, country):
        if country == 'EEUU' or \
           country == 'CHINA':
            return True
        return False

    def get_country_alias(self, country):
        if country == 'EEUU':   return 'US'
        if country == 'CHINA':    return 'CN'

    def print_console(self, message):
        print 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
        print message
        print 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
