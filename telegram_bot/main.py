from typing import Any
import logging
import asyncio

from bot import TelegramBot


async def main() -> None:
    bot = TelegramBot()
    try:
        await bot.start()
    finally:
        await bot.stop()


if __name__ == "__main__":
    asyncio.run(main())
