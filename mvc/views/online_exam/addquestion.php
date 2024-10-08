<div class="row">
    <div class="col-sm-8">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-puzzle-piece"></i> <?=$this->lang->line('online_exam_question_bank')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("online_exam/index")?>"> <?=$this->lang->line('panel_title')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_add')?> <?=$this->lang->line('online_exam_question_bank')?></li>
                </ol>
            </div><!-- /.box-header -->
            <!-- form start -->
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="<?php echo form_error('levelID') ? 'form-group has-error' : 'form-group'; ?>" >
                                        <label for="levelID" class="control-label">
                                            <?=$this->lang->line('online_exam_question_level')?>
                                        </label>
                                        <?php
                                            $array = array("0" => $this->lang->line('online_exam_select'));
                                            foreach ($levels as $level) {
                                                $array[$level->questionLevelID] = $level->name;
                                            }
                                            echo form_dropdown("levelID", $array, set_value("levelID"), "id='levelID' class='form-control select2'");
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="<?php echo form_error('groupID') ? 'form-group has-error' : 'form-group'; ?>" >
                                        <label for="groupID" class="control-label">
                                            <?=$this->lang->line('online_exam_question_group')?>
                                        </label>
                                        <?php
                                            $array = array("0" => $this->lang->line('online_exam_select'));
                                            foreach ($groups as $group) {
                                                $array[$group->questionGroupID] = $group->title;
                                            }
                                            echo form_dropdown("groupID", $array, set_value("groupID"), "id='groupID' class='form-control select2'");
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class='col-sm-12' id="questions">
                    </div>
                </div>
            </div>
        </div>


        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-puzzle-piece"></i> <?=$this->lang->line('online_exam_associate_question')?></h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12" id="asso-questions">
                        <?=isset($associateQuestionList) ? $associateQuestionList : ''?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="col-sm-4">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-puzzle-piece"></i> <?=$this->lang->line('panel_title')?></h3>
            </div><!-- /.box-header -->
            <!-- form start -->
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="info-box">
                            <p>
                                <span><?=$this->lang->line('online_exam_name')?> : </span>
                                <?=$onlineExam->name?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_type')?> : </span>
                                <?=$examType->title?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_startdatetime')?> : </span>
                                <?=$onlineExam->startDateTime?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_enddatetime')?> : </span>
                                <?=$onlineExam->endDateTime?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_duration')?> : </span>
                                <?=$onlineExam->duration?> <?=$this->lang->line('online_exam_minutes')?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_payment_status')?> : </span>
                                <?=($onlineExam->paid == 0) ? $this->lang->line('online_exam_free') : $this->lang->line('online_exam_paid')?>
                            </p>
                            <?php if($onlineExam->paid == 1) { ?>
                                <p>
                                    <span><?=$this->lang->line('online_exam_cost')?> : </span>
                                    <?=number_format($onlineExam->cost, '2')?> <?=$siteinfos->currency_code?>
                                </p>
                            <?php } ?>
                            <p>
                                <span><?=$this->lang->line('online_exam_description')?> : </span>
                                <?=namesorting($onlineExam->description,50)?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_markType')?> : </span>
                                <?php
                                    $markTypeArray[5]   = $this->lang->line("online_exam_percentage");
                                    $markTypeArray[10]  = $this->lang->line("online_exam_fixed");
                                ?>
                                <?=isset($markTypeArray[$onlineExam->markType]) ? $markTypeArray[$onlineExam->markType] : ''?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_passValue')?> : </span>
                                <?=($onlineExam->markType == 5) ? $onlineExam->percentage .'%' : $onlineExam->percentage ?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_instruction')?> : </span>
                                <?=inicompute($instruction) ? $instruction->title : ''?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_class')?> : </span>
                                <?=inicompute($class) ? $class->classes : ''?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_section')?> : </span>
                                <?=inicompute($section) ? $section->section : ''?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_studentGroup')?> : </span>
                                <?=inicompute($studentGroup) ? $studentGroup->group : ''?>
                            </p>
                            <p>
                                <span><?=$this->lang->line('online_exam_subject')?> : </span>
                                <?=inicompute($subject) ? $subject->subject : ''?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-puzzle-piece"></i> <?=$this->lang->line('online_exam_question_summary')?></h3>
            </div>
            
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12" id="questionSummary">
                        <?=isset($questionSummary) ? $questionSummary : ''?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script type="application/javascript">
    $('#groupID').change(function () {
        var groupID = $(this).val();
        var levelID = $('#levelID').val();
        $.ajax({
            type: 'POST',
            url: "<?=base_url('online_exam/showQuestions')?>",
            data: {"groupID" : groupID, 'levelID': levelID},
            dataType: "html",
            success: function(data) {
                $('#questions').html(data);
            }
        })
    });
    $('#levelID').change(function () {
        var levelID = $(this).val();
        var groupID = $('#groupID').val();
        $.ajax({
            type: 'POST',
            url: "<?=base_url('online_exam/showQuestions')?>",
            data: {"groupID" : groupID, 'levelID': levelID},
            dataType: "html",
            success: function(data) {
                $('#questions').html(data);
            }
        });
    });

    function addQuestion(questionID)
    {
        var onlineExamID = '<?=$onlineExamID?>';
        $.ajax({
            type: 'POST',
            url: "<?=base_url('online_exam/addQuestionDatabase')?>",
            data: {"questionID" : questionID, 'onlineExamID': onlineExamID},
            dataType: "html",
            success: function(data) {
                data = JSON.parse(data);
                var mainID = '#asso-questions';
                var subMainID = '#questionSummary';
                $(mainID).html(data.associateQuestionList).hide().show("fast");
                $(subMainID).html(data.questionSummary).hide().show("fast");
            }
        });
    }

    function removeQuestion(onlineExamQuestionID)
    {
        var onlineExamID = '<?=$onlineExamID?>';
        $.ajax({
            type: 'POST',
            url: "<?=base_url('online_exam/removeQuestionDatabase')?>",
            data: {"onlineExamQuestionID" : onlineExamQuestionID, 'onlineExamID': onlineExamID},
            dataType: "html",
            success: function(data) {
                data = JSON.parse(data);
                var mainID = '#asso-questions';
                var subMainID = '#questionSummary';
                $(mainID).html(data.associateQuestionList).hide().show("fast");
                $(subMainID).html(data.questionSummary).hide().show("fast");
            }
        });
    }
</script>