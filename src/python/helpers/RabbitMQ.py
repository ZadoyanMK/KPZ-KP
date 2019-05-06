from pika import BlockingConnection, ConnectionParameters, BasicProperties
from pika.credentials import PlainCredentials
import os
from scrapy.settings import Settings
from configparser import ConfigParser


class RabbitMQ:
    @staticmethod
    def get_channel():
        config = ConfigParser()
        config.read("python/configs/config.ini")

        connection = BlockingConnection(ConnectionParameters(
            host=config.get("RABBIT", "host"),
            port=config.get("RABBIT", "port"),
            credentials=PlainCredentials(
                username=config.get("RABBIT", "user"),
                password=config.get("RABBIT", "pass")),
            heartbeat=0
        ))

        channel = connection.channel(1)
        channel.basic_qos(prefetch_count=1)

        return channel
