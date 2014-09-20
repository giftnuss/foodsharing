<?php 
function validEmail($email)
{
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) 
	{
		return true;
	}
	else
	{
		return false;
	}
}

function validPlz($plz)
{
	$plz = preg_replace('/[^0-9]/', '', $plz);
	
	
	
	if(strlen($plz) == 5)
	{
		return true;
	}
	else
	{
		return false;
	}
}
?>