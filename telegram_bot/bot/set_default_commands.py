from aiogram import Bot
from aiogram.types import (
    BotCommand, 
    BotCommandScopeDefault,
)


COMMANDS: dict[str, str] = {
    "/info": "Получить информацию о боте",
}


async def set_default_commands(bot: Bot) -> None:
    await bot.set_my_commands(
        commands=[
            BotCommand(
                command=k,
                description=v,
            ) for k, v in COMMANDS.items()
        ],
        scope=BotCommandScopeDefault()
    )
