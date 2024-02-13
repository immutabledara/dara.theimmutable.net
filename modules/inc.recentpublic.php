<?PHP  ?>
<div class="toprighticon">
	<a class="icon"><i class="bi bi-question-diamond" data-bs-toggle="tooltip" data-bs-placement="left" title="This content is user curated and is NOT moderated by Project DARA"></i></a>
</div>
<?PHP if ($whereAmI == "dashboard"){ echo '<div class="card-title">Recent Public Posts</div>'; } ?>
<div class="card-body">
	<div class="activity" id="latestStoredContent"></div>
</div>
<script type="text/javascript">
	function getPublicRecords(records=10) {
		var html = '';
		var serial_no = 1;
		axios.post( "/backend.php", { request: 'getPublicRecords', limit: records }, { headers: { "Content-Type": "application/json" } })
		.then(function(response) {
			if (response.status == 200 && response['data']['status'] == 'OK') {
				var publicRecord = response['data']['results'];
				if(response['data']['count'] > 0) {
					for(var count = 0; count < publicRecord.length; count++) {
						if ((publicRecord[count].txnhash != null)&&(publicRecord[count].anonymous == false)) {
							activityBadgeBall = 'text-success';
						} else if ((publicRecord[count].txnhash != null)&&(publicRecord[count].anonymous == true)) {
							activityBadgeBall = 'text-primary';
						} else {
							activityBadgeBall = 'text-danger';
						}
						html += '<div class="activity-item d-flex align-middle	">';
//						html += '	<div class="activity-label">'+publicRecord[count].created.replace(' ', '<br>')+'</div>';
						html += '	<i class="bi bi-circle-fill activity-badge '+activityBadgeBall+' align-self-start"></i>';
						html += '	<div class="activity-content">';
						html += '		<a href="#" onClick="showIPFSModal(\''+publicRecord[count].cidhash+'\')">';
						html += '			<span class="maintable-title">'+publicRecord[count].pagename+'</span><br><span class="maintable-cid">'+publicRecord[count].ipfshash+'</span>';
						html += '		</a>';
						html += '	</div>';
						html += '</div>';
						serial_no++;
					}
					document.getElementById('latestStoredContent').innerHTML = html;
				} else {
					document.getElementById('latestStoredContent').innerHTML = '<div class="activity-item d-flex">No records found</div>';
				}
			} else if (response.status == 200 && response['data']['status'] == 'ERR' && response['data']['results'] == '') {
				document.getElementById('latestStoredContent').innerHTML = '<div class="activity-item d-flex">No records found</div>';
			} else {
				document.getElementById('latestStoredContent').innerHTML = '<div class="activity-item d-flex">Invalid Request</div>';
			}
		}).catch(
			function (error) {
				document.getElementById('latestStoredContent').innerHTML = '<div class="activity-item d-flex">'+error+'</div>';
			}
		)
	};
	const numofPublicRecords = window.numofPublicRecords;
	getPublicRecords(numofPublicRecords);
	const interval = setInterval(function() { getPublicRecords(); }, 60000);
	// clearInterval(interval);
</script>
