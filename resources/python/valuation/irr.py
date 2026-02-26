import sys 
import numpy_financial as npf
import json 
 
def printName(name):
    return name
data = sys.argv[1] ;
print("calculate irr for " + data)
cash_flows =json.loads(data)
irr = npf.irr(cash_flows)
print(f"The IRR is: {irr * 100:.2f}%")


