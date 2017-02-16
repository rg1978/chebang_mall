
local json = require("json")

input = json.decode(input)

input.update_time = os.time()
input._id = nil

print(input)
output = input