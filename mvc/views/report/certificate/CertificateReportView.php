<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-certificate"></i> Laporan Bulanan</h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active">Laporan Bulnan</li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
        
            <div class="col-sm-12">
            <h5 class="page-header">
            <?php if ($this->session->userdata('usertypeID') != 1) {?>
            <a href="<?php echo base_url('certificatereport/add_laporan') ?>">
                <i class="fa fa-plus"></i>
                Upload Laporan
            </a>
            <?php }?>
                    </h5>
            <?php if ($this->session->userdata('usertypeID') == 1) { ?>
            <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                <thead>
                    <tr>
                        <th width="2%">#</th>
                        <th width="70%">Rsponsible</th>
                        <th width="2%"> Action </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1; foreach($data_sudent as $key => $report) {
                    ?>
                        <tr>
                            <!-- <td class="hidden"><?=$key?></td> -->
                            <td data-title="id"><?php echo $i; ?></td>
                            <td data-title="namaKaryawan"><?=$report->name?></td>
                             <td>
                                <!-- <a href="<?= base_url('certificatereport/update_status/' . strval($report->id))?>" class="fa fa-eye" >Verifikasi</a> -->
                                 <button class="btn btn-primary showDetails" id="fufu<?= $report->studentID ?>"><i class="fa fa-bars"></i></button>
                                
                            </td>
                            
                        </tr>
                    <?php $i++; }
                     ?>
                </tbody>
            </table>
            
    <!-- Kondisi pertama: jika usertypeID tidak sama dengan 1 -->
            <?php } else { ?>
                <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                <thead>
                    <tr>
                        <th width="2%">No</th>
                        <th>Responsible</th>
                        <th width="20%">Nama</th>
                        <th width="30%">File Name</th>
                        <th> Status </th>
                        <th> Date </th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1; foreach($subresult as $key => $report) {
                    ?>
                        <tr>
                            <!-- <td class="hidden"><?=$key?></td> -->
                            <td data-title="id"><?php echo $i; ?></td>
                            <td data-title="namaKaryawan"><?=$report->p_name?></td>
                            <td data-title="namaKaryawan"><?=$report->name?></td>
                            <td><a href="<?= base_url('certificatereport/download/'.strval($report->file))?>"><?= $report->original_name ?></a></td>
                            <?php
                            if ($report->is_verified == 1){
                                $state = 'Verified';
                            }else{
                                $state = 'Not Verified';
                            }
                            ?>
                             <td><?= $state ?></td>
                             <td><?= $report->date ?></td>
                             <td>
                                <?php
                                if ($this->session->userdata('usertypeID') == 1){
                                 ?>
                                 <?php 
                                 if ($report->is_verified == 0)
                                 { ?>
                                 <a href="<?= base_url('certificatereport/update_status/' . strval($report->id))?>" class="btn btn-success" >Verifikasi</a>
                                 <?php } ?>
                                 
                                 <?php }?>
                                
                            </td>
                            
                        </tr>
                    <?php $i++; }
                     ?>
                </tbody>
            </table>
                <!-- Kondisi kedua: jika usertypeID sama dengan 1 -->
            <?php } ?>
            
            
                <!-- <div class="form-group col-sm-4" id="classesDiv">
                    <label><?=$this->lang->line("certificatereport_class")?></label><span class="text-red">*</span>
                    <?php
                        $array = array("0" => $this->lang->line("certificatereport_please_select"));
                        if(inicompute($classes)) {
                            foreach ($classes as $classa) {
                                 $array[$classa->classesID] = $classa->classes;
                            }
                        }
                        echo form_dropdown("classesID", $array, set_value("classesID"), "id='classesID' class='form-control select2'");
                     ?>
                </div>

                <div class="form-group col-sm-4" id="sectionDiv">
                    <label><?=$this->lang->line("certificatereport_section")?></label>
                    <select id="sectionID" name="sectionID" class="form-control select2">
                        <option value="0"><?php echo $this->lang->line("certificatereport_please_select"); ?></option>
                    </select>
                </div>

                <div class="form-group col-sm-4" id="templateDiv">
                    <label><?=$this->lang->line("certificatereport_template")?></label> <span class="text-red">*</span>
                    <?php
                        $templateArray = array("0" => $this->lang->line("certificatereport_please_select"));
                        if(inicompute($templates)) {
                            foreach ($templates as $template) {
                                 $templateArray[$template->certificate_templateID] = $template->name;
                            }
                        }
                        echo form_dropdown("templateID", $templateArray, set_value("templateID"), "id='templateID' class='form-control select2'");
                     ?>
                </div>

                <div class="col-sm-4">
                    <button id="get_student_list" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("certificatereport_submit")?></button>
                </div> -->
            </div>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div class="box" id="load_certificatereport"></div>
<script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/jquery.dataTables.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/dataTables.buttons.min.js'); ?>"></script>
<!-- dataTables Tools / -->
<script type="text/javascript" src="<?php echo base_url('assets/datatables/dataTables.bootstrap.js'); ?>"></script>
<script>
    // $(document).on('click', '#close-preview', function(){
    //     $('.image-preview').popover('hide');
    //     // Hover befor close the preview
    //     $('.image-preview').hover(
    //         function () {
    //            $('.image-preview').popover('show');
    //            $('.content').css('padding-bottom', '100px');
    //         },
    //          function () {
    //            $('.image-preview').popover('hide');
    //            $('.content').css('padding-bottom', '20px');
    //         }
    //     );
    // });

    $(function() {
        // Create the close button
        var closebtn = $('<button/>', {
            type:"button",
            text: 'x',
            id: 'close-preview',
            style: 'font-size: initial;',
        });
        closebtn.attr("class","close pull-right");
        // Set the popover default content
        $('.image-preview').popover({
            trigger:'manual',
            html:true,
            title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
            content: "There's no image",
            placement:'bottom'
        });
        // Clear event
        $('.image-preview-clear').click(function(){
            $('.image-preview').attr("data-content","").popover('hide');
            $('.image-preview-filename').val("");
            $('.image-preview-clear').hide();
            $('.image-preview-input input:file').val("");
            $(".image-preview-input-title").text("<?=$this->lang->line('question_bank_file_browse')?>");
        });
        // Create the preview image
        $(".image-preview-input input:file").change(function (){
            var img = $('<img/>', {
                id: 'dynamic',
                width:250,
                height:200,
                overflow:'hidden'
            });
            var file = this.files[0];
            var reader = new FileReader();
            // Set preview image into the popover data-content
            reader.onload = function (e) {
                $(".image-preview-input-title").text("<?=$this->lang->line('question_bank_file_browse')?>");
                $(".image-preview-clear").show();
                $(".image-preview-filename").val(file.name);
                img.attr('src', e.target.result);
                // $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");    
                $('.content').css('padding-bottom', '100px');
            }
            reader.readAsDataURL(file);
        });
    });
</script>
<script>
    
    $(document).ready(function () {
        var reports = <?php echo $data_sudent ?>;
        var data_sudent = <?php echo $data_sudent?>;
        var parentTable = $('#example1').DataTable({
            dom: 'Bfrtip',
            buttons: [
                
                
                // {
                //     extend: 'pdfHtml5',
                //     exportOptions: {
                //         columns: ':visible'
                //     }
                // }
            ],
        });
        // $.each(reports, function(key, value) {
        //     $(document).on('click', '#fufu', function() {
        //         console.log("Key: " + key + ", Value: " + value.name);
        //     });
        // });
        // $('#example1 tbody').on('click', '.showDetails', function() {
        //         var tr = $(this).closest('tr');
        //         var row = parentTable.row(tr);
        //         var dt = row.data();
        //         console.log('test');

        //         if (row.child.isShown()) {
        //             row.child.hide();
        //             tr.removeClass('shown');
        //         } else {
        //             row.child(formatDetails(dt[0])).show();
        //             tr.addClass('shown');
        //         }
        //     });
            
    });
        
</script>

<script>
    var student = <?php echo json_encode($data_sudent) ?>;
    // $(document).ready(function() {
    //     console.log(student);
    // });
    $.each(student, function(key, value) {
        $("#fufu" + value.studentID).click(function() {
            $.ajax({
                url: `<?= base_url("certificatereport/get_bulan/") ?>${value.studentID}`,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // console.log(data);
                    $.each(data, function(key, vals) {
                        var uniq = vals.student_id + vals.month + vals.year;
                        var detailRow = $('#detail' + uniq);
                        // var detailview = $('#fafa' + uniq);
                        if (detailRow.length === 0) {
                            var detailContent = `<tr id="detail${uniq}">
                            <td colspan="2"><h5><strong>Laporan Bulan ${vals.bulan_nama}  ${vals.year}</strong></h5></td>
                            <td><button class="btn btn-primary showDetails" id="fafa${uniq}"><i class="fa fa-eye" aria-hidden="true"></i></button></td>
                            </tr>`;
                            $('#fufu' + vals.student_id).closest('tr').after(detailContent);
                            $("#fafa" + uniq).click(function() {
                                console.log('test fafa')
                                var uniq2 =  $('#list_user' + uniq);
                                if (uniq2.length === 0) {
                                    var content_list = `
                                        <tr id="list_user${uniq}">
                                            <td colspan="3">
                                                <div style="width: 100%" class='box' style='margin-bottom:20px'>
                                                    <div class='box-body'>
                                                        <table class="table table-striped table-bordered table-hover dataTable no-footer">
                                                            <thead>
                                                                <tr>
                                                                    <th width="2%">No</th>
                                                                    <th>Responsible</th>
                                                                    <th width="20%">Nama</th>
                                                                    <th width="30%">File Name</th>
                                                                    <th> Status </th>
                                                                    <th> Date </th>
                                                                    <th >Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="body${uniq}">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                        </tr>`;
                                    // console.log(vals.student_id);
                                    $.ajax({
                                        url: `<?= base_url("certificatereport/get_student_by_period") ?>`,
                                        type: 'POST',
                                        data: {
                                            student_id : Number(vals.student_id),
                                            month : vals.month,
                                            year : vals.year, 
                                        },
                                        dataType: 'json',
                                        success: function(response) {
                                            $.each(response, function(key, valss) {
                                                // console.log(valss);
                                                var state = '';
                                                if (valss.is_verified == 1)
                                                {
                                                    state = 'Verified';
                                                }
                                                else{
                                                    state = 'Not Verified';
                                                }
                                                var body = '';
                                                $('#body' + uniq).append
                                                body += `
                                                    <tr>
                                                        <td>${key + 1}</td>
                                                        <td>${valss.s_name}</td>
                                                        <td>${valss.name}</td>
                                                        <td>${valss.oridinal_name}</td>
                                                        <td>${state}</td>
                                                        <td>${valss.date}</td>
                                                    </tr>
                                                `;
                                                $('#body' + uniq).append(body);
                                            });
                                            
                                        },
                                        error:function(){
                                            console.log('error retrive data');
                                        }
                                    });
                                    $('#detail' + uniq).closest('tr').after(content_list);
                                }else{
                                    uniq2.remove();
                                }
                            });
                        } else {
                            detailRow.remove();
                            // detailview.remove();
                        }
                    });
                    
                },
                error: function() {
                    console.log('error retrive data');
                }
            });
        
        });
    });
    
</script>

<script type="text/javascript">
    $('.select2').select2();
    function printDiv(divID) {
        //Get the HTML of div
        var divElements = document.getElementById(divID).innerHTML;
        //Get the HTML of whole page
        var oldPage = document.body.innerHTML;
        //Reset the page's HTML with div's HTML only
        document.body.innerHTML =
            "<html><head><title></title></head><body>" +
            divElements + "</body>";
        //Print Page
        window.print();
        //Restore orignal HTML
        document.body.innerHTML = oldPage;
    }

    function divHide(){
        $('#sectionDiv').hide('slow');  
        $('#templateDiv').hide('slow');  
    }

    function divShow(){
        $('#sectionDiv').show('slow');  
        $('#templateDiv').show('slow');  
    }

    $(document).ready(function() {
        divHide();
    });

    $("#classesID").change(function() {
        var id = $(this).val();
        if(id == '0') {
            divHide();
        } else {
            divShow()
        }

        if(id == '0') {
            $('#sectionID').html('<option value="">'+"<?=$this->lang->line("certificatereport_please_select")?>"+'</option>');
            $('#sectionID').val('');    
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('certificatereport/getSection')?>",
                data: {"id" : id},
                dataType: "html",
                success: function(data) {
                   $('#sectionID').html(data);
                }
            });
        }
    });

    $("#get_student_list").click(function() {
        var error = 0 ;
        var field ={
            'classesID' : $('#classesID').val(), 
            'sectionID' : $('#sectionID').val(), 
            'templateID' : $('#templateID').val(), 
        }

        if (field['classesID'] == 0) {
            $('#classesDiv').addClass('has-error');
            error++;
        } else {
            $('#classesDiv').removeClass('has-error');
        }


        if (field['templateID'] == 0) {
            $('#templateDiv').addClass('has-error');
            error++;
        } else {
            $('#templateDiv').removeClass('has-error');
        }

        if(error === 0) {
            makingPostDataPreviousofAjaxCall(field);
        }
    });

    function makingPostDataPreviousofAjaxCall(field) {
        passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('certificatereport/getStudentList')?>",
            data: passData,
            dataType: "html",
            success: function(data) {
                var response = JSON.parse(data);
                renderLoder(response, passData);
            }
        });
    }

    function renderLoder(response, passData) {
        if(response.status) {
            $('#load_certificatereport').html(response.render);
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
