
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-sitemap"></i> Departements</h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <?php if($siteinfos->school_type == 'classbase') { ?>
                <li class="active">Departement</li>
            <?php } else { ?>
                <li class="active">Departement</li>
            <?php } ?>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">

                <?php 
                    $usertype = $this->session->userdata("usertype");
                    if(permissionChecker('classes_add')) {
                ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('classes/add') ?>">
                            <i class="fa fa-plus"></i> 
                            <?=$this->lang->line('add_title')?>
                        </a>
                    </h5>
                <?php } ?>

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-lg-1">No</th>
                                <th class="col-lg-1">Departement_ID</th>
                                <th class="col-lg-2">Departement</th>
                                 <?php if(permissionChecker('classes_edit') || permissionChecker('classes_delete')) { ?>
                                <th class="col-lg-2"><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(inicompute($classes)) {$i = 1; foreach($classes as $class) { ?>
                                <tr>
                                    <td data-title="No">
                                        <?php echo $i; ?>
                                    </td>
                                    <td data-title="Departement_ID">
                                        <?php echo $class->classesID; ?>
                                    </td>
                                    <td data-title="Departement">
                                        <?php echo $class->classes; ?>
                                    </td>
                                    <?php if(permissionChecker('classes_edit') || permissionChecker('classes_delete')) { ?>
                                    <td data-title="<?=$this->lang->line('action')?>">
                                        <?php echo btn_edit('classes/edit/'.$class->classesID, $this->lang->line('edit')) ?>
                                        <?php echo btn_delete('classes/delete/'.$class->classesID, $this->lang->line('delete')) ?>
                                    </td>
                                    <?php } ?>
                                </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
