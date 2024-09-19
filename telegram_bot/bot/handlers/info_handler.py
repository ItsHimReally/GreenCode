from aiogram import Dispatcher
from aiogram import Router
from aiogram.types import Message
from aiogram.filters import Command


router = Router(name="Info")


@router.message(Command(commands="info"))
async def test(message: Message) -> None:
    text = "Авторизируйся в боте на [платформе EcoTime](gcmos.tw1.su) через вкладку \"Профиль\""
    await message.reply(
        text=text,
        parse_mode="MarkdownV2",
    )


async def register(dp: Dispatcher) -> None:
    dp.include_router(router)
