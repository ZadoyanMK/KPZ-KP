import scrapy


class BusinessItem(scrapy.Item):
    name = scrapy.Field()
    category = scrapy.Field()
    phone = scrapy.Field()
    clear_phone = scrapy.Field()
    fax = scrapy.Field()
    website = scrapy.Field()
    address_locality = scrapy.Field()
    address_region = scrapy.Field()
    postal_code = scrapy.Field()
    street_address = scrapy.Field()
    url = scrapy.Field()
    hash_url = scrapy.Field()
    parse_status = scrapy.Field()
    id = scrapy.Field()
