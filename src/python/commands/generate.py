from scrapy.commands import ScrapyCommand
import argparse
import time
import hashlib
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

    def get_link(self, number):
        return f"http://www.icc.org/details/{number}.htm"

    def run(self, args, opts, *a, **kw):
        config = ConfigParser()
        config.read("python/configs/config.ini")

        message_count = int(config.get("RABBIT", "message_count"))
        QUEUE_NAME = config.get("QUEUES", "links_pusher")

        channel = RabbitMQ.get_channel()

        logger = get_logger('[' + QUEUE_NAME + ']')
        for i in range(1000, 16939579):
            try:
                stats = channel.queue_declare(queue=QUEUE_NAME, durable=True)
                if stats.method.message_count < 6000:
                    link = self.get_link(i)
                    try:
                        channel.basic_publish(
                            exchange='',
                            routing_key=QUEUE_NAME,
                            properties=BasicProperties(delivery_mode=2),
                            body=JSONEncoder().encode({
                                'url': link,
                            })
                        )
                    except AMQPError as connection_error:
                        time.sleep(10)
                        logger.error("AMQP error")
                        channel = RabbitMQ.get_channel()
                        channel.queue_declare(queue=QUEUE_NAME, durable=True)
                        time.sleep(3)
                    logger.info(f"Pushed url: {link}")
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
