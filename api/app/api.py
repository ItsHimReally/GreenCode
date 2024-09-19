from typing import Any
import os

from fastapi import FastAPI, Request, Response
import uvicorn
from dotenv import load_dotenv, find_dotenv
import mysql.connector

from .utils import *
from .recsys import *
from .libs.broadcaster import Broadcaster


app = FastAPI()

load_dotenv(find_dotenv())

YANDEX_KEY = os.environ.get("YANDEX_GPT")
YANDEX_FOLDER = os.environ.get("FOLDER")
DADATA_TOKEN = os.getenv("DADATA_TOKEN")

mydb = mysql.connector.connect(
    host=os.environ.get("MYSQL_HOST"),
    user=os.environ.get("MYSQL_USER"),
    password=os.environ.get("MYSQL_PASSWORD"),
    database=os.environ.get("MYSQL_DATABASE")
)

events, users, likes, dataset = rebuild_recsys(mydb)

print('db connect:', mydb.is_connected())

@app.get("/api/")
async def read_root():
    return {"message": "Hello, FastAPI!"}


@app.get("/api/ya_gpt")
async def ya_gpt(request: Request):
    """
        Взаимодействие с виртуальным ассистентом
    """
    question = request.query_params.get('question')
    user_id = request.query_params.get('user_id')
    item_id = request.query_params.get('item_id')

    ans = yagpt_request(question, mydb, item_id, YANDEX_KEY, YANDEX_FOLDER)
    ids = add_quesion(mydb, question, user_id, item_id, ans)
    res = {'id': ids, 'text': ans}
    return res


@app.get("/api/not_solved")
async def not_solved(request: Request):
    """
        Взаимодействие с виртуальным ассистентом
    """
    idx = request.query_params.get('id')

    update_solved(mydb, idx)
    return {'status': 'success'}


@app.get("/api/rebuild_recsys")
async def recsys_rebuild(request: Request):
    """
        Взаимодействие с виртуальным ассистентом
    """

    global events, users, likes, dataset

    events, users, likes, dataset = rebuild_recsys(mydb)

    return {'status': 'success'}


@app.get("/api/get_recs")
async def recs(request: Request):
    """
        Взаимодействие с виртуальным ассистентом
    """

    user_id = request.query_params.get('user_id')
    rec = recom(int(user_id), dataset)

    return list(rec)


@app.get("/api/notify_user")
async def notify_user(request: Request):
    """
        Уведомить пользователя о регистрации на мероприятие
    """
    user_id = request.query_params.get("user_id")
    event_id = request.query_params.get("event_id")
    
    assert user_id and event_id
    
    await Broadcaster.notify_user_about_event(
        user_id=int(user_id),
        event_id=int(event_id),
    )

@app.get("/api/check_org")
async def org(request: Request):
    """
        Проверить валидность организации
    """

    org_id = request.query_params.get('org_id')
    if not org_id:
        return Response(status_code=400)
    
    res = check_org(int(org_id), DADATA_TOKEN)

    return res
