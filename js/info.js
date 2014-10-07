var info = {
	
	/*
	 * preselect elements to reduce dom querys
	 */
	$infobar:null,
	$badge:null,
	$linklist:null,
	$linkwrapper:null,
	
	/*
	 * an array of the services that the heartbeat have to call
	 */
	services:null,
	
	/*
	 * little var for everyone to play with user data
	 */
	user:null,
	
	/*
	 * count for heartbeat times
	 */
	hbCount:0,
	
	/*
	 * here an array to store recieved data for each item
	 */
	data:null,
	
	/*
	 * in this array we store the last refreshing times of each info item
	 */
	refreshTime:[],
	
	/*
	 * after this time we want to refrsh the info content
	 */
	refreshTimeout:30000,
	
	hbXhr:null,	
	
	
	startupTimeout:false,
	
	/*
	 * pseudo construct
	 */
	init: function()
	{	
		
		setTimeout(function(){
			info.startupTimeout = true;
			info.heartbeat();
		},5000);
		
		//
		this.services = new Array();
		
		this.$infobar = $('#infobar');
		
		// init data array
		this.data = new Array();
		this.data['msg'] = {};
		this.data['info'] = {};
		this.data['basket'] = {};
		
		// init badge dom querys
		this.$badge = new Array();
		this.$badge['msg'] = $('#infobar > li.msg > a .badge');
		this.$badge['info'] = $('#infobar > li.info > a .badge');
		this.$badge['basket'] = $('#infobar > li.basket > a .badge');
		
		// init linklist dom querys
		this.$linklist = new Array();
		this.$linklist['msg'] = $('#infobar .msg .linklist');
		this.$linklist['info'] = $('#infobar .info .linklist');
		this.$linklist['basket'] = $('#infobar .basket .linklist');
		
		// init linkwrappers its where the conten comes in
		this.$linkwrapper = new Array();
		this.$linkwrapper['msg'] = $('#infobar .msg .linkwrapper');
		this.$linkwrapper['info'] = $('#infobar .info .linkwrapper');
		this.$linkwrapper['basket'] = $('#infobar .basket .linkwrapper');	
				
		// add nice scroller to lists
		$('#infobar .linkwrapper .linklist').slimScroll();
		
		// init dom events
		this.initEvents();
		
		// start continiusly heartbeat
		
	},
	
	/*
	 * function to init all the ui stuff
	 */
	initEvents: function()
	{
		// onclick="$(this).children(\'.linkwrapper\').toggle();" class="msg" onmouseover="$(this).children(\'.linkwrapper\').show();info.refresh(\'msg\');" onmouseout="$(this).children(\'.linkwrapper\').hide();"
		
		this.$infobar.children('li').each(function(){
			var $this = $(this);
			var type = $this.attr('class');
			
			$this.mouseover(function(){
				info.refresh(type);
				info.$linkwrapper[type].show();
			});
			
			$this.mouseout(function(){
				info.$linkwrapper[type].hide();
			});
			
			$this.click(function(){
				info.$linkwrapper[type].toggle();
			});
			
		});
	},
	
	/*
	 * function to set and display the badge number in top of an info item
	 */
	badge: function(type,val)
	{
		this.$badge[type].text(val);
		if(val > 0)
		{
			this.$badge[type].show();
		}
		else
		{
			this.$badge[type].hide();
		}
	},
	
	/**
	 * Method to add an polling service
	 * options are send as GET Parameter to the module action
	 * 
	 * the there are 3 polling speed options {speed:slow|moderate|fast}
	 * default is slow = every 10 seconds 
	 * 		moderate is slow/4  => 2.5 seconds as default
	 * 		fast is slow/20 	=> 0.5 seconds as default
	 * 
	 * option {premethod:[methodName]} with this option you can define an method which is called before the session is locked for writing
	 */
	addService: function(app,method,options)
	{
		this.services.push({
			a:app,
			m:method,
			o:options
		});
		
		this.restart();
	},
	
	/**
	 * remove an polling service
	 */
	removeService: function(app,method)
	{		
		var tmp = new Array();
		for(var i=0;i<info.services.length;i++)
		{
			if(!(info.services[i].a == app || info.services.m == method))
			{
				tmp.push(info.services[i]);
			}
		}
		this.services = tmp;
		this.restart();
	},
	
	/**
	 * modify service parameter
	 */
	editService: function(app,method,options)
	{		
		var tmp = new Array();
		for(var i=0;i<info.services.length;i++)
		{
			if(!(info.services[i].a == app || info.services.m == method))
			{
				tmp.push(info.services[i]);
			}
		}
		
		/**
		 * if the service is not in the list just add it
		 */
		tmp.push({
			a:app,
			m:method,
			o:options
		});
		
		
		info.services = tmp;
		this.restart();
	},
	
	/**
	 * restart the heartbead
	 */
	restart: function()
	{
		if(_.isNull(this.hbXhr) !== true)
		{
			info.hbCount = 0;
			this.hbXhr.abort();
		}
	},
	
	/**
	 * continiously checking for updates
	 */
	heartbeat: function()
	{
		if(this.startupTimeout)
		{
			this.hbXhr = ajax.req('info','heartbeat',{
				loader:false,
				data:{
					c:info.hbCount,
					
					// add services to param list
					s:info.services
				},
				success: function(ret){
					
					if(info.hbCount == 0)
					{
						info.user = ret.user;
					}

					if(ret.info != undefined && ret.info.length > 0)
					{
						for(var i=0;i<ret.info.length;i++)
						{	
							// set badge count for each item
							info.badge(ret.info[i].type,ret.info[i].badge);
							
							// store specific data for each item
							info.data[ret.info[i].type] = ret.info[i].data;
						}
					}
				},
				complete: function(){
					info.heartbeat();
					info.hbCount++;
				}
			});
		}
		
	},
	
	/**
	 * show status loading on specific info item
	 */
	showLoader: function(item)
	{
		
	},
	
	/**
	 * check if its time for refresh reload the info content
	 */
	refresh: function(item)
	{
		if(info.refreshTime[item] == undefined)
		{
			info.refreshTime[item] = 0;
		}

		if($.now() - info.refreshTime[item] > info.refreshTimeout)
		{
			info.refreshTime[item] = $.now();
			ajax.req(item,'infobar',{
				loader:false,
				data:info.data[item],
				success: function(ret){
					if(ret.html != undefined)
					{
						info.$linklist[item].html(ret.html);
					}
				}
			});
		}
	}
};