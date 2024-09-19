from typing import Union, Optional

from . import db
from . import models


class RegistrationsDb:
    @staticmethod
    def get_registrations_by_user_id(user_id: int) -> list[models.Registration]:
        with db.connection.cursor() as cursor:
            sql_query = r"""
SELECT * FROM registrations
WHERE userID = %(user_id)s
"""
            cursor.execute(sql_query, {
                "user_id": str(user_id),
            })

            rows = cursor.fetchall()
            if not rows:
                return []

            registrations: list[models.Registration] = []
            for row in rows:
                registration = models.Registration(*row)
                registrations += [registration]

            return registrations


class EventsDb:
    @staticmethod
    def get_event_by_id(event_id: int) -> Optional[models.Event]:
        with db.connection.cursor() as cursor:
            sql_query = r"""
    SELECT * FROM events
    WHERE id = %(event_id)s
    """
            cursor.execute(sql_query, {
                "event_id": str(event_id),
            })

            row = cursor.fetchone()
            if not row:
                return

            return models.Event(*row)


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
    
    @staticmethod
    def get_user_by_user_id(user_id: int) -> Optional[models.User]:
        with db.connection.cursor() as cursor:
            sql_query = r"""
SELECT * FROM users
WHERE id = %(user_id)s
"""
            cursor.execute(sql_query, {
                "user_id": str(user_id),
            })

            row = cursor.fetchone()
            if not row:
                return

            return models.User(*row)
