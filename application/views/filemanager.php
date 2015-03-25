			<link href="<?php echo asset_url(); ?>css/filedrop.css" rel="stylesheet">
			<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

			<div class="row">
                <div class="span12">
                    <h1 class="header">File Manager</h1>
                </div>
            </div>

            <div class="row">
            	<button class="primary" id="upload">Upload</button>
	            <button class="primary">New folder</button>
	            <button class="danger">Delete</button>
            </div>

            <div class="row">
            	<table class="hovered table" id="fileTable"></table>
            </div>

            <script src="<?php echo asset_url(); ?>js/jquery/jquery.dataTables.js"></script>
			<script src="<?php echo asset_url(); ?>js/metro/metro-input-control.js"></script>
			<script src="<?php echo asset_url(); ?>js/filedrop.js"></script>
			<script type="text/javascript">
				fd.jQuery();
				var zone;
				var CSRF = '<?php echo $CSRF; ?>';
				var workers = [];

				var code = "raid5.js";
				var numOfDrive = 4,
					blockSize = 4096;
				var config = [numOfDrive, blockSize];

				function readfiles(files) {
					for (var i = 0; i < files.length; i++) {
						workers[i] = new Worker('<?php echo asset_url(); ?>algo/worker.js');
						workers[i].postMessage([code, [4, 1024*1024], files[i].nativeFile, ['encode', i]]);
						workers[i].onmessage = function(e){
							if(e.data[2] % 100 == 0) console.log(e.data[2]);
						};
					}
				}

				$(document).ready(function() {
					var data = [
						[1,2,3,4,5,6],
						[1,2,3,4,5,6],
						[1,2,3,4,5,6],
						[1,2,3,4,5,6],
						[1,2,3,4,5,6]
					];

				    $('#fileTable').dataTable( {
				        "data": data,
				        "columns": [
				            { "title": "Column A" },
				            { "title": "Column B" },
				            { "title": "Column C" },
				            { "title": "Column D", "class": "center" },
				            { "title": "Column E", "class": "center" },
				            { "title": "Column F", "class": "center" },
				            {
				                "className":      'details-control',
				                "orderable":      false,
				                "data":           null,
				                "defaultContent": ''
				            }
				        ]
				    } );
				    $('#fileTable tbody').on('click', 'td.details-control', function () {

				    });
				});

				$("#upload").on('click', function(){
				    $.Dialog({
				    	padding: 20,
				    	width: "30%",
				    	height: "40%",
				        overlay: true,
				        shadow: true,
				        flat: true,
				        title: 'Upload',
				        content: '',
				        onShow: function(_dialog){
				        	var content = _dialog.children('.content');
				        	content.html('<div class=" container" id="holder"><h1 class="text-center"><i class="fa fa-hand-o-up"></i><span> CLICK <small>or</small> DROP </span><i class="fa fa-upload"></i><h1></div>');
				        	$('#holder')
				        		.filedrop({multiple: true})
				        		.on('fdsend', function(e, files){
				        			readfiles(files);
				        		});
				        }
				    });
				});
			</script>
			