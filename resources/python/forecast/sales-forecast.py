import os
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from xgboost import XGBRegressor
from sklearn.ensemble import RandomForestRegressor
from sklearn.linear_model import LinearRegression
from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from tensorflow.keras.callbacks import EarlyStopping, ModelCheckpoint

data = json.loads(sys.argv[1]) 
df = pd.DataFrame(data)
print(df)
print('------------------------------')
df.index = pd.to_datetime(df['date'], format='%Y-%m-%d')
del df["date"];
plt.ylabel('Net Sales Value')
plt.xlabel('Date')
plt.xticks(rotation=45)
train = df[df.index < pd.to_datetime("2024-06-30", format='%Y-%m-%d')]
test = df[df.index > pd.to_datetime("2024-06-30", format='%Y-%m-%d')]




y = train['net_sales_value']


ARIMAmodel = ARIMA(y, order = (5,4,3))
ARIMAmodel = ARIMAmodel.fit()

y_pred = ARIMAmodel.get_forecast(len(test.index))
y_pred_df = y_pred.conf_int(alpha = 0.05) 
y_pred_df["Predictions"] = ARIMAmodel.predict(start = y_pred_df.index[0], end = y_pred_df.index[-1])
y_pred_df.index = test.index
y_pred_out = y_pred_df["Predictions"] 
print('preduction')
print(y_pred_out)
arma_rmse = np.sqrt(mean_squared_error(test["net_sales_value"].values, y_pred_df["Predictions"]))
print("RMSE: ",arma_rmse)
