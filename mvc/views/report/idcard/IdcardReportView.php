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
            <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                <thead>
                    <tr>
                        <th width="2%">No</th>
                        <th width="40%">Nama</th>
                        <th width="15%">Total Dijawab</th>
                        <th width="15%">Total Score</th>
                        <th width="15%">Actual Score</th>
                        <th class="hidden"></th>
                        <th >Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1; foreach($reports as $key => $report) {
                    ?>
                        <tr>
                            <td class="hidden"><?=$key?></td>
                            <td data-title="id"><?php echo $i; ?></td>
                            <td data-title="namaKaryawan"><?=$report['nama']?></td>
                            <td><?= $report['total_dijawab'] ?></td>
                            <td><?= $report['total_score'] ?></td>
                            <td><?= $report['actual_score'] ?></td>
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
        var reports = <?php echo json_encode($reports)?>;
        var parentTable = $('#example1').DataTable({
            dom: 'Bfrtip',
            buttons: [
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

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(formatDetails(dt[0])).show();
                tr.addClass('shown');
            }
        });

        // Function to format the details to be displayed in the child row
        function formatDetails(idx) {
            var sub = reports[idx]['sub_kriteria'];
            var template = ``
            // console.log(sub)
            $.map(sub, function(val, i){
                template += `<div class='box' style='margin-bottom:20px'>`
                var verif = false;
                // Build the outer table with header
                template += `
                <div class='box-header'>
                    <p class='box-title' style='font-weight:bold'>${val.nama_kriteria}</p>
                </div>
                <div class='box-body'>
                    <table class="table table-bordered table-hover" style='margin-top:20px' id='table_${idx}-${val.level_report_id}'>
                        <tr>
                            <th rowspan='2'>Uraian</th>
                            <th rowspan='2'>Poin</th>
                            <th rowspan='2'>Jumlah</th>
                            <th rowspan='2'>Nilai</th>
                            <th rowspan='2'>Total</th>
                           
                            <th rowspan='2'>Surat Perkara</th>
                            <th colspan='3' class='text-center'>Verifikasi</th>
                            <th rowspan='2'>Catatan</th>
                        </tr>
                        <tr>
                            <th width='10%'>Jumlah</th>
                            <th>Nilai</th>
                            <th>Total</th>
                        </tr>
                `;
                // <th rowspan='2'>Bukti</th>
                $.map(val.sub_tahap, function(detailVal, detailKey) {
                    template += `
                        <tr>
                            <th colspan='10' class='h5 bg-orange-dark' style="font-weight:bold;">${detailVal.nama_tahap}</th>
                        </tr>
                        `

                    let no=0;
                    $.map(detailVal.detail_soal, function(soal, soalKey) {
                        no++
                        verif = soal.is_verif == 1? true: false;
                        template += `
                            <tr>
                                <td>${no}. ${soal.question}</td>
                                <td>${soal.mark}</td>
                                <td>${soal.value}</td>
                                <td>${soal.score}</td>
                                <td>${soal.total}</td>
                                
                                <td>${soal.s_perkara}</td>
                                `;
                        if(soal.is_verif == 1){
                            template += `
                                <td>${soal.actual_value}</td>
                                <td>${soal.actual_score}</td>
                                <td>${soal.total_actual_score}</td>
                                <td>${soal.catatan}</td>
                            `;
                        }else{
                            template += `
                                <td>
                                    <input class='form-control' type="number" min='0' name="input_${soal.onlineExamUserAnswerOptionID}" value="" data-q='${soal.questionBankID}' id="input_${soal.onlineExamUserAnswerOptionID}" data-id="${soal.onlineExamUserAnswerOptionID}">
                                </td>
                                <td>${soal.actual_score ? soal.actual_score : 0}</td>
                                <td>${soal.total_actual_score ? soal.total_actual_score : 0}</td>
                                <td>
                                    <textarea class='form-control' name="catatan_${soal.onlineExamUserAnswerOptionID}" id="catatan_${soal.onlineExamUserAnswerOptionID}" data-id='${soal.onlineExamUserAnswerOptionID}'></textarea>
                                </td>
                            `;
                        }
                        template +=`</tr><tr><td colspan='10'></td></tr>`;
                    });
                });
                template += `</table>`

                if(!verif){
                    template += `
                    <div class='row' style='margin-bottom:20px'>
                        <div class='col text-right'>
                            <button class="btn btn-success" style='margin-right:15px;margin-top:20px' data-status='${val.status_id}' data-group='${val.group_id}' data-index='${idx}-${val.level_report_id}'>Verifikasi</button>
                        </div>
                    </div>`;
                }
                template +='</div>'
            });

            return template;
        }

        $(document).on('click', '.btn-success', function() {
            var idx = $(this).data('index');  // Or whatever index you need
            var groupid = $(this).data('group');
            var statusid = $(this).data('status');
            verifikasi(idx, groupid, statusid);
        });

        function verifikasi(idx, groupid, statusid) {
            var inputs = $(`#table_${idx} input[data-id`);  // Select all inputs in the collapsed section
            var texts = $(`#table_${idx} textarea[data-id`);  // Select all inputs in the collapsed section
            var dataToSend = [];

            var jumlah = [];
            var ids = []
            var qids = []
            inputs.each(function() {
                var id = $(this).data('id');
                ids.push(id)
                var qid = $(this).data('q');
                qids.push(qid)

                jumlah.push($(this).val() ? parseInt($(this).val()) : 0);
            });

            var catatan = []
            texts.each(function() {
                var id = $(this).data('id');
                catatan.push($(this).val());
            });
            $.each(ids, function(idx, val){
                dataToSend.push({
                    'questionID':qids[idx],
                    'statusID': statusid,
                    'groupid':groupid,
                    'optionID' : val,
                    'jumlah' : jumlah[idx],
                    'catatan':catatan[idx]
                });
            });
            // console.log(dataToSend)

            $.ajax({
                type: 'POST',
                url: "<?=base_url('idcardreport/update_nilai')?>",
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


