<?PHP
$whereAmI = "profile";
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/gatekeeper.php');

$profileid=htmlspecialchars($_GET['cid'], ENT_QUOTES, 'UTF-8');

if (($profileid)&&($profileid!="")){
	$profiledata=fetchPublicProfile($profileid);
	if ($profiledata['status'] === 'OK') {
		$username = $profiledata['result'][0]['username'];
		$avatarIPFS = $profiledata['result'][0]['avatarIPFS'];
		#$userBio = $profiledata['result'][0]['userBio'];
	}
}
if (strlen($username)) {
	$tpl['head.title'] = "".$username." / DARA Profile";
	$metatags = [
		'description' => "{$username}'s publications and shared content on Project DARA, The Uncensorable Publishing Platform.",
		'og_site_name' => 'Project DARA',
		'og_title' => "{$username} / Project DARA",
		'og_url' => "https://dara.theimmutable.net/@{$username}",
		'og_type' => 'profile',
		'og_description' => "{$username}'s publications and shared content on Project DARA, The Uncensorable Publishing Platform.",
		'og_image' => "https://opfs.dara.global/ipfs/{$avatarIPFS}",
		'twitter_card' => 'summary_large_image',
		'twitter_site' => '@dara_proj',
		'twitter_title' => "{$username} / Project DARA",
		'twitter_description' => "{$username}'s publications and shared content on Project DARA, The Uncensorable Publishing Platform.",
		'twitter_image' => "https://opfs.dara.global/ipfs/{$avatarIPFS}"
	];
}

require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.htmlheaders.php');
?>
<section class="dara-background overflow-auto">
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.top.php'); ?>
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.left.php'); ?>
	<main id="main" class="main">
		<div class="pagetitle">
			<h1>&nbsp;</h1>
		</div>
		<!-- End Page Title -->
		<section class="section dashboard">
			<div class="row">
				<!-- Center Stage -->
				<div class="col-xxl-9 col-lg-12">
					<div class="row">
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.login.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.searchresults.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.showipfs.php'); ?>
						<div class="pagetitle d-xxl-block d-none" id="contentTableHeader">
							<h1>Published Content</h1>
						</div>
						<div class="col-12">
							<div class="card recent-uploads overflow-auto" id="topCardBody">
								<div class="card-body d-none" id="mainCardBody">
									<table class="table table-hover table-borderless table-responsive datatable" id="publicUserUploadsTable">
										<thead>
											<tr>
												<th class="text-left" scope="col">Date</th>
												<th class="text-left" scope="col">Details</th>
												<th class="td-text-col3 text-center" scope="col">Source</th>
												<th class="text-center" scope="col">Status</th>
											</tr>
										</thead>
										<tbody id="profile_search_results"></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-xl-12 order-first order-xxl-last d-none" id="pubUserProfileContainer">
					<div class="card">
						<div class="row mt-3 text-center"><h5><a href="#" class="d-none" id="ppvUsername" target="_blank"></a></h5>
							<div class="col-lg-4 col-12" id="pubCardThumbnail">
								<div class="card">
									<div class="card-body profile-card d-flex flex-column align-items-center">
										<div class="ppvProfilePicContainer">
											<img src="/assets/images/avatar.png" alt="Profile" class="rounded-circle" id="ppvProfilePic">
										</div>
									<div class="social-links mt-1">
										<a href="#" class="twitter d-none" id="ppvTwitter" target="_blank"><i class="bi bi-twitter"></i></a>
										<a href="#" class="medium d-none" id="ppvMedium" target="_blank"><i class="bi bi-medium"></i></a>
										<a href="#" class="email d-none" id="ppvEmail" target="_blank"><i class="bi bi-envelope"></i></a>
									</div>
									</div>
								</div>
							</div>
							<div class="col-lg-8 col-12 align-self-center" id="ppvBioCard">
								<div class="card text-start" id="ppvBio"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>
<script type="text/javascript">
function getPublicProfile() {
axios.post( "/backend.php", { request: 'getPublicProfile', profileID : '<?=$profileid?>' }, { headers: { "Content-Type": "application/json" } })
	.then(function(publicProfileData) {
		if (publicProfileData.status == 200 && publicProfileData['data']['status'] == 'OK') {
			if (publicProfileData.data.result[0].avatarIPFS) {
				document.getElementById('ppvProfilePic').src = ''+window.ipfsGateway+''+publicProfileData.data.result[0].avatarIPFS+'';
			}
			if (publicProfileData.data.result[0].username) {
				document.getElementById('ppvUsername').href = '/u\/'+publicProfileData.data.result[0].username+'';
				document.getElementById('ppvUsername').innerText = publicProfileData.data.result[0].username;
				showSelectors("#ppvUsername");
			} else {
				clearAndHideSelectors("#ppvUsername");
			}
			if (publicProfileData.data.result[0].userEmail) {
				document.getElementById('ppvEmail').href = 'mailto: '+publicProfileData.data.result[0].userEmail;
				showSelectors("#ppvEmail");
			} else {
				clearAndHideSelectors("#ppvEmail", "#rovEmail");
			}
			if (publicProfileData.data.result[0].userTwitter) {
				document.getElementById('ppvTwitter').href = 'https:\/\/twitter.com\/'+publicProfileData.data.result[0].userTwitter;
				showSelectors("#ppvTwitter");
			} else {
				clearAndHideSelectors("#ppvTwitter");
			}
			if (publicProfileData.data.result[0].userMedium) {
				document.getElementById('ppvMedium').href = 'https:\/\/medium.com\/@'+publicProfileData.data.result[0].userMedium;
				showSelectors("#ppvMedium");
			} else {
				clearAndHideSelectors("#ppvMedium");
			}
			if (publicProfileData.data.result[0].userBio) {
				document.getElementById('ppvBio').innerHTML = publicProfileData.data.result[0].userBio;
				showSelectors("#ppvBioCard");
			} else {
				clearAndHideSelectors("#ppvBioCard");
				$("#pubCardThumbnail").addClass("w-100");
			}
			showSelectors("#pubUserProfileContainer");
			showSelectors("#mainCardBody");
			getPublicProfileRecords();
		} else {
			hideSelectors("#pubUserProfileContainer", "#contentTableHeader");
			clearAndHideSelectors("#contentTableHeader");
			updateAndShowSelector("#topCardBody", "Profile not found");
		}
	})
	.catch((err) => {
		console.log(err);
	})
};

function getPublicProfileRecords() {
	if (typeof dataTableUser !== 'undefined') {
		document.getElementById('publicUserUploadsTable').style.display = "none";
		dataTableUser.destroy();
	}
	var html = '';
	var serial_no = 1;
	axios.post( "/backend.php", { request: 'getPublicProfileRecords', profileID : '<?=$profileid?>' }, { headers: { "Content-Type": "application/json" } })
	.then(function(response) {
		if (response.status == 200 && response['data']['status'] == 'OK') {
			var userRecord = response['data']['results'];
			if(response['data']['count'] > 0) {
				for(var count = 0; count < userRecord.length; count++) {
					html += '<tr>';
					html += '	<td class="td-text-col1 align-middle text-left">'+userRecord[count].created.replace("-", "&#8209;")+'</td>';
					html += '	<td class="td-text-col2 align-middle text-left">';
					html += '		<a href="#" onClick="showIPFSModal(\''+userRecord[count].cidhash+'\')">';
					html += '			<span class="maintable-title">'+userRecord[count].pagename+'</span>';					
					html += '		</a>';
					html += '	</td>';
               if (userRecord[count].contentsource == 'website') {
                  html += '   <td class="td-text-col3 text-center align-middle">Direct Upload</td>';
               } else if (userRecord[count].contentsource == 'zwifileupload') {
                  html += '   <td class="td-text-col3 text-center align-middle">Direct ZWI Upload</td>';
					} else {
						html += '	<td class="td-text-col3 text-center align-middle"><a href='+userRecord[count].pageurl+' target=_blank>View</a></td>';
					}
					if ((+userRecord[count].txnhash != '')&&(+userRecord[count].anonymous == false)) {
						html += '<td class="td-text-col5 text-center align-middle"><span class="badge bg-success"><a href="'+window.bscExplorer+'tx/'+userRecord[count].txnhash+'" target=_blank>Signed</a></span></td>';
					} else if ((+userRecord[count].txnhash != '')&&(+userRecord[count].anonymous == true)) {
						html += '<td class="td-text-col5 text-center align-middle"><span class="badge bg-primary">Signed (a)</span></td>';
					} else {
						html += '<td class="td-text-col5 text-center align-middle"><span class="badge bg-danger">Unsigned</span></a></td>';
					}
					html += '</tr>';
					serial_no++;
				}
				document.getElementById('profile_search_results').innerHTML = html;
					dataTableUser = new simpleDatatables.DataTable("#publicUserUploadsTable", {
						searchable: false,
						fixedHeight: false,
						perPage: 50,
						layout: {
							top: "",
							bottom: "{pager}"
						},
						labels: {
							noRows: "No records found",
							info: "Showing {start} to {end} of {rows} records",
						}
					});
					document.getElementById('publicUserUploadsTable').style.display = "table";
			} else {
				document.getElementById('profile_search_results').innerHTML = '<tr><td colspan="5" class="text-center">No Records Found</td></tr>';
			}
		} else if (response.status == 200 && response['data']['status'] == 'ERR' && response['data']['results'] == '') {
			document.getElementById('profile_search_results').innerHTML = '<tr><td colspan="5" class="text-center">No Records Found</td></tr>';
		} else {
			document.getElementById('profile_search_results').innerHTML = '<tr><td colspan="5" class="text-center">Invalid Request 5</td></tr>';
		}
	}).catch(
		function (error) {
			document.getElementById('profile_search_results').innerHTML = '<tr><td colspan="5" class="text-center">Invalid Request 5</td></tr>';
			console.log(error);
		}
	)
};

$(document).ready(function () {
	getPublicProfile();
});

</script>
<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.footer.php');
?>
