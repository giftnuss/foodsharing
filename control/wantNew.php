<?php
goPage('newarea');
addBread('Neuanmeldungen ohne Region');

if(isBotschafter())
{
	if($new = $db->getWantNew(getBezirkId()))
	{
	
		addJs('
			$(".wbutton").button().click(function(){
				data = $(this).attr("data").split(";");
				
				id = data[0].toLowerCase().replace(/[^a-z]/g,"");
				
				if($("#" + id).length == 0)
				{
					$(".wb-" + id).css("visibility","hidden");
					$("#newRegDrag").append(v_field(\'<ul class="block" id="drop-\'+id+\'" style="padding:15px;"><li class="placeholder">Hierher ziehen</li></ul><span data="\'+data[0]+\';\'+data[1]+\';drop-\'+id+\'" id="nw-button-\'+id+\'">Speichern</span> \',data[0],id)+\'\');
					$("#drop-"+id).droppable({
						activeClass: "ui-state-highlight",
						hoverClass: "ui-state-hover",
						drop: function( event, ui ) {
							fs_id = ui.draggable.find("td input.fs_id").val();
							//alert(fs_id);
							$( this ).find( ".placeholder" ).remove();
							$( "<li></li>" ).html( v_hidden("fsid",fs_id) +  $("#" + fs_id + "-name").val() ).appendTo( this );
							$("#fsrow-" + fs_id).css("display","none");
						}
					});
					
					$("#nw-button-"+id).button().click(function(){
						
						data = $(this).attr("data").split(";");
						nbezirk = data[0];
						
						out = "";
						
						$("#"+ data[2] +" .fsid").each(function(){
							out += ";"+$(this).val();
						});
						
						parent_id = data[1];
						
						out = out.substring(1);
						
						//alert(nbezirk);
						//alert(parent_id);
						//alert(out);
				
						showLoader();
						$.ajax({
							dataType:"json",
							url:"xhr.php?f=orderWantNew",
							data: "new_bezirk=" + nbezirk + "&parent_id=" + parent_id + "&fs=" + out,
							success : function(data){
								if(data.status == 1)
								{
									info("gespeichert, Seite wird neu geladen...");
									setTimeout(function(){reload();},2000);
								}
							},
							complete : function(){
								hideLoader();
							}
						});
				
					});
				
				}
			});	
			$("#newWants tr.drag").draggable({
				appendTo: "body",
				helper: "clone"
			});	
		');
		
		$out = '';
		
		$newrows = array();
		$out .= '<table class="dragRow" id="newWants">';
		
		
		foreach ($new as $n)
		{
			$out .= '
			<tr class="drag even" id="fsrow-'.$n['id'].'">
				<td style="vertical-align:middle;"><input class="fs_id" type="hidden" value="'.$n['id'].'" /><input id="'.$n['id'].'-name" type="hidden" value="'.$n['name'].'" />'.$n['name'].' m&ouml;chte in '.$n['new_bezirk'].' Aktiv werden</td>
				<td align="right"><span class="wbutton wb-'.preg_replace('/[^a-z]/', '', strtolower($n['new_bezirk'])).'" data="'.$n['new_bezirk'].';'.$n['bezirk_id'].'">'.$n['new_bezirk'].' anlegen?</span></td>
			</tr>';
		}
		
		$out .= '</table>';
		
		$content = v_field($out, count($new).' Foodsaver wollen in einer region aktiv werden die noch nicht existiert.',array('class'=>'ui-padding'));
	
		$right = '<div id="newRegDrag"></div>';

	}
	else
	{
		info("In Deinem Bezirk möchten sich keine Gruppen bilden");
	}
}