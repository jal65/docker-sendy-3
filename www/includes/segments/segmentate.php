<?php ini_set('display_errors', 0);?>
<?php include('main.php');?>
<?php 
	include('../config.php');
	//--------------------------------------------------------------//
	function dbConnect() { //Connect to database
	//--------------------------------------------------------------//
	    // Access global variables
	    global $mysqli;
	    global $dbHost;
	    global $dbUser;
	    global $dbPass;
	    global $dbName;
	    global $dbPort;
	    
	    // Attempt to connect to database server
	    if(isset($dbPort)) $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
	    else $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
	
	    // If connection failed...
	    if ($mysqli->connect_error) {
	        fail();
	    }
	    
	    global $charset; mysqli_set_charset($mysqli, isset($charset) ? $charset : "utf8");
	    
	    return $mysqli;
	}
	//--------------------------------------------------------------//
	function fail() { //Database connection fails
	//--------------------------------------------------------------//
	    print 'Database error';
	    exit;
	}
	// connect to database
	dbConnect();
?>
<?php include('../helpers/locale.php');?>
<?php 
	//Vars
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;
	$sid = isset($_GET['s']) && is_numeric($_GET['s']) ? mysqli_real_escape_string($mysqli, $_GET['s']) : exit;
	$app = isset($_GET['i']) && is_numeric($_GET['i']) ? mysqli_real_escape_string($mysqli, $_GET['i']) : exit;
	$timezone = isset($_GET['t']) ? mysqli_real_escape_string($mysqli, $_GET['t']) : exit;
	$redirect_url = isset($_GET['r']) ? mysqli_real_escape_string($mysqli, $_GET['r']) : '';
	$conditions = '';
	$conditions_cf = '';
	$conditions_array = array();
	$conditions_cf_array = array();
	$cf_hold = array();
	$prev_group = 0;	
	$count = 0;
	$first_condition = true;
	$first_cf_condition = true;
	$time = time();
	$non_custom_fields = array('name', 'email', 'timestamp', 'join_date');
	//date_default_timezone_set(get_app_info('timezone'));
	
	function delete_all_from_subscribers_seg()
	{
		global $mysqli;
		global $sid;
		
		//Delete all subscribers from segment
	    $q = 'DELETE FROM subscribers_seg WHERE seg_id = '.$sid;
	    $r = mysqli_query($mysqli, $q);
	    if (!$r) echo _('Error: Can\'t delete all subscribers from segment. '); 
	}
	
	function insert_subscriber_into_segment($subscriber_id)
	{
		global $mysqli;
		global $sid;
		
		$q = 'SELECT seg_id FROM subscribers_seg WHERE seg_id = '.$sid.' AND subscriber_id = '.$subscriber_id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r)==0)
		{
		    $q2 = 'INSERT INTO subscribers_seg (seg_id, subscriber_id) VALUES ('.$sid.', '.$subscriber_id.')';
			$r2 = mysqli_query($mysqli, $q2);
			if (!$r2) _('Error: Can\'t insert subscriber into segment.'); 
		}
	}
	
	function insert_subscriber_into_segment_cf($key)
	{
		global $mysqli;
		global $conditions_cf_array;
		global $lid;
		global $sid;
		global $cf_hold;
		global $first_cf_condition;
		
		$cfa = explode('%n%', $conditions_cf_array[$key]);
		
		for($i = 0; $i<count($cfa)-1; $i++)
		{
			//If so, check if custom field condition is satisfied, then INSERT subscriber into subscribers_seg
			//Get custom field condition's 'field', 'comparison' and 'value'
			$cf_condition = $cfa[$i];
			$cf_array = explode('%s%', $cf_condition);
			$cf_field = $cf_array[0];
			$cf_comparison = $cf_array[1];
			$cf_value = $cf_array[2];
			
			//Get the array's position of custom field
			$q5 = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
			$r5 = mysqli_query($mysqli, $q5);
			if ($r5)
			{
			    while($row5 = mysqli_fetch_array($r5)) $custom_fields = $row5['custom_fields'];
			    $custom_fields_array = explode('%s%', $custom_fields);
			    $cf_count = count($custom_fields_array);
			    
			    for($j=0;$j<$cf_count;$j++)
			    {
				    $cf_array = explode(':', $custom_fields_array[$j]);
				    $key = str_replace(' ', '', $cf_array[0]);
				    
				    //if custom field matches
				    if($cf_field==$key)
				    {
					    //get array position and break out of loop
				    	$cf_position = $j;
				    	$cf_type = $cf_array[1];
				    	break;
				    }
			    }
			}
			//check if custom field condition is satisfied, if so, INSERT subscriber into subscribers_seg 
			if($first_cf_condition) //Check if custom field condition is the first condition
				$q = 'SELECT id, custom_fields FROM subscribers WHERE list in ('.$lid.') AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
			else
				$q = 'SELECT subscribers.id as id, subscribers.custom_fields as custom_fields FROM subscribers LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id) WHERE subscribers.list in ('.$lid.') AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1 AND subscribers_seg.seg_id = '.$sid;
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0)
			{
			    while($row = mysqli_fetch_array($r))
			    {
				    $subscriber_id = $row['id'];
					$custom_values = $row['custom_fields'];
				    $custom_values_array = explode('%s%', $custom_values);
				    $subscriber_cf_value = $custom_values_array[$cf_position];
				    
				    //Compare then INSERT subscriber into subscribers_seg
				    compare_custom_field_condition($cf_type, $cf_comparison, $subscriber_cf_value, $cf_value, $subscriber_id);
			    }  
			}
			else
			{				
				 //Delete all subscribers from subscribers_seg table that matches this segment
			    delete_all_from_subscribers_seg();
			    break;
			}
		}
		
		delete_all_from_subscribers_seg(); 
		
		//echo count($cf_hold).": CF<br/>";				//3.0.4 edit
		//foreach($cf_hold as $cfh)						//3.0.4 edit
		//	insert_subscriber_into_segment($cfh);		//3.0.4 edit
		
		insert_subscriber_into_segment_array($cf_hold);	//3.0.4 edit
		
		//empty the $cf_hold array
		$cf_hold = array();
		
		//Set $first_cf_condition to false so that the next time this function runs, the results checks against the subscribers_seg table
		$first_cf_condition = false;
	}
	
	//3.0.4 edit
	function insert_subscriber_into_segment_array($array)
	{ 
	  	if (is_array($array) && (count($array) > 0)) { 
			global $mysqli; 
			global $sid; 
			$q2 = 'INSERT IGNORE INTO subscribers_seg (seg_id, subscriber_id) VALUES '; 
			foreach ($array as $subscriber_id) { 
				$q2 .= '('.$sid.', '.$subscriber_id.'),'; 
			} 
			$q2 = substr($q2, 0, -1).";"; 
			$r2 = mysqli_query($mysqli, $q2); 
			if (!$r2) _('Error: Can\'t insert subscriber into segment.');  
		} 
	} 
	
	function push_into_cf_hold_array($subscriber_id)
	{
		global $cf_hold;
		
		//if(!in_array($subscriber_id, $cf_hold))	//3.0.4 edit
		//	array_push($cf_hold, $subscriber_id);	//3.0.4 edit
		
		$cf_hold[$subscriber_id] = $subscriber_id;	//3.0.4 edit
	}
		    
    //Get segmentation conditions
	$q = 'SELECT 
			seg_cons.id as id,
    		seg_cons.grouping as grouping, 
    		seg_cons.operator as operator,
    		seg_cons.field as field,
    		seg_cons.comparison as comparison,
    		seg_cons.val as val  
    	FROM seg, seg_cons WHERE seg_cons.seg_id = seg.id AND seg.list = '.$lid.' AND seg_id = '.$sid.' ORDER BY grouping ASC';
    $r = mysqli_query($mysqli, $q);
    if ($r && mysqli_num_rows($r) > 0)
    {
        while($row = mysqli_fetch_array($r))
        {
	        $grouping = $row['grouping'];
	        $operator = $row['operator'];
	        $field = $row['field'];
	        $comparison = $row['comparison'];
	        $val = $row['val'];
	        
	        //Group 'OR'statements into $conditions_array
	        if($grouping != $prev_group)
    		{
	    		// If not a custom field
	    		if(in_array($field, $non_custom_fields))
		    	{
			    	$conditions_array[$count] = $conditions;
			    	
			    	//If field is a 'date' and operator is '=', then set query to find results between 00.00am and 11.59pm
		    		if($field=='join_date' || $field=='timestamp')
		    		{
			    		if($comparison=='=')
			    		{
				    		$comparison = 'BETWEEN';
							$current_day = strftime("%d", $val);
							$current_month = strftime("%b", $val);
							$current_year = strftime("%G", $val);
							$current_hour = strftime("%H", $val);
							$current_mins = strftime("%M", $val);
							$val_day = strtotime($current_day.' '.$current_month.' '.$current_year.' 12am');
							$val_night = strtotime($current_day.' '.$current_month.' '.$current_year.' 11.59pm');
							$val = "$val_day AND $val_night";
			    		}
		    		}
			    	
			    	//Add quotes around the query's value only if comparison is not 'BETWEEN'
		    		$conditions = $comparison=='BETWEEN' ? $operator.' subscribers.'.$field.' '.$comparison.' '.$val.' ' : $operator.' subscribers.'.$field.' '.$comparison.' "'.$val.'" ';
		    		
		    		$conditions_cf_array[$count] = $conditions_cf;
		    		$conditions_cf = '';
				}
				// Is a custom field condition
				else
				{
					$conditions_cf_array[$count] = $conditions_cf;
		    		$conditions_cf = $field.'%s%'.$comparison.'%s%'.$val.'%n%';
		    		
		    		$conditions_array[$count] = $conditions;
		    		$conditions = '';
				}
				$count++;
    		}
    		else 
    		{
	    		// If not a custom field
	    		if(in_array($field, $non_custom_fields))
		    	{
			    	//If field is a 'date' and operator is '=', then set query to find results between 00.00am and 11.59pm
		    		if($field=='join_date' || $field=='timestamp')
		    		{
			    		if($comparison=='=')
			    		{
				    		$comparison = 'BETWEEN';
							$current_day = strftime("%d", $val);
							$current_month = strftime("%b", $val);
							$current_year = strftime("%G", $val);
							$current_hour = strftime("%H", $val);
							$current_mins = strftime("%M", $val);
							$val_day = strtotime($current_day.' '.$current_month.' '.$current_year.' 12am');
							$val_night = strtotime($current_day.' '.$current_month.' '.$current_year.' 11.59pm');
							$val = "$val_day AND $val_night";
			    		}
		    		}
		    		$conditions .= $comparison=='BETWEEN' ? $operator.' subscribers.'.$field.' '.$comparison.' '.$val.' ' : $operator.' subscribers.'.$field.' '.$comparison.' "'.$val.'" ';
		    	}
		    	// Is a custom field condition
		    	else
		    	{
			    	$conditions_cf .= $field.'%s%'.$comparison.'%s%'.$val.'%n%';
		    	}
	    	}
			
			$prev_group = $grouping;
        }
        $conditions_array[$count] = $conditions;
        $conditions_array = array_slice($conditions_array, 1);
        $conditions_cf_array[$count] = $conditions_cf;
        $conditions_cf_array = array_slice($conditions_cf_array, 1);
    }
    else echo _('No segments found for this list.');
    
    //Delete all subscribers from subscribers_seg table that matches this segment
    delete_all_from_subscribers_seg();
    
    //Loop through each group of segmentation conditions
    foreach ($conditions_array as $key => $ca)
    {
	    $ca = $ca=='' ? '' : '('.$ca.')'; //Wrap OR queries in brackets
	    
	    //Show segmentation results for this list
	    //Extract subscribers from the first group of conditions
	    if($first_condition)
	    {
		    if($ca!='')
		    {
			    $ca = substr($ca, 0, 3)=='(OR' ? '('.substr($ca, 3) : $ca ;
			    $q2 = 'SELECT id FROM subscribers WHERE list in ('.$lid.') AND '.$ca.' AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
			    $r2 = mysqli_query($mysqli, $q2);
			    $total_rows = mysqli_num_rows($r2);
			    if ($r2 && $total_rows > 0)
			    {
			        while($row = mysqli_fetch_array($r2)) 
			        {
				        if($conditions_cf_array[$key]=='')
				    		insert_subscriber_into_segment($row['id']);
				    	else
				    		push_into_cf_hold_array($row['id']);
			    	}
			        //echo $total_rows.": $ca<br/>";
			    }
			    else 
			    {
				    //echo '0 results';
				    
				     //Delete all subscribers from subscribers_seg table that matches this segment
				    delete_all_from_subscribers_seg();
				    break;
				}
			}
			
			//Check if custom field conditions exists,
    		if($conditions_cf_array[$key]!='')
	    		insert_subscriber_into_segment_cf($key);
			
		    $first_condition = false;
		    $first_cf_condition = false;
		}
		//Subsequent conditions will be checked against the previously extracted list
		else 
	    {
		    if($ca!='')
		    {
			    $ca = substr($ca, 0, 3)=='(OR' ? '('.substr($ca, 3) : $ca ;
			    $q2 = 'SELECT subscribers.id AS id FROM subscribers LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id)  WHERE subscribers.list in ('.$lid.') AND '.$ca.' AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1 AND subscribers_seg.seg_id = '.$sid;
			    $r2 = mysqli_query($mysqli, $q2);
			    $total_rows = mysqli_num_rows($r2);
			    if ($r2 && $total_rows > 0)
			    {
				    if($conditions_cf_array[$key]=='')
					    delete_all_from_subscribers_seg(); 
				    
			        while($row = mysqli_fetch_array($r2))
			        {
				        if($conditions_cf_array[$key]=='')
				    		insert_subscriber_into_segment($row['id']);
				    	else
				    		push_into_cf_hold_array($row['id']);
			    	}
			        //echo $total_rows.": $ca<br/>";
			    }
			    else 
			    {
				    //echo '0 results';
				    
				    //Delete all subscribers from subscribers_seg table that matches this segment
				    delete_all_from_subscribers_seg();
				    break;
				}
			}
			
			//Check if custom field conditions exists,
    		if($conditions_cf_array[$key]!='')
	    		insert_subscriber_into_segment_cf($key);
		}
    }
    
    //Update segment 'last updated'
    $q = 'UPDATE seg SET last_updated = "'.$time.'" WHERE id = '.$sid;
	mysqli_query($mysqli, $q);
    
    if($redirect_url!='')
    {
	    if($redirect_url=='list')
	    	$redirect_url = APP_PATH."/segments-list?i=$app&l=$lid";
	    else if($redirect_url=='conditions')
		    $redirect_url = APP_PATH."/segment?i=$app&l=$lid&s=$sid";
		header("Location: $redirect_url");
	}
?>