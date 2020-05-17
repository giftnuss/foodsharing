local user_id = ARGV[1]

local all_session_ids = {}

local function collect(user_key, session_prefix)
  local session_ids = redis.call("SMEMBERS", user_key)
  for id, session_id in ipairs(session_ids) do
    if redis.call("EXISTS", session_prefix .. session_id) == 1 then
      table.insert(all_session_ids, session_id)
    else
      redis.call("SREM", user_key, session_id)
    end
  end
end

collect("php:user:" .. user_id .. ":sessions", "PHPREDIS_SESSION:")

return all_session_ids
