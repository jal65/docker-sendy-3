<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$email_list_incl = isset($_POST['include_lists']) ? mysqli_real_escape_string($mysqli, $_POST['include_lists']) : exit;	
	$email_list_excl = isset($_POST['exclude_lists']) ? mysqli_real_escape_string($mysqli, $_POST['exclude_lists']) : exit;	
	$email_list_seg_incl = isset($_POST['include_lists_seg']) ? mysqli_real_escape_string($mysqli, $_POST['include_lists_seg']) : exit;	
	$email_list_seg_excl = isset($_POST['exclude_lists_seg']) ? mysqli_real_escape_string($mysqli, $_POST['exclude_lists_seg']) : exit;	
	
	if($email_list_incl==0 && $email_list_seg_incl==0) 
	{
		echo 0; 
		exit;
	}
	if(($email_list_excl != 0 || $email_list_seg_excl != 0) && ($email_list_incl==0 && $email_list_seg_incl==0)) 
	{
		echo 0; 
		exit;
	}
	
	//Include main list query
	$main_query = $email_list_incl == 0 ? '' : 'subscribers.list in ('.$email_list_incl.') ';
	
	//Include segmentation query
	$seg_query = $main_query != '' && $email_list_seg_incl != 0 ? 'OR ' : ''; 
	$seg_query .= $email_list_seg_incl == 0 ? '' : '(subscribers_seg.seg_id IN ('.$email_list_seg_incl.')) ';
	
	//Exclude list query
	$exclude_query = $email_list_excl == 0 ? '' : 'subscribers.email NOT IN (SELECT email FROM subscribers WHERE list IN ('.$email_list_excl.')) ';
	
	//Exclude segmentation query
	$exclude_seg_query = $exclude_query != '' && $email_list_seg_excl != 0 ? 'AND ' : ''; 
	$exclude_seg_query .= $email_list_seg_excl == 0 ? '' : 'subscribers.email NOT IN (SELECT subscribers.email FROM subscribers LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id) WHERE subscribers_seg.seg_id IN ('.$email_list_seg_excl.'))';
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//Get totals from lists
	$q  = 'SELECT 1 FROM subscribers';
	$q .= $email_list_seg_incl==0 && $email_list_seg_excl==0 ? ' ' : ' LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id) ';
	$q .= 'WHERE ('.$main_query.$seg_query.') ';
	$q .= $exclude_query != '' || $exclude_seg_query != '' ? 'AND ('.$exclude_query.$exclude_seg_query.') ' : '';
	$q .= 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1 
		   GROUP BY subscribers.email';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    $total = mysqli_num_rows($r);
	    echo $total; 
	}
	else echo 'failed';
?>