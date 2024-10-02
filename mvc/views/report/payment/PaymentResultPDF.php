<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

</head>

<body>
    <div class="mainpdf">
        <div class="exam_info" style="width:100%">
            <div>
                <div class="box">
                    <div class="box-header bg-gray">
                        <h3 class="box-title text-navy" style="float: left;"><i class="fa fa-clipboard"></i> <?= $this->lang->line('onlineexamreport_report_for') ?> <?= $this->lang->line('onlineexamreport_payment_history') ?></h3>
                        <h3 class="box-title text-navy float-right" style="float: right;"> <?= $this->lang->line('onlineexamreport_exam') ?>: <?= $onlineexam ?></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">

                            <div class="col-sm-12">
                                <span style="padding-left:5px"><b><?= $this->lang->line('onlineexamreport_classes'); ?> : </b><?= inicompute($classes) ? $classes->classes : $this->lang->line('onlineexamreport_select_all_classes') ?></span>
                                <span style=""><b><?= $this->lang->line('onlineexamreport_section') ?> :</b> <?= inicompute($section) ? $section->section : '' ?></span>
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
        </div>
    </div><!-- row -->
</body>

</html>