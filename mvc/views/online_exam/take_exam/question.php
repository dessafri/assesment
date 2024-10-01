<style type="text/css">
    .fuelux .wizard .step-content {
        border: 0px;
    }
</style>
<div class="col-sm-12 do-not-refresh">
    <div class="callout callout-danger">
        <h4><?= $this->lang->line('take_exam_warning') ?></h4>
        <p><?= $this->lang->line('take_exam_page_refresh') ?></p>
    </div>
</div>

<div class="row">
    <div class="col-sm-8 fu-example section">
        <div class="box outheBoxShadow wizard" data-initialize="wizard" id="questionWizard">
            <!-- <div class="box-header bg-white">
                <div class="checkbox hints">
                    <label>
                    </label>
                    <span class="pull-right">
                        <label>
                        </label>
                    </span>
                </div>
            </div> -->
            <div class="steps-container">
                <ul class="steps hidden" style="margin-left: 0">
                    <?php
                    $countOnlineExamQuestions = inicompute($onlineExamQuestions);
                    foreach (range(1, $countOnlineExamQuestions) as $value) { ?>
                        <li data-step="<?= $value ?>" class="<?= $value == 1 ? 'active' : '' ?>"></li>
                    <?php } ?>
                </ul>
            </div>

            <form id="answerForm" method="post" enctype="multipart/form-data">
                <div class="box-body step-content">
                    <input style="display:none" type="text" name="studentfinishstatus">
                    <?php
                    if ($countOnlineExamQuestions) {
                        foreach ($newArray as $key => $entry) {
                            ?>
                            <div class="clearfix step-pane sample-pane <?= $key == 0 ? 'active' : '' ?>" data-questionID="<?= $entry->idresult ?>" data-step="<?= $key + 1 ?>">
                                <div class="question-body">
                                    <label class="lb-title"><?= $onlineExam->name ?>
                                    </label>
                                    <label class="lb-content" style="font-weight: bold; font-size: 1.5em;"><?= $entry->nameresult ?></label>
                                </div>
                        
                                <?php
                                // Loop through each 'detail_soal' (questions)
                                foreach ($entry->detail_soal as $index => $question) {
                                    $questionOptions = isset($options[$question->questionBankID]) ? $options[$question->questionBankID] : [];
                                    $questionAnswers = isset($answers[$question->questionBankID]) ? $answers[$question->questionBankID] : [];
                        
                                    if ($question->typeNumber == 1 || $question->typeNumber == 2) {
                                        $questionAnswers = pluck($questionAnswers, 'optionID');
                                    }
                                    $optionCount = $question->totalOption;
                                    ?>
                                    <div class="question-body">
                                        <label class="lb-title">Pertanyaan
                                            <?= $index + 1 ?> dari
                                            <?= count($entry->detail_soal) ?>
                                        </label>
                                        <label class="lb-content"><?= $question->question ?></label>
                                        <label class="lb-mark">
                                            <?= $question->mark != "" ? $question->mark . ' ' . $this->lang->line('take_exam_mark') : '' ?>
                                        </label>
                                        <?php if ($question->upload != '') { ?>
                                            <div>
                                                <img style="width:240px;height:140px;object-fit: cover; padding-top: 10px; padding-bottom: 25px;"
                                                     src="<?= base_url('uploads/images/' . $question->upload) ?>" alt="">
                                            </div>
                                        <?php } ?>
                                        <?php
                                        if ($question->explanation) {
                                            ?>
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title">Explanation</h3>
                                                </div>
                                                <div class="panel-body">
                                                    <p><strong><?= $question->explanation ?></strong></p>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                        
                                    <div class="question-answer" id="step<?= $key + 1 ?>">
                                        <table class="table">
                                            <tr>
                                                <?php
                                                $tdCount = 0;
                                                $oc = 1;
                                                foreach ($questionOptions as $option) {
                                                    if ($optionCount >= $oc) {
                                                        $oc++; ?>
                                                        <td>
                                                            <input id="option<?= $option->optionID ?>" value="<?= $option->optionID ?>"
                                                                   name="answer[<?= $question->typeNumber ?>][<?= $question->questionBankID ?>][]"
                                                                   type="<?= ($question->typeNumber == 1 || $question->typeNumber == 4) ? 'radio' : '' ?>">
                                                            <label for="option<?= $option->optionID ?>">
                                                                <span class="fa-stack <?= $question->typeNumber == 1 ? 'radio-button' : 'checkbox-button' ?>">
                                                                    <i class="active fa fa-check"></i>
                                                                </span>
                                                                <span><?= $option->name ?></span>
                                                                <?php if (!is_null($option->img) && $option->img != "") { ?>
                                                                    <div style="background-color: white; box-shadow: 2px 2px 2px 2px; margin-top: 10px; padding: 10px; text-align: center; border-radius: 5px;">
                                                                        <img style="width: 100px; height: 80px; object-fit: cover;" src="<?= base_url('uploads/images/' . $option->img) ?>" alt="Image"/>
                                                                    </div>
                                                                <?php } ?>
                                                            </label>
                                                        </td>
                                                        <?php
                                                    }
                                                    $tdCount++;
                                                    if ($tdCount == 2) {
                                                        $tdCount = 0;
                                                        echo "</tr><tr>";
                                                    }
                                                }
                        
                                                if ($question->typeNumber == 3) {
                                                    foreach ($questionAnswers as $answerKey => $answer) {
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <input type="button" value="<?= $answerKey + 1 ?>"> 
                                                                <input class="fillInTheBlank" id="answer<?= $answer->answerID ?>" name="answer[<?= $question->typeNumber ?>][<?= $question->questionBankID ?>][<?= $answer->answerID ?>]" value="" type="text">
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                }

                                                if ($question->typeNumber == 5) { ?>
                                                    <tr>
                                                        <td>
                                                            <div class="form-group">
                                                                <label for="answer<?= $question->questionBankID ?>">Masukkan jawaban</label>
                                                                <input type="number" name="answer[<?= $question->typeNumber ?>][<?= $question->questionBankID ?>]" id="answer<?= $question->questionBankID ?>" value="" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="s_perkara<?= $question->questionBankID ?>">Surat Perkara</label>
                                                                <input style="text-align: left;" type="text" name="s_perkara[<?= $question->typeNumber ?>][<?= $question->questionBankID ?>]" id="s_perkara<?= $question->questionBankID ?>" value="" class="form-control">
                                                            </div>
                        
                                                            <!-- <div class="form-group">
                                                                <label for="file<?= $question->questionBankID ?>">Masukkan file</label>
                                                                <input type="file" id="file<?= $question->questionBankID ?>" name="file[<?= $question->typeNumber ?>][<?= $question->questionBankID ?>]" class="form-control">
                                                            </div> -->
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                    } 
                    else {
                        echo "<p class='text-center'>" . $this->lang->line('take_exam_no_question') . "</p>";
                    } ?>
                    <div class="question-answer-button">
                        <button class="btn oe-btn-answered btn-prev" type="button" name="" id="prevbutton" disabled>
                            <i class="fa fa-angle-left"></i> <?= $this->lang->line('take_exam_previous') ?>
                        </button>

                        <button class="btn oe-btn-notvisited" type="button" name="" id="reviewbutton">
                            <?= $this->lang->line('take_exam_mark_review') ?>
                        </button>

                        <button class="btn oe-btn-answered btn-next" type="button" name="" id="nextbutton"
                            data-last="<?= $this->lang->line('take_exam_finish') ?> ">
                            <?= $this->lang->line('take_exam_next') ?> <i class="fa fa-angle-right"></i>
                        </button>

                        <!-- <button class="btn oe-btn-notvisited" type="button" name="" id="clearbutton">
                            <?= $this->lang->line('take_exam_clear_answer') ?>
                        </button> -->

                        <button class="btn oe-btn-notanswered" type="button" name="" id="finishedbutton"
                            onclick="finished()">
                            <?= $this->lang->line('take_exam_finish') ?>
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="row">
            <div class="col-sm-12 counterDiv" style="display: none;">
                <div class="box outheBoxShadow">
                    <div class="box-body outheMargAndBox">
                        <div class="box outheBoxShadow">
                            <div class="box-header bg-white">
                                <h3 class="box-title fontColor"> <?= $this->lang->line('take_exam_time_status') ?></h3>
                            </div>
                            <div class="box-body">
                                <div id="timerdiv" class="timer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 counterDiv" style="display: none;">
                <div class="box outheBoxShadowColor">
                    <div class="box-body innerMargAndBox">
                        <div class="row">
                            <div class="col-sm-6">
                                <h3 class="fontColor"><?= $this->lang->line('take_exam_total_time') ?></h3>
                            </div>
                            <div class="col-sm-6">
                                <h3 class="fontColor duration">00:00:00</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="box outheBoxShadow">
                    <div class="box-body outheMargAndBox">
                        <!-- <div class="box-header bg-white">
                            <h3 class="box-title fontColor">
                                <?= $onlineExam->name ?>
                                <br>
                            </h3>
                        </div> -->

                        <div class="box-body margAndBox">
                            <nav aria-label="Page navigation">
                                <ul class="examQuesBox questionColor" style="width: 170px !important;">
                                    <?php
                                    foreach ($newArray as $key => $entry) {
                                        ?>
                                        <li><a class="notvisited" id="question<?= $key + 1 ?>" href="javascript:void(0);"
                                                onclick="jumpQuestion(<?= $key + 1 ?>)">
                                                <?= $entry->nameresult?>
                                            </a></li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </nav>


                            <!-- <nav aria-label="Page navigation">
                                <h2><?= $this->lang->line('take_exam_summary') ?></h2>
                                <ul class="examQuesBox text">
                                    <li><a class="answered" id="summaryAnswered" href="#">0</a>
                                        <?= $this->lang->line('take_exam_answered') ?></li>
                                    <li><a class="marked" id="summaryMarked" href="#">0</a>
                                        <?= $this->lang->line('take_exam_marked') ?></li>
                                    <li><a class="notanswered" id="summaryNotAnswered" href="#">0</a>
                                        <?= $this->lang->line('take_exam_not_answer') ?></li>
                                    <li><a class="notvisited" id="summaryNotVisited"
                                            href="#">0</a><?= $this->lang->line('take_exam_not_visited') ?></li>
                                </ul>
                            </nav> -->
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $('#reviewbutton').on('click', function () {
        marked = 1;
        $('#questionWizard').wizard('next');
    });

    $('#clearbutton').on('click', function () {
        clearAnswer();
    });

    $('#questionWizard').on('actionclicked.fu.wizard', function (evt, data) {

        totalQuestions = parseInt(totalQuestions);
        var steps = 0;
        if (data.direction == "next") {
            steps = data.step + 1;
        } else {
            steps = data.step - 1;
        }

        if (steps == totalQuestions) {
            $('#nextbutton').removeClass('oe-btn-answered');
            $('#nextbutton').addClass('oe-btn-notanswered');
            $('#nextbutton i').remove();
            $('#finishedbutton').hide();
        } else if (steps == totalQuestions + 1) {
            finished();
        } else {
            $('#nextbutton').removeClass('oe-btn-notanswered');
            $('#nextbutton').addClass('oe-btn-answered');
            $('#nextbutton i').remove();
            $('#nextbutton').append(' <i class="fa fa-angle-right"></i>');
            $('#finishedbutton').show();
        }
        NowStep = steps;

        changeColor(data.step);
        summaryUpdate();
    });

    function summaryUpdate() {
        var summaryNotVisited = $('.questionColor li .notvisited').length;
        var summaryAnswered = $('.questionColor li .answered').length;
        var summaryMarked = $('.questionColor li .marked').length;
        var summaryNotAnswered = $('.questionColor li .notanswered').length;
        $('#summaryNotVisited').html(summaryNotVisited);
        $('#summaryAnswered').html(summaryAnswered);
        $('#summaryMarked').html(summaryMarked);
        $('#summaryNotAnswered').html(summaryNotAnswered);
    }

    function changeColor(stepID) {
        list = $('#answerForm #step' + stepID + ' input ');
        var have = 0;
        var result = $.each(list, function () {
            elementType = $(this).attr('type');
            if (elementType == 'radio' || elementType == 'checkbox') {
                if ($(this).prop('checked')) {
                    have = 1;
                    return have;
                }
            } else if (elementType == 'text') {
                if ($(this).val() != '') {
                    have = 1;
                    return have;
                }
            } else if (elementType == 'number') {
                if ($(this).val() != '') {
                    have = 1;
                    return have;
                }
            }
        });
        if (have) {
            $('#question' + stepID).removeClass('notvisited');
            $('#question' + stepID).removeClass('notanswered');
            $('#question' + stepID).removeClass('marked');
            $('#question' + stepID).addClass('answered');
        } else {
            $('#question' + stepID).removeClass('notvisited');
            $('#question' + stepID).removeClass('answered');
            if ($('#question' + stepID).attr('class') != 'marked') {
                $('#question' + stepID).addClass('notanswered');
            }
        }

        if (marked) {
            marked = 0;
            if ($('#question' + stepID).attr('class') != 'answered') {
                $('#question' + stepID).removeClass('notvisited');
                $('#question' + stepID).removeClass('notanswered');
                $('#question' + stepID).addClass('marked');
            }
        }
    }

    function jumpQuestion(questionNumber) {
        changeColor(NowStep);
        NowStep = questionNumber;
        $('#questionWizard').wizard('selectedItem', {
            step: questionNumber
        });
        changeColor(questionNumber);
        if (questionNumber == totalQuestions) {
            $('#nextbutton').removeClass('oe-btn-answered');
            $('#nextbutton').addClass('oe-btn-notanswered');
            $('#nextbutton i').remove();
            $('#finishedbutton').hide();
        } else {
            $('#nextbutton').removeClass('oe-btn-notanswered');
            $('#nextbutton').addClass('oe-btn-answered');
            $('#nextbutton i').remove();
            $('#nextbutton').append(' <i class="fa fa-angle-right"></i>');
            $('#finishedbutton').show();
        }
        summaryUpdate();
    }

    function clearAnswer() {
        list = $('#answerForm #step' + NowStep + ' input ');
        $.each(list, function () {
            elementType = $(this).attr('type');
            switch (elementType) {
                case 'radio': $(this).prop('checked', false); break;
                case 'checkbox': $(this).attr('checked', false); break;
                case 'text': $(this).val(''); break;
                case 'number': $(this).val(''); break;
                case 'file': $(this).val(''); break;
            }
        });
        if ($('#question' + NowStep).attr('class') == 'marked') {
            $('#question' + NowStep).removeClass('marked');
            $('#question' + NowStep).addClass('notanswered');
        }
    }

    function finished() {
        $('#answerForm').submit();
    }

    function counter() {
        setInterval(function () {
            durationUpdate();
            $('#timerdiv').html(((hours < 10) ? '0' + hours : hours) + ':' + ((minutes < 10) ? '0' + minutes : minutes) + ':' + ((seconds < 10) ? '0' + seconds : seconds));
            duration = (hours * 60) + minutes;
        }, 1000);
    }

    function durationUpdate() {
        hours = 0;
        minutes = duration;
        if (minutes > 60) {
            hours = parseInt(duration / 60, 10);
            minutes = duration % 60;
        }
        --seconds;
        minutes = (seconds < 0) ? --minutes : minutes;
        if (minutes < 0 && hours != 0) {
            --hours;
            minutes = 59;
        }

        if (hours < 0) {
            hours = 0;
        }

        seconds = (seconds < 0) ? 59 : seconds;
        if (minutes < 0 && hours == 0) {
            minutes = 0;
            seconds = 0;
            finished();
            clearInterval(interval);
        }
    }

    function timeString() {
        return ((hours < 10) ? '0' + hours : hours) + ':' + ((minutes < 10) ? '0' + minutes : minutes) + ':' + ((seconds < 10) ? '0' + seconds : seconds);
    }

    var duration = parseInt("<?= $onlineExam->duration ?>");
    var totalQuestions = parseInt("<?= $countOnlineExamQuestions ?>");
    var seconds = 1;
    var hours = 0;
    var minutes = -1;
    var NowStep = 1;
    var marked = 0;
    durationUpdate();
    $('.duration').html(timeString());
    if (duration != 0) {
        counter();
    } else {
        $('.counterDiv').hide();
    }
    summaryUpdate();

    $('.sidebar-menu li a').css('pointer-events', 'none');

    function disableF5(e) {
        if (((e.which || e.keyCode) == 116) || (e.keyCode == 82 && e.ctrlKey)) {
            e.preventDefault();
        }
    }

    $(document).bind("keydown", disableF5);

    function Disable(event) {
        if (event.button == 2) {
            window.oncontextmenu = function () {
                return false;
            }
        }
    }

    document.onmousedown = Disable;

    if (totalQuestions == 1) {
        $('#nextbutton').removeClass('oe-btn-answered');
        $('#nextbutton').addClass('oe-btn-notanswered');
        $('#nextbutton i').remove();
        $('#finishedbutton').hide();
    }
</script>