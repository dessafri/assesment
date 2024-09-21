<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-idcardreport"></i> Report Penilaian</h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"> <?=$this->lang->line('menu_idcardreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">

            <div class="col-sm-12">
            <!-- <button class="button btn btn-primary"><a class="text-white" href="<?=base_url('idcardreport/getReport')?>">Print</a></button> -->
            <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                <thead>
                    <tr>
                        <th width="2%">No</th>
                        <th width="50%">Nama</th>
                        <th width="20%">Total Dijawab</th>
                        <th width="20%">Total Score</th>
                        <th class="hidden"></th>
                        <th >Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1; foreach($results as $key => $result) {
                    ?>
                        <tr>
                            <td data-title="id"><?php echo $i; ?></td>
                            <td data-title="namaKaryawan"><?=$result['name']?></td>
                            <td><?= $result['total'] ?></td>
                            <td><?= $result['score'] ?></td>
                            <td class="hidden"><?=$key?></td>
                            <td>
                                <button class="btn btn-primary showDetails"><i class="fa fa-eye"></i></button>
                            </td>
                        </tr>
                    <?php $i++; }
                     ?>
                </tbody>
            </table>
            </div>

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_idcardreport"></div>
<!-- Jquery datatable tools js -->
<script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/jquery.dataTables.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/dataTables.buttons.min.js'); ?>"></script>
<!-- dataTables Tools / -->
<script type="text/javascript" src="<?php echo base_url('assets/datatables/dataTables.bootstrap.js'); ?>"></script>

<script>
    $(document).ready(function () {
        var results = <?php echo json_encode($results)?>;
        var parentTable = $('#example1').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'csvHtml5',
                    exportOptions: {
                        columns: ':visible',
                        format: {
                        body: function(data, row, column, node) {
                            // Customize data formatting
                            // Example: Remove HTML tags and trim spaces
                            return data.replace(/<.*?>/g, '').trim();
                        }
                    }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
        });
        $('#example1 tbody').on('click', '.showDetails', function() {
            var tr = $(this).closest('tr');
            var row = parentTable.row(tr);
            var dt = row.data();
            // console.log(dt)

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(formatDetails(dt[4])).show();
                tr.addClass('shown');
            }
        });

        // Function to format the details to be displayed in the child row
        function formatDetails(idx) {
            var sub = results[idx]['detail'];
            var template = ``
            $.map(sub, function(val, i){
                var nm = i.replaceAll(" ","_");

                // Build the outer table with header
                template += `
                <table class='table'>
                    <tr>
                        <td class='col-sm-6' colspan=4>
                            <h4 class='font-weight-bold'>${i}</h4>
                        </td>
                        <td class='col-sm-6 text-right'>
                            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#multiCollapse${idx}-${nm}" aria-expanded="false" aria-controls="multiCollapse${idx}-${nm}">Detail <i class='fa fa-arrow-down'></i></button>
                        </td>
                    </tr>
                </table>
                `;
                
                // Create a collapsible section for each row of details
                template += `
                    <table class="table table-bordered collapse multi-collapse" id="multiCollapse${idx}-${nm}">
                        <tbody>
                `;

                // Loop through the details in 'val' and generate table rows for each detail
                $.map(val, function(detailVal, detailKey) {
                    template += `
                        <tr>
                            <td>${detailVal.question}</td>
                            <td>${detailVal.value}</td>
                            <td>
                                <input class='form-control' type="number" min='0' name="inputValue" value="" id="input_${detailVal.questionLevelReportID}" data-id="${detailVal.questionLevelReportID}" data-option='${detailVal.onlineExamUserAnswerOptionID}' data-answer='${detailVal.onlineExamUserAnswerID}'>
                            </td>
                            <td>
                                <a class='${detailVal.fileAnswer ? '' : 'disabled'}' href="${detailVal.fileAnswer ? detailVal.fileAnswer : '#' }"><i class='fa fa-file'></i> ${detailVal.fileAnswer ? 'File' : 'No File'}</a>
                            </td>
                            <td class='col-sm-1'>
                            </td>
                        </tr>
                    `;
                });

                template += `
                        <tr>
                            <td colspan='4'></td>
                            <td>
                                <button class="btn btn-success" data-index='${idx}-${nm}'>Verifikasi</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                `;

            });

            return template;
        }

        $(document).on('click', '.btn-success', function() {
            var idx = $(this).data('index');  // Or whatever index you need
            verifikasi(idx);
        });

        function verifikasi(idx) {
            var inputs = $(`#multiCollapse${idx} input[data-id]`);  // Select all inputs in the collapsed section
            var dataToSend = [];

            // Loop through inputs and collect values and IDs
            inputs.each(function() {
                var questionID = $(this).data('id');
                var optionID = $(this).data('option');
                var answerID = $(this).data('answer');
                var value = $(this).val();
                // console.log(optionID)

                dataToSend.push({
                    questionLevelReportID: questionID,
                    answer: value,
                    optionID : optionID,
                    answerID:answerID,
                });
            });

            $.ajax({
                type: 'POST',
                url: "<?=base_url('idcardreport/update_insert_question')?>",
                data: {
                    data: dataToSend
                },
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    console.log(response)
                    if (response.success == true) {
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error while saving data:', error);
                }
            });
        }

        
    });
</script>

<script type="text/javascript">
    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        var divElements = document.getElementById(divID).innerHTML;
        document.body.innerHTML = "<html><head><title></title></head><body>" + divElements + "</body>";

        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }
    

    $(function(){
        $("#usertypeID").val(0);
        $("#classesID").val(0);
        $("#sectionID").val(0);
        $("#userID").val(0);
        $("#type").val(0);
        $("#background").val(0);
        $('#classesDiv').hide('slow');
        $('#sectionDiv').hide('slow');
        $('#userDiv').hide('slow');
        $(".select2").select2();
    });

    $(document).on('change', "#usertypeID", function() {
        $('#load_idcardreport').html("");
        var usertypeID = $(this).val();
        var classesID = $("#classesID").val();
        var sectionID = $("#sectionID").val();
        var idcardtext = $('#usertypeID option:selected').text();
        var error = 0;

        $('#userDivlabel').text(idcardtext);
        if(usertypeID == '0'){
            $('#classesDiv').hide('slow');
            $('#sectionDiv').hide('slow');
            $('#userDiv').hide('slow');
        } else if(usertypeID == '3') {
            $("#classesID").val(0);
            $("#sectionID").val(0);
            $("#userID").val(0);
            $('#classesDiv').show('slow');
            $('#sectionDiv').show('slow');
            $('#userDiv').show('slow');
        } else if(usertypeID !='3') {
            $("#classesID").val(0);
            $("#sectionID").val(0);
            $("#userID").val(0);
            $('#classesDiv').hide('slow');
            $('#sectionDiv').hide('slow');
            $('#userDiv').show('slow');
        }

        var passData = {
            'usertypeID':usertypeID,
            'classesID':classesID,
            'sectionID':sectionID,
        }

        if(usertypeID > 0)  {
            $.ajax({
                type : 'POST',
                url  : '<?=base_url('idcardreport/getUser')?>',
                data : passData,
                success : function(data) {
                    $('#userID').html(data);
                }
            });
        }
    });

    $(document).on('change', "#classesID", function() {
        $('#load_idcardreport').html('');
        var usertypeID = $('#usertypeID').val();
        var classesID = $(this).val();
        if(classesID == 0) {
            $('#sectionID').html('<option value="0">'+"<?=$this->lang->line("idcardreport_please_select")?>"+'</option>');
            $('#userID').html('<option value="0">'+"<?=$this->lang->line("idcardreport_please_select")?>"+'</option>');
        } else {
            $.ajax({
                type:'POST',
                url:'<?=base_url('idcardreport/getSection')?>',
                data:{'classesID':classesID},
                success:function(data) {
                    $('#sectionID').html(data);
                }
            });
        }

        if(classesID > 0 && usertypeID == 3) {
            $.ajax({
                type:'POST',
                url:'<?=base_url('idcardreport/getStudentByClass')?>',
                data:{'usertypeID':usertypeID,'classesID':classesID},
                success:function(data) {
                    $('#userID').html(data);
                }
            });
        }
    });

    $(document).on('change', "#sectionID", function() {
        $('#load_idcardreport').html('');
        var usertypeID = $('#usertypeID').val();
        var classesID = $('#classesID').val();
        var sectionID = $('#sectionID').val();

        if(classesID > 0 && usertypeID == 3) {
            $.ajax({
                type:'POST',
                url:'<?=base_url('idcardreport/getStudentBySection')?>',
                data:{'usertypeID':usertypeID,'classesID':classesID,'sectionID':sectionID},
                success:function(data) {
                    $('#userID').html('0');
                    $('#userID').html(data);
                }
            });
        }
    });

    $(document).on('change', "#userID", function() {
        $('#load_idcardreport').html("");
    });


    $(document).on('change', "#type", function() {
        $('#load_idcardreport').html("");
    });

    $(document).on('change', "#background", function() {
        $('#load_idcardreport').html("");
    });

    $(document).on('click','#get_idcardreport', function() {
        var usertypeID = $('#usertypeID').val();
        var classesID = $('#classesID').val();
        var sectionID = $('#sectionID').val();
        var userID    = $('#userID').val();
        var type      = $('#type').val();
        var background= $('#background').val();
        var error = 0;
        var field = {
            'usertypeID': usertypeID,
            'classesID' : classesID,
            'sectionID' : sectionID,
            'userID'    : userID,
            'type'      : type,
            'background': background,
        }

        if(usertypeID == 0 ) {
            $('#usertypeIDDiv').addClass('has-error');
            error++;
        } else {
            $('#usertypeIDDiv').removeClass('has-error');
        }

        if(usertypeID == 3 && classesID == 0 ) {
            $('#classesDiv').addClass('has-error');
            error++;
        } else {
            $('#classesDiv').removeClass('has-error');
        }

        if(type == 0 ) {
            $('#typeDiv').addClass('has-error');
            error++;
        } else {
            $('#typeDiv').removeClass('has-error');
        }

        if(background == 0 ) {
            $('#backgroundDiv').addClass('has-error');
            error++;
        } else {
            $('#backgroundDiv').removeClass('has-error');
        } 

        if(error == 0 ) {
            makingPostDataPreviousofAjaxCall(field);
        }
    });

    function makingPostDataPreviousofAjaxCall(field) {
        var passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type:'POST',
            url:'<?=base_url('idcardreport/getIdcardReport')?>',
            data:passData,
            dataType:'html',
            success:function(data) {
                var response = JSON.parse(data);
                renderLoder(response, passData);
            }
        });
    }

    function renderLoder(response, passData) {
        if(response.status) {
            $('#load_idcardreport').html(response.render);
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }
        } else {
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }

            for (var key in response) {
                if (response.hasOwnProperty(key)) {
                    $('#'+key).parent().addClass('has-error');
                }
            }
        }
    }

</script>


