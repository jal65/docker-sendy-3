<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>

<?php 
	//IDs
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/segments-list?i='.get_app_info('restricted_to_app').'&l='.$lid.'"</script>';
			exit;
		}
	}
	
	if(isset($_GET['e'])) $err = $_GET['e'];
	else $err = '';
?>

<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>

<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div class="row-fluid">
	    	<div class="span12">
		    	<div>
			    	<p class="lead"><?php echo get_app_data('app_name');?></p>
		    	</div>
		    	<h2><?php echo _('Segments');?></h2>
				<br/>
		    	<p class="well"><?php echo _('List');?>: <a href="<?php echo get_app_info('path');?>/subscribers?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" title=""><span class="label label-info"><?php echo get_lists_data('name', $lid);?></span></a> | <a href="<?php echo get_app_info('path')?>/list?i=<?php echo get_app_info('app');?>" title=""><?php echo _('Back to lists');?></a>
		    	</p><br/>
	    	</div>
	    </div>
	    
	    <p><a href="<?php echo get_app_info('path');?>/segment?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" id="new-segment-btn" class="btn btn-inverse btn-large"><i class="icon-plus icon-white"></i> <?php echo _('Create a new segment');?></a></p>	    
	    
	    <br/><br/>
	    
	   <?php if(get_segments_count()==0):?> 
	    <div class="alert alert-info">
			<p><strong><?php echo _('What are segments?');?></strong></p>
			<p><?php echo _('Segments are subscribers filtered from a list based on certain conditions you define. Research shows sending emails to targeted segments not only improves open and click through rates, revenue can increase by more than 25%. Sendy enables you to create dynamic segments for any list. Target segments of subscribers based on conditions you define using default fields like \'name\' and \'email\' or using any of your custom fields. Create multiple conditions and group them using \'AND\' and \'OR\' for each segment. Once you\'ve created your segments, you can send targeted campaigns to these segments in addition to lists. You can even choose segments to \'exclude\' from your sends.');?></p>
		</div>
		<?php endif;?>
	    
	    <div class="row-fluid">
		    <div class="span12">
		    	<h3><?php echo _('Existing segments');?></h3><hr/>
				<table class="table table-striped responsive">
	              <thead>
	                <tr>
	                  <th><?php echo _('Segment');?></th>
	                  <th><?php echo _('Subscribers');?></th>
	                  <th><?php echo _('Update');?></th>
	                  <th><?php echo _('Delete');?></th>
	                </tr>
	              </thead>
	              <tbody>
	                	<?php 
		                	$q = 'SELECT id, name FROM seg WHERE list = '.$lid.' ORDER BY id DESC';
		                	$r = mysqli_query($mysqli, $q);
		                	if ($r && mysqli_num_rows($r) > 0)
		                	{
		                	    while($row = mysqli_fetch_array($r))
		                	    {
		                	    	$seg_id = $row['id'];
		                			$seg_name = $row['name'];
		                			$subscriber_count = get_totals_in_seg($seg_id);
		                			
		                			echo '
		                			<tr id="seg-'.$seg_id.'">
			                			<td><a href="'.get_app_info('path').'/segment?i='.get_app_info('app').'&l='.$lid.'&s='.$seg_id.'">'.$seg_name.'</a></td>
			                			<td>'.$subscriber_count.'</td>
			                			<td><a href="'.get_app_info('path').'/includes/segments/segmentate.php?i='.get_app_info('app').'&l='.$lid.'&s='.$seg_id.'&t='.get_app_info('timezone').'&r=list" title="'._('Update totals for').' '.$seg_name.'? '._('Last update').' '.parse_date(get_seg_data('last_updated', $seg_id), 'short').'" id="update-'.$seg_id.'" data-id="'.$seg_id.'"><i class="icon-refresh"></i></a></td>
			                			<td><a href="javascript:void(0)" title="'._('Delete').' '.$seg_name.'?" id="delete-'.$seg_id.'" data-id="'.$seg_id.'"><i class="icon-trash"></i></a></td>
			                			<script type="text/javascript">
						            	$("#delete-'.$seg_id.'").click(function(e){
						            		e.preventDefault(); 
											c = confirm("'._('All conditions in this segment will be permanently deleted.').' '._('Confirm delete').' \''.$seg_name.'\'?");
											if(c)
											{
								            	$.post("'.get_app_info('path').'/includes/segments/delete.php", { sid: $(this).data("id") },
							            		  function(data) {
							            		      if(data)
							            		      {
								            		      if(data=="failed") 
								            		      	alert("'._('Failed to delete segment').'");
														  else if(data=="deleted")
														  {
								            		      	  $("#seg-'.$seg_id.'").fadeOut(function(){
								            		      		  window.location = "'.get_app_info('path').'/segments-list?i='.get_app_info('app').'&l='.$lid.'";
								            		      	  });
								            		      }
							            		      }
							            		      else
							            		      {
							            		      	alert("'._('Sorry, unable to delete. Please try again later!').'");
							            		      }
							            		  }
							            		);
							            	}
						            	});
							            </script>
		                			</tr>
		                			';
		                	    }  
		                	}	
		                	else
		                	{
			                	echo '
			                	<tr>
			                		<td colspan="4">'._('No segments').'</td>
			                	</tr>
			                	';
		                	}
	                	?>                
	              </tbody>
	            </table>
			</div>
	    </div>
    </div>
</div>

<?php include('includes/footer.php');?>
