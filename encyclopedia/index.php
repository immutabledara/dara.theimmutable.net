<?PHP
$whereAmI = "encyclopedia";
$tpl['head.title'] = 'Encyclopedia Archives';
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/gatekeeper.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.htmlheaders.php');
?>
<section class="wiki-background overflow-auto">
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.top.php'); ?>
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.left.php'); ?>
	<main id="main" class="main">
		<div class="pagetitle"><h1>&nbsp;</h1></div>
		<section class="section dashboard">
			<div class="row">
				<!-- Center Stage -->
				<div class="col-lg-10">
					<div class="row">
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.login.php'); ?>
						<div class="card overflow-auto d-none" id="searchMain" name="searchMain">
							<div class="card-body">
								<div id="spinner-row" class="d-none align-middle text-center">
									<div class="d-flex justify-content-center align-items-center">
										<div class="spinner-border mr-2 text-dara" style="width: 3rem; height: 3rem;"></div>
										<span>&nbsp;Loading Records...</span>
									</div>
								</div>
								<table class="table table-hover table-borderless table-responsive datatable" id="searchResultsTable">
									<thead class="d-none">
										<tr>
											<th class="text-left" scope="col" data-sortable="true">Title</th>
											<th class="text-left" scope="col" data-sortable="true">Source</th>
											<th class="text-left" scope="col" data-sortable="true">Latest Snapshot</th>
											<th class="text-center" scope="col" data-sortable="false" data-searchable="false">Download</th>
										</tr>
									</thead>
									<tbody id="local_search_results"></tbody>
								</table>
							</div>
						</div>
						<div class="modal fade" id="storeWPContent" tabindex="-1">
							<div class="modal-dialog modal-dialog-centered modal-xl">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="storeWPContentTitle"></h5><i class="bi bi-x-square" data-bs-dismiss="modal" onclick="$('#storeWPContent').modal('hide');"></i>
									</div>
									<div class="modal-body" id="storeWPContentBodyLog"></div>
									<div class="modal-body" id="storeWPContentBody"></div>
									<div class="modal-footer" id="storeWPContentFooter"></footer>
									</div>
								</div>
							</div>
						</div>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.showipfs.php'); ?>
                  <script type="text/javascript">
                  function showWPIPFS(requestCIDHash=''){
                     var targetModal = document.getElementById('showIPFS');
                     var targetHeader = targetModal.getElementsByClassName('modal-title');
                     var targetBody = targetModal.getElementsByClassName('modal-body');
                     var targetFooter = targetModal.getElementsByClassName('modal-footer');
                     axios.post( "/backend.php", { request: 'getWPIPFSData', qtzCIDHash: requestCIDHash}, { headers: { "Content-Type": "application/json" } })
                     .then(function(response) {
                        if (response.status == 200 && response['data']['status'] == 'OK') {
                           var IPFSModalContent = response['data']['results'][0];
                           targetHeader[0].innerHTML = IPFSModalContent['qtzhead'];
                           targetBody[0].innerHTML = IPFSModalContent['qtzbody'];
                           targetFooter[0].innerHTML = IPFSModalContent['qtzfooter'];
                           $(targetModal).modal('show');
                        } else {
                           targetHeader[0].innerHTML = 'Error';
                           targetBody[0].innerHTML = 'Error: Invalid Request1';
                           $(targetModal).modal('show');
                        }
                     }).catch(
                        function (error) {
                           targetHeader[0].innerHTML = 'Error';
                           targetBody[0].innerHTML = 'Error: Invalid Request2';
                           $(targetModal).modal('show');
                           console.log(error);
                        }
                     )
                  };
                  </script>
						<script type="text/javascript">
							$(document).ready(function () {
								getencyclopediaRecords();
								showSelectors('#searchMain');
							});

							$('#btn-encyclopediaSearch').click(function () {
								window.query = $('#encyclopediaSearch').val();
								getencyclopediaRecords(query);
								showSelectors('#searchMain');
							});
							var input = document.getElementById("encyclopediaSearch");
								input.addEventListener("keypress", function(event) {
								if (event.key === "Enter") {
									event.preventDefault();
									document.getElementById("btn-encyclopediaSearch").click();
								}
							});
							function getencyclopediaRecords(query='') {
								if (typeof dataTableUser !== 'undefined') {
									document.getElementById('searchResultsTable').style.display = "none";
									dataTableUser.destroy();
								}
								if ((query == '')||(query==='undefined')) { query = '#~#' };
								var html = '';
								var serial_no = 1;
								document.getElementById("spinner-row").classList.remove("d-none");
								axios.post( "/backend.php", { request: 'encyclopediaSearch', searchQuery: query }, { headers: { "Content-Type": "application/json" } })
								.then(function(response) {
									if (response.status == 200 && response['data']['status'] == 'OK') {
										var encyclopediaRecord = response['data']['results'];
										if(response['data']['count'] > 0) {
											for(var count = 0; count < encyclopediaRecord.length; count++) {
												html += '<tr>';
												html += '	<td class="td-text-col2 align-middle text-left"><a href='+encyclopediaRecord[count].link+' target=_blank>'+encyclopediaRecord[count].title+'</a></td>';
												html += '	<td class="td-text-col2 align-middle text-left"><a href='+encyclopediaRecord[count].link+' target=_blank>'+encyclopediaRecord[count].source+'</a></td>';
												if (encyclopediaRecord[count].ipfshash != null) {
													html += '	<td class="td-text-col2 align-middle text-left">';
													html += '			<a href="#" onClick="showWPIPFS(\''+encyclopediaRecord[count].cidhash+'\')">'+encyclopediaRecord[count].stored.replaceAll("-", "&#8209;")+'</a>';
													html += '	</td>';
													html += '	<td class="td-text-col2 text-center align-middle">';
													html += '		<a class=\"datatable-hreflinks\" href=\"'+window.ipfsGateway+''+encyclopediaRecord[count].ipfshash+'?download&filename='+encyclopediaRecord[count].zwifile+'\"><i class="bi bi-cloud-arrow-down-fill"></i></a>';
													if (encyclopediaRecord[count].torrenthash != null) {
														html += '		<a class=\"datatable-hreflinks\" href="magnet:?xt=urn:btih:'+encyclopediaRecord[count].torrenthash+'&dn='+encyclopediaRecord[count].zwifile+'&tr=ws%3A%2F%2F154.27.88.225%3A8000%2Cwss%3A%2F%2Ftracker.btorrent.xyz%2Cwss%3A%2F%2Ftracker.openwebtorrent.com" target="_blank"><i class="bi bi-magnet-fill"></i></a>';

													}
													html += '		'+(encyclopediaRecord[count].filesize / (1024*1024)).toFixed(2)+' MB</td>';
												}else{
													html += '	<td class="td-text-col1 align-middle text-left"><span class="maintable-title"><a href="#" onClick="genZWIFile(\''+encyclopediaRecord[count].cidhash+'\', \''+encodeURI(encyclopediaRecord[count].title)+'\', \''+encodeURI(encyclopediaRecord[count].link)+'\')">Fetch</a></span></td>';
													html += '	<td class="td-text-col1 text-center align-middle">N/A</td>';
												}
												html += '</tr>';
												serial_no++;
											}
											document.getElementById('local_search_results').innerHTML = html;
												dataTableUser = new simpleDatatables.DataTable("#searchResultsTable", {
													columns: [
														{
															select: 3,
															type: "date",
															format: "YYYY-MM-DD hh:mm:ss.nnnnnn"
														}
													],
													searchable: true,
													fixedHeight: false,
													perPage: 15,
													perPageSelect: [15,25,50],
													layout: {
														top: "{info}{search}",
														bottom: "{select}{pager}"
													},
													labels: {
														placeholder: "Search within results..",
														perPage: "records per page",
														noRows: "No records found",
														info: "Showing {start} to {end} of {rows} recently saved articles",
													}
												});

												function cleanupAmpersands(){
													var elements = document.getElementsByClassName("datatable-hreflinks");
													for (var i = 0, l = elements.length; i < l; i++) {
													  elements[i].href = elements[i].href.replace(/&amp;/g, '&');
													}
												}
												dataTableUser.on('datatable.init', function() { cleanupAmpersands(); });
												dataTableUser.on('datatable.page', function() { cleanupAmpersands(); });
												dataTableUser.on('datatable.perpage', function() { cleanupAmpersands(); });
												dataTableUser.on('datatable.refresh', function() { cleanupAmpersands(); });
												dataTableUser.on('datatable.selectrow', function() { cleanupAmpersands(); });
												dataTableUser.on('datatable.search', function() { cleanupAmpersands(); });
												dataTableUser.on('datatable.sort', function() { cleanupAmpersands(); });
												dataTableUser.on('datatable.update', function() { cleanupAmpersands(); });
												document.getElementById('searchResultsTable').style.display = "table";
												document.getElementById("spinner-row").classList.add("d-none");
										} else {
											document.getElementById("spinner-row").classList.add("d-none");
											document.getElementById('local_search_results').innerHTML = '<tr><td colspan="5" class="text-center">No Records Found</td></tr>';
											document.getElementById('searchResultsTable').style.display = "table";
										}
									} else if (response.status == 200 && response['data']['status'] == 'ERR' && response['data']['results'] == '') {
										document.getElementById("spinner-row").classList.add("d-none");
										document.getElementById('local_search_results').innerHTML = '<tr><td colspan="5" class="text-center">No Records Found</td></tr>';
										document.getElementById('searchResultsTable').style.display = "table";
									} else {
										document.getElementById("spinner-row").classList.add("d-none");
										document.getElementById('local_search_results').innerHTML = '<tr><td colspan="5" class="text-center">Invalid Request 3</td></tr>';
										document.getElementById('searchResultsTable').style.display = "table";
									}
								}).catch(
									function (error) {
										document.getElementById("spinner-row").classList.add("d-none");
										document.getElementById('local_search_results').innerHTML = '<tr><td colspan="5" class="text-center">'+error+'</td></tr>';
										document.getElementById('searchResultsTable').style.display = "table";
									}
								)
							};
							function genZWIFile(cidhash='', cidtitle='', cidlink=''){
								var modaltitle='Storing snapshot of [<a href="'+cidlink+'" target="_blank">'+decodeURI(cidtitle)+'</a>]';
								updateAndShowSelector('#storeWPContentTitle', modaltitle);
								updateAndShowSelector('#storeWPContentBody', '<div class="text-center"><strong>Please Wait</strong><br><div class="spinner-border ml-auto" role="status" aria-hidden="true"></div></div>');
								clearAndHideSelectors('#storeWPContentBodyLog');
								$("#storeWPContent").modal('show');
								axios.post( "/backend.php", { request: 'genZWIFile', cidhash: cidhash}, { headers: { "Content-Type": "application/json" } })
								.then(function(response) {
									if (response.status == 200 && response['data']['status'] == 'OK') {
										var IPFSModalContent = response['data']['results'][0];
										clearAndHideSelectors('#storeWPContentBody');
										updateSelector('#storeWPContentTitle', modaltitle.replace('Storing', 'Successfully stored'));
										updateAndShowSelector('#storeWPContentBodyLog', IPFSModalContent['qtzlog']);
										axios.post( "/backend.php", { request: 'storeZWIIPFS', cidhash: cidhash}, { headers: { "Content-Type": "application/json" } })
											.then(function(response) {
												if (response.status == 200 && response['data']['status'] == 'OK') {
													var ipfsHash = response['data']['hash'];
													var torrenthash = response['data']['torrenthash'];
													var filename = response['data']['filename'];
													updateAndShowSelector('#storeWPContentBody', '<b>Stored IPFS with Hash:</b> <a href="'+window.ipfsGateway+''+ipfsHash+'" target="_blank"><span class="maintable-cid" style="color: #39e981; text-decoration: underline;">'+ipfsHash+'</a><br><b>Torrent Hash:</b> '+torrenthash+'');
													getencyclopediaRecords(window.query);
												} else {
													console.log(response['data']);
													updateSelector('#storeWPContentTitle', 'Error');
													updateAndShowSelector('#storeWPContentBody', 'Failed to store IPFS Record');
												}
											}).catch(
												function (ipfserror) {
													updateSelector('#storeWPContentTitle', 'Error');
													updateAndShowSelector('#storeWPContentBody', 'Error: Invalid Request2');
													$("#storeWPContent").modal('show');
													console.log(ipfserror);
												}
											)
									} else if (response.status == 200 && response['data']['status'] == 'WARN') {
										var IPFSModalContent = response['data']['results'][0];
										clearAndHideSelectors('#storeWPContentBody');
										updateSelector('#storeWPContentTitle', modaltitle.replace('Storing', 'Failed to store'));
										updateAndShowSelector('#storeWPContentBodyLog', IPFSModalContent['qtzlog']);
										console.log(response['data']);
									} else {
										clearAndHideSelectors('#storeWPContentBody');
										updateSelector('#storeWPContentTitle', 'Error');
										updateSelector('#storeWPContentBodyLog', 'Error: Invalid Request1');
										$("#storeWPContent").modal('show');
									}
								}).catch(
									function (error) {
										updateSelector('#storeWPContentTitle', 'Error');
										updateSelector('#storeWPContentBody', 'Error: Invalid Request2');
										$("#storeWPContent").modal('show');
										console.log(error);
									}
								)
							};
						</script>
					</div>
				</div>
			</div>
		</section>
	</main>
<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.footer.php');
if(isset($_GET['s'])) {
	$wikirequest=$_GET['s'];
	//error_log($wikirequest);
	echo '<script type="text/javascript">document.getElementById("encyclopediaSearch").value = "'.$wikirequest.'"; document.getElementById("btn-encyclopediaSearch").click();</script>';
}
?>
