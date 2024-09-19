import dataclasses
import datetime


@dataclasses.dataclass
class User:
    id: int
    nick: str
    name: str
    imageUrl: str
    status: str
    password: str
    regTime: str
    links: str 
    "json"
    bio: str
    sex: int
    "tinyint"
    birthDate: datetime.datetime
    xp: int
    bonusPoints: int
    countTrees: int
    statusTree: str
    co2kg: int
    healthApp: str
    "json"
    tgID: str
    mosRuID: str
    yaID: str
    age: int
    isAdmin: int
    """tinyint"""


@dataclasses.dataclass
class TelegramAuthCode:
    id: int
    code: str
    userID: int
    created_at: datetime.datetime
    used: int
    "tinyint"
