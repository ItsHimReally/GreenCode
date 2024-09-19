from typing import Any
import os
import asyncio

import dotenv

from aiogram import Bot, Dispatcher
from aiogram.fsm.storage.memory import MemoryStorage

from .register_handlers import register_handlers
from .set_default_commands import set_default_commands
from . import db


class TelegramBot:
    def __init__(self) -> None:
        dotenv.load_dotenv()
        
        BOT_TOKEN = os.getenv("BOT_TOKEN")
        assert BOT_TOKEN, "BOT_TOKEN env var is not set"
        
        self.bot = Bot(
            token=BOT_TOKEN,
        )
        self.dp = Dispatcher(
            bot=self.bot,
            storage=MemoryStorage()
        )
    
    async def __on_startup(self, *args) -> None:
        print("Bot started")
    
    async def __on_shutdown(self, *args) -> None:
        print("Bot is shuting down")
    
    async def start(self) -> None:
        await register_handlers(self.dp)
        await set_default_commands(self.bot)

        self.dp.startup.register(self.__on_startup)
        self.dp.shutdown.register(self.__on_shutdown)

        await self.dp.start_polling(self.bot)

    async def stop(self) -> None:
        await self.dp.stop_polling()
        await self.dp.storage.close()
        
        if (session := self.bot.session):
            await session.close()
