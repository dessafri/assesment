<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-slideshare"></i> <?= $this->lang->line('panel_title') ?></h3>
        <ol class="breadcrumb">
            <li><a href="<?= base_url("dashboard/index") ?>"><i class="fa fa-laptop"></i> <?= $this->lang->line('menu_dashboard') ?></a></li>
            <li class="active"><?= $this->lang->line('panel_title') ?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <?php
                if (permissionChecker('online_exam_add')) {
                ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('online_exam/add') ?>">
                            <i class="fa fa-plus"></i>
                            <?= $this->lang->line('add_title') ?>
                        </a>
                    </h5>
                <?php } ?>
                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-sm-0"><?= $this->lang->line('slno') ?></th>
                                <th class="col-sm-2"><?= $this->lang->line('online_exam_name') ?></th>
                                <th class="col-sm-2"><?= $this->lang->line('online_exam_payment_status') ?></th>
                                <th class="col-sm-2"><?= $this->lang->line('online_exam_cost') ?></th>
                                <th class="col-sm-2"><?= $this->lang->line('online_exam_date') ?></th>
                                <?php if (permissionChecker('online_exam_edit')) { ?>
                                    <th class="col-sm-2"><?= $this->lang->line('online_exam_published') ?></th>
                                <?php }  ?>
                                <?php if (permissionChecker('online_exam_edit') || permissionChecker('online_exam_delete') || permissionChecker('online_exam_view')) { ?>
                                    <th class="col-sm-2"><?= $this->lang->line('action') ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (inicompute($online_exams)) {
                                $i = 0;
                                foreach ($online_exams as $online_exam) {
                                    $showStatus = FALSE;
                                    if ($usertypeID == '3') {
                                        if (inicompute($student)) {
                                            if ((($student->classesID == $online_exam->classID) || ($online_exam->classID == '0')) && (($student->sectionID == $online_exam->sectionID) || ($online_exam->sectionID == '0')) && (($student->studentgroupID == $online_exam->studentGroupID) || ($online_exam->studentGroupID == '0')) && ($online_exam->published == '1') && (($online_exam->subjectID == '0') || (in_array($online_exam->subjectID, $userSubjectPluck)))) {
                                                $showStatus = TRUE;
                                                $i++;
                                            }
                                        }
                                    } else {
                                        $i++;
                                        $showStatus = TRUE;
                                    }

                                    if ($showStatus) { ?>
                                        <tr>
                                            <td data-title="<?= $this->lang->line('slno') ?>">
                                                <?php echo $i; ?>
                                            </td>
                                            <td data-title="<?= $this->lang->line('online_exam_name') ?>">
                                                <?php
                                                if (strlen($online_exam->name) > 25)
                                                    echo strip_tags(substr($online_exam->name, 0, 25) . "...");
                                                else
                                                    echo strip_tags(substr($online_exam->name, 0, 25));
                                                ?>
                                            </td>

                                            <td data-title="<?= $this->lang->line('online_exam_payment_status') ?>">
                                                <?= ($online_exam->paid == 1) ? $this->lang->line('online_exam_paid') : $this->lang->line('online_exam_free'); ?>
                                            </td>
                                            <td data-title="<?= $this->lang->line('online_exam_cost') ?>">
                                                <?= ($online_exam->paid == 1) ? number_format($online_exam->cost, '2') : number_format($online_exam->cost, '2'); ?> <?= $siteinfos->currency_code ?>
                                            </td>
                                            <td data-title="<?= $this->lang->line('online_exam_date') ?>">
                                                <?php
                                                if (isset($online_exam->startDateTime) && isset($online_exam->endDateTime)) {
                                                    echo date("d-M-Y", strtotime($online_exam->startDateTime)) . ' - ' . date('d-M-Y', strtotime($online_exam->endDateTime));
                                                }
                                                ?>
                                            </td>
                                            <?php if (permissionChecker('online_exam_edit')) { ?>
                                                <td data-title="<?= $this->lang->line('online_exam_published') ?>">
                                                    <div class="onoffswitch-small" id="<?= $online_exam->onlineExamID ?>">
                                                        <input type="checkbox" id="myonoffswitch<?= $online_exam->onlineExamID ?>" class="onoffswitch-small-checkbox" name="paypal_demo" <?php if ($online_exam->published == '1') echo "checked='checked'"; ?>>
                                                        <label for="myonoffswitch<?= $online_exam->onlineExamID ?>" class="onoffswitch-small-label">
                                                            <span class="onoffswitch-small-inner published-switch"></span>
                                                            <span class="onoffswitch-small-switch"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                            <?php } ?>
                                            <?php if (permissionChecker('online_exam_edit') || permissionChecker('online_exam_delete') || permissionChecker('online_exam_view')) { ?>
                                                <td data-title="<?= $this->lang->line('action') ?>">
                                                    <?php
                                                    if ($online_exam->published != 1) {
                                                        echo btn_extra('online_exam/addquestion/' . $online_exam->onlineExamID, $this->lang->line('addquestion'), 'online_exam_add');
                                                    }
                                                    ?>
                                                    <?php echo btn_edit('online_exam/edit/' . $online_exam->onlineExamID, $this->lang->line('edit')); ?>
                                                    <?php echo btn_delete('online_exam/delete/' . $online_exam->onlineExamID, $this->lang->line('delete')); ?>
                                                </td>
                                            <?php } ?>
                                        </tr>
                            <?php }
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var published = '';
    var id = 0;
    $('.onoffswitch-small-checkbox').click(function() {
        if ($(this).prop('checked')) {
            published = 1;
            id = $(this).parent().attr("id");
        } else {
            published = 2;
            id = $(this).parent().attr("id");
        }

        if ((published != '' || published != null) && (id != '')) {
            $.ajax({
                type: 'POST',
                url: "<?= base_url('online_exam/published') ?>",
                data: "id=" + id + "&published=" + published,
                dataType: "html",
                success: function(data) {
                    if (data == 'Success') {
                        toastr["success"]("Success")
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
                    } else {
                        toastr["error"]("Error")
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
                    location.reload();
                }
            });
        }
    });
</script>