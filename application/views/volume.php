			<?php
				$colors = array('lime', 'green', 'emerald', 'teal', 'cyan', 'cobalt', 'indigo', 'violet', 'pink', 'magenta', 'crimson', 'red', 'orange', 'amber', 'yellow', 'brown', 'olive', 'steel', 'mauve', 'taupe', 'gray', 'dark', 'darker', 'darkBrown', 'darkCrimson', 'darkMagenta', 'darkIndigo', 'darkCyan', 'darkCobalt', 'darkTeal', 'darkEmerald', 'darkGreen', 'darkOrange', 'darkRed', 'darkPink', 'darkViolet', 'darkBlue', 'lightBlue', 'lightTeal', 'lightOlive', 'lightOrange', 'lightPink', 'lightRed', 'lightGreen');
			?>
			<div class="row">
                <div class="span12">
                    <h1 class="header">Volumes Management</h1>
                </div>
            </div>
            <div class="row">
                <div>
					<div  id="mainPanel" class="panel" data-role="panel">
						<div class="panel-header bg-lightBlue fg-white collapse">Create volume</div>
						<div   class="panel-content">
							<p>You may add volume that span across your cloud drives.</p>
							<p>Configuration with multiple drives will be able to support fault-tolerance.</p>
							<?php echo form_open('main/createVolume'); ?>
							<legend>Create New Volume</legend>
							<div class="input-control text" data-role="input-control">
                                <input type="text" id="name" placeholder="Drive Name" required>
                            </div>
                            <div class="panel">
	                            <div class="panel-content">
	                                <div class="input-control checkbox">
	                                	<label>Select drive(s)</label>
	                                	<?php
	                                		foreach ($cloudDrives as $clouddrive) {
	                                			$status = ($clouddrive['info']['status']?'':'disable');
	                                	?>
	                        <label>
	                        	<input <?php echo $status;?> 
	                        			type="checkbox" 
	                        			name="newCD" 
	                        			value="<?php echo $clouddrive['id'];?>" />
	                        	<span class="check"></span>
	                        	<span class="text" data-role="input-control">
	                                <input type="number" name="volume" id="vol_<?php echo $clouddrive['id'];?>" placeholder="size" maximum="<?php echo $clouddrive['info']['available'];?>">
	                            </span>
	                        	
                    			<?php echo $clouddrive['info']['name'];?>
                    			<span class="text-muted">
                        			( Provider: <?php echo $clouddrive['provider'] ;?>, Available: <?php echo $clouddrive['info']['available'];?> GB / <?php echo $clouddrive['info']['quota'];?> GB )
                        		</span>
	                        </label>
	                                	<?php
	                                		}
	                                	?>
									</div>
	                            </div>

	                            <div class="input-control select panel-content">
								    <select id="algo" name="algo">
								    	<?php foreach ($algo as $a) { ?>
								    		<option value="<?php echo $a->id;?>"><?php echo $a->name;?></option>
								    	<?php } ?>
								    </select>
								    <div class="panel-content">
								    	<div id="algodesc" class="tertiary-text"></div>
									</div>
								</div>

								

	                        </div>
                            
                            <div class="clearfix"></div>

                            <div class="panel">
                            	<div class="panel-content">
                            		<div id="response"></div>
                            	</div>
                            </div>

                            <div class="input-control">
								<button id="submit" type="submit" class="large primary">Create</button>
							</div>
							
						</div>
					</div>
				</div>
			</div>

			<script src="<?php echo asset_url(); ?>js/metro/metro-input-control.js"></script>
			<script type="text/javascript">
				$('#mainPanel').toggleClass('collapsed');

				<?php
					$desc = array();
					foreach ($algo as $a) {
						array_push($desc, $a->id . ":'" . $a->desc ."'");
					}
				?>
				var algoDesc = {<?php echo implode(', ', $desc)?>};

				$('#algodesc').html(algoDesc[$('#algo').val()]);

				$(document).ready(function(){
					$("#algo").on('change', function(e){
						$('#algodesc').html(algoDesc[$('#algo').val()]);
					});
				});

				$('form').change(function(){
					var checked = [];
					var drives = document.getElementsByName('newCD');
					for(var i = 0; i < drives.length; i++){
						if(drives[i].checked) 
							checked.push(drives[i].value);
					}
					if(checked.length > 0){
						var inputSize = {};
						for(var i = 0; i < checked.length; i++){
							if($('#vol_'+checked[i]).val() == '') return false;
							inputSize[checked[i]] = $('#vol_'+checked[i]).val();
						}
						$.ajax({
							url: '<?php echo site_url("main/volumeAjax");?>',
							type: 'POST',
							data: {
								uid: <?php echo $this->session->userdata('login_data')['id']; ?>, 
								drives: checked,
								algo: $("#algo").val(),
								inputSize: JSON.stringify(inputSize)
							},
							success: function(response){
								$('#response').html(response);
							}
						});
					}
				});

				$("#submit").click(function(event) {
					/* Act on the event */
					event.preventDefault();
					var checked = [];
					var drives = document.getElementsByName('newCD');
					for(var i = 0; i < drives.length; i++){
						if(drives[i].checked) 
							checked.push(drives[i].value);
					}
					if($('#name').val() == ''){
						alert("Please enter volume name!");
						return false;
					}
					if(checked.length == 0){
						alert("Please select at least 1 cloud drive.");
					}else{
						var inputSize = {};
						for(var i = 0; i < checked.length; i++){
							if($('#vol_'+checked[i]).val() == '') return false;
							inputSize[checked[i]] = $('#vol_'+checked[i]).val();
						}
						$.ajax({
							url: '<?php echo site_url("main/createVolume");?>',
							type: 'POST',
							data: {
								name: $('#name').val(),
								uid: <?php echo $this->session->userdata('login_data')['id']; ?>, 
								drives: checked,
								algo: $("#algo").val(),
								inputSize: JSON.stringify(inputSize)
							},
							success: function(response){
								var res = JSON.parse(response);
								if(res.status == 0){
									window.location = '<?php echo site_url("main/volume");?>';
								}else{
									alert(res.message);
									$('form').reset();
								}
							}
						});
					}
				});
			</script>
