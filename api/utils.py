import requests
from random import randint
import numpy as np


def add_quesion(sql, question, userID, itemID, answer):
    ids = randint(10 ** 12, 10 ** 13 - 1)
    mycursor = sql.cursor()
    mycursor.execute(
        """INSERT INTO questions (id, timestamp, userID, itemID, question, answer, isSolved) VALUES (%s, NOW(), %s, %s, %s, %s, 1)""",
        [ids, userID, itemID, question, answer])
    sql.commit()

    mycursor.close()
    return ids


def update_solved(sql, queID):
    mycursor = sql.cursor()
    mycursor.execute(
        """UPDATE questions SET isSolved=0 WHERE id=%s""",
        [queID])
    sql.commit()

    mycursor.close()


def generate_context(sql, itemID):
    mycursor = sql.cursor()
    mycursor.execute(
        """SELECT `title`, `description`, `timeEnd`, `timeStart`, `locationAddress`, `ageLimit`, `price`, `bonusPoints` FROM `events` WHERE `id`=%s""",
        [itemID])

    info = mycursor.fetchone()

    context = f"""
    Название мероприятия: {info[0]},
    Описание: {info[1]},
    Время начала: {info[3].strftime('%m/%d/%Y %H:%M')}
    Время завершения: {info[2].strftime('%m/%d/%Y %H:%M')},
    Место проведения: {info[4]},
    Возрастное ограничение: страше {info[5]},
    Стоимость билета: {info[6]},
    Бонусные очки за участие: {info[7]}
    """

    mycursor.close()

    return context

def yagpt_request(question, sql, itemID, YANDEX_KEY, YANDEX_FOLDER):
    """
    Подаем запрос в YandexGPT, возвращаем ответ


    Args:
        question (str): Вопрос
        context (str): Контекст

    Returns:
        text (str): Ответ
        render (str): Источник
    """

    query = """
    Ты — виртуальный помощник на сайте-агрегаторе экологических мероприятий. 
    Твоя задача — ответить на вопросы, связанные с экологией, устойчивым развитием и активным образом жизни.
    В месте с запросом придет описание мероприятия по которому задан вопрос, ответь использую эту информацию.
    Если исходя из контекста невозможно дать ответ, скажи об этом.
    """

    context = generate_context(sql, itemID)

    url = "https://llm.api.cloud.yandex.net/foundationModels/v1/completion"
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Api-Key {YANDEX_KEY}"  # Вставьте ваш api ключ
    }

    folder = YANDEX_FOLDER  # id папки

    ya_prompt = {
        "modelUri": f"gpt://{folder}/yandexgpt/latest",
        "completionOptions": {
            "stream": False,
            "temperature": 0.0,
            "maxTokens": "1024"
        },
        "messages": [
            {
                "role": "system",
                "text": query
            },
            {
                "role": "user",
                "text": f"""
                Информация о мероприятии: {context}
                
                Вопрос пользователя: {question}"""
            }
        ]
    }

    response = requests.post(url, headers=headers, json=ya_prompt)
    result = eval(response.text)

    return result['result']['alternatives'][0]['message']['text']

def check_org(id, token):
    """
    :param id: ИНН или ОГРН организации
    :param return: возвращает True, если организация найдена
    """

    K1 = np.array([7, 2, 4, 10, 3, 5, 9, 4, 6, 8])
    K2 = np.array([3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8])

    id_numbers = np.array([int(i) for i in id]) # Массив цифр

    def __ogrn_checksum(val_number):  # Контрольная сумма ОГРН
        return int(val_number) // 10 % 11 % 10 

    def __inn_checksum(val_number):  # Контрольная сумма ИНН
        if len(id) == 12:
            return np.array([id_numbers[0:10] @ K1.T, id_numbers[0:11] @ K2.T]) % 11
        if len(id) == 10:
            return (id_numbers[:9] @ K2[2:].T) % 11

    # id другой длины быть не может
    if len(id) not in [10, 12, 13]:
        return False

    # Проверка контрольных чисел
    # Для ОГРН
    if len(id) == 13:
        if __ogrn_checksum(id) != id_numbers[-1]:
            return False

    # Для ИНН
    if len(id) == 12:
        if __inn_checksum(id)[0] != id_numbers[-2] or __inn_checksum(id)[1] != id_numbers[-1]:
            return False 

    if len(id) == 10:
        if __inn_checksum(id) != id_numbers[-1]:
            return False

    url = "http://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party"
    headers = {"Authorization": f"Token {token}"}
    payload = {"query": f"{id}"}
    result = requests.post(url, headers=headers, json=payload).json()["suggestions"]

    return True if result else False
