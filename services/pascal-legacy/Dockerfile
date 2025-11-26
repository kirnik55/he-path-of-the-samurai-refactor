FROM debian:12-slim

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        fpc \
        fp-units-fcl \
        postgresql-client-15 \
        ca-certificates \
        bash \
        coreutils; \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY legacy.pas .

RUN fpc -Mobjfpc -Scghi -O2 legacy.pas -olegacy

ENV CSV_OUT_DIR=/data/csv
VOLUME ["/data/csv"]

CMD ["./legacy"]
