<div class='modal fade' id='manualUploader' tabindex='-1'>
	<div class='modal-dialog modal-dialog-centered modal-lg'>
		<div class='modal-content'>
			<div class='modal-header'>
				<h5 class='modal-title'>Upload to DARA</h5>
				<i class='bi bi-x-square' data-bs-dismiss='modal'></i>
			</div>
			<div class='modal-body'>
				<div class='row'>
					<div class='col-lg-12'>
						<form id="fileUploadForm">
						<div class='file-drop-area' id='fileSelectArea'>
							<span class='selectFileButton'>Choose file</span>
							<span class='selectedFileName' id='fileSelectMessage'>or drag and drop file here</span>
							<input class='fileToUpload' id='fileToUpload' name='fileToUpload' type='file'>
						</div>
						<input type='text' class='d-none' name='comment'>
						<div id='fileUploadArea' class='d-none'>
							<span id='fileUploadButton' name='fileUploadButton' class='fileUploadButton'>Click to store file</span>
							<span id='fileUploadMessage' class='selectedFileName'></span>
						</div>
						<p class='info-block' id='fileDefaultInfoLine'><?PHP echo "Only ".preg_replace('/plain/i', 'TXT', preg_replace('/[a-z]+\//i', '', strtoupper(implode(", ", $conf['manualUploadTypes']))))." files allowed, with a Maximum size of ".($conf['manualUploadLimit'] / 1024 / 1024)."MB"; ?></p>
						<p class='error-block d-none' id='fileUploadErrorLine'></p>
						<p class='notice-block d-none' id='fileUploadResult1'></p>
						<p class='info-block d-none' id='fileUploadResult2'></p>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function () { 
	$('#manualUploader').on('hide.bs.modal', function () {
		disableButtons('#fileUploadButton');
		document.getElementById('fileToUpload').value = null;
		showSelectors('div#fileSelectArea', 'p#fileDefaultInfoLine');
		updateAndShowSelector('span#fileSelectMessage', 'or drag and drop file here');
		clearAndHideSelectors('p#fileUploadResult1', 'p#fileUploadResult2', 'span#fileUploadMessage', 'p#fileUploadErrorLine');	
		hideSelectors('div#fileUploadArea');
		$('#fileUploadForm')[0].reset();
	});
	$('#manualUploader').on('shown.bs.modal', function (e) {
		$(document).on('change', '#fileToUpload', function() {
			clearAndHideSelectors('p#fileUploadErrorLine');	
			var fileToUpload = $(this)[0].files;
			if ( fileToUpload.length === 1 && fileToUpload[0].name != "undefined" ) {
				if (fileToUpload[0].size > window.manualUploadLimit) { 
					updateAndShowSelector('p#fileUploadErrorLine', 'Error: File exceeds maximum size of <?=($conf['manualUploadLimit'] / 1024 / 1024)."MB"?>'); 
					return false; 
				}
				if (!window.manualUploadTypes.includes(fileToUpload[0].type)) {
					updateAndShowSelector('p#fileUploadErrorLine', 'Error: <?="Only ".preg_replace('/plain/i', 'TXT', preg_replace('/[a-z]+\//i', '', strtoupper(implode(", ", $conf['manualUploadTypes']))))." files allowed"?>');
					return false; 
				}
				hideSelectors('div#fileSelectArea');
				showSelectors('div#fileUploadArea', 'p#fileDefaultInfoLine');
				updateAndShowSelector('span#fileUploadMessage', fileToUpload[0].name);
				clearSelectors('p#fileUploadResult1', 'p#fileUploadResult2', 'p#fileUploadErrorLine');	
				enableButtons('#fileUploadButton');
			}
		});
	});

});

$('#fileUploadButton').click(function(event) {
	if(!event.detail || event.detail == 1){
		storeIPFSData();
	} else { return false; }
});

function storeIPFSData(){
	$('#fileUploadButton').addClass("button-disabled");
	var postFormData = new FormData();
	var fileToUpload = document.querySelector('#fileToUpload');
	postFormData.append('request', 'uploadfile');
	postFormData.append('filename', fileToUpload.files[0].name);
	postFormData.append('purpose', 'fileupload');
	postFormData.append('file', fileToUpload.files[0]);
	axios.post('/backend.php', postFormData, { headers: { 'Content-Type': 'multipart/form-data' }
	})
	.then(function(fileUploadResponse) {
		if (fileUploadResponse['data']['status'] == 'OK') {
			fileUploadedHash = fileUploadResponse['data']['hash'];
			fileUploadedSize = fileUploadResponse['data']['size'];
			fileUploadedFileName = fileUploadResponse['data']['filename'];
			fileUploadedqtzCIDHash = fileUploadResponse['data']['qtzCIDHash'];
			updateAndShowSelector('p#fileUploadResult1', 'Successfully stored '+fileToUpload.files[0].name+'');
			updateAndShowSelector('p#fileUploadResult2', 'DARA CID:&nbsp;<a href="#" onClick="showIPFSModal(\''+fileUploadedqtzCIDHash+'\')"><span class=\'toolong\'>'+fileUploadedHash+'</span> <span class=\'supertiny\'>(click to preview)</span></a>');
			hideSelectors('p#fileDefaultInfoLine');
			disableButtons('#fileUploadButton');
			document.getElementById('fileToUpload').value = null;
			showSelectors('div#fileSelectArea');
			updateAndShowSelector('span#fileSelectMessage', 'or drag and drop file here');
			hideSelectors('div#fileUploadArea');
			$('#fileUploadForm')[0].reset();
		} else {
			console.log(fileUploadResponse);
			updateAndShowSelector('p#fileUploadErrorLine', 'Failed to store on DARA IPFS - '+fileUploadResponse['data']['status']);
		}
		getCreditsBalance();
		getUserRecords();
	});
}
</script>
