FROM debian:12-slim

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        fpc \
        fp-units-fcl \
        postgresql-client-15 \
        ca-certificates \
        bash \
        coreutils \
        libreoffice-core \
        libreoffice-calc; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /app

COPY legacy.pas .

RUN fpc -Mobjfpc -Scghi -O2 legacy.pas -olegacy

# Каталог для CSV/XLSX
ENV CSV_OUT_DIR=/data/csv
VOLUME ["/data/csv"]

CMD ["./legacy"]
