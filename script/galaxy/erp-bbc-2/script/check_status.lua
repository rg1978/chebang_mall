
local json = require("json")

input = json.decode(input)


function status( input )
	local switch = {
		["1"] = "0",
		["2"] = "0",
		["3"] = "1",
		["4"] = "4",
		["5"] = "3",
		["6"] = "5",
		["7"] = "5",
		["8"] = "补差价",
		["9"] = "3",
	}
	return switch[tostring(input.status)]
end


output =  status(input)
