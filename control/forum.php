<?php

$category_id = 0;
if($categories = $db->getForumThemes())
{
	foreach ($categories as $cat)
	{
		addContent(u_category_head(s($cat['name'])));
		
		if(is_array($cat['themes']))
		{
			
		}
		
	}
}


addContent(u_category_foot());

function u_category_head($category)
{
	return '
	<div class="forum_category">
		<h3>'.s('foum_category_'.$category).'</h3>
		<div class="forum_themes">';
}

function u_category_foot()
{
	return '
		</div>
	</div>';
}