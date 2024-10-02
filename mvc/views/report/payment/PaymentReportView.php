<div class="row">
	<div class="col-sm-12" style="margin:10px 0px">
		<?php
		echo btn_printReport('onlineexampaymentreport', $this->lang->line('report_print'), 'printablediv');
		echo btn_pdfPreviewReport('onlineexampaymentreport',  base_url('onlineexampaymentreport/pdf/' . $onlineexamID . '/' . $studentID . '/' . $sectionID . '/' . $classesID), $this->lang->line('report_pdf_preview'));
		echo btn_sentToMailReport('onlineexampaymentreport', $this->lang->line('report_send_pdf_to_mail'));
		?>
	</div>
</div>
<div id="printablediv">
	<div class="box">
		<div class="box-header bg-gray">
			<h3 class="box-title text-navy"><i class="fa fa-clipboard"></i> <?= $this->lang->line('onlineexamreport_report_for') ?> <?= $this->lang->line('onlineexamreport_payment_history') ?></h3>
			<h3 class="box-title text-navy float-right" style="float: right;"> <?= $this->lang->line('onlineexamreport_exam') ?>: <?= ucfirst($onlineexam) ?></h3>
		</div><!-- /.box-header -->
		<div class="box-body">
			<div class="row">
			<div class="col-sm-12" style="margin-bottom: 25px;">
				<?= reportheader($siteinfos) ?>
			</div>
				<div class="col-sm-12">
					<h5 class="pull-left"><?= $this->lang->line('onlineexamreport_classes'); ?> : <?= inicompute($classes) ? $classes->classes : $this->lang->line('onlineexamreport_select_all_classes') ?></h5>
					<h5 class="pull-right"><?= $this->lang->line('onlineexamreport_section') ?> : <?= inicompute($section) ? $section->section : '' ?></h5>
				</div>

				<div class="col-sm-12">
					<?php if (inicompute($paymentreports)) { ?>
						<div id="hide-table">
							<table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
								<thead>
									<tr>
										<th>#</th>
										<th><?= $this->lang->line('onlineexamreport_photo') ?></th>
										<th><?= $this->lang->line('onlineexamreport_name') ?></th>
										<?php if ($sectionID == 0) { ?>
											<th><?= $this->lang->line('onlineexamreport_section') ?></th>
										<?php } ?>

										<th><?= $this->lang->line('onlineexampaymentreport_amount') ?></th>
										<th><?= $this->lang->line('onlineexampaymentreport_method') ?></th>
										<th><?= $this->lang->line('onlineexampaymentreport_date') ?></th>
										<th><?= $this->lang->line('onlineexampaymentreport_transaction') ?></th>
									</tr>
								</thead>

								<?php $i = 1;
								foreach ($paymentreports as $paymentreport) { ?>
									<tr>
										<td data-title="#">
											<?php echo $i; ?>
										</td>

										<td data-title="<?= $this->lang->line('onlineexamreport_photo') ?>">
											<?php
											if (isset($students[$paymentreport->userID])) {
												$array = array(
													"src" => base_url('uploads/images/' . $students[$paymentreport->userID]->photo),
													'width' => '35px',
													'height' => '35px',
													'class' => 'img-rounded'
												);
											} else {
												$array = array(
													"src" => base_url('uploads/images/default.png'),
													'width' => '35px',
													'height' => '35px',
													'class' => 'img-rounded'
												);
											}
											echo img($array);
											?>
										</td>

										<td data-title="<?= $this->lang->line('onlineexamreport_name') ?>">
											<?= isset($students[$paymentreport->userID]) ? $students[$paymentreport->userID]->name : '' ?>
										</td>

										<?php if ($sectionID == 0) { ?>
											<td data-title="<?= $this->lang->line('onlineexamreport_section') ?>">
												<?php
												if (isset($students[$paymentreport->userID])) {
													if (isset($sections[$students[$paymentreport->userID]->sectionID])) {
														echo $sections[$students[$paymentreport->userID]->sectionID]->section;
													}
												}
												?>
											</td>
										<?php } ?>


										<td data-title="<?= $this->lang->line('onlineexamreport_datetime') ?>">
											<?= number_format($paymentreport->paymentamount, 2) ?>
										</td>
										<td data-title="<?= $this->lang->line('onlineexamreport_datetime') ?>">
											<?= ucfirst($paymentreport->paymentmethod) ?>
										</td>
										<td data-title="<?= $this->lang->line('onlineexamreport_datetime') ?>">
											<?= date('d M Y', strtotime($paymentreport->paymentdate)) ?>
										</td>

										<td data-title="<?= $this->lang->line('onlineexamreport_obtained_mark') ?>">
											<?= $paymentreport->transactionID ?>
										</td>
									</tr>
								<?php $i++;
								} ?>


							</table>
						</div>
					<?php } else { ?>
						<div class="callout callout-danger">
							<p><b class="text-info"><?= $this->lang->line('onlineexamreport_data_not_found') ?></b></p>
						</div>
					<?php } ?>

				</div>
				<div class="col-sm-12 text-center footerAll" style="margin-bottom: 25px;">
					<?= reportfooter($siteinfos) ?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- email modal starts here -->
<form class="form-horizontal" role="form" action="<?= base_url('onlineexampaymentreport/send_pdf_to_mail'); ?>" method="post">
	<div class="modal fade" id="mail">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?= $this->lang->line('onlineexamreport_close') ?></span></button>
					<h4 class="modal-title"><?= $this->lang->line('onlineexamreport_mail') ?></h4>
				</div>
				<div class="modal-body">
					<?php
					if (form_error('to'))
						echo "<div class='form-group has-error' >";
					else
						echo "<div class='form-group' >";
					?>
					<label for="to" class="col-sm-2 control-label">
						<?= $this->lang->line("onlineexamreport_to") ?> <span class="text-red">*</span>
					</label>
					<div class="col-sm-6">
						<input type="email" class="form-control" id="to" name="to" value="<?= set_value('to') ?>">
					</div>
					<span class="col-sm-4 control-label" id="to_error">
					</span>
				</div>

				<?php
				if (form_error('subject'))
					echo "<div class='form-group has-error' >";
				else
					echo "<div class='form-group' >";
				?>
				<label for="subject" class="col-sm-2 control-label">
					<?= $this->lang->line("onlineexamreport_subject") ?> <span class="text-red">*</span>
				</label>
				<div class="col-sm-6">
					<input type="text" class="form-control" id="subject" name="subject" value="<?= set_value('subject') ?>">
				</div>
				<span class="col-sm-4 control-label" id="subject_error">
				</span>

			</div>

			<?php
			if (form_error('message'))
				echo "<div class='form-group has-error' >";
			else
				echo "<div class='form-group' >";
			?>
			<label for="message" class="col-sm-2 control-label">
				<?= $this->lang->line("onlineexamreport_message") ?>
			</label>
			<div class="col-sm-6">
				<textarea class="form-control" id="message" style="resize: vertical;" name="message" value="<?= set_value('message') ?>"></textarea>
			</div>
		</div>


	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?= $this->lang->line('close') ?></button>
		<input type="button" id="send_pdf" class="btn btn-success" value="<?= $this->lang->line("onlineexamreport_send") ?>" />
	</div>
	</div>
	</div>
	</div>
</form>
<!-- email end here -->

<script>
	function check_email(email) {
		var status = false;
		var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
		if (email.search(emailRegEx) == -1) {
			$("#to_error").html('');
			$("#to_error").html("<?= $this->lang->line('onlineexamreport_mail_valid') ?>").css("text-align", "left").css("color", 'red');
		} else {
			status = true;
		}
		return status;
	}


	$('#send_pdf').click(function() {
		var field = {
			'to': $('#to').val(),
			'subject': $('#subject').val(),
			'message': $('#message').val(),
			'onlineexamID': "<?= inicompute($onlineexamID) ? $onlineexamID : 0; ?>",
			'studentID': "<?= inicompute($studentID) ? $studentID : 0; ?>",
			'sectionID': "<?= inicompute($sectionID) ? $sectionID : 0; ?>",
			'classesID': "<?= inicompute($classesID) ? $classesID : 0; ?>",
		};
		var to = $('#to').val();
		var subject = $('#subject').val();
		var error = 0;

		$("#to_error").html("");
		$("#subject_error").html("");
		if (to == "" || to == null) {
			error++;
			$("#to_error").html("<?= $this->lang->line('onlineexamreport_mail_to') ?>").css("text-align", "left").css("color", 'red');
		} else {
			if (check_email(to) == false) {
				error++
			}
		}

		if (subject == "" || subject == null) {
			error++;
			$("#subject_error").html("<?= $this->lang->line('onlineexamreport_mail_subject') ?>").css("text-align", "left").css("color", 'red');
		} else {
			$("#subject_error").html("");
		}

		if (error == 0) {
			$('#send_pdf').attr('disabled', 'disabled');
			$.ajax({
				type: 'POST',
				url: "<?= base_url('onlineexampaymentreport/send_pdf_to_mail') ?>",
				data: field,
				dataType: "html",
				success: function(data) {
					var response = JSON.parse(data);
					if (response.status == false) {
						$('#send_pdf').removeAttr('disabled');
						$.each(response, function(index, value) {
							if (index != 'status') {
								toastr["error"](value)
								toastr.options = {
									"closeButton": true,
									"debug": false,
									"newestOnTop": false,
									"progressBar": false,
									"positionClass": "toast-top-right",
									"preventDuplicates": false,
									"onclick": null,
									"showDuration": "500",
									"hideDuration": "500",
									"timeOut": "5000",
									"extendedTimeOut": "1000",
									"showEasing": "swing",
									"hideEasing": "linear",
									"showMethod": "fadeIn",
									"hideMethod": "fadeOut"
								}
							}
						});
					} else {
						location.reload();
					}
				}
			});
		}
	});
</script>