
		<nav class="navigation-bar dark">
			<nav class="navigation-bar-content container">
				<a href="<?php echo site_url();?>" class="element">
					<i class="icon-cloud"></i> JBOCD <sup>BETA</sup>
				</a>
				<span class="element-divider"></span>
				<?php foreach($menus as $menu){?>
					<?php if($menu['dividerBefore']){ ?><span class="element-divider"></span><?php } ?>
					<ul class="element-menu">
					<?php if(isset($menu['submenu'])){ ?>
						<li>
							<a class="dropdown-toggle" href="#"><?php echo $menu['title'];?></a>
							<ul class="dropdown-menu dark" data-role="dropdown">
								<?php foreach($menu['submenu'] as $submenu){?>
								<?php if($submenu['dividerBefore']){ ?><li class="divider"></li><?php } ?>
								<li>
									<a href="<?php echo $submenu['link']; ?>"><?php echo $submenu['title']; ?></a>
								</li>
								<?php } ?>
							</ul>
						</li>
					<?php }else{ ?>
						<li>
							<a href="<?php echo $menu['link']; ?>">
								<?php echo $menu['title'];?>
							</a>
						</li>
					<?php } ?>
					</div>
				<?php } ?>
				
				<a id="logout" class="element place-right" href="#">
					<span class="icon-exit"></span>
				</a>
				<span class="element-divider place-right"></span>
				<a href="<?php echo site_url();?>" class="element place-right">
					<i class="icon-user"></i> <?php echo $profile->name;?>
				</a>
			</nav>
		</nav>
		
		<script>
		$("#logout").on('click', function(){
			$.Dialog({
				overlay: true,
				shadow: true,
				flat: true,
				title: 'Logout',
				width: '300px',
				height: "90px",
				padding: '32px 10px',
				content: '\
					<center>\
						<a class="button command-button inverse" href="<?php echo site_url('main/logout');?>">\
							<i class="icon-exit on-right"></i>\
							Logout?\
							<small>Click here to confirm logout</small>\
						</a>\
					</center>\
					'
			});
		});
		
		</script>