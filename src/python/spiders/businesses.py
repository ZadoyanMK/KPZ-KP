import re
import gzip
import hashlib
import zlib
import html
import time
import traceback

from scrapy.spider import Spider
from scrapy.http import Request, Response, XmlResponse
import scrapy

from python.helpers.RabbitMQ import RabbitMQ
from configparser import ConfigParser
import logging
import json

from pika import BasicProperties
from pika.exceptions import ChannelClosed
from configparser import ConfigParser
from scrapy.spidermiddlewares.httperror import HttpError
from twisted.internet.error import DNSLookupError
from twisted.internet.error import TimeoutError, TCPTimedOutError
import os
from ..items import *


class BusinessesSpider(Spider):
    name = 'businesses'

    threads = 1
    channel = None

    @classmethod
    def from_crawler(cls, crawler, *args, **kwargs):
        spider = super(BusinessesSpider, cls).from_crawler(crawler, *args, **kwargs)
        crawler.signals.connect(spider.spider_idle, signal=scrapy.signals.spider_idle)
        return spider

    def __init__(self, threads=1, *args, **kwargs):
        super().__init__()
        config = ConfigParser()
        config.read("python/configs/config.ini")
        self.config = config
        self.threads = int(threads)
        self.rabbitmq_connect()

    def rabbitmq_connect(self):
        logging.getLogger("pika").setLevel(logging.WARNING)
        self.channel = RabbitMQ.get_channel()
        self.channel.queue_declare(queue=self.config.get('QUEUES', 'links_pusher'), durable=True)

    def spider_idle(self, spider):
        self.logger.info('=' * 80)
        self.logger.info('Spider idle. Sleeping for 13 seconds and reconnecting to the RabbitMQ...')
        self.logger.info('=' * 80)
        time.sleep(10)
        self.rabbitmq_connect()
        time.sleep(3)
        for _ in range(self.threads):
            self.crawler.engine.crawl(self.next_request(), spider=self)

    def start_requests(self):
        for _ in range(0, self.threads):
            yield self.next_request()

    def next_request(self):
        while True:
            stats = self.channel.queue_declare(self.config.get('QUEUES', 'business_pusher'), durable=True)
            if stats.method.message_count > 0:
                meta, header_frame, data = self.channel.basic_get(self.config.get('QUEUES', 'business_pusher'))
                if data:
                    data = json.loads(data)
                    request = scrapy.Request(
                        data.get('url'),
                        meta={
                            'id': data.get("id"),
                            'delivery_tag': meta.delivery_tag
                        }, callback=self.parse_page, dont_filter=True,
                        errback=self.errback_httpbin
                     )
                    self.logger.info("Next url = {url}".format(url=data.get("url")))
                    return request
            else:
                self.logger.info('_' * 70)
                self.logger.info('There are few messages in the queue. Wait for more messages...')
                self.logger.info('_' * 70)
                time.sleep(10)

    def mark_link(self, id, error=None, http_code="0", url="NULL"):
        status = http_code if not http_code == "" else "0"
        self.channel.basic_publish(
            exchange='',
            routing_key=self.config.get('QUEUES', 'business_saver'),
            properties=BasicProperties(delivery_mode=2),
            body=json.JSONEncoder().encode({
                'parse_status': status,
                'id': id,
                'url': url,
                'phone': "NULL",
                'fax': "NULL",
                'website': "NULL",
                'address_region': "NULL",
                'postal_code': "NULL",
                'street_address': "NULL",
                'category': "NULL",
            })
        )
        self.logger.info('_' * 70)
        self.logger.info('[{queue}] Pushed {url}'.format(url=url, queue=self.config.get('QUEUES', 'business_saver')))
        self.logger.info('_' * 70)

    def errback_httpbin(self, failure):
        self.logger.error(repr(failure))
        error = "ERROR"
        id = failure.request.meta['id']
        code = None
        url = ""
        if failure.check(HttpError):
            code = failure.value.response.status
            response = failure.value.response
            self.logger.error('HttpError on %s', response.url)
            error = "HTTP"
            url = response.url
        elif failure.check(DNSLookupError):
            request = failure.request
            self.logger.error('DNSLookupError on %s', request.url)
            error = "DNS"
            url = request.url
        elif failure.check(TimeoutError, TCPTimedOutError):
            request = failure.request
            self.logger.error('TimeoutError on %s', request.url)
            error = "TIMEOUT"
            url = request.url

        self.mark_link(id=id, error=error, http_code=str(code), url=url)
        self.channel.basic_ack(delivery_tag=failure.request.meta['delivery_tag'])
        yield self.next_request()

    def parse_page(self, response):
        self.logger.info("Crawled: " + str(response.url))
        try:
            item = BusinessItem()
            name = response.xpath(
                '//h3[@id="business"]//text()'
            ).extract()
            name = ' '.join([x.strip() for x in name if x.strip()])

            category = response.xpath(
                '//div[contains(*/text(), "Categories")]/ul/li//text()'
            ).extract()
            if not category:
                category = response.xpath(
                    '//*[@id="cat"]/text()'
                ).extract()
            category = ','.join([x.strip() for x in category if x.strip()])

            fax = response.xpath(
                '//script[contains(text(), "Fax")]//text()'
            ).extract()
            fax = ''.join([x.strip() for x in fax if x.strip()])
            if fax:
                start_pos = fax.find('Fax')
                end_pos = re.search(r'[a-zA-Z<>]', fax[(start_pos + 4):]).start()
                self.logger.info([start_pos, end_pos])
                fax = fax[start_pos:start_pos + end_pos + 4]
                fax = fax.replace('Fax:', '')
                fax = fax.replace(' ', '')

            street_address = response.xpath(
                '//span[@id="business1"]//text()'
            ).extract()
            street_address = ''.join([x.strip() for x in street_address if x.strip()])

            address_locality = response.xpath(
                '//span[@id="business3"]//text()'
            ).extract()
            address_locality = ''.join([x.strip() for x in address_locality if x.strip()])

            address_region = response.xpath(
                '//span[@id="business4"]//text()'
            ).extract()
            address_region = ''.join([x.strip() for x in address_region if x.strip()])

            postal_code = response.xpath(
                '//span[@id="business5"]//text()'
            ).extract()
            postal_code = ''.join([x.strip() for x in postal_code if x.strip()])

            phone = response.xpath(
                '//span[@id="phone"]//text()'
            ).extract()
            phone = ''.join([x.strip() for x in phone if x.strip()])
            if phone:
                if not phone.find('Tel:') == -1:
                    phone = phone.replace('Tel:', '')
                    phone = phone.replace(' ', '')

            website = response.xpath(
                '//*[@id="website"]/@href'
            ).extract()
            website = ''.join([x.strip() for x in website if x.strip()])

            item['name'] = name if name else 'NULL'
            item['category'] = category if category else 'NULL'
            item['phone'] = phone if phone else 'NULL'
            item['clear_phone'] = ''.join([x for x in re.findall(r'\d+', phone)]) if phone else 'NULL'
            item['fax'] = fax if fax else 'NULL'
            item['website'] = website if website else 'NULL'
            item['address_locality'] = address_locality if address_locality else 'NULL'
            item['address_region'] = address_region if address_region else 'NULL'
            item['postal_code'] = postal_code if postal_code else 'NULL'
            item['street_address'] = street_address if street_address else 'NULL'
            item['url'] = response.url
            item['parse_status'] = "2"
            item['id'] = response.meta.get('id', 'NULL')

            yield item

            self.logger.info('_' * 70)
            self.logger.info('[{queue}] Pushed link, URL = {url}'.format(url=response.url, queue=self.config.get('QUEUES', 'business_saver')))
            self.logger.info('_' * 70)
            self.channel.basic_ack(delivery_tag=response.meta.get('delivery_tag'))
        except ChannelClosed as ch_closed_exc:
            self.logger.error('*' * 70)
            self.logger.error(traceback.format_exc())
            self.logger.error('Sleeping for 20 seconds...')
            self.logger.error('*' * 70)
            time.sleep(20)
            self.logger.error('Reconnecting...')
            self.rabbitmq_connect()
            self.channel.basic_reject(delivery_tag=response.meta.get('delivery_tag'), requeue=True)
        except Exception as error:
            self.logger.error('*' * 70)
            self.logger.error(error)
            self.logger.error(repr(error))
            self.logger.error('Sleeping for 20 seconds...')
            self.logger.error('*' * 70)
            time.sleep(20)
        finally:
            yield self.next_request()
