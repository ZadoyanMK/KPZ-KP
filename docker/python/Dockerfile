FROM python:3.6.8
MAINTAINER zadoyan_mk@localhost

COPY ./src /src

WORKDIR /src

RUN pip install --no-cache-dir -r requirements.txt
# apt-get update \
#     && apt-get install -y gcc openssl ca-certificates\
#     wget libffi-dev python-dev python3-dev python-mysqldb\
#     && 

COPY ./docker/python/entrypoint.sh /entrypoint.sh

# RUN chmod +x /entrypoint.sh

ENTRYPOINT ["sh", "/entrypoint.sh"]