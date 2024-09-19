FROM debian:12-slim AS deps-builder

RUN apt-get update \
    && apt-get install -y --no-install-suggests --no-install-recommends \
        pipx \
    && rm -rf /var/lib/apt/lists/*

ENV PATH="/root/.local/bin:${PATH}"
RUN pipx install poetry \
    && pipx inject poetry poetry-plugin-export

WORKDIR /opt/deps/
COPY pyproject.toml .
COPY poetry.lock .
RUN poetry export -f requirements.txt --output ./requirements.txt


ARG TAG
FROM python:${TAG:-latest}

RUN groupadd --gid 1000 app \
    && useradd --uid 1000 --gid 1000 --shell /bin/bash --create-home app

RUN mkdir /opt/app/ \
    && chown app:app /opt/app/

WORKDIR /opt/app/
COPY --from=deps-builder /opt/deps/requirements.txt .

USER app

RUN python3 -m venv .venv \
    && ./.venv/bin/python3 -m pip install -r ./requirements.txt

COPY . .

ENTRYPOINT ["./.venv/bin/python3", "./main.py"]
