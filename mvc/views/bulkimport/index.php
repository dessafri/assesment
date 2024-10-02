<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-upload"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_import_question')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <form enctype="multipart/form-data" style="" action="<?=base_url('bulkimport/question_bulkimport');?>" class="form-horizontal" role="form" method="post">
                    <div class="form-group">
                        <label for="photo" class="col-sm-2 control-label col-xs-8 col-md-2">
                            Upload Employee
                            &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Download the parent sample csv file first then see the format and make a copy of that file and add your data with exact format which is used in our csv file then upload the file."></i>
                        </label>
                        <div class="col-sm-3 col-xs-4 col-md-3">
                            <input class="form-control parent" id="uploadFile" placeholder="Choose File" disabled />
                        </div>

                        <div class="col-sm-2 col-xs-6 col-md-2">
                            <div class="fileUpload btn btn-success form-control">
                                <span class="fa fa-repeat"></span>
                                <span><?=$this->lang->line("bulkimport_upload")?></span>
                                <input id="uploadBtn" type="file" class="upload parentUpload" name="csvQuestion" />
                            </div>
                        </div>

                        <div class="col-md-1 rep-mar">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("bulkimport_import")?>" >
                        </div>

                        <div class="col-md-1 rep-mar">
                            <a class="btn btn-info" href="<?=base_url('assets/csv/sample-question.csv')?>"><i class="fa fa-download"></i> <?=$this->lang->line("bulkimport_download_sample")?></a>
                        </div>
                    </div>
                </form>
                <form enctype="multipart/form-data" style="" action="<?=base_url('bulkimport/relation_bulkimport');?>" class="form-horizontal" role="form" method="post">
                    <div class="form-group">
                        <label for="photo" class="col-sm-2 control-label col-xs-8 col-md-2">
                            Upload Data Relasi Menilai
                            &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Download the parent sample csv file first then see the format and make a copy of that file and add your data with exact format which is used in our csv file then upload the file."></i>
                        </label>
                        <div class="col-sm-3 col-xs-4 col-md-3">
                            <input class="form-control parent" id="uploadFile" placeholder="Choose File" disabled />
                        </div>

                        <div class="col-sm-2 col-xs-6 col-md-2">
                            <div class="fileUpload btn btn-success form-control">
                                <span class="fa fa-repeat"></span>
                                <span><?=$this->lang->line("bulkimport_upload")?></span>
                                <input id="uploadBtn" type="file" class="upload parentUpload" name="csvRelation" />
                            </div>
                        </div>

                        <div class="col-md-1 rep-mar">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("bulkimport_import")?>" >
                        </div>

                        <div class="col-md-1 rep-mar">
                            <a class="btn btn-info" href="<?=base_url('assets/csv/sample-question.csv')?>"><i class="fa fa-download"></i> <?=$this->lang->line("bulkimport_download_sample")?></a>
                        </div>
                    </div>
                </form>


                <?php if ($this->session->flashdata('msg')): ?>
                    <div class="callout callout-danger">
                        <h4>These data not inserted</h4>
                        <p><?=$this->session->flashdata('msg'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->
<script type="text/javascript">
    document.getElementById("uploadBtn").onchange = function() {
        document.getElementById("uploadFile").value = this.value;
    };

    $('.parentUpload').on('change', function() {
        $('.parent').val($(this).val());
    });

</script>
