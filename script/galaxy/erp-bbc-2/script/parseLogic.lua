
local json = require("json")

input = json.decode(input)


output = {
	code = input.code or input.logistic_code or input.company_code,
	-- name = input.name or input.logistic_name or input.company_name
}
