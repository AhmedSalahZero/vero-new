from prophet import Prophet
import pandas as pd
import json
import sys 
import numpy as np

data = json.loads(sys.argv[1]) 
cap = float(sys.argv[2]) 
df = pd.DataFrame(data)

df['cap'] = cap
model = Prophet(growth="logistic")
model.fit(df)
future = model.make_future_dataframe(periods=4	, freq='M')  # 12 months forecast
future['cap'] = cap  
forecast = model.predict(future)

array = forecast['yhat'].values
print(array)
