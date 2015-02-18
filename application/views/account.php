			<?php
				$colors = array('lime', 'green', 'emerald', 'teal', 'cyan', 'cobalt', 'indigo', 'violet', 'pink', 'magenta', 'crimson', 'red', 'orange', 'amber', 'yellow', 'brown', 'olive', 'steel', 'mauve', 'taupe', 'gray', 'dark', 'darker', 'darkBrown', 'darkCrimson', 'darkMagenta', 'darkIndigo', 'darkCyan', 'darkCobalt', 'darkTeal', 'darkEmerald', 'darkGreen', 'darkOrange', 'darkRed', 'darkPink', 'darkViolet', 'darkBlue', 'lightBlue', 'lightTeal', 'lightOlive', 'lightOrange', 'lightPink', 'lightRed', 'lightGreen');
			?>
			<script src="<?php echo asset_url(); ?>js/metro/metro-progressbar.js"></script>
			<div class="row">
                <div class="span12">
                    <h1 class="header"><?php echo $title; ?> Account Management</h1>
                </div>
            </div>
			<div class="row">
                <div>
					<div class="panel"  data-role="panel">
						<div class="panel-header bg-lightBlue fg-white">Add Accounts</div>
						<div class="panel-content">
							<p>All connected account will be shown in this page. You may link new accounts to the application. Informations will be added after authorization.</p>
							<a class="command-button primary" href="<?php echo site_url('main/addAccount/'.$dir);?>">
								<i class="icon-share-3 on-right"></i>
								Add Account
								<small>Redirect to authenticate</small>
							</a>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<h2>Currently Managed Accounts</h2>
			</div>
			
			<div class="row">
				<?php if($tbody){ ?>
					<?php foreach($tbody as $t){ ?>
						<?php if($t['status'] == "Normal"){ ?>
					<div class="tile double bg-<?php echo $colors[$t['id'] % sizeof($colors)];?> fg-white">
						<?php } else { ?>
					<div class="tile double ribbed-amber fg-white">
						<?php } ?>
						<div class="tile-content tile-status">
							<div class="text"><?php echo $t['name'];?></div>
							<div class="text"><?php echo $t['status'];?></div>
						</div>
						<div class="tile-status">
							
							<div class="progress-bar" data-role="progress-bar" data-color="bg-green" data-value="<?php echo ($t['available']!='---'?($t['available']/$t['quota'])*100:0);?>"></div>
							<span class="text">Available: <?php echo $t['available'];?> GB / <?php echo $t['quota'];?> GB</span>
							
						</div>
						<div class="brand">
							<div class="badge bg-red"><?php echo $t['action'];?></div>
						</div>
					</div>
					<?php } ?>
				<?php }else{ ?>
					<h3>No account yet!</h3>
				<?php } ?>
			</div>
			