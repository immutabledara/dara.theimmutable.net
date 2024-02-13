<div class="modal fade" id="DARAProfile" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Your Profile</h5>
				<i class="bi bi-x-square" data-bs-dismiss="modal"></i>
			</div>
			<div class="modal-body">
				<section class="section profile">
					<div class="row">
						<div class="col-xl-4">
							<div class="card">
								<div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
									<div class="pvProfilePicContainer">
										<img src="/assets/images/avatar.png" alt="Profile" class="rounded-circle" id="pvProfilePic">
									</div>
									<a href="#" class="d-none" id="pvUsername" target="_blank"></a>
									<div class="social-links mt-1">
										<a href="#" class="twitter d-none" id="pvTwitter" target="_blank"><i class="bi bi-twitter"></i></a>
										<a href="#" class="medium d-none" id="pvMedium" target="_blank"><i class="bi bi-medium"></i></a>
										<a href="#" class="email d-none" id="pvEmail" target="_blank"><i class="bi bi-envelope"></i></a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-8">
							<div class="card">
								<div class="card-body pt-3">
									<ul class="nav nav-tabs nav-tabs-bordered">
										<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-ov" id="profileOverviewTab">Overview</button></li>
										<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit" id="profileEditTab">Edit Profile Info</button></li>
										<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#profImage-edit" id="profileEditTab">Change Profile Image</button></li>
										<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#profSettings-edit" id="profileSettingsTab">Settings</button></li>
									</ul>
									<div class="tab-content pt-2">
										<div class="tab-pane fade show active profile-ov" id="profile-ov">
											<div class="row mt-3 mb-3"><div class="col-3 label">Signature:</div><div class="col-9" id="ovWalletAddress"><?=$_SESSION['walletAddress']?></div></div>
											<div class="row d-none" id="rovUsername"><div class="col-3 label">Name:</div><div class="col-9" id="ovUsername"></div></div>
											<div class="row d-none" id="rovEmail"><div class="col-3 label">email:</div><div class="col-9" id="ovEmail"></div></div>
											<div class="row d-none" id="rovTwitter"><div class="col-3 label">Twitter:</div><div class="col-9" id="ovTwitter"></div></div>
											<div class="row d-none" id="rovMedium"><div class="col-3 label">Medium:</div><div class="col-9" id="ovMedium"></div></div>
											<div class="row d-none" id="rovBio"><div class="col-3 label">About:</div><div class="col-9" id="ovBio"></div></div>
										</div>
										<div class="tab-pane fade profImage-edit pt-3" id="profImage-edit">
												<div class="row mb-1">
													<label for="inputProfilePic" class="col-md-4 col-lg-3 col-form-label" id="inputProfilePic">Profile Image</label>
													<div class="col-md-8 col-lg-8 col-xl-8">
														<div class='pic-drop-area' id='picSelectArea'>
															<span class='selectPicButton'>Choose pic</span>
															<span class='selectedPicName' id='picSelectMessage'>or drag and drop pic here</span>
															<input class='picToUpload' id='picToUpload' name='picToUpload' type='file'>
														</div>
														<input type='text' class='d-none' name='comment'>
														<div id='picUploadArea' class='d-none'>
															<span id='picUploadButton' name='picUploadButton' class='picUploadButton'>Update profile image</span>
															<span id='picUploadMessage' class='selectedPicName'></span>
														</div>
														<p class='notice-block' id='picDefaultInfoLine'><?PHP echo "Only ".preg_replace('/plain/i', 'TXT', preg_replace('/[a-z]+\//i', '', strtoupper(implode(", ", $conf['picUploadTypes']))))." files allowed, with a Maximum size of ".($conf['picUploadLimit'] / 1024 / 1024)."MB"; ?></p>
														<p class='error-block d-none' id='picUploadErrorLine'></p>
														<p class='notice-block d-none' id='picUploadResult1' ></p>
														<p class='info-block d-none' id='picUploadResult2'></p>
													</div>
												</div>
										</div>
										<div class="tab-pane fade profile-edit pt-3" id="profile-edit">
										<form id="userProfileForm">
											<div class="row mb-1"><label for="inputUserName" class="col-md-4 col-lg-3 col-form-label">Name</label><div class="col-md-8 col-lg-9"><input name="inputUserName" type="text" class="form-control" id="inputUserName" pattern="^[A-Za-z0-9\-_]{3,128}$" placeholder="" value=""></div></div>
											<div class="row mb-1"><label for="inputEmail" class="col-md-4 col-lg-3 col-form-label">Email</label><div class="col-md-8 col-lg-9"><input name="inputUserEmail" type="email" class="form-control" id="inputEmail" pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6}$" placeholder="user@domain.com" value=""></div></div>
											<div class="row mb-1"><label for="inputTwitter" class="col-md-4 col-lg-3 col-form-label">Twitter Handle</label><div class="col-md-8 col-lg-9"><input name="inputTwitter" type="text" class="form-control" id="inputTwitter" pattern="^(((http:\/\/|https:\/\/)?(twitter.com\/|mobile.twitter.com\/))|(@))?[A-Za-z0-9_]{4,15}/?$" placeholder="@username" value=""></div></div>
											<div class="row mb-1"><label for="inputMedium" class="col-md-4 col-lg-3 col-form-label">Medium Profile</label><div class="col-md-8 col-lg-9"><input name="inputMedium" type="text" class="form-control" id="inputMedium" pattern="^(((http:\/\/|https:\/\/)?[A-Za-z0-9_]{1,30}(\.medium.com))|(((http:\/\/|https:\/\/)?(medium.com\/@))?[A-Za-z0-9_]{1,30}))/?$" placeholder="username.medium.com" value=""></div></div>
											<div class="row mb-1"><label for="inputBio" class="col-md-4 col-lg-3 col-form-label">About</label><div class="col-md-8 col-lg-9"><textarea name="inputBio" type="text" class="form-control" id="inputBio" placeholder="About me" maxlength="160" value=""></textarea></div></div>
											<input name="inputProfilePic" type="hidden" class="form-control" id="inputProfilePic" value="">
											<div class="text-center"><button type="button" class="btn btn-success" id="setProfileData">Save Profile Info</button></div>
											<div class="row mb-1"><p class='error-block d-none' id='ProfileUpdateError'></p></div>
										</form>
										</div>
										<div class="tab-pane fade profSettings-edit pt-3" id="profSettings-edit">
										<form id="userSettingsForm">
											<div class="form-check"><input class="form-check-input" type="checkbox" id="isPublicProfile"><label class="form-check-label" for="isPublicProfile">Public Profile</label></div>
											<div class="form-check"><input class="form-check-input" type="checkbox" id="isDefaultPublic"><label class="form-check-label" for="isDefaultPublic">Store Publicaly by default</label></div>
											<div class="form-check"><input class="form-check-input" type="checkbox" id="isDefaultAnon"><label class="form-check-label" for="isDefaultAnon">Sign Anonymously by Default</label></div>
											<div class="text-center"><button type="button" class="btn btn-success" id="setProfileSettings">Save Settings</button></div>
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

<script type="text/javascript">
$(document).ready(function () {
	getProfileData();
	$('#userSettingsForm').on('keyup change', function(){enableButtons('#setProfileSettings'); });
	$('#userProfileForm').on('keyup change', function(){enableButtons('#setProfileData'); });
	$('#DARAProfile').on('hide.bs.modal', function () {
		enableButtons('#picUploadButton');
		document.getElementById('picToUpload').value = null;
		showSelectors('div#picSelectArea', 'p#picDefaultInfoLine');
		updateAndShowSelector('span#picSelectMessage', 'or drag and drop pic here');
		clearAndHideSelectors('p#picUploadResult1', 'p#picUploadResult2', 'span#picUploadMessage', 'p#picUploadErrorLine');	
		hideSelectors('div#picUploadArea');
		clearAndHideSelectors('#ProfileUpdateError');
	});
	$('#DARAProfile').on('shown.bs.modal', function (e) {
		disableButtons('#setProfileData', '#setProfileSettings');
		$('#picUploadButton').removeClass("button-disabled");
		document.getElementById('picToUpload').value = null;
		showSelectors('div#picSelectArea', 'p#picDefaultInfoLine');
		updateAndShowSelector('span#picSelectMessage', 'or drag and drop pic here');
		clearAndHideSelectors('p#picUploadResult1', 'p#picUploadResult2', 'span#picUploadMessage', 'p#picUploadErrorLine');	
		hideSelectors('div#picUploadArea');
    });
	$(document).on('change', '#picToUpload', function() {
		clearAndHideSelectors('p#picUploadErrorLine');
		var picToUpload = $(this)[0].files;
		if ( picToUpload.length === 1 && picToUpload[0].name != "undefined" ) {
			hideSelectors('p#picDefaultInfoLine');
			if (picToUpload[0].size > window.picUploadLimit) {
				updateAndShowSelector('p#picUploadErrorLine', 'Error: Pic exceeds maximum size of <?=($conf['picUploadLimit'] / 1024 )."KB"?>'); 
				return false;
			}
			if (!window.picUploadTypes.includes(picToUpload[0].type)) {
				updateAndShowSelector('p#picUploadErrorLine', 'Error: <?="Only ".preg_replace('/plain/i', 'TXT', preg_replace('/[a-z]+\//i', '', strtoupper(implode(", ", $conf['picUploadTypes']))))." files allowed"?>');
				return false;
			}	
			hideSelectors('div#picSelectArea');
			showSelectors('div#picUploadArea');
			updateAndShowSelector('span#picUploadMessage', picToUpload[0].name);
		}
	});
});

function setProfileData() {
	axios.post( "/backend.php", {
		request: 'updateProfile',
		avatarIPFS: document.getElementById('inputProfilePic').value, 
		username: document.getElementById('inputUserName').value, 
		userEmail: document.getElementById('inputEmail').value, 
		userTwitter: document.getElementById('inputTwitter').value, 
		userMedium: document.getElementById('inputMedium').value, 
		userBio: document.getElementById('inputBio').value,
		isPublicProfile: document.getElementById('isPublicProfile').checked, 
		isDefaultAnon: document.getElementById('isDefaultAnon').checked, 
		isDefaultPublic: document.getElementById('isDefaultPublic').checked
	}, { headers: { "Content-Type": "application/json" } })
	.then(function(response) {
			if (response.status == 200 && response['data']['status'] == 'OK') {
				clearAndHideSelectors('#ProfileUpdateError');
				updateSelector('#setProfileData', 'Saved!');
				updateSelector('#setProfileSettings', 'Saved!');
				getProfileData();
                sleep(2000).then(() => { 
					$('#profileOverviewTab').click();
					updateSelector('#setProfileData', 'Save Changes');
					updateSelector('#setProfileSettings', 'Save Changes');
					enableButtons('#picUploadButton');
					document.getElementById('picToUpload').value = null;
					showSelectors('div#picSelectArea', 'p#picDefaultInfoLine');
					updateAndShowSelector('span#picSelectMessage', 'or drag and drop pic here');
					clearAndHideSelectors('p#picUploadResult1', 'p#picUploadResult2', 'span#picUploadMessage', 'p#picUploadErrorLine', '#ProfileUpdateError');	
					hideSelectors('div#picUploadArea');
				});
			} else {
				updateAndShowSelector('#ProfileUpdateError', 'Error: '+response['data']['message']);
			}
	}).catch(
		function (error) {
			updateAndShowSelector('#ProfileUpdateError', 'Internal Server Error');
			console.log(error);
		}
	)
}

var userProfileForm = document.getElementById('userProfileForm');
$('#setProfileSettings').click(function(event) {
	if(!event.detail || event.detail == 1){
		if (userProfileForm.checkValidity() != false) {
			setProfileData();
		}
	} else { return false; }
});
var userSettingsForm = document.getElementById('userSettingsForm');
$('#setProfileData').click(function(event) {
	if(!event.detail || event.detail == 1){
		if (userSettingsForm.checkValidity() != false) {
			setProfileData();
		}
	} else { return false; }
});

$('#picUploadButton').click(function(event) {
	if(!event.detail || event.detail == 1){
		storePicData();
	} else { return false; }
});

function getProfileData() {
axios.post( "/backend.php", { request: 'getProfileData' }, { headers: { "Content-Type": "application/json" } })
	.then(function(userProfileData) {
		if (userProfileData.data.result[0].avatarIPFS) {
			document.getElementById('pvProfilePic').src = ''+window.ipfsGateway+''+userProfileData.data.result[0].avatarIPFS+'';
			document.getElementById('inputProfilePic').value = userProfileData.data.result[0].avatarIPFS;
		}
		if (userProfileData.data.result[0].username) {
			document.getElementById('pvUsername').href = '/u\/'+userProfileData.data.result[0].username+'';
			document.getElementById('pvUsername').innerText = userProfileData.data.result[0].username;
			document.getElementById('ovUsername').innerHTML = document.getElementById('inputUserName').value = userProfileData.data.result[0].username;
			showSelectors("#pvUsername", "#rovUsername");
		} else {
			document.getElementById('pvUsername').innerText = null;
			hideSelectors("#pvUsername", "#rovUsername");
		}
		if (userProfileData.data.result[0].userEmail) {
			document.getElementById('pvEmail').href = 'mailto: '+userProfileData.data.result[0].userEmail;
			document.getElementById('ovEmail').innerHTML = document.getElementById('inputEmail').value = userProfileData.data.result[0].userEmail;
			showSelectors("#pvEmail", "#rovEmail");
		} else {
			document.getElementById('pvEmail').href = null;
			hideSelectors("#pvEmail", "#rovEmail");
		}
		if (userProfileData.data.result[0].userTwitter) {
			document.getElementById('pvTwitter').href = 'https:\/\/twitter.com\/'+userProfileData.data.result[0].userTwitter;
			document.getElementById('ovTwitter').innerHTML = document.getElementById('inputTwitter').value = userProfileData.data.result[0].userTwitter;
			showSelectors("#pvTwitter", "#rovTwitter");
		} else {
			document.getElementById('pvTwitter').href = null;
			hideSelectors("#pvTwitter", "#rovTwitter");
		}
		if (userProfileData.data.result[0].userMedium) {
			document.getElementById('pvMedium').href = 'https:\/\/medium.com\/@'+userProfileData.data.result[0].userMedium;
			document.getElementById('ovMedium').innerHTML = document.getElementById('inputMedium').value = userProfileData.data.result[0].userMedium;
			showSelectors("#pvMedium", "#rovMedium");
		} else {
			document.getElementById('pvMedium').href = null;
			hideSelectors("#pvMedium", "#rovMedium");
		}
		if (userProfileData.data.result[0].userBio) {
			document.getElementById('ovBio').innerText = document.getElementById('inputBio').value = userProfileData.data.result[0].userBio;
			showSelectors("#rovBio");
		} else {
			document.getElementById('ovBio').innerText = null;
			hideSelectors("#rovBio");
		}
		document.getElementById('isPublicProfile').checked = userProfileData.data.result[0].isPublicProfile;
		document.getElementById('isDefaultAnon').checked = userProfileData.data.result[0].isDefaultAnon;
		document.getElementById('isDefaultPublic').checked = userProfileData.data.result[0].isDefaultPublic;
	});
};

function storePicData(){
	$('#picUploadButton').addClass("button-disabled");
	var postFormData = new FormData();
	var picToUpload = document.querySelector('#picToUpload');
	postFormData.append('request', 'uploadfile');
	postFormData.append('filename', picToUpload.files[0].name);
	postFormData.append('purpose', 'profilepic');
	postFormData.append('file', picToUpload.files[0]);
	axios.post('/backend.php', postFormData, { headers: { 'Content-Type': 'multipart/form-data' }
	})
	.then(function(picUploadResponse) {
		if (picUploadResponse['data']['status'] == 'OK') {
			picUploadedHash = picUploadResponse['data']['hash'];
			document.getElementById('pvProfilePic').src = ''+window.ipfsGateway+''+picUploadedHash+'';
			document.getElementById('inputProfilePic').value = picUploadedHash;
			updateAndShowSelector('p#picUploadResult1', 'Stored '+picToUpload.files[0].name+' successfully.');
			setProfileData();
		} else {
			updateAndShowSelector('p#picUploadErrorLine', 'Failed to update image - '+fileUploadResponse['data']['status']);
		}
		getCreditsBalance();
		getUserRecords();
	});
}
</script>
