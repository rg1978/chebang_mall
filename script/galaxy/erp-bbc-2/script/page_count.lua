
local json = require("json")

input = json.decode(input)

output = {}


count = tonumber(input.count)

if count ==  nil or count < 0 then
	count = 0
end
page_size = tonumber(input.page_size)

local i = 1
while i <= math.ceil(count/page_size) do
	table.insert(output, i)
	i = i + 1
end

-- for k, v in pairs(output) do
-- 	print(k, v)
-- end
