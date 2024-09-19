from aiogram import Dispatcher, Router
from aiogram import F
from aiogram.filters import Command
from aiogram.fsm.context import FSMContext
from aiogram.types import (
    Message,
    ReplyKeyboardRemove,
)


router = Router(name="Cancel")


@router.message(Command("cancel"))
@router.message(F.text.casefold() == "cancel")
async def cancel_handler(message: Message, state: FSMContext) -> None:
    current_state = await state.get_state()
    if current_state is None:
        return

    await state.clear()
    await message.answer(
        "Cancelled",
        reply_markup=ReplyKeyboardRemove(),
    )


async def register(dp: Dispatcher) -> None:
    dp.include_router(router)
