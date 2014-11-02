var storage = {
		
	prefix: '',
		
	setPrefix: function(prefix)
	{
		this.prefix = prefix+':';
	},
	set: function(key,val)
	{
		val = JSON.stringify({v:val});
		localStorage.setItem(storage.prefix+key, val);
	},
	get: function(key)
	{
		val = localStorage.getItem(storage.prefix+key);
		if(val != undefined)
		{
			val = JSON.parse(val);
			return val.v;
		}
		return val;
	},
	del: function(key)
	{
		removeItem(storage.prefix+key);
	}
};