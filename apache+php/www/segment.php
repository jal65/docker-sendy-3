<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>

<?php 
	//IDs
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;
	$sid = isset($_GET['s']) && is_numeric($_GET['s']) ? mysqli_real_escape_string($mysqli, $_GET['s']) : '';
	$new = $sid == '' ? true : false;
	
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
	
	date_default_timezone_set(get_app_info('timezone'));
?>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/pickaday/pikaday.js"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/pickaday/pikaday.jquery.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/js/pickaday/pikaday.css" />
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$("#seg-form").validate({
			rules: {
				seg_name: {
					required: true	
				}
			},
			messages: {
				seg_name: "<?php echo addslashes(_('Please name your segment'));?>"
			}
		});
	});
</script>

<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10" id="holder">
    	<div class="row-fluid">
	    	<div class="span12">
		    	<div>
			    	<p class="lead"><?php echo get_app_data('app_name');?></p>
		    	</div>
		    	<h2><?php echo _('Segments');?></h2>
				<br/>
		    	<p class="well"><?php echo _('List');?>: <a href="<?php echo get_app_info('path');?>/subscribers?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" title=""><span class="label label-info"><?php echo get_lists_data('name', $lid);?></span></a> | <a href="<?php echo get_app_info('path');?>/segments-list?i=<?php echo $_GET['i']?>&l=<?php echo $lid?>" title=""><?php echo _('Back to segments');?></a>
		    	</p><br/>
	    	</div>
	    </div>  
	    
	    <h3><?php echo $new ? _('New segment') : get_seg_data('name', $sid);?></h3>
	    <br/>
	    
	    <form action="<?php echo get_app_info('path')?>/includes/segments/save.php" method="POST" accept-charset="utf-8" class="form-vertical" id="seg-form">
		    
		    <div class="rounded-dashed-box">
		    
			    <!-- Group -->
			    <div id="group" class="row-fluid group" style="display:none;">
				    <div class="span12 well">	
					    
					    <!-- OR group -->		
						<div class="or-group" style="display:none;">
							<div class="dropdown condition-dropdown">
								<button class="btn dropdown-toggle" type="button" data-toggle="dropdown"><span class="dropdown-label first-dropdown" data-selected="" data-fieldtype=""><i class="icon icon-filter"></i> <?php echo _('Create a condition based on');?>..</span>
								<span class="caret"></span></button>
								<ul class="dropdown-menu">
									<li class="dropdown-header">Default fields</li>
									<li class="divider"></li>
									<li><a href="javascript:void(0)" class="date-fields" data-field="join_date"><i class="icon icon-expand-alt"></i> <?php echo _('Signed up');?></a></li>
									<li><a href="javascript:void(0)" class="date-fields" data-field="timestamp"><i class="icon icon-time"></i> <?php echo _('Last activity');?></a></li>
									<li><a href="javascript:void(0)" class="text-fields" data-field="name"><i class="icon icon-user"></i> <?php echo _('Name');?></a></li>
									<li><a href="javascript:void(0)" class="text-fields" data-field="email"><i class="icon icon-envelope-alt"></i> <?php echo _('Email address');?></a></li>
									<br/>
									<?php 
										//Load custom fields if they exists
										$custom_fields = get_lists_data('custom_fields', $lid);
										if($custom_fields != '')
										{
											echo '<li class="dropdown-header">Custom fields</li><li class="divider"></li>';
											
											$cf_array = explode('%s%', $custom_fields);
											
											foreach ($cf_array as $cf)
											{
												$cf_array2 = explode(':', $cf);
												$cf = $cf_array2[0];
												$cf_type = $cf_array2[1];
											    $cf_id = str_replace(' ', '', $cf);
											    $date_or_text = $cf_type == 'Date' ? 'date' : 'text';
												echo '<li><a href="javascript:void(0)" class="'.$date_or_text.'-fields" data-field="'.$cf_id.'">'.$cf.'</a></li>';
											}
										}
									?>
									
								</ul>
							</div>
							
							<div class="date-comparisons" style="display:none;">
								<div class="dropdown date-condition-dropdown">
									<button class="btn dropdown-toggle" type="button" data-toggle="dropdown"><span class="dropdown-label" data-selected="="><?php echo _('on');?></span>
									<span class="caret"></span></button>
									<ul class="dropdown-menu">
										<li><a href="javascript:void(0)" data-field="="><?php echo _('on');?></a></li>
										<li><a href="javascript:void(0)" data-field="<"><?php echo _('before');?></a></li>
										<li><a href="javascript:void(0)" data-field=">"><?php echo _('after');?></a></li>
										<li><a href="javascript:void(0)" data-field="<="><?php echo _('on or before');?></a></li>
										<li><a href="javascript:void(0)" data-field=">="><?php echo _('on or after');?></a></li>
										<li><a href="javascript:void(0)" data-field="BETWEEN"><?php echo _('between');?></a></li>
									</ul>
								</div>
								<div class="input-prepend date date1">
									<label for="datepicker" class="datepicker-label"><span class="add-on"><i class="icon-calendar cal1"></i></span></label>
						             <input type="text" name="datepicker" class="datepicker1" readonly>
					            </div>
					            <div class="input-prepend date date2" style="display:none;">
						            <span class="datepicker-and">and</span>
									<label for="datepicker" class="datepicker-label"><span class="add-on"><i class="icon-calendar cal2"></i></span></label>
						             <input type="text" name="datepicker" class="datepicker2" readonly>
					            </div>
					        </div>
					        
					        <div class="text-comparisons" style="display:none;">
								<div class="dropdown text-condition-dropdown">
									<button class="btn dropdown-toggle" type="button" data-toggle="dropdown"><span class="dropdown-label" data-selected="="><?php echo _('is');?></span>
									<span class="caret"></span></button>
									<ul class="dropdown-menu">
										<li><a href="javascript:void(0)" data-field="="><?php echo _('is');?></a></li>
										<li><a href="javascript:void(0)" data-field="!="><?php echo _('is not');?></a></li>
										<li><a href="javascript:void(0)" data-field="LIKE"><?php echo _('contains');?></a></li>
										<li><a href="javascript:void(0)" data-field="NOT LIKE"><?php echo _('does not contain');?></a></li>
										<li><a href="javascript:void(0)" data-field="STARTS WITH"><?php echo _('starts with');?></a></li>
										<li><a href="javascript:void(0)" data-field="ENDS WITH"><?php echo _('ends with');?></a></li>
										<li><a href="javascript:void(0)" data-field="DOES NOT START WITH"><?php echo _('does not start with');?></a></li>
										<li><a href="javascript:void(0)" data-field="DOES NOT END WITH"><?php echo _('does not end with');?></a></li>
									</ul>
								</div>
								<input type="text" class="input-xlarge text-value" name="text-value">
					        </div>
					        
					        <a href="javascript:void(0)" class="delete-btn"><i class="icon icon-trash"></i></a>
						    <a href="javascript:void(0)" class="btn btn-inverse or-btn"><i class="icon-plus icon-white"></i> <?php echo _('OR');?></a>
						</div>
						<!-- /OR group -->
						
						<div class="before"></div>
				        
					</div>
			    </div>
			    <!-- /Group -->
		    
		    <a href="javascript:void(0)" class="btn btn-inverse" id="and-btn"><i class="icon-plus icon-white"></i> <?php echo _('AND');?></a>
		    
		    <br/><br/>
		    
		    </div>
		    
		    <div class="row-fluid" style="margin: 20px 0 0 0;">
			    <div class="span10">	
				    <label class="control-label" for="seg_name"><?php echo $new ? _('Name your segment') : _('Edit segment name');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="seg_name" name="seg_name" placeholder="<?php echo _('Name of this segment');?>" value="<?php echo $new ? '' : get_seg_data('name', $sid);?>">
			            </div>
			        </div>
			    </div>
		    </div>
		    
		    <input type="hidden" id="app_path" name="app_path" value="<?php echo get_app_info('path');?>" />
		    <input type="hidden" id="app" name="app" value="<?php echo $_GET['i'];?>" />
		    <input type="hidden" id="lid" name="lid" value="<?php echo $lid;?>" />
		    
		    <button type="submit" class="btn btn-inverse btn-large" id="save-btn" style="float:left; margin-bottom: 50px;"><i class="icon-ok icon-white"></i> <?php echo _('Save this segment');?></button>
		    
	    </form>
	    
	    <script type="text/javascript">
		    $(document).ready(function() {
			    
			    var group_no = 0;
				
				<?php 
					//Code when editing a segment
				    if(!$new)
				    {
					    $default_fields = array('join_date', 'timestamp', 'name', 'email');								
					    $icons = array($default_fields[0]=>'expand-alt', $default_fields[1]=>'time', $default_fields[2]=>'user', $default_fields[3]=>'envelope-alt');
					    $field_text_array = array($default_fields[0]=>_('Signed up'), $default_fields[1]=>_('Last activity'), $default_fields[2]=>_('Name'), $default_fields[3]=>_('Email address'));
					    $field_type_array = array($default_fields[0]=>'date-fields', $default_fields[1]=>'date-fields', $default_fields[2]=>'text-fields', $default_fields[3]=>'text-fields');						
					    
						$date_comparisons = array('=', '<', '>', '<=', '>=', 'BETWEEN');
						$date_comparisons_text = array($date_comparisons[0]=>_('on'), $date_comparisons[1]=>_('before'), $date_comparisons[2]=>_('after'), $date_comparisons[3]=>_('on or before'), $date_comparisons[4]=>_('on or after'), $date_comparisons[5]=>_('between'));
						
						$text_comparisons = array('=', '!=', 'LIKE', 'NOT LIKE', 'STARTS WITH', 'ENDS WITH', 'DOES NOT START WITH', 'DOES NOT END WITH');
						$text_comparisons_text = array($text_comparisons[0]=>_('is'), $text_comparisons[1]=>_('is not'), $text_comparisons[2]=>_('contains'), $text_comparisons[3]=>_('does not contain'), $text_comparisons[4]=>_('starts with'), $text_comparisons[5]=>_('ends with'), $text_comparisons[6]=>_('does not start with'), $text_comparisons[7]=>_('does not end with'));
					    
					    $q = 'SELECT id, grouping, operator, field, comparison, val FROM seg_cons WHERE seg_id = '.$sid;
					    $r = mysqli_query($mysqli, $q);
					    if ($r && mysqli_num_rows($r) > 0)
					    {				  
						    $prev_group = 0;
					        while($row = mysqli_fetch_array($r))
					        {
						        $con_id = $row['id'];
					    		$grouping = $row['grouping'];
					    		$operator = $row['operator'];
					    		$field = $row['field'];
					    		$comparison = $row['comparison'];
					    		$val = $row['val'];
					    		$field_text = $field_text_array[$field] == '' ? $field : $field_text_array[$field];
					    		$field_type = $field_type_array[$field];
					    		
					    		//Check if field is a custom field
					    		if(!in_array($field, $default_fields))
					    		{
						    		//Check if custom field is a 'text' or 'date' type
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
										    $type = $cf_array[1];
										    
										    if($key==$field)
										    {
											    if($type=='Date')
											    	$field_type = 'date-fields';
											    else if($type=='Text')
											    	$field_type = 'text-fields';
											    break;
										    }
									    }
									}
					    		}
					    		
					    		if($grouping != $prev_group)
					    		{
	    		?>
						    		
						    		$prev_group = $("#group<?php echo $grouping-1;?>");
						    		no_of_or_btns_to_remove = $prev_group.find('.or-group').length - 1;
						    		r = 0;
						    		$prev_group.find('.or-group').each(function(){
							    		if(r<no_of_or_btns_to_remove) $(this).find(".or-btn").remove();
							    		r++;
						    		});
						    		
						    		//GROUP
						    		group_no++;
									$new = $("#group").clone().insertBefore("#and-btn");
									$new.attr("id", "group"+group_no);
									$new.attr("data-group", group_no);
									$new.find(".or-group").show();
									$new.fadeIn();
									$new.find(".condition-dropdown .dropdown-menu li a").html($(this).html());
									$new.find(".condition-dropdown .dropdown-menu li a").attr("data-selected", $(this).data("field"));
									$new.find(".condition-dropdown .dropdown-menu li a").attr("data-fieldtype", $(this).attr("class"));
									
									//OR
									$new_or = $new.find(".or-group");
									$new_or.find(".first-dropdown").html("<i class=\"icon icon-<?php echo $icons[$field];?>\"></i> <?php echo $field_text;?>");
									$new_or.find(".condition-dropdown button span.dropdown-label").attr("data-selected", "<?php echo $field;?>");
									$new_or.find(".condition-dropdown button span.dropdown-label").attr("data-fieldtype", "<?php echo  $field_type;?>");
									$new_or.find(".date-comparisons").hide();
									$new_or.find(".text-comparisons").hide();
									$new_or.find(".or-btn").click(cloneORCondition);
									<?php if($field_type=='date-fields'):?>
										$new_or.find(".date-comparisons").show();
										$new_or.find(".date-condition-dropdown .dropdown-label").text("<?php echo $date_comparisons_text[$comparison];?>");
										$new_or.find(".date-condition-dropdown button span.dropdown-label").attr("data-selected", "<?php echo $comparison;?>");
										<?php 
											if($comparison=='BETWEEN'):
												$c_a = explode(' AND ', $val);
												$year1 = date('Y', $c_a[0]);
												$year2 = date('Y', $c_a[1]);
												$start_date = strftime("%a %b %d $year1", $c_a[0]);
												$end_date = strftime("%a %b %d $year2", $c_a[1]);
										?>
											$new_or.find(".date1 .datepicker1").val("<?php echo $start_date;?>");
											$new_or.find(".date2 .datepicker2").val("<?php echo $end_date;?>");
											$new_or.find(".date2").show();
										<?php else:?>
											<?php $year = date('Y', $val);?>
											$new_or.find(".date1 .datepicker1").val("<?php echo strftime("%a %b %d $year", $val);?>");
										<?php endif;?>
										$new_or.find(".date1 .cal1, .date1 .datepicker1").css("cursor", "pointer");
										$new_or.find(".date1 .cal1").click(function(){$new_or.find(".date1 .datepicker1").click();});
									 	$new_or.find(".date2 .cal2, .date2 .datepicker2").css("cursor", "pointer");
										$new_or.find(".date2 .cal2").click(function(){$new_or.find(".date2 .datepicker2").click();});
									<?php elseif($field_type='text-fields'):?>
										$new_or.find(".text-comparisons").show();
										<?php 
											if($comparison=='LIKE')
											{
												if(substr($val, 0, 1)=='%' && substr($val, -1, 1)=='%')
													$comparison = 'LIKE';
												else if(substr($val, 0, 1)=='%' && substr($val, -1, 1)!='%')
													$comparison = 'ENDS WITH';
												else if(substr($val, 0, 1)!='%' && substr($val, -1, 1)=='%')
													$comparison = 'STARTS WITH';
											}
											else if($comparison=='NOT LIKE')
											{
												if(substr($val, 0, 1)=='%' && substr($val, -1, 1)=='%')
													$comparison = 'NOT LIKE';
												else if(substr($val, 0, 1)=='%' && substr($val, -1, 1)!='%')
													$comparison = 'DOES NOT END WITH';
												else if(substr($val, 0, 1)!='%' && substr($val, -1, 1)=='%')
													$comparison = 'DOES NOT START WITH';
											}
												
										?>
										$new_or.find(".text-condition-dropdown button span.dropdown-label").text("<?php echo $text_comparisons_text[$comparison]?>");
										$new_or.find(".text-condition-dropdown button span.dropdown-label").attr("data-selected", "<?php echo $comparison;?>");
										$new_or.find(".text-comparisons .text-value").val("<?php $val = ltrim($val, '%'); $val = rtrim($val, '%'); echo $val;?>");
									<?php endif;?>
						    		
	    		<?php
					    		}
					    		else
					    		{
	    		?>
	    							//OR
	    							$new_or = $new.find(".or-group").last().clone().insertBefore($new.find(".before"));
									$new_or.find(".first-dropdown").html("<i class=\"icon icon-<?php echo $icons[$field];?>\"></i> <?php echo $field_text;?>");
									$new_or.find("button span.dropdown-label").attr("data-selected", "<?php echo $field;?>");
									$new_or.find("button span.dropdown-label").attr("data-fieldtype", "<?php echo $field_type;?>");
									$new_or.find(".date-comparisons").hide();
									$new_or.find(".text-comparisons").hide();
									$new_or.find(".or-btn").click(cloneORCondition);
									<?php if($field_type=='date-fields'):?>
										$new_or.find(".date-comparisons").show();
										$new_or.find(".date-condition-dropdown .dropdown-label").text("<?php echo $date_comparisons_text[$comparison];?>");
										$new_or.find(".date-condition-dropdown button span.dropdown-label").attr("data-selected", "<?php echo $comparison;?>");
										<?php 
											if($comparison=='BETWEEN'):
												$c_a = explode(' AND ', $val);
												$year1 = date('Y', $c_a[0]);
												$year2 = date('Y', $c_a[1]);
												$start_date = strftime("%a %b %d $year1", $c_a[0]);
												$end_date = strftime("%a %b %d $year2", $c_a[1]);
										?>
											$new_or.find(".date1 .datepicker1").val("<?php echo $start_date;?>");
											$new_or.find(".date2 .datepicker2").val("<?php echo $end_date;?>");
											$new_or.find(".date2").show();
										<?php else:?>
											<?php $year = date('Y', $val);?>
											$new_or.find(".date1 .datepicker1").val("<?php echo strftime("%a %b %d $year", $val);?>");
										<?php endif;?>
										$new_or.find(".date1 .cal1, .date1 .datepicker1").css("cursor", "pointer");
										$new_or.find(".date1 .cal1").click(function(){$new_or.find(".date1 .datepicker1").click();});
									 	$new_or.find(".date2 .cal2, .date2 .datepicker2").css("cursor", "pointer");
										$new_or.find(".date2 .cal2").click(function(){$new_or.find(".date2 .datepicker2").click();});
									<?php elseif($field_type='text-fields'):?>
										$new_or.find(".text-comparisons").show();
										<?php 
											if($comparison=='LIKE')
											{
												if(substr($val, 0, 1)=='%' && substr($val, -1, 1)=='%')
													$comparison = 'LIKE';
												else if(substr($val, 0, 1)=='%' && substr($val, -1, 1)!='%')
													$comparison = 'ENDS WITH';
												else if(substr($val, 0, 1)!='%' && substr($val, -1, 1)=='%')
													$comparison = 'STARTS WITH';
											}
											else if($comparison=='NOT LIKE')
											{
												if(substr($val, 0, 1)=='%' && substr($val, -1, 1)=='%')
													$comparison = 'NOT LIKE';
												else if(substr($val, 0, 1)=='%' && substr($val, -1, 1)!='%')
													$comparison = 'DOES NOT END WITH';
												else if(substr($val, 0, 1)!='%' && substr($val, -1, 1)=='%')
													$comparison = 'DOES NOT START WITH';
											}
												
										?>
										$new_or.find(".text-condition-dropdown button span.dropdown-label").text("<?php echo $text_comparisons_text[$comparison]?>");
										$new_or.find(".text-condition-dropdown button span.dropdown-label").attr("data-selected", "<?php echo $comparison;?>");
										$new_or.find(".text-comparisons .text-value").val("<?php $val = ltrim($val, '%'); $val = rtrim($val, '%'); echo $val;?>");
									<?php endif;?>
									$new_or.show();
									$new_or.fadeIn();
									
									
	    		<?php
					    		}
	    		?>
	    						clickEvents();
	    		<?php
					    		$prev_group = $grouping;
					    	}
		    	?>
		    					$last_group = $(".group").last();
					    		no_of_or_btns_to_remove = $last_group.find('.or-group').length - 1;
					    		r = 0;
					    		$last_group.find('.or-group').each(function(){
						    		if(r<no_of_or_btns_to_remove) $(this).find(".or-btn").remove();
						    		r++;
					    		});
		    	<?php
					        
					    }
					}
					else echo 'cloneANDCondition();';
			    ?>
				
				function cloneANDCondition()
				{
					group_no++;
					$new = $("#group").clone().insertBefore("#and-btn");
					$new.attr("id", "group"+group_no);
					$new.attr("data-group", group_no);
					$new.find(".or-btn").click(cloneORCondition);
					$new.find(".or-group").show();
					$new.fadeIn();
					clickEvents();
				}
				
				function cloneORCondition(e)
				{
					e.preventDefault();
					$new_or = $(this).parent().clone().insertBefore($(this).parent().parent().find(".before"));
					$new_or.find(".first-dropdown").html("<i class=\"icon icon-filter\"></i> <?php echo _('Create a condition based on');?>..");
					$new_or.find(".date-comparisons").hide();
					$new_or.find(".text-comparisons").hide();
					$new_or.find("button span.dropdown-label").attr("data-selected", "");
					$new_or.find(".text-comparisons button span.dropdown-label, .date-comparisons button span.dropdown-label").attr("data-selected", "=");
					$new_or.find(".text-comparisons button span.dropdown-label").text("is");
					$new_or.find(".date-comparisons button span.dropdown-label").text("on");
					$new_or.find(".text-comparisons input, .date-comparisons input").val("");
					$new_or.find(".text-comparisons input, .date-comparisons input").css("border-color", "#cccccc");
					$new_or.find(".condition-dropdown button").css("border-color", "#cccccc");
					$new_or.find(".or-btn").click(cloneORCondition);
					$new_or.hide();
					$new_or.fadeIn();
					$(this).remove();
					clickEvents();
				}
				
				function clickEvents()
				{
					$('.datepicker1').pikaday({ firstDay: 1 });
					$('.datepicker2').pikaday({ firstDay: 1 });
				
					//Condition drop down 'click'
					$(".condition-dropdown .dropdown-menu li a").click(function(e){
						e.preventDefault();
						$(this).each(function(){
							$(this).parent().parent().parent().find("button span.dropdown-label").html($(this).html());
							$(this).parent().parent().parent().find("button span.dropdown-label").attr("data-selected", $(this).data("field"));
							$(this).parent().parent().parent().find("button span.dropdown-label").attr("data-fieldtype", $(this).attr("class"));
							$(this).parent().parent().parent().find("button").css("border-color", "#cccccc");
							if($(this).hasClass("date-fields"))
							{
								//show date-comparisons drop down
								$(this).parent().parent().parent().parent().find(".date-comparisons").show();
								$(this).parent().parent().parent().parent().find(".date-comparisons .date1 .datepicker1").click();
								
								//hide the rest
								$(this).parent().parent().parent().parent().find(".text-comparisons").hide();
							}
							else
							{
								//show text comparisons drop down
								$(this).parent().parent().parent().parent().find(".text-comparisons").show();
								$(this).parent().parent().parent().parent().find(".text-comparisons .text-value").focus();
								
								//hide the rest
								$(this).parent().parent().parent().parent().find(".date-comparisons").hide();
							}
							//Calendar click
							$(this).parent().parent().parent().parent().find(".date1 .cal1, .date1 .datepicker1").css("cursor", "pointer");
							$(this).parent().parent().parent().parent().find(".date1 .cal1").click(function(){
						     	$(this).parent().parent().parent().parent().find(".date1 .datepicker1").click();
						 	});
						 	$(this).parent().parent().parent().parent().find(".date2 .cal2, .date2 .datepicker2").css("cursor", "pointer");
							$(this).parent().parent().parent().parent().find(".date2 .cal2").click(function(){
						     	$(this).parent().parent().parent().parent().find(".date2 .datepicker2").click();
						 	});
						});
					});
					
					//signed up drop down 'click'
					$(".date-comparisons .dropdown-menu li a").click(function(e){
						e.preventDefault();
						$(this).each(function(){
							$(this).parent().parent().parent().find("button span.dropdown-label").text($(this).text());
							$(this).parent().parent().parent().find("button span.dropdown-label").attr("data-selected", $(this).data("field"));
							if($(this).text()=='between')
							{
								$(this).parent().parent().parent().parent().find(".date2").show();
								$(this).parent().parent().parent().parent().find(".date1 .datepicker1").click();
							}
							else 
							{
								$(this).parent().parent().parent().parent().find(".date2").hide();
								$(this).parent().parent().parent().parent().find(".date1 .datepicker1").click();
							}
							//Calendar click
							$(this).parent().parent().parent().parent().find(".date1 .cal1, .date1 .datepicker1").css("cursor", "pointer");
							$(this).parent().parent().parent().parent().find(".date1 .cal1").click(function(){
						     	$(this).parent().parent().parent().parent().find(".date1 .datepicker1").click();
						 	});
						 	$(this).parent().parent().parent().parent().find(".date2 .cal2, .date2 .datepicker2").css("cursor", "pointer");
							$(this).parent().parent().parent().parent().find(".date2 .cal2").click(function(){
						     	$(this).parent().parent().parent().parent().find(".date2 .datepicker2").click();
						 	});
						});
					});
				
					//signed up drop down 'click'
					$(".text-comparisons .dropdown-menu li a").click(function(e){
						e.preventDefault();
						$(this).each(function(){
							$(this).parent().parent().parent().find("button span.dropdown-label").text($(this).text());
							$(this).parent().parent().parent().find("button span.dropdown-label").attr("data-selected", $(this).data("field"));
							$(this).parent().parent().parent().parent().parent().parent().find(".text-value").focus();
						});
					});
					
					//delete button 'click'
					$(".delete-btn").click(function(e){
						e.preventDefault();
						//if the deleted condition has OR btn, clone a new one in the last condition in the group
						if($(this).parent().find(".or-btn").length)
						{
							//clone a new OR btn in the last condition of this group
							$new_or_btn = $(this).parent().find(".or-btn").clone().insertAfter($(this).parent().parent().find(".or-group").last().prev().find(".delete-btn"));
							$new_or_btn.click(cloneORCondition);
						}
						
						//remove the whole group if the last OR condition is removed
						no_of_or_conditions = $(this).parent().parent().find(".or-group").length;
						if(no_of_or_conditions==1)
						{
							$(this).parent().parent().parent().remove(); //remove the whole of this group
						}
						
						//add a new group when all groups have been removed
						no_of_groups_left = $("#holder .group").length;
						if(no_of_groups_left==1)
						{
							cloneANDCondition();
						}
						
						//last but not least, remove the OR condition
						$(this).parent().remove(); 
					});
				}
				
				//AND button 'click'
				$("#and-btn").click(function(e){
					e.preventDefault();
					cloneANDCondition();
				});
	
		    	//Save button 'submit'
				$("#seg-form").submit(function(e){
					e.preventDefault(); 
					
					group_array = [];
					var $form = $(this);
					seg_name = $("#seg_name").val();
					app_path = $("#app_path").val();
					app = $("#app").val();
					lid = $("#lid").val();
					edit = "<?php echo $new ? 0 : 1;?>";
					sid = "<?php echo $sid;?>";
					url = $form.attr('action');
					
					//If segment name is filled in, allow form to be submitted
					if(seg_name!="")
					{
						//Check if no conditions are created
						$(".group").each(function(){
							if($(this).attr("id")!='group')
							{
								$(this).find(".or-group").each(function(){
									$con_dropdown = $(this).find(".condition-dropdown button .dropdown-label");
									$text_comparisons_input = $(this).find(".text-comparisons input");
									$date1 = $(this).find('.date-comparisons .date1 input');
									$date2 = $(this).find('.date-comparisons .date2 input');
									if($text_comparisons_input.val()=="" && $text_comparisons_input.parent().is(":visible"))
									{
										$text_comparisons_input.css("border-color", "red");
										form_validates = false;
										return false;
									}
									else if($date1.val()=="" && $date1.parent().is(":visible") && $(this).find(".date-comparisons").is(":visible"))
									{
										$date1.css("border-color", "red");
										form_validates = false;
										return false;
									}
									else if($date2.val()=="" && $date2.parent().is(":visible") && $(this).find(".date-comparisons").is(":visible"))
									{
										$date2.css("border-color", "red");
										form_validates = false;
										return false;
									}
									else if($con_dropdown.attr("data-selected")=="")
									{
										$con_dropdown.parent().css("border-color", "red");
										form_validates = false;
										return false;
									}
									else
									{
										form_validates = true;
										$text_comparisons_input.css("border-color", "#cccccc");
										$date1.css("border-color", "#cccccc");
										$date2.css("border-color", "#cccccc");
										$con_dropdown.parent().css("border-color", "#cccccc");
									}
								});
								if(!form_validates) return false;
							}
						});
						
						if(form_validates)
						{
							//in each AND group
							$(".group").each(function(){
								if($(this).is(":visible"))
								{
									or_array = [];
									//in each OR group
									$(this).find(".or-group").each(function(){
										if($(this).is(":visible"))
										{
											field = $(this).find(".condition-dropdown .dropdown-label").data("selected");
											field_type = $(this).find(".condition-dropdown .dropdown-label").data("fieldtype");
											if(field_type=="date-fields")
											{
												comparison = $(this).find(".date-condition-dropdown .dropdown-label").data("selected");
												if(comparison=="BETWEEN")
													val = $(this).find(".datepicker1").val()+" AND "+$(this).find(".datepicker2").val();
												else
													val = $(this).find(".datepicker1").val();
											}
											else
											{
												comparison = $(this).find(".text-condition-dropdown .dropdown-label").data("selected");
												if(comparison=="LIKE" || comparison=="NOT LIKE")
													val = "%" + $(this).find(".text-comparisons .text-value").val() + "%";
												else if(comparison=="STARTS WITH")
												{
													comparison = "LIKE";
													val = $(this).find(".text-comparisons .text-value").val() + "%";
												}
												else if(comparison=="ENDS WITH")
												{
													comparison = "LIKE";
													val = "%" + $(this).find(".text-comparisons .text-value").val();
												}
												else if(comparison=="DOES NOT START WITH")
												{
													comparison = "NOT LIKE";
													val = $(this).find(".text-comparisons .text-value").val() + "%";
												}
												else if(comparison=="DOES NOT END WITH")
												{
													comparison = "NOT LIKE";
													val = "%" + $(this).find(".text-comparisons .text-value").val();
												}
												else
													val = $(this).find(".text-comparisons .text-value").val();
											}
												
											condition = [field, comparison, val];
											or_array.push(condition);
										}
									});
									
									group_array.push(or_array);
								}
							});
							
							//Convert array of all the conditions into a string and pass it to save.php
							group_json = JSON.stringify(group_array);
							
				    		$.post(url, { 
					    		seg_name: seg_name
					    	   ,lid: lid
					    	   ,app: app
					    	   ,group_json: group_json
					    	   ,edit: edit
					    	   ,sid: sid
					    	},
				    		  function(data) {
				    		      if(data)
				    		      {
					    		      if(data=="cannot-insert-name-into-segment")
						    		      alert("<?php echo _('Unable to save the segment name.');?>");
						    		  else if(data=="cannot-save-conditions")
						    		  	  alert("<?php echo _('Unable to save segment conditions.');?>");
					    		      else
					    		      {
										  //redirect to segement-edit page
										  window.location = app_path+"/includes/segments/segmentate.php?i="+app+"&l="+lid+"&s="+data+"&t="+"<?php echo get_app_info('timezone');?>"+"&r=conditions";
									  }
				    		      }
				    		      else
				    		      {
				    		      	  alert("<?php echo _('Sorry, unable to save your segment.');?>");
				    		      }
				    		  }
				    		);
				    	}
			    	}
				});
		    });
	    </script>
	    
    </div>
    
    <?php if(!$new):?>
    <div class="row-fluid">
		<div class="span2"></div> 
	    <div class="span10">    
		    <?php $total_in_seg = get_totals_in_seg($sid);?>
		    <hr style="margin-top:-10px;"/>
		    <p>
			    <span class="label label<?php echo $total_in_seg==0 ? '' : '-success';?>" style="font-size: 20px;"><?php echo $total_in_seg;?></span> <?php echo _('subscribers found for this segment');?> 
		    	<span class="seg-note"> (<?php echo $total_in_seg!=0 ? _('showing a preview of up to 10 records below') : _('your conditions does not match any subscribers');?>)</span>
		    	<a href="<?php echo get_app_info('path');?>/includes/segments/export-csv.php?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>&s=<?php echo $sid;?>" class="export-seg-csv" title="<?php echo _('Export CSV of this segment');?>"><i class="icon icon-download-alt"></i> <?php echo _('Export');?></a>
		    </p>
		    <hr/>
		    
		    <?php if($total_in_seg!=0):?>
			    <table class="table table-striped table-condensed">
			    <thead>
			      <tr>
			        <th><?php echo _('Name');?></th>
			        <th><?php echo _('Email');?></th>
			        <th><?php echo _('Last activity');?></th>
			      </tr>
			    </thead>
			    <tbody>
				    
				  <?php 
					  $q = 'SELECT subscribers.id, subscribers.name, subscribers.email, subscribers.timestamp FROM subscribers LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id) WHERE subscribers_seg.seg_id = '.$sid.' ORDER BY timestamp DESC LIMIT 10';
					  $r = mysqli_query($mysqli, $q);
					  if ($r && mysqli_num_rows($r) > 0)
					  {
					      while($row = mysqli_fetch_array($r))
					      {
					  		$subscriber_id = $row['id'];
					  		$subscriber_name = $row['name'];
					  		$subscriber_email = $row['email'];
					  		$subscriber_timestamp = parse_date($row['timestamp'], 'long', true); 
		  		?>
		  					<tr>
						       <td><a href="#subscriber-info" data-id="<?php echo $subscriber_id;?>" data-toggle="modal" class="subscriber-info"><?php echo $subscriber_name=='' ? '[No name]' : $subscriber_name;?></a></td>
						       <td><a href="#subscriber-info" data-id="<?php echo $subscriber_id;?>" data-toggle="modal" class="subscriber-info"><?php echo $subscriber_email;?></a></td>
						       <td><?php echo $subscriber_timestamp;?></td>
						     </tr>
		  		<?php
					      }  
					  }
				  ?>
				  
				<!-- Subscriber info card -->
				<div id="subscriber-info" class="modal hide fade">
				    <div class="modal-header">
				      <button type="button" class="close" data-dismiss="modal">&times;</button>
				      <h3><?php echo _('Subscriber info');?></h3>
				    </div>
				    <div class="modal-body">
					    <p id="subscriber-text"></p>
				    </div>
				    <div class="modal-footer">
				      <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign" style="margin-top: 5px;"></i> <?php echo _('Close');?></a>
				    </div>
				  </div>
				<script type="text/javascript">
					$(".subscriber-info").click(function(){
						s_id = $(this).data("id");
						$("#subscriber-text").html("<?php echo _('Fetching');?>..");
						
						$.post("<?php echo get_app_info('path');?>/includes/subscribers/subscriber-info.php", { id: s_id, app:<?php echo get_app_info('app');?> },
						  function(data) {
						      if(data)
						      {
						      	$("#subscriber-text").html(data);
						      }
						      else
						      {
						      	$("#subscriber-text").html("<?php echo _('Oops, there was an error getting the subscriber\'s info. Please try again later.');?>");
						      }
						  }
						);
					});
				</script>
				<!-- Subscriber info card -->
			      
			    </tbody>
			  </table>
		  <hr/>
		  
		  <span class="last-update"><?php echo _('Last update');?>: <?php echo parse_date(get_seg_data('last_updated', $sid), 'short');?></span> 
		  <a href="<?php echo get_app_info('path')?>/includes/segments/segmentate.php?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>&s=<?php echo $sid;?>&t=<?php echo get_app_info('timezone')?>&r=conditions" style="margin-left: 5px;" title="<?php echo _('Update segmentation results?');?>">
			  <span class="icon icon-refresh"></span>
		  </a>
		  
		  <?php 
			  //check if cron is set up
		    	$q = 'SELECT cron_seg FROM login WHERE id = '.get_app_info('main_userID');
		    	$r = mysqli_query($mysqli, $q);
		    	if ($r) while($row = mysqli_fetch_array($r)) $cron = $row['cron_seg'];
		    	
		    	//get server path
		    	$server_path_array = explode('segment.php', $_SERVER['SCRIPT_FILENAME']);
			    $server_path = $server_path_array[0];
		    	
			if(!$cron && !get_app_info('is_sub_user')): ?>
	        <p class="alert alert-info" style="margin-top: 20px; "><strong><?php echo _('Note');?>:<br/></strong> <?php echo _('When subscribers are added or removed from the list, the segmentation results will not update automatically unless you click the refresh button '); echo '<span class="icon icon-refresh"></span>.<br/>'; echo _('To have your segmentation results update automatically,')?> <a href="#cron-instructions" data-toggle="modal" style="text-decoration:underline;"><?php echo _('setup a cron job');?></a>.</p>
	        
	         <div id="cron-instructions" class="modal hide fade">
	            <div class="modal-header">
	              <button type="button" class="close" data-dismiss="modal">&times;</button>
	              <h3><i class="icon icon-time" style="margin-top: 5px;"></i> <?php echo _('Add a cron job');?></h3>
	            </div>
	            <div class="modal-body">
	            <p><?php echo _('Add a cron job that runs every 15 minutes with the following command:');?></p>
	            <h3><?php echo _('Command');?></h3>
	            <pre id="command">php <?php echo $server_path;?>update-segments.php > /dev/null 2>&amp;1</pre>
	            <p><em><?php echo _('(Note that adding cron jobs vary from hosts to hosts, most offer a UI to add a cron job easily. Check your hosting control panel or consult your host if unsure.)');?></em>.</p>
	            <h3><?php echo _('Cron job');?></h3>
	            <pre id="cronjob">*/15 * * * * php <?php echo $server_path;?>update-segments.php > /dev/null 2>&amp;1</pre>
	            <p><?php echo _('Once added, wait for the cron job to start running in 15 minutes. If your cron job is functioning correctly, the blue informational message will disappear and your segmentation results will be updated automatically every 15 minutes by the cron job.');?></p>
	            </div>
	            <div class="modal-footer">
	              <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
	            </div>
	        </div>
	        <script type="text/javascript">
			$(document).ready(function() {
				$("#command, #cronjob").click(function(){
					$(this).selectText();
				});
			});
			</script>
	        <?php endif;?>
		  
		  <?php endif;?>
		  
	    </div>
	</div>
	<?php endif;?>
    
    <br/><br/>
    
</div>

<?php include('includes/footer.php');?>
