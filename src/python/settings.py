BOT_NAME = 'internet_chamber_commerce'

SPIDER_MODULES = ['spiders']
NEWSPIDER_MODULE = 'spiders'


ROBOTSTXT_OBEY = False

CONCURRENT_REQUESTS = 32

CONCURRENT_REQUESTS_PER_DOMAIN = 32

DOWNLOAD_DELAY = 0.5

COOKIES_ENABLED = True

TELNETCONSOLE_ENABLED = False

# DEFAULT_REQUEST_HEADERS = {
#   'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
#   'Accept-Language': 'en',
# }

DOWNLOADER_MIDDLEWARES = {
   # 'rotating_proxies.middlewares.RotatingProxyMiddleware': 610,
   # 'rotating_proxies.middlewares.BanDetectionMiddleware': 620,
}

EXTENSIONS = {
   'scrapy.extensions.telnet.TelnetConsole': None,
}

ITEM_PIPELINES = {
   'pipelines.PageBusinessPipeline': 300,
}

MESSAGE_COUNT = 4000

# ROTATING_PROXY_LIST = [
#     "5.79.73.131:13150",
# ]

COMMANDS_MODULE = 'commands'