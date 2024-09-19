<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-reset_password"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_resetpassword')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" role="form" method="POST">
                    
                    <div class='form-group <?=form_error('usertypeID') ? 'has-error' : ''?>'>
                        <label for="usertypeID" class="col-sm-2 control-label">
                            <?=$this->lang->line("resetpassword_usertype")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                           <?php
                                $usertypeArray[0] = $this->lang->line("resetpassword_select_usertype");
                                if(inicompute($usertypes)) {
                                    foreach ($usertypes as $usertype) {
                                        $usertypeArray[$usertype->usertypeID] = $usertype->usertype;
                                    }
                                }
                                echo form_dropdown("usertypeID", $usertypeArray, set_value("usertypeID"), " id='usertypeID' class='form-control select2' autocomplete='off' ");
                            ?>
                        </div>
                        <div class="col-sm-4 control-label">
                            <?=form_error('usertypeID'); ?>
                        </div>
                    </div>

                    <div class='form-group <?=form_error('userID') ? 'has-error' : ''?>'>
                        <label for="userID" class="col-sm-2 control-label">
                            <?=$this->lang->line("resetpassword_user")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $userArray[0]  = $this->lang->line("resetpassword_select_user");
                                if(inicompute($users)) {
                                    $tableID   = $tableInfo['tableID'];
                                    foreach ($users as $user) {
                                        if($tableID == 'systemadminID') {
                                            if($user->$tableID != 1) {
                                                $userArray[$user->$tableID] = $user->name." ( ".$user->username." )";
                                            }
                                        } else {
                                            $userArray[$user->$tableID] = $user->name." ( ".$user->username." )";
                                        }
                                    }
                                }
                                echo form_dropdown("userID", $userArray, set_value("userID"), " id='userID' class='form-control select2' autocomplete='off' ");
                            ?>
                        </div>
                        <div class="col-sm-4 control-label">
                            <?=form_error('userID'); ?>
                        </div>
                    </div>

                    <div class='form-group <?=form_error('new_password') ? 'has-error' : ''?>'>
                        <label for="new_password" class="col-sm-2 control-label">
                            <?=$this->lang->line("resetpassword_new_password")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="off">
                        </div>
                         <div class="col-sm-4 control-label">
                            <?=form_error('new_password'); ?>
                        </div>
                    </div>

                    <div class='form-group <?=form_error('re_password') ? 'has-error' : ''?>'>
                        <label for="re_password" class="col-sm-2 control-label">
                            <?=$this->lang->line("resetpassword_re_password")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="re_password" name="re_password" autocomplete="off">
                        </div>
                         <div class="col-sm-4 control-label">
                            <?=form_error('re_password'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("resetpassword")?>" >
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(".select2").select2();

    $('#usertypeID').change(function() {
        var usertypeID = $(this).val();
        $.ajax({
            type: 'POST',
            url: "<?=base_url('resetpassword/get_user')?>",
            data: {'usertypeID': usertypeID},
            dataType: "html",
            success: function(data) {
               $('#userID').html(data);
            }
        });
    });

</script>

