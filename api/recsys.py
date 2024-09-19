import pandas as pd
import numpy as np
from rectools.dataset import Dataset
from rectools import Columns
from rectools.models.pure_svd import PureSVDModel

from rectools.models.lightfm import LightFMWrapperModel
from lightfm import LightFM

model = LightFMWrapperModel(LightFM(no_components=20, loss="bpr", random_state=42))

# model = PureSVDModel(factors=10)


def get_tables(sql):
    with sql.cursor() as mycursor:
        mycursor.execute(
            """SELECT `id`, `org`, `isOnline`, `tags` FROM `events`""")

        events = mycursor.fetchall()

        mycursor.execute(
            """SELECT `id`, `age`, `sex` FROM `users`""")

        users = mycursor.fetchall()

        mycursor.execute(
            """SELECT `userID`, `eventsID`, `reaction`, `mark`, `time` FROM `likes`""")

        likes = mycursor.fetchall()

    events = pd.DataFrame(events, columns=['id', 'org', 'isOnline', 'tags'])
    users = pd.DataFrame(users, columns=['id', 'age', 'sex'])
    likes = pd.DataFrame(likes, columns=['userID', 'eventsID', 'reaction', 'mark', 'time'])

    return events, users, likes





def process_explicit_features(df, id_col, features):
    features_frames = []

    for feature in features:
        feature_frame = df[[id_col, feature]]
        feature_frame.columns = [Columns.User, feature]

        feature_frame.columns = ["id", "value"]
        feature_frame["feature"] = feature
        features_frames.append(feature_frame)

    features = pd.concat(features_frames)

    return features


def rebuild_recsys(sql):
    global model
    events, users, likes = get_tables(sql)

    conditions = [
        (likes['mark'] == 1),
        (likes['mark'] == 2),
        (likes['reaction'] == -1),
        (likes['mark'] == 3),
        (likes['reaction'] == 1) & (likes['mark'] == 0),
        (likes['mark'] == 4),
        (likes['mark'] == 5)
    ]
    weights = [-3, -2, -1, 0, 1, 2, 3]

    likes['weight'] = np.select(conditions, weights)

    likes['time'] = pd.to_datetime(likes['time'])
    likes = likes.drop(columns=['reaction', 'mark'])
    likes = likes[['userID', 'eventsID', 'weight', 'time']]
    likes.columns = [Columns.User, Columns.Item, Columns.Weight, Columns.Datetime]

    events = events.rename(columns={'id': 'item_id'})
    events = events.loc[events[Columns.Item].isin(likes[Columns.Item])].copy()

    events = process_explicit_features(events, 'item_id', ['org', 'isOnline', 'tags'])
    users = process_explicit_features(users, 'id', ["sex", "age"])

    dataset = Dataset.construct(
        likes,
        user_features_df=users,
        item_features_df=events,
        cat_user_features=['sex'],
        cat_item_features=['tags']
    )

    model.fit(dataset)

    return events, users, likes, dataset


def recom(userID, dataset):
    rec = model.recommend(
        users=[userID],
        dataset=dataset,
        k=9,
        filter_viewed=True,
    )['item_id']

    return rec
