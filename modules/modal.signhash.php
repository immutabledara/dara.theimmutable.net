<div class="modal fade" id="manualSignature" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Sign stored DARA Content</h5>
				<i class="bi bi-x-square" data-bs-dismiss="modal"></i>
			</div>
			<div class="modal-body">
				<div class="container" id='signHash'>
					<span id='signHashButtonTop'></span><br>
					<button type='button' id='signHashButton' name='signHashButton' class='btn btn-sm btn-dara btn-outline-dark' disabled>Store Hash on Blockchain</button>&nbsp;
					<?php
						$stmt = $conn->prepare("SELECT \"isDefaultAnon\" FROM ".$conf['db_tableUsers']." WHERE LOWER(walletaddress) = ? LIMIT 1");
						$stmt->bindParam(1, $_SESSION['walletAddress']);
						$stmt->execute();
						$isAnon=(bool)$stmt->fetchColumn();
						if ($isAnon==true){ 
							$anoncheck="checked"; 
						} else { 
							$anoncheck=""; 
						}
					?>
					<input type='checkbox' id='signHashButtonAnon' name='signHashButtonAnon' disabled <?=$anoncheck?>>
					<label class='supertiny'> Sign anonymously</label><br>
					<span id='signHashButtonBottom'></span>
				</div>
			</div>
		</div>
	</div>
</div>