from typing import Optional
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
class Registration:
    id: int
    userID: int
    eventID: int
    Ticket: str
    Form: str
    """json"""
    timeReg: datetime.datetime
    isPresence: int
    """tinyint"""
    isPaid: int
    """tinyint"""
    isApproved: int
    """tinyint"""


@dataclasses.dataclass
class Event:
    id: int
    org: int
    imageUrl: str
    title: str
    short: str
    description: str
    timeStart: datetime.datetime
    timeEnd: datetime.datetime
    isOnline: int
    """tinyint"""
    locationAddress: Optional[str]
    locationCoords: str
    locationUrl: str
    tags: str
    ageLimit: int
    isClosed: int
    """tinyint"""
    whiteList: str
    """json"""
    price: int
    Tickets: str
    """json"""
    Form: str
    """json"""
    bonusPoints: int
    bonusTree: int
    addXP: int
