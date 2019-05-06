from scrapy.commands import ScrapyCommand
import argparse
import time

import os
import sys


from configparser import ConfigParser
from pika import channel, BasicProperties
from pika.exceptions import ChannelClosed, AMQPError
from json import JSONEncoder

from python.helpers.RabbitMQ import RabbitMQ
from python.helpers.db import DB
from python.helpers.logger import get_logger

class Command(ScrapyCommand):

    def get_links(self):
        return DB.getByStatus(table="businesses", limit=500, offset=0)

    def run(self, args, opts):
        config = ConfigParser()
        config.read("python/configs/config.ini")

        message_count = int(config.get("RABBIT", "message_count"))
        QUEUE_NAME = config.get("QUEUES", "business_pusher")

        channel = RabbitMQ.get_channel()
        # arg_parser = argparse.ArgumentParser()
        # arg_parser.add_argument('-o', '--offset', type=int, help='SQL select offset', default=0)
        # arg_parser.add_argument('-l', '--limit', type=int, help='SQL select limit', default=0)
        # args = arg_parser.parse_args()

        offset = 1
        if offset == 1:
            offset = 0
        else:
            offset = offset * 1000
        limit = 500
        logger = get_logger('['+QUEUE_NAME+']')
        while True:
            try:
                stats = channel.queue_declare(queue=QUEUE_NAME, durable=True)
                if stats.method.message_count < message_count:
                    links = self.get_links()
                    if links:
                        for link in links:
                            try:
                                channel.basic_publish(
                                    exchange='',
                                    routing_key=QUEUE_NAME,
                                    properties=BasicProperties(delivery_mode=2),
                                    body=JSONEncoder().encode({
                                        'id': link['id'],
                                        'url': link['url']
                                    })
                                )
                                DB.update_link_status(table="businesses",item={
                                    'id': link['id'],
                                    'parse_status': "1"
                                })
                            except AMQPError as connection_error:
                                time.sleep(10)
                                logger.error("AMQP error")
                                channel = RabbitMQ.get_channel()
                                channel.queue_declare(queue=QUEUE_NAME, durable=True)
                                time.sleep(3)
                        logger.info("Pushed chunk")
                    else:
                        logger.info('There are no links in the DB! Sleep for 15 seconds...')
                        time.sleep(15)
                else:
                    logger.debug(f'There are more then {message_count} messages in the Queue! Sleep for 10 seconds...')
                    time.sleep(10)
            except ChannelClosed as channel_closed_error:
                logger.error("Channel closed")
                logger.info("Sleep for 10 seconds...")
                time.sleep(10)
                channel = RabbitMQ.get_channel()
                channel.queue_declare(queue=QUEUE_NAME, durable=True)
            except Exception as error:
                logger.error(repr(error))
                DB.reconnect()
                logger.info("Sleep for 10 seconds...")
                time.sleep(10)
