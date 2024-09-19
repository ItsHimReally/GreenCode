from typing import Union, Optional

from . import db
from . import models


class UsersDb:
    @staticmethod
    def get_user_by_tid(tid: int) -> Optional[models.User]:
        with db.connection.cursor() as cursor:
            sql_query = r"""
SELECT * FROM users
WHERE tgID = %(tid)s
"""
            cursor.execute(sql_query, {
                "tid": str(tid),
            })

            row = cursor.fetchone()
            if not row:
                return

            return models.User(*row)

    @staticmethod
    def check_user_exists_by_tid(tid: int) -> bool:
        user = UsersDb.get_user_by_tid(tid)
        exists = bool(user)
        return exists


class TelegramAuthCodesDb:
    @staticmethod
    def get_telegram_auth_code(code: str) -> Optional[models.TelegramAuthCode]:
        with db.connection.cursor() as cursor:
            sql_query = r"""
SELECT * FROM telegram_auth_codes
WHERE code = %(code)s
    AND used = 0
"""
            cursor.execute(sql_query, {
                "code": code,
            })

            row = cursor.fetchone()
            if not row:
                return

            return models.TelegramAuthCode(*row)

    @staticmethod
    def complete_telegram_auth_code(code: str, user_id: int, tid: int) -> None:
        with db.connection.cursor() as cursor:
            sql_query = r"""
UPDATE telegram_auth_codes
SET used = 1
WHERE code = %(code)s
"""

            cursor.execute(sql_query, {
                "code": code,
            })
            
            sql_query = r"""
UPDATE users
SET tgID = %(tid)s
WHERE id = %(user_id)s
"""

            cursor.execute(sql_query, {
                "tid": str(tid),
                "user_id": user_id,
            })
        db.connection.commit()
