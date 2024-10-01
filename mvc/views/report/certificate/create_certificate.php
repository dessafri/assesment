<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-certificate"></i> Laporan Bulanan</h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("certificatereport/index")?>">Laporan Bulanan</a></li>
            <li class="active">Add</li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                
                <form class="form-horizontal" action="<?php echo base_url('certificatereport/cretae_laporan'); ?>" role="form" method="post" id="laporan_bulanan"
                     enctype="multipart/form-data">
                    <?php
                        if (form_error('name')) {
                            echo "<div class='form-group has-error' >";
                        } else {
                            echo "<div class='form-group' >";
                        }

                        ?>
                        <label for="name" class="col-sm-1 control-label">
                            Nama <span class='text-red'>*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="name" name="name" value="<?=set_value('name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('name'); ?>
                        </span>
                    </div>
                    <?php
                    if (form_error('file')) {
                        echo "<div class='form-group has-error' >";
                    } else {
                        echo "<div class='form-group' >";
                    }

                    ?>
                    <label for="file" class="col-sm-1 control-label">
                        Dokumen <span class='text-red'>*</span>
                    </label>
                    <div class="col-sm-6">
                        <div class="input-group image-preview">
                            <input type="text" class="form-control image-preview-filename" disabled="disabled">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                    <span class="fa fa-remove"></span>
                                    <?=$this->lang->line('question_bank_clear')?>
                                </button>
                                <div class="btn btn-success image-preview-input">
                                    <span class="fa fa-repeat"></span>
                                    <span class="image-preview-input-title">
                                    Unggah File</span>
                                    <input type="file" name="file"/>
                                    <?php echo form_error('file'); ?>
                                </div>
                            </span>
                        </div>
                    </div>
                    <br>
                    <div class="form-group">
                        <div class="col-sm-offset-1 col-sm-8" style="padding-left: 28px; margin-top: 20px;">
                        <button type="submit" class="btn btn-success">Submit Laporan</button>
                        </div>
                    </div>
                    <!-- <div class="form-group">
                        <div class="col-sm-1 mt-3 offset-2 col-sm-8">
                            <button type="submit" class="btn btn-success">Upload Laporan</button>
                        </div>
                    </div> -->
                </form>
                <hr>
                
                
                
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
        })
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
