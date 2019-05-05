import logging


def get_logger(logger_name) -> logging.Logger:
    logging.basicConfig(
        format='%(asctime)s - %(levelname)s - %(name)s - %(message)s')
    logger = logging.getLogger(logger_name)
    logger.setLevel(10)
    return logger
