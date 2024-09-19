import asyncio
from typing import Any
import os
import datetime

import dotenv
import aiohttp_toolkit as aiohtk
import aiohttp
import yarl

from .db import queries, models


dotenv.load_dotenv(dotenv.find_dotenv())
BOT_TOKEN = os.getenv("BOT_TOKEN")
assert BOT_TOKEN, "BOT_TOKEN is not set"


class Broadcaster:
    @staticmethod
    async def notify_by_tid(tid: int, event: models.Event) -> None:
        async with aiohttp.ClientSession() as session:
            event_image_url = event.imageUrl or "https://coffective.com/wp-content/uploads/2018/06/default-featured-image.png.jpg"
            photo_url = yarl.URL(event_image_url)
            
            event_text = """\
Вы зарегистрировались на мероприятие.

**{title}**
📅 __{start}__ - __{end}__
{onoff_line}, {age_limit}+
📍 {location}
🏷️ {tags}
"""
            event_text = event_text.replace("{title}", event.title or "Noname")
            event_text = event_text.replace("{start}", str(event.timeStart) or "?")
            event_text = event_text.replace("{end}", str(event.timeEnd) or "?")
            event_text = event_text.replace("{onoff_line}", "Онлайн" if event.isOnline else "Офлайн")
            event_text = event_text.replace("{age_limit}", str(event.ageLimit))
            event_text = event_text.replace("{location}", event.locationAddress or "-")
            event_text = event_text.replace("{tags}", event.tags or "-")

            url = yarl.URL(f"https://api.telegram.org/bot{BOT_TOKEN}/sendPhoto")
            
            out, err = await aiohtk.RequestHandler.request(
                session=session,
                response_callback=aiohtk.callbacks.text,
                response_callback_kwargs={},
                method="GET",
                url=url,
                params={
                    "chat_id": str(tid),
                    "caption": event_text,
                    "photo": str(photo_url),
                    "parse_mode": "markdown",
                },
            )
            if err:
                raise err

            res = aiohtk.models.Text(**out)
            if not res.ok:
                raise RuntimeError(f"response is not ok: {res.text=}")

    
    @staticmethod
    async def notify_user_about_event(user_id: int, event_id: int) -> None:
        user = queries.UsersDb.get_user_by_user_id(user_id=user_id)
        if not user:
            return
        
        tid = int(user.tgID)
        
        event = queries.EventsDb.get_event_by_id(event_id=event_id)
        if not event:
            return

        try:
            await Broadcaster.notify_by_tid(tid=tid, event=event)
        except: pass
