<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$seg_name = isset($_POST['seg_name']) ? mysqli_real_escape_string($mysqli, $_POST['seg_name']) : '';
	$app = is_numeric($_POST['app']) ? $_POST['app'] : exit;
	$lid = is_numeric($_POST['lid']) ? $_POST['lid'] : exit;
	$sid = isset($_POST['sid']) ? $_POST['sid'] : 0;
	if($sid!=0)
		if(!is_numeric($sid)) exit;
	$group_json = isset($_POST['group_json']) ? $_POST['group_json'] : '';
	$edit = isset($_POST['edit']) ? mysqli_real_escape_string($mysqli, $_POST['edit']) : '';
	
	$conditions_array = json_decode($group_json);
	
	date_default_timezone_set(get_app_info('timezone'));
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	if($edit)
	{
		//Delete all conditions from this segment and update seg name
		$q = 'DELETE FROM seg_cons WHERE seg_id = '.$sid;
		$q2 = 'UPDATE seg SET name = "'.$seg_name.'" WHERE id = '.$sid;
		mysqli_query($mysqli, $q);
		mysqli_query($mysqli, $q2);
	}
	else
	{
		//Insert segmentation name into database
		$q = 'INSERT INTO seg (name, app, list) VALUES ("'.$seg_name.'", '.$app.', '.$lid.')';
		$r = mysqli_query($mysqli, $q);
		if ($r) 
			$sid = mysqli_insert_id($mysqli);
		else 
			echo 'cannot-insert-name-into-segment';
	}
	
	$i = 1;
	foreach ($conditions_array as $and_group)
	{
		$first = true;
		foreach($and_group as $or_group)
		{
			$field = mysqli_real_escape_string($mysqli, $or_group[0]);
			$comparison = mysqli_real_escape_string($mysqli, $or_group[1]);
			$val = mysqli_real_escape_string($mysqli, $or_group[2]);
			$operator = $first ? '' : 'OR';
			
			//Check if value is a date
			if(strtotime($val) && strlen($val)==15)
			{
				$val = strtotime($val);
			}
			else if($comparison=='BETWEEN')
			{
				$btw_array = explode(' AND ', $val);
				$start_date = strtotime($btw_array[0]);
				$end_date = strtotime($btw_array[1]);
				$val = $start_date.' AND '.$end_date;
			}
			
			//Insert into 'seg_cons' table
			$q = 'INSERT INTO seg_cons (seg_id, grouping, operator, field, comparison, val) VALUES ('.$sid.', '.$i.', "'.$operator.'", "'.$field.'", "'.$comparison.'", "'.$val.'")';
			$r = mysqli_query($mysqli, $q);
			if (!$r)
			{
				echo 'cannot-save-conditions';
			    exit;
			}
			
			$first = false;
		}
		
		$i++;
	}
	
	echo $sid;
?>