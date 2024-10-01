        </div><!-- ./wrapper -->

        <script type="text/javascript" src="<?php echo base_url('assets/bootstrap/bootstrap.min.js'); ?>"></script>
        <!-- Style js -->
        <script type="text/javascript" src="<?php echo base_url('assets/inilabs/style.js'); ?>"></script>

        <!-- Jquery datatable tools js -->
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/jquery.dataTables.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/dataTables.buttons.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/jszip.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/pdfmake.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/vfs_fonts.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/buttons.html5.min.js'); ?>"></script>
        <!-- dataTables Tools / -->
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/dataTables.bootstrap.js'); ?>"></script>

        <script type="text/javascript" src="<?php echo base_url('assets/inilabs/inilabs.js'); ?>"></script>

        <script>
            $(document).ready(function() {
                // $('#example3, #example1, #example2').DataTable({
                //     dom: 'Bfrtip',
                //     buttons: [
                //         {
                //             extend: 'copyHtml5',
                //             exportOptions: {
                //                 columns: ':visible'
                //             }
                //         },
                //         {
                //             extend: 'excelHtml5',
                //             exportOptions: {
                //                 columns: ':visible'
                //             }
                //         },
                //         {
                //             extend: 'csvHtml5',
                //             exportOptions: {
                //                 columns: ':visible',
                //                 format: {
                //                 body: function(data, row, column, node) {
                //                     // Customize data formatting
                //                     // Example: Remove HTML tags and trim spaces
                //                     return data.replace(/<.*?>/g, '').trim();
                //                 }
                //             }
                //             }
                //         },
                //         {
                //             extend: 'pdfHtml5',
                //             exportOptions: {
                //                 columns: ':visible'
                //             }
                //         }
                //     ],
                //     search: false,
                //     columnDefs: [
                //         {
                //             targets: 'hidden-data',
                //             visible: false
                //         }
                //     ]
                // });
                
            });
        </script>

        <script type="text/javascript">
            $(function() {
                $("#withoutBtn").dataTable();
            });
        </script>

        <?php if ($this->session->flashdata('success')): ?>
            <script type="text/javascript">
                toastr["success"]("<?=$this->session->flashdata('success');?>")
                toastr.options = {
                    "closeButton": true,
                    "allowHtml": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "500",
                    "hideDuration": "500",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
            </script>
            <?php $this->session->unset_userdata('success');?>
        <?php endif ?>
        <?php if ($this->session->flashdata('error')): ?>
           <script type="text/javascript">
                toastr["error"]("<?=$this->session->flashdata('error');?>")
                toastr.options = {
                    "closeButton": true,
                    "allowHtml": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "500",
                    "hideDuration": "500",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
            </script>
            <?php $this->session->unset_userdata('error');?>
        <?php endif ?>

        <?php
            if(isset($footerassets)) {
                foreach ($footerassets as $assetstype => $footerasset) {
                    if($assetstype == 'css') {
                        if(inicompute($footerasset)) {
                            foreach ($footerasset as $keycss => $css) {
                                echo '<link rel="stylesheet" href="'.base_url($css).'">'."\n";
                            }
                        }
                    } elseif($assetstype == 'js') {
                        if(inicompute($footerasset)) {
                            foreach ($footerasset as $keyjs => $js) {
                                echo '<script type="text/javascript" src="'.base_url($js).'"></script>'."\n";
                            }
                        }
                    }
                }
            }
        ?>

        <script type="text/javascript">
            $("ul.sidebar-menu li").each(function(index, value) {
                if($(this).attr('class') == 'active') {
                    $(this).parents('li').addClass('active');
                }
            });

            $(document).ready(function () {
                setTimeout(function () {
                    $.ajax({
                        type : 'GET',
                        dataType : "html",
                        url : "<?=base_url('alert/alert')?>",
                        success : function (data) {
                            $(".my-push-message-list").html(data);
                            var alertNumber = 0;
                            $('.my-push-message-list li').each(function () {
                                alertNumber++;
                            });
                            if (alertNumber > 0) {
                                $('.my-push-message-ul').removeAttr('style');
                                $('.my-push-message-a').append('<span class="label label-danger"><lable class="alert-image">' + alertNumber + '</lable> </span>');
                                $('.my-push-message-number').html('<?=$this->lang->line("la_fs") . " "?>' + alertNumber + '<?=" " . $this->lang->line("la_ls")?>');
                            } else {
                                $('.my-push-message-ul').remove();
                            }
                        }
                    });
                }, 10000);
          });
      </script>
    </body>
</html>
