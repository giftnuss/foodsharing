$(function(){
	var nba = new Bloodhound({
		datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.team); },
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		prefetch: 'xhrapp.php?app=search&m=betriebe'
	});
	
	var nhl = new Bloodhound({
		datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.team); },
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		prefetch: 'xhrapp.php?app=search&m=people'
	});
	 
	nba.initialize();
	nhl.initialize();
	
	$('#bar-search').typeahead({
		highlight: true
	},
	{
		name: 'nba',
		displayKey: 'team',
		source: nba.ttAdapter()
	},
	{
		name: 'nhl',
		displayKey: 'team',
		source: nhl.ttAdapter()
	});
	$('.twitter-typeahead').css({
		'position':'absolute',
		'width':'128px',
		'height':'19px',
		'line-height':'23px',
		'margin':'0 0 0 -272px',
		'top':'8px',
		'z-index':'50',
		'left':'50%'

	});
	/*
	$('.tt-hint').css({
		'position':'absolute',
		'width':'128px',
		'height':'19px',
		'line-height':'23px',
		'margin':'0 0 0 -272px',
		'top':'8px',
		'z-index':'50',
		'left':'50%'
	});
	$('#bar-search').css({
		'position':'absolute',
		'width':'128px',
		'height':'19px',
		'line-height':'23px',
		'margin':'0 0 0 -272px',
		'top':'8px',
		'z-index':'50',
		'left':'50%'
	});
	*/
});
