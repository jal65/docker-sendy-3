<?php 
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	function compare_custom_field_condition($cf_type, $com, $haystack, $needle, $sid)
	//------------------------------------------------------//
	{		
		// $cf_type is the type of custom field, currently either 'Text' or 'Date'
		// $com is the operator for the comparison
		// $needle is the custom field value extracted from the subscriber saved in the database
		// $haystack is the custom field value from the segmentation condition that the user sets to check against $needle
		// $sid is the subscriber's 'id' to be inserted into the `subscribers_seg` table
		
		//If condition is for a 'Text' based custom field
		if($cf_type == 'Text')
		{
			if($com=='=') //is
		    {
			    if($haystack==$needle)
			    	push_into_cf_hold_array($sid);
			}
			else if($com=='!=') //is not
		    {
			    if($haystack!=$needle)
			    	push_into_cf_hold_array($sid);
			}
			else if($com=='LIKE') //contains, starts with, ends with
			{
				//contains
				if(startsWith($needle, '%') && endsWith($needle, '%'))
				{
					$needle = substr($needle, 1, -1);
					if(strpos($haystack, $needle) !== false)
					    push_into_cf_hold_array($sid);
			   }
			   //starts with
			   else if(!startsWith($needle, '%') && endsWith($needle, '%'))
			   {
				   $needle = substr($needle, 0, -1);
				   if(substr($haystack, 0, strlen($needle)) == $needle)
					    push_into_cf_hold_array($sid);
			   }
			   //ends with
			   else if(startsWith($needle, '%') && !endsWith($needle, '%'))
			   {
				   $needle = substr($needle, 1);
				   if(strlen($haystack) - strlen($needle) == strrpos($haystack,$needle))
					    push_into_cf_hold_array($sid);
			   }
			}
			else if($com=='NOT LIKE') //does not contain, does not start with, does not end with, 
			{
				//does not contain
				if(startsWith($needle, '%') && endsWith($needle, '%'))
				{
					$needle = substr($needle, 1, -1);
					if(strpos($haystack, $needle) === false)
					    push_into_cf_hold_array($sid);
			   }
			   //does not start with
			   else if(!startsWith($needle, '%') && endsWith($needle, '%'))
			   {
				   $needle = substr($needle, 0, -1);
				   if(substr($haystack, 0, strlen($needle)) != $needle)
					    push_into_cf_hold_array($sid);
			   }
			   //does not end with
			   else if(startsWith($needle, '%') && !endsWith($needle, '%'))
			   {
				   $needle = substr($needle, 1);
				   if(strlen($haystack) - strlen($needle) != strrpos($haystack,$needle))
					    push_into_cf_hold_array($sid);
			   }
			}
		}
		//If condition is for a 'Date' based custom field
		else if($cf_type == 'Date')
		{
			if($com=='=') //on
    		{
				$current_day = strftime("%d", $needle);
				$current_month = strftime("%b", $needle);
				$current_year = strftime("%G", $needle);
				$current_hour = strftime("%H", $needle);
				$current_mins = strftime("%M", $needle);
				$val_day = strtotime($current_day.' '.$current_month.' '.$current_year.' 12am');
				$val_night = strtotime($current_day.' '.$current_month.' '.$current_year.' 11.59pm');
				if($haystack >= $val_day && $haystack < $val_night)
					push_into_cf_hold_array($sid);
    		}
    		else if($com=='<') //before
    		{
	    		if($haystack < $needle)
					push_into_cf_hold_array($sid);
    		}
    		else if($com=='>') //after
    		{
	    		if($haystack > $needle)
					push_into_cf_hold_array($sid);
    		}
    		else if($com=='<=') //on or before
    		{
	    		if($haystack <= $needle)
					push_into_cf_hold_array($sid);
    		}
    		else if($com=='>=') //on or after
    		{
	    		if($haystack >= $needle)
					push_into_cf_hold_array($sid);
    		}
    		else if($com=='BETWEEN') //between
    		{
	    		$date_array = explode(' AND ', $needle);
	    		$date_start = $date_array[0];
	    		$date_end = $date_array[1];
	    		if($haystack >= $date_start && $haystack <= $date_end)
					push_into_cf_hold_array($sid);
    		}
		}
	}
	
	//------------------------------------------------------//
	function startsWith($haystack, $needle)
	//------------------------------------------------------//
	{
	     $length = strlen($needle);
	     return (substr($haystack, 0, $length) === $needle);
	}
	
	//------------------------------------------------------//
	function endsWith($haystack, $needle)
	//------------------------------------------------------//
	{
	    $length = strlen($needle);
	    if ($length == 0) {
	        return true;
	    }
	    return (substr($haystack, -$length) === $needle);
	}
?>