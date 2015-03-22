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
					<div class="panel">
						<div class="panel-header bg-lightBlue fg-white">Create volume</div>
						<div class="panel-content">
							<p>You may add volume that span across your cloud drives.</p>
							<p>Configuration with multiple drives will be able to support fault-tolerance.</p>
							<?php echo form_open('main/createVolume'); ?>
							<legend>Create New Volume</legend>
							<div class="input-control text" data-role="input-control">
                                <input type="text" placeholder="Drive Name">
                            </div>
                            <div class="panel">
	                            <div class="panel-content">
	                                <div class="input-control checkbox">
	                                	<label>Select drive(s)</label>
	                                	<?php
	                                		foreach ($cloudDrives as $clouddrive) {
	                                			$status = ($clouddrive['info']['status']?'':'disable');
	                                			echo '<label><input '.$status.' type="checkbox" name="newCD" value="'
	                                			.$clouddrive['id'].'" /><span class="check"></span>'
	                                			.$clouddrive['info']['name'].'<span class="text-muted"> ( Provider: '
	                                			.$clouddrive['provider']
	                                			.', Available: '
	                                			.$clouddrive['info']['available'].' GB / '.$clouddrive['info']['quota']
	                                			.' GB )</span></label>';
	                                		}
	                                	?>
									</div>
	                            </div>
	                        </div>
                            
                            <div class="clearfix"></div>

							<button type="submit" class="large primary ">Create</button>
							
						</div>
					</div>
				</div>
			</div>

			<script src="<?php echo asset_url(); ?>js/metro/metro-input-control.js"></script>
