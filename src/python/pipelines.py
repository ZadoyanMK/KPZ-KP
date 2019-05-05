import time
import json
from scrapy.exporters import JsonItemExporter
from pika import BasicProperties


class PageBusinessPipeline(object):
    def process_item(self, item, spider):
        self.spider = spider
        self.publish_business(item)
        return item

    def publish_business(self, item):
        sent = False
        while not sent:
            stats = self.spider.channel.queue_declare(
                queue=self.spider.config.get('QUEUES', 'business_saver'),
                durable=True
            )
            if stats.method.message_count < self.spider.settings.get('MESSAGE_COUNT', 3000):
                self.spider.channel.basic_publish(
                    exchange='',
                    routing_key=self.spider.config.get('QUEUES', 'business_saver'),
                    properties=BasicProperties(delivery_mode=2),
                    body=json.dumps(
                        dict(item),
                        sort_keys=True,
                        indent=4,
                        separators=(',', ': ')
                    )
                )
                sent = True
            else:
                self.spider.logger.info('_' * 70)
                self.spider.logger.info('Sleeping 15 seconds')
                self.spider.logger.info('_' * 70)
                time.sleep(15)
