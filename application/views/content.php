			<?php
				$colors = array('lime', 'green', 'emerald', 'teal', 'cyan', 'cobalt', 'indigo', 'violet', 'pink', 'magenta', 'crimson', 'red', 'orange', 'amber', 'yellow', 'brown', 'olive', 'steel', 'mauve', 'taupe', 'gray', 'dark', 'darker', 'darkBrown', 'darkCrimson', 'darkMagenta', 'darkIndigo', 'darkCyan', 'darkCobalt', 'darkTeal', 'darkEmerald', 'darkGreen', 'darkOrange', 'darkRed', 'darkPink', 'darkViolet', 'darkBlue', 'lightBlue', 'lightTeal', 'lightOlive', 'lightOrange', 'lightPink', 'lightRed', 'lightGreen');
			?>

			<div class="row">
                <h1 class="header">JBOCD<sup><small>BETA</small></sup></h1>
				<small>Just a bunch of cloud drive</small>
            </div>
			
			<div class="row">
				<?php foreach($modules as $i => $module){?>
				<div class="tile bg-<?php echo $colors[$i]; ?>">
					<div class="tile-status">
						<span class="name"><i class="fa fa-dropbox"></i><?php echo $module['name']; ?></span>
					</div>
				</div>
				<?php } ?>
				<?php for($i = 2;$i<48;$i++){?>
				<div class="tile bg-<?php echo $colors[$i%sizeof($colors)]; ?>">
					<div class="tile-status">
						<span class="name">test</span>
					</div>
				</div>
				<?php } ?>
			</div>