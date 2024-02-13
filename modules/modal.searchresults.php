<div class='modal fade' id='DARASearchResults' tabindex='-1'>
	<div class='modal-dialog modal-dialog-centered modal-xl'>
		<div class='modal-content'>
			<div class='modal-header'>
				<h5 class='modal-title'>Search Results</h5>
				<i class="bi bi-x-square" data-bs-dismiss="modal"></i>
			</div>
			<div class='modal-body'>
				<table class="table table-hover table-borderless table-responsive datatable" id="daraSearchTable">
					<thead>
						<tr>
							<th class="text-left" scope="col">Date</th>
							<th class="text-left" scope="col">Details</th>
							<th class="text-center" scope="col">Signature</th>
						</tr>
					</thead>
					<tbody id="global_search_results"></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$('#DARASearchResults').modal({ backdrop: 'static', keyboard: true });
	});
	var input = document.getElementById("publicDARASearch");
		input.addEventListener("keypress", function(event) {
		if (event.key === "Enter") {
			event.preventDefault();
			document.getElementById("btn-publicDARASearch").click();
		}
	});

	function searchPublicRecords() {
	if (typeof dataTable !== 'undefined') {
		//dataTable.destroy();
	}
	$("#DARASearchResults").modal('show');
	var query = $('#publicDARASearch').val();
	var html = '';
	var serial_no = 1;
	axios.post( "/backend.php", { request: 'searchPublicRecords', searchQuery: query }, { headers: { "Content-Type": "application/json" } })
	.then(function(response) {
		if (response.status == 200 && response['data']['status'] == 'OK') {
			var publicRecord = response['data']['results'];
			if(response['data']['count'] > 0) {
				for(var count = 0; count < publicRecord.length; count++) {
					html += '<tr>';
					html += '	<td class="td-text-col1 text-left align-middle">'+publicRecord[count].created+'</td>';
					html += '	<td class="td-text-col2 text-left align-middle">';
					html += '		<a href="#" onClick="showIPFSModal(\''+publicRecord[count].cidhash+'\')">';
					html += '			<span class="maintable-title">'+publicRecord[count].pagename+'</span><br><span class="maintable-cid">'+publicRecord[count].ipfshash+'';
					html += '		</a>';
					if ((publicRecord[count].txnhash != '' )&&(publicRecord[count].anonymous != true )&&(publicRecord[count].walletaddress != '')) {
						html += '<br>Signed by: '+publicRecord[count].walletaddress+'';
					}else if ((publicRecord[count].txnhash != '' )&&(publicRecord[count].anonymous == true )&&(publicRecord[count].walletaddress == '')) {
						html += '<br>Signed anonymously';
					}
					html += '</a></td>';
					if ((publicRecord[count].txnhash != null )&&(publicRecord[count].walletaddress != '')) {
						html += '<td class="td-text-col3 text-center align-middle"><span class="badge bg-success"><a target="_blank" href="'+window.bscExplorer+'tx/'+publicRecord[count].txnhash+'">Signed</a></span></td>';
					}else if ((publicRecord[count].txnhash != null )&&(publicRecord[count].walletaddress == '')) {
						html += '<td class="td-text-col3 text-center align-middle"><span class="badge bg-primary"><a target="_blank" href="'+window.bscExplorer+'tx/'+publicRecord[count].txnhash+'">Signed (a)</a></span></td>';
					}else {
						html += '<td class="td-text-col3 text-center align-middle"><span class="badge bg-danger">unsigned</span></td>';
					}
					html += '</tr>';
					serial_no++;
				}
				document.getElementById('global_search_results').innerHTML = html;
				dataTableSearch = new simpleDatatables.DataTable("#daraSearchTable", {
					searchable: true,
					fixedHeight: false,
					perPage: 10,
					perPageSelect: [5,10,25,50],
					labels: {
						placeholder: "Search within results..",
						perPage: "records per page",
						noRows: "No records found",
						info: "Showing {start} to {end} of {rows} records",
					}
				});
			} else {
				document.getElementById('global_search_results').innerHTML = '<tr><td colspan="4" class="text-center">No Records Found</td></tr>';
			}
		} else if (response.status == 200 && response['data']['status'] == 'ERR' && response['data']['results'] == '') {
			document.getElementById('global_search_results').innerHTML = '<tr><td colspan="4" class="text-center">No Records Found</td></tr>';
		} else {
			document.getElementById('global_search_results').innerHTML = '<tr><td colspan="4" class="text-center">Invalid Request 3</td></tr>';
		}
	}).catch(
		function (error) {
			document.getElementById('global_search_results').innerHTML = '<tr><td colspan="4" class="text-center">'+error+'</td></tr>';
		}
	)
	};
</script>
