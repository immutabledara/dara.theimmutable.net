<?PHP
$whereAmI = "deathrow";
$tpl['head.title'] = 'Wikipedia Deathrow';
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/gatekeeper.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.htmlheaders.php');

# Cron job creates directory with date, runs this
# curl -s "https://en.wikipedia.org/wiki/Wikipedia:Articles_for_deletion/Log/Yesterday" | tr '">' '">\n' | grep vector-toc-link | sed -e 's/.*href="#\(.*\)">.*/\1/' | grep -v vector-toc-link | while read line; do python3 zwi_mediawiki.py -s wikipedia -t "$line"; done
# PHP script checks directory contents and imports the ZWI files with their dates and Source, and status (Nominated)
# Cron job checks if the articles were deleted and updates the status.

?>
<section class="wiki-background overflow-auto">
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.top.php'); ?>
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.left.php'); ?>
	<main id="main" class="main">
		<div class="pagetitle"><h1>&nbsp;</h1></div>
		<section class="section dashboard">
			<div class="row">
				<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.login.php'); ?>
				<!-- Center Stage -->
				<div class="col-lg-10">
					<div class="row">
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
											<th class="text-left" scope="col" data-sortable="true">Status</th>
											<th class="text-left" scope="col" data-sortable="true">Save Date (click to view)</th>
											<th class="text-center" scope="col" data-searchable="false">Download (ZWI)</th>
										</tr>
									</thead>
									<tbody id="local_search_results"></tbody>
								</table>
							</div>
						</div>
						<div class="modal fade" id="showIPFS" tabindex="-1">
							<div class="modal-dialog modal-dialog-centered modal-xxl">
								<div class="modal-content embeded-ipfs">
									<div class="modal-header"><h6 class="modal-title"></h6><i class="bi bi-x-square" data-bs-dismiss="modal"></i></div>
									<div class="modal-body"></div>
									<div class="modal-footer justify-content-xl-between"></footer>
									</div>
								</div>
							</div>
						</div>
						<script type="text/javascript">
						function showWPIPFS(requestCIDHash=''){
							var targetModal = document.getElementById('showIPFS');
							var targetHeader = targetModal.getElementsByClassName('modal-title');
							var targetBody = targetModal.getElementsByClassName('modal-body');
							var targetFooter = targetModal.getElementsByClassName('modal-footer');
							axios.post( "/backend.php", { request: 'getDRIPFSData', qtzCIDHash: requestCIDHash}, { headers: { "Content-Type": "application/json" } })
							.then(function(response) {
								console.log(response);
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
								getdeathrowRecords();
								showSelectors('#searchMain');
							});
							/*
							$('#btn-deathrowSearch').click(function () {
								window.query = $('#deathrowSearch').val();
								getencyclopediaRecords(query);
								showSelectors('#searchMain');
							});
							var input = document.getElementById("deathrowSearch");
								input.addEventListener("keypress", function(event) {
								if (event.key === "Enter") {
									event.preventDefault();
									document.getElementById("btn-deathrowSearch").click();
								}
							});
							*/
							function getdeathrowRecords(query='') {
								if (typeof dataTableUser !== 'undefined') {
									document.getElementById('searchResultsTable').style.display = "none";
									dataTableUser.destroy();
								}
								if ((query == '')||(query==='undefined')) { query = '' };
								var html = '';
								var serial_no = 1;
								document.getElementById("spinner-row").classList.remove("d-none");
								axios.post( "/backend.php", { request: 'deathrowSearch', searchQuery: query }, { headers: { "Content-Type": "application/json" } })
								.then(function(response) {
									if (response.status == 200 && response['data']['status'] == 'OK') {
										var deathrowRecord = response['data']['results'];
										if(response['data']['count'] > 0) {
											for(var count = 0; count < deathrowRecord.length; count++) {
												deathrowRecord[count].talk = deathrowRecord[count].link.replace('wikipedia.org/wiki/','wikipedia.org/wiki/Wikipedia:Articles_for_deletion/');
												if (deathrowRecord[count].status == 'Deleted') {
													deathrowRecord[count].deleted = 'class="text-danger"';
												}else{
													deathrowRecord[count].deleted = '';
												}
												html += '<tr>';
												html += '	<td class="td-text-col2 align-middle text-left"><a href='+deathrowRecord[count].link+' '+deathrowRecord[count].deleted+' target=_blank>'+deathrowRecord[count].title+'</a></td>';
												html += '	<td class="td-text-col2 align-middle text-left"><a href='+deathrowRecord[count].talk+' '+deathrowRecord[count].deleted+' target=_blank>'+deathrowRecord[count].status+'</a></td>';
												html += '	<td class="td-text-col2 align-middle text-left">';
												html += '         <a href="'+window.ipfsGateway+''+deathrowRecord[count].ipfshash+'" onClick="showWPIPFS(\''+deathrowRecord[count].cidhash+'\'); return false;" '+deathrowRecord[count].deleted+'>'+deathrowRecord[count].created.replaceAll("-", "&#8209;")+'</a>';
												html += '	</td>';
												html += '	<td class="td-text-col2 text-center align-middle">';
												html += '		<a class="datatable-hreflinks" href="'+window.ipfsGateway+''+deathrowRecord[count].ipfshash+'?download&filename='+deathrowRecord[count].zwifile+'"><i class="bi bi-cloud-arrow-down-fill"></i></a>';
												if (deathrowRecord[count].torrenthash != null) {
													html += '		<a class="datatable-hreflinks" href="magnet:?xt=urn:btih:'+deathrowRecord[count].torrenthash+'&dn='+deathrowRecord[count].zwifile+'&tr=ws%3A%2F%2F154.27.88.225%3A8000%2Cwss%3A%2F%2Ftracker.btorrent.xyz%2Cwss%3A%2F%2Ftracker.openwebtorrent.com" target="_blank"><i class="bi bi-magnet-fill"></i></a>';
												}
												html += '		'+(deathrowRecord[count].filesize / (1024*1024)).toFixed(2)+' MB</td>';
												html += '</tr>';
												serial_no++;
											}
											document.getElementById('local_search_results').innerHTML = html;
												dataTableUser = new simpleDatatables.DataTable("#searchResultsTable", {
													searchable: true,
													fixedHeight: false,
													perPage: 15,
													perPageSelect: [15,25,50,100],
													layout: {
														top: "{info}{search}",
														bottom: "{select}{pager}"
													},
													labels: {
														placeholder: "Search within results..",
														perPage: "records per page",
														noRows: "No records found",
														info: "Showing {start} to {end} of {rows} records",
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
	echo '<script type="text/javascript">document.getElementById("deathrowSearch").value = "'.$wikirequest.'"; document.getElementById("btn-deathrowSearch").click();</script>';
}
?>
