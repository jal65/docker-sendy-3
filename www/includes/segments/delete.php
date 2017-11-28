<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php	
	$sid = is_numeric($_POST['sid']) ? $_POST['sid'] : exit;
	
	$q = 'DELETE FROM seg WHERE id = '.$sid;
	$q2 = 'DELETE FROM seg_cons WHERE seg_id = '.$sid;
    $q3 = 'DELETE FROM subscribers_seg WHERE seg_id = '.$sid;
	$r = mysqli_query($mysqli, $q);
	$r2 = mysqli_query($mysqli, $q2);
	$r3 = mysqli_query($mysqli, $q3);
	if ($r && $r2 && $r3)
		echo 'deleted';  
	else
		echo 'failed';
?>