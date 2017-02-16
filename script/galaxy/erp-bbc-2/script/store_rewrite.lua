local json = require("json")
input = json.decode(input)


output = {}

local message =  {}

if input.message ~= nil do
	message = json.decode(input.message)
end


output.error_response =  message