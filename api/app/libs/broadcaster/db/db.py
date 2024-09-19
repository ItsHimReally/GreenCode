import os
import asyncio

import dotenv
import pymysql
import pymysql.connections


__all__ = ["connection"]


dotenv.load_dotenv()

DB_USER = os.getenv("DB_USER")
DB_PASSWORD = os.getenv("DB_PASSWORD")
DB_HOST = os.getenv("DB_HOST")
DB_PORT = int(os.getenv("DB_PORT", 0))
DB_DATABASE = os.getenv("DB_DATABASE")

assert (DB_USER 
        and DB_PASSWORD 
        and DB_HOST 
        and DB_PORT 
        and DB_DATABASE), "Not all env vars are set for db connection"

connection: pymysql.connections.Connection = pymysql.connect(
    user=DB_USER,
    password=DB_PASSWORD,
    host=DB_HOST,
    port=DB_PORT,
    database=DB_DATABASE,
    connect_timeout=1,
)
