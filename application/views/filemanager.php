			<link href="<?php echo asset_url(); ?>css/filedrop.css" rel="stylesheet">
			<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

			<div class="row">
                <div class="span12">
                    <h1 class="header">File Manager</h1>
                </div>
            </div>

            <div class="row" id="fileControl">
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
				$('#fileControl').hide();
				fd.jQuery();
				var zone;
				var workers = [];
				var dt;

				$(document).ready(function() {
					var data = [];

				    dt = $('#fileTable').dataTable( {
				        "data": data,
				        "oLanguage": {
							"sEmptyTable": "No file"
						},
				        "columns": [
				            { "title": "Filename" },
				            { "title": "Size" }
				        ],
        				"order": [[1, 'asc']]
				    } );

				    $('#fileTable tbody').on('click', 'td.details-control', function () {
				    	$(this)
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
			<script type="text/javascript">
				var uid = <?php echo $uid; ?>;
				var ldid = <?php echo $ldid; ?>;
				var CSRF = '<?php echo $CSRF; ?>';
				var script = '<?php echo $algo; ?>';
				var files = [];
				var dir = 0;
				var drives;
				var numOfDrive;
				var workerTemp = {};
				var fileTemp = {};

				var format = function( d ) {
				    // `d` is the original data object for the row
				    console.log(d);
				}

				//File array = [name, size]
				var refreshFilelist = function(e){
					files = [];
					var fileList = e.response.fileList;
					console.log(fileList);
					for(var i = 0; i < fileList.length; i++){
						var size = fileList[i].size;
						var unit;
						if(fileList[i].size < 1024) unit = " bytes";
						else if(fileList[i].size < 1048576) unit = " KB";
						else if(fileList[i].size < 1073741824) unit = " MB";
						else if(fileList[i].size < 1099511627776) unit = " GB";
						while(size > 1024){
							size /= 1024;
						}
						files[i] = [
							fileList[i].name,
							parseFloat(Math.round(size * 100) / 100).toFixed(2) + unit
						];
					}
					dt.fnClearTable();
					if(files.length > 0) dt.fnAddData( files );
				}

				var workerCollection = function(id){
					//if(fileTemp[id])
				}

				var unwork = {};
				var testfile = {};

				var readfiles = function(files) {
					for (var i = 0; i < files.length; i++) {
						var file = files[i];
						var fileSize = files[i].size;
						JBOCD.Socket.createFile(ldid, dir, files[i].size, files[i].name, function(e){
							var fid = e.response.fID;
							console.log("res",e);
							if(workers[fid] == undefined){
								workers[fid] = new Worker('<?php echo asset_url(); ?>algo/worker.js');
							}
							//console.log("POST:", [script, [numOfDrive, 1024*1024], file.nativeFile, ['encode', e.response.fID]]);
							workers[fid].postMessage([script, [numOfDrive, 1024*1024], file.nativeFile, ['encode', e.response.fID]]);
							workers[fid].onmessage = function(e){
								console.log("PutChunk:", [ldid, drives[e.data[3]].cdID, e.data[1], e.data[2], '', e.data[0]]);
								if(fileTemp[e.data[1]] == undefined) fileTemp[fid] = { totalNumOfChunks: e.data[5], completedChunks:0 };
								fileTemp[e.data[1]].completedChunks += 1;
								if(testfile[e.data[1]] == undefined) testfile[e.data[1]] = { chunkList:{} };
								testfile[e.data[1]].chunkList[e.data[2]] = e.data[0];
								if(fileTemp[e.data[1]].completedChunks == fileTemp[e.data[1]].totalNumOfChunks){
									workers[fid].postMessage("close");
									delete workers[e.data[1]];
									delete fileTemp[e.data[1]];
									JBOCD.Socket.list(ldid, dir, refreshFilelist);
								}
								JBOCD.Socket.putChunk(ldid, drives[e.data[3]].cdID, e.data[1], e.data[2], '', e.data[0], function(e){
									console.log("Fin Put chunk:", e);
								});
							}
						});
					}
				};

				var getFileCallback = function(e){
					//var data = 
				}

				JBOCD.Socket.init(function(e){
					console.log("JBOCD connected!");
					var loginOp = JBOCD.Socket.login(uid, CSRF, function(e){
						console.log("JBOCD authenticated!");
						JBOCD.Socket.getLogicalDrive(function(e){
							for(var i = 0; i < e.response.ldList.length; i++){
								console.log("CHK", e.response.ldList[i]);
								if(e.response.ldList[i].ldID == ldid){
									drives = e.response.ldList[i].cdList;
									numOfDrive = e.response.ldList[i].cdList.length;
									break;
								}
							}
							JBOCD.Socket.list(ldid, dir, refreshFilelist);
							$('#fileControl').show();
						});
					});
				});

			</script>
			