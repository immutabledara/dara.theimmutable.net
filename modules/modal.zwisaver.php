<div class='modal fade' id='ZWISaver' tabindex='-1'>
	<div class='modal-dialog modal-dialog-centered modal-lg'>
		<div class='modal-content'>
			<div class='modal-header'>
				<h5 class='modal-title'>Store ZWI with DARA</h5>
				<i class='bi bi-x-square' data-bs-dismiss='modal'></i>
			</div>
			<div class="modal-body">
				<section class="section zwi">
					<div class="row">
						<div class="col-lg-12">
							<div class="card">
								<div class="card-body pt-3">
									<ul class="nav nav-tabs nav-tabs-bordered">
										<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#zwiFileUpload" id="zwiFileTab">Upload ZWI File</button></li>
										<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#zwiURLStore" id="zwiURLTab">Store ZWI URL</button></li>
									</ul>
									<div class="tab-content pt-2">
										<div class="tab-pane fade show active zwiFileUpload" id="zwiFileUpload">
											<form id="zwiFileForm">
											<div class='file-drop-area' id='zwiFileSelectArea'>
												<span class='selectZWIFileButton'>Choose file</span>
												<span class='selectedZWIFileName' id='zwiFileSelectMessage'>or drag and drop file here</span>
												<input class='zwiFileToUpload' id='zwiFileToUpload' name='zwiFileToUpload' type='file'>
											</div>
											<div id='zwiFileUploadArea' class='d-none'>
												<span id='zwiFileUploadButton' name='zwiFileUploadButton' class='zwiFileUploadButton'>Click to store file</span>
												<span id='zwiFileUploadMessage' class='selectedZWIFileName'></span>
											</div>
											<p class='notice-block' id='zwiFileDefaultInfoLine'>
												<?PHP echo "Please ensure file is ZWI v1.3+ Compliant, with .zwi or .zip extension and a Maximum size of ".($conf['zwiUploadLimit'] / 1024 / 1024)."MB"; ?>
											</p>
											<p class='error-block d-none' id='zwiFileUploadErrorLine'></p>
											<p class='notice-block d-none' id='zwiFileUploadResult1' ></p>
											<p class='info-block d-none' id='zwiFileUploadResult2'></p>
											</form>
										</div>
										<div class="tab-pane fade zwiURLStore" id="zwiURLStore">
											<form id="zwiURLForm">
											<div class="input-group mb-3" id='zwiURLLoader'>
											  <input type="text" id='zwiURLToUpload' name='zwiURLToUpload' class="form-control" placeholder="https://encycloreader.org/db/view.php?id=Z...">
											  <div class="input-group-append">
												<button class="btn zwiURLStoreButton" id='zwiURLStoreButton' name='zwiURLStoreButton'>Save</button>
											  </div>
											</div>
											<p class='notice-block' id='zwiURLStoreInfoLine'>
												<?PHP echo "Supports EncycloReader links for Encyclopedia Britannica, Jewish Encyclopedia and EnHub"; ?>
											</p>
											<p class='error-block d-none' id='zwiURLStoreErrorLine'></p>
											<p class='notice-block d-none' id='zwiURLStoreResult1' ></p>
											<p class='info-block d-none' id='zwiURLStoreResult2'></p>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
<?php
# Extract Title and url where available
# don't zip a zip
?>
<script type="text/javascript">
$(document).ready(function () {
	$('#zwiURLForm').on('keyup change', function(){
		enableButtons('#zwiURLStoreButton'); 
		clearAndHideSelectors('p#zwiURLStoreResult1', 'p#zwiURLStoreResult2', 'span#zwiURLStoreMessage', 'p#zwiURLStoreErrorLine');
		updateAndShowSelector('#zwiURLStoreInfoLine', 'Supports EncycloReader links for Encyclopedia Britannica, Jewish Encyclopedia and EnHub');
	});
	$('#ZWISaver').on('hide.bs.modal', function () {
		showSelectors('div#zwiFileSelectArea', 'p#zwiFileDefaultInfoLine', 'p#zwiURLStoreInfoLine');
		updateAndShowSelector('span#zwiFileSelectMessage', 'or drag and drop file here');
		clearAndHideSelectors('p#zwiFileUploadResult1', 'p#zwiFileUploadResult2', 'span#zwiFileUploadMessage', 'p#zwiFileUploadErrorLine');	
		clearAndHideSelectors('p#zwiURLStoreResult1', 'p#zwiURLStoreResult2', 'span#zwiURLStoreMessage', 'p#zwiURLStoreErrorLine');	
		clearSelectors('#zwiURLToUpload', '#zwiFileToUpload');
		$('#zwiURLForm')[0].reset();
		$('#zwiFileForm')[0].reset();
		$('#ZWISaver').off('hidden');
	});
	$('#ZWISaver').on('show.bs.modal', function (e) {
		disableButtons('#zwiFileUploadButton','#zwiURLStoreButton');
		$(document).on('change', '#zwiFileToUpload', function() {
			var zwiFileToUpload = $(this)[0].files;
			if ( zwiFileToUpload.length === 1 && zwiFileToUpload[0].name != "undefined" ) {
				hideSelectors('p#zwiFileDefaultInfoLine');
				if (zwiFileToUpload[0].size > window.zwiUploadLimit) { 
					updateAndShowSelector('p#zwiFileUploadErrorLine', 'Error: File exceeds maximum size of <?=($conf['zwiUploadLimit'] / 1024 / 1024)."MB"?>'); 
					clearAndHideSelectors('p#zwiFileUploadResult1', 'p#zwiFileUploadResult2', 'span#zwiFileUploadMessage');	
					return false; 
				}
				if (!window.zwiUploadTypes.includes(zwiFileToUpload[0].type)) {
					if ((zwiFileToUpload[0].type == "")&&(!zwiFileToUpload[0].name.endsWith(".zwi"))) {
						updateAndShowSelector('p#zwiFileUploadErrorLine', 'Error: <?="Only .zip and .zwi files allowed"?>');
						clearAndHideSelectors('p#zwiFileUploadResult1', 'p#zwiFileUploadResult2', 'span#zwiFileUploadMessage');	
						return false;
					}
				}
				hideSelectors('div#zwiFileSelectArea');
				showSelectors('div#zwiFileUploadArea');
				enableButtons('#zwiFileUploadButton');
				updateAndShowSelector('span#zwiFileUploadMessage', zwiFileToUpload[0].name);
				clearAndHideSelectors('p#zwiFileUploadResult1', 'p#zwiFileUploadResult2', 'p#zwiFileUploadErrorLine');	
				//kaka
				updateAndShowSelector('p#zwiFileDefaultInfoLine', 'Please ensure file is ZWI v1.3+ Compliant, with .zwi or .zip extension and a Maximum size of <?=$conf["zwiUploadLimit"]/1024/1024;?>MB');
			}
		});
	});
});

$('#zwiURLStoreButton').click(function(event) {
	if(!event.detail || event.detail == 1){
		storeZWIURL();
	} else { return false; }
});

$('#zwiFileUploadButton').click(function(event) {
	if(!event.detail || event.detail == 1){
		storeZWIFile();
	} else { return false; }
});

function storeZWIURL(){
	disableButtons('#zwiURLStoreButton');
	var zwiURLToUpload = document.querySelector('#zwiURLToUpload');
	if(!zwiURLToUpload.value.includes('encycloreader.org/db/')){
		updateAndShowSelector('p#zwiURLStoreErrorLine', 'Invalid URL');
	} else {
		hideSelectors('p#zwiURLStoreInfoLine');
	}
	axios.post( "/backend.php", { request: 'storeZWIURL', zwiURL: zwiURLToUpload.value }, { headers: { "Content-Type": "application/json" }
	})
	.then(function(zwiURLStoreResponse) {
		if (zwiURLStoreResponse['data']['status'] == 'OK') {
			fileUploadedHash = zwiURLStoreResponse['data']['hash'];
			fileUploadedSize = zwiURLStoreResponse['data']['size'];
			fileUploadedFileName = zwiURLStoreResponse['data']['filename'];
			fileUploadedqtzCIDHash = zwiURLStoreResponse['data']['qtzCIDHash'];
			updateAndShowSelector('p#zwiURLStoreResult1', 'Successfully stored '+zwiURLToUpload.value+'');
			updateAndShowSelector('p#zwiURLStoreResult2', 'DARA CID:&nbsp;<a href="#" onClick="showIPFSModal(\''+fileUploadedqtzCIDHash+'\')"><span class=\'toolong\'>'+fileUploadedHash+'</span> <span class=\'supertiny\'>(click to preview)</span></a>');
			$('#zwiURLForm')[0].reset();
		} else {
			updateAndShowSelector('p#zwiURLStoreErrorLine', 'Failed to store on DARA IPFS - '+zwiURLStoreResponse['data']['status']);
			clearAndHideSelectors('p#zwiURLStoreResult1', 'p#zwiURLStoreResult2', 'span#zwiURLStoreMessage');
		}
		getCreditsBalance();
		getUserRecords();
	});
};

function storeZWIFile(){
	disableButtons('#zwiFileUploadButton');
	var postFormData = new FormData();
	var zwiFileToUpload = document.querySelector('#zwiFileToUpload');
	postFormData.append('request', 'uploadfile');
	postFormData.append('filename', zwiFileToUpload.files[0].name);
	postFormData.append('purpose', 'zwiupload');
	postFormData.append('file', zwiFileToUpload.files[0]);
	axios.post('/backend.php', postFormData, { headers: { 'Content-Type': 'multipart/form-data' }
	})
	.then(function(fileUploadResponse) {
		if (fileUploadResponse['data']['status'] == 'OK') {
			fileUploadedHash = fileUploadResponse['data']['hash'];
			fileUploadedSize = fileUploadResponse['data']['size'];
			fileUploadedFileName = fileUploadResponse['data']['filename'];
			fileUploadedqtzCIDHash = fileUploadResponse['data']['qtzCIDHash'];
			updateAndShowSelector('p#zwiFileUploadResult1', 'Successfully stored '+zwiFileToUpload.files[0].name+'');
			updateAndShowSelector('p#zwiFileUploadResult2', 'DARA CID:&nbsp;<a href="#" onClick="showIPFSModal(\''+fileUploadedqtzCIDHash+'\')"><span class=\'toolong\'>'+fileUploadedHash+'</span> <span class=\'supertiny\'>(click to preview)</span></a>');
			$('#zwiFileForm')[0].reset();
			disableButtons('#zwiFileUploadButton','#zwiURLStoreButton');
			showSelectors('div#zwiFileSelectArea', 'p#zwiURLStoreInfoLine');
			updateAndShowSelector('span#zwiFileSelectMessage', 'or drag and drop file here');
			clearSelectors('#zwiURLToUpload', '#zwiFileToUpload');
			clearAndHideSelectors('p#zwiFileDefaultInfoLine');
			hideSelectors('div#zwiFileUploadArea');
		} else {
			updateAndShowSelector('p#zwiFileUploadErrorLine', 'Failed to store on DARA IPFS - '+fileUploadResponse['data']['status']);
		}
		getCreditsBalance();
		getUserRecords();
	});
};
</script>