from aiogram import Dispatcher

from . import handlers


async def register_handlers(dp: Dispatcher) -> None:
    await handlers.start_handler.register(dp)
    await handlers.info_handler.register(dp)
    await handlers.cancel_handler.register(dp)
