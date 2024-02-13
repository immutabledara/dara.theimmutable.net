<div class="card recent-uploads overflow-auto">
	<div class="card-body">
		<div id="spinner-row" class="d-none align-middle text-center">
			<div class="d-flex justify-content-center align-items-center">
				<div class="spinner-border mr-2 text-dara" style="width: 3rem; height: 3rem;"></div>
				<span>&nbsp;Loading Records...</span>
			</div>
		</div>
		<table class="table table-hover table-borderless table-responsive datatable" id="userUploadsTable">
			<thead class="d-none">
				<tr>
					<th class="text-left" scope="col">Date</th>
					<th class="text-left" scope="col">Details</th>
					<th class="td-text-col3 text-center" scope="col">Source</th>
					<th class="text-center" scope="col">Public</th>
					<th class="text-center" scope="col">Status</th>
				</tr>
			</thead>
			<tbody id="local_search_results"></tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
	function getUserRecords(currentTablePage=1) {
	if (typeof dataTableUser !== 'undefined') {
		document.getElementById('userUploadsTable').style.display = "none";
		dataTableUser.destroy();
	}
	var html = '';
	var serial_no = 1;
	document.getElementById("spinner-row").classList.remove("d-none");
	axios.post( "/backend.php", { request: 'getUserRecords' }, { headers: { "Content-Type": "application/json" } })
	.then(function(response) {
		if (response.status == 200 && response['data']['status'] == 'OK') {
			var userRecord = response['data']['results'];
			if(response['data']['count'] > 0) {
                for(var count = 0; count < userRecord.length; count++) {
                    html += '<tr id="'+userRecord[count].cidhash+'">';
					html += '	<td class="td-text-col1 align-middle text-left">'+userRecord[count].created.replaceAll("-", "&#8209;")+'</td>';
					html += '	<td class="td-text-col2 align-middle text-left">';
					html += '		<a href="#" onClick="showIPFSModal(\''+userRecord[count].cidhash+'\')">';
					html += '			<span class="maintable-title">'+userRecord[count].pagename+'</span><br><span class="maintable-cid">'+userRecord[count].ipfshash+'';
					html += '		</a>';
					html += '	</td>';
					if (userRecord[count].contentsource == 'website') {
						html += '	<td class="td-text-col3 text-center align-middle">Direct Upload</td>';
					} else if (userRecord[count].contentsource == 'zwifileupload') {
						html += '	<td class="td-text-col3 text-center align-middle">Direct ZWI Upload</td>';
					} else if (userRecord[count].contentsource == 'zwiurlupload') {
						html += '	<td class="td-text-col3 text-center align-middle"><a href='+userRecord[count].pageurl+' target=_blank>Encycloreader</a></td>';
					} else {
						html += '	<td class="td-text-col3 text-center align-middle"><a href='+userRecord[count].pageurl+' target=_blank>View</a></td>';
					}
					if ((+userRecord[count].ispublic != '')&&(+userRecord[count].ispublic == true)) {
						html += '<td class="td-text-col4 text-center align-middle"><div class="form-check form-switch"><input class="form-check-input float-none" type="checkbox" name="toggleRecordBox" data-public_record=TRUE onchange="toggleState(\''+userRecord[count].cidhash+'\')" value="yes" checked></div></td>';
					}else{
						html += '<td class="td-text-col4 text-center align-middle"><div class="form-check form-switch"><input class="form-check-input float-none" type="checkbox" name="toggleRecordBox" onchange="toggleState(\''+userRecord[count].cidhash+'\')" value="no"></div></td>';
					}
					if ((+userRecord[count].txnhash != '')&&(+userRecord[count].anonymous == false)) {
						html += '<td class="td-text-col5 text-center align-middle"><span class="badge bg-success"><a href="'+window.bscExplorer+'tx/'+userRecord[count].txnhash+'" target=_blank>Signed</a></span></td>';
					} else if ((+userRecord[count].txnhash != '')&&(+userRecord[count].anonymous == true)) {
						html += '<td class="td-text-col5 text-center align-middle"><span class="badge bg-primary"><a href="'+window.bscExplorer+'tx/'+userRecord[count].txnhash+'" target=_blank>Signed (a)</a></span></td>';
					} else {
						html += '<td class="td-text-col5 text-center align-middle"><a href="#" data-bs-toggle="modal" data-bs-target="#manualSignature" onClick="manualSign(\''+userRecord[count].cidhash+'\',\''+userRecord[count].ipfshash+'\')"><span class="badge bg-danger">Unsigned</span></a></td>';
					}
					html += '</tr>';
					serial_no++;
				}
				document.getElementById('local_search_results').innerHTML = html;
					dataTableUser = new simpleDatatables.DataTable("#userUploadsTable", {
						searchable: true,
						fixedHeight: false,
						perPage: 10,
						perPageSelect: [10,25,50,100],
						layout: {
							top: "{info}{search}",
							bottom: "{select}{pager}"
						},
						labels: {
							placeholder: "Search your uploads..",
							perPage: "records per page",
							noRows: "No records found",
							info: "Showing {start} to {end} of {rows} records",
						}
					});
					dataTableUser.page(currentTablePage);
					document.getElementById("spinner-row").classList.add("d-none");
					document.getElementById('userUploadsTable').style.display = "table";
			} else {
				document.getElementById("spinner-row").classList.add("d-none");
				document.getElementById('local_search_results').innerHTML = '<tr><td colspan="5" class="text-center">No Records Found</td></tr>';
				document.getElementById('userUploadsTable').style.display = "table";
			}
		} else if (response.status == 200 && response['data']['status'] == 'ERR' && response['data']['results'] == '') {
			document.getElementById("spinner-row").classList.add("d-none");
			document.getElementById('local_search_results').innerHTML = '<tr><td colspan="5" class="text-center">No Records Found</td></tr>';
			document.getElementById('userUploadsTable').style.display = "table";
		} else {
			document.getElementById("spinner-row").classList.add("d-none");
			document.getElementById('local_search_results').innerHTML = '<tr><td colspan="5" class="text-center">Invalid Request 3</td></tr>';
			document.getElementById('userUploadsTable').style.display = "table";
		}
	}).catch(
		function (error) {
			document.getElementById("spinner-row").classList.add("d-none");
			document.getElementById('local_search_results').innerHTML = '<tr><td colspan="5" class="text-center">'+error+'</td></tr>';
			document.getElementById('userUploadsTable').style.display = "table";
		}
	)
	};
	
	function toggleState(toggleHash) {
		axios.post( "/backend.php", { request: 'togglePublic', cidHash: toggleHash }, { headers: { "Content-Type": "application/json" } })
		.then(function(response) {
			if (response.status == 200 && response['data']['status'] == 'OK') {
				window.currentTablePage = dataTableUser._currentPage;
				getUserRecords(window.currentTablePage);
				getPublicRecords(numofPublicRecords);
			}
		});
	}
	
	function manualSign(cidhash, ipfsHash){
			if (window.currentCreditBalance >= <?= $_SESSION['daraTxnFee'] ?>) {
			document.getElementById('signHashButton').disabled = false;
			document.getElementById('signHashButtonAnon').disabled = false;
		} else {
			document.getElementById('signHashButtonBottom').innerHTML = '<span style="color: red;">Insufficient Credits to sign transaction</span>';
		}
		document.getElementById('signHashButtonTop').innerHTML = 'Signing IPFS Hash <a href="#" class=\'link-warning\' onClick="showIPFSModal(\''+cidhash+'\')">'+ipfsHash+'';
		ipfsUploadedHash=ipfsHash;
	}
	getUserRecords(window.currentTablePage);
</script>
