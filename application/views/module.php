			<?php
				$colors = array('lime', 'green', 'emerald', 'teal', 'cyan', 'cobalt', 'indigo', 'violet', 'pink', 'magenta', 'crimson', 'red', 'orange', 'amber', 'yellow', 'brown', 'olive', 'steel', 'mauve', 'taupe', 'gray', 'dark', 'darker', 'darkBrown', 'darkCrimson', 'darkMagenta', 'darkIndigo', 'darkCyan', 'darkCobalt', 'darkTeal', 'darkEmerald', 'darkGreen', 'darkOrange', 'darkRed', 'darkPink', 'darkViolet', 'darkBlue', 'lightBlue', 'lightTeal', 'lightOlive', 'lightOrange', 'lightPink', 'lightRed', 'lightGreen');
			?>
			<script src="<?php echo asset_url(); ?>js/metro/metro-input-control.js"></script>
			<div class="row">
                <div class="span12">
                    <h1 class="header">Modules Management</h1>
                </div>
            </div>
			
            <div class="row">
                <div>
					<div class="panel">
						<div class="panel-header bg-lightBlue fg-white">Add Modules</div>
						<div class="panel-content">
							<p>All installed modules will be shown in this page. You may install new modules to the application.</p>
							<p>You may upgrade or update the module by uploading package again.</p>
							<?php echo form_open_multipart('main/installModule'); ?>
							<div class="input-control file info-state" data-role="input-control">
								<input type="file" name="userfile" tabindex="-1" style="z-index: 0;">
								<input type="text" id="__input_file_wrapper__" readonly="" style="z-index: 1; cursor: default;">
								<button class="btn-file" type="button"></button>
							</div>
							<button type="submit" class="large primary ">Upload</button>
							
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<?php if($tbody){ ?>
					<?php foreach($tbody as $t){ ?>
					<div class="tile double bg-<?php echo $colors[$t[0] % sizeof($colors)];?> fg-white">
						<div class="tile-content tile-status">
							<div class="text"><span class="item-title fg-white"><?php echo $t[1];?></span></div>
						</div>
						<div class="tile-status">
							<div class="progress-bar" data-role="progress-bar" data-color="bg-lightGreen" data-value="100"></div>
							<span class="text">Directory: <?php echo $t[2];?></span>
							
						</div>
						<div class="brand">
							<div class="badge bg-red"><?php echo $t[3];?></div>
						</div>
					</div>
					<?php } ?>
				<?php }else{ ?>
					<h3>No module yet!</h3>
				<?php } ?>
			</div>