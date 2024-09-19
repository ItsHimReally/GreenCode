from aiogram import Dispatcher
from aiogram import Router
from aiogram.types import Message
from aiogram.filters import Command, CommandObject
from aiogram.fsm.context import FSMContext

from bot import db


router = Router(name="Start")


@router.message(Command(commands="start"))
async def test(message: Message, command: CommandObject) -> None:
    if db.UsersDb.check_user_exists_by_tid(message.chat.id):
        text = "Твой аккаунт уже авторизирован на платформе EcoTime :)"
        await message.reply(text=text)
        return

    start_code = command.args
    if not start_code:
        text = (
            "Привет\\!\n"
            "Авторизируйся в боте на [платформе EcoTime](gcmos.tw1.su) на вкладке \"Профиль\""
        )
        await message.reply(
            text=text,
            parse_mode="MarkdownV2"
        )
        return

    tg_auth_code = db.TelegramAuthCodesDb.get_telegram_auth_code(code=start_code)
    if not tg_auth_code:
        text = "К сожалению, твой код авторизации не существует или уже был использован :("
        await message.reply(text=text)
        return


    user_id = tg_auth_code.userID
    db.TelegramAuthCodesDb.complete_telegram_auth_code(
        code=start_code,
        user_id=user_id,
        tid=message.chat.id,
    )

    text = "Поздравляем, ты был успешно авторизован на платформе EcoTime 🎉"
    await message.reply(text=text)


async def register(dp: Dispatcher) -> None:
    dp.include_router(router)
