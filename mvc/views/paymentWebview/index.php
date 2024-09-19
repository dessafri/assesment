<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Take exam</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="SHORTCUT ICON" href="<?=base_url('uploads/images/site.png')?>" />

    <link rel="stylesheet" href="<?=base_url('assets/pace/pace.css')?>">

    <script type="text/javascript" src="<?=base_url('assets/inilabs/jquery.min.js')?>"></script>
    <!-- <script type="text/javascript" src="<?=base_url('assets/slimScroll/jquery.slimscroll.min.js')?>"></script> -->

    <script type="text/javascript" src="<?=base_url('assets/toastr/toastr.min.js')?>"></script>


    <link href="<?=base_url('assets/bootstrap/bootstrap.min.css')?>" rel="stylesheet">

    <link href="<?=base_url('assets/fonts/font-awesome.css')?>" rel="stylesheet">

    <link href="<?=base_url('assets/fonts/icomoon.css')?>" rel="stylesheet">

    <link href="<?=base_url('assets/fonts/ini-icon.css')?>" rel="stylesheet">

    <link href="<?=base_url('assets/datatables/dataTables.bootstrap.css')?>" rel="stylesheet">

    <link id="headStyleCSSLink" href="<?=base_url('assets/inilabs/themes/default/style.css')?>" rel="stylesheet">

    <link href="<?=base_url('assets/inilabs/hidetable.css')?>" rel="stylesheet">

    <link id="headInilabsCSSLink" href="<?=base_url('assets/inilabs/themes/default/inilabs.css')?>" rel="stylesheet">

    <link href="<?=base_url('assets/inilabs/responsive.css')?>" rel="stylesheet">

    <link href="<?=base_url('assets/toastr/toastr.min.css')?>" rel="stylesheet">

    <link href="<?=base_url('assets/inilabs/mailandmedia.css')?>" rel="stylesheet">

    <link rel="stylesheet" href="<?=base_url('assets/datatables/buttons.dataTables.min.css')?>" >

    <link rel="stylesheet" href="<?=base_url('assets/inilabs/combined.css')?>" >

    <link rel="stylesheet" href="<?=base_url('assets/select2/css/select2.css')?>">
    <link rel="stylesheet" href="<?=base_url('assets/select2/css/select2-bootstrap.css')?>">
    <script type="text/javascript" src="<?=base_url('assets/select2/select2.js')?>"></script>

    <script type="text/javascript">
        $(window).load(function() {
            $(".se-pre-con").fadeOut("slow");
        });
    </script>
</head>


<body class="skin-blue fuelux">
<form class="form-horizontal" role="form" method="post" id="paymentAddDataForm" enctype="multipart/form-data" action="<?=base_url('paymentWebview/index/'.$loginuserID.'/'.$usertypeID.'/'.$onlineExamID)?>">
    <div class="modal fade" id="addpayment">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button"  class="close closeModel" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title"><?=$this->lang->line('take_exam_add_payment')?></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="text" name="onlineExamID" id="onlineExamID" style="display:none">
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <div class="form-group <?=form_error('paymentAmount') ? 'has-error' : ''; ?>" id="paymentAmountErrorDiv">
                                    <label for="paymentAmount"><?=$this->lang->line('take_exam_payment_amount')?> <span class="text-red">*</span></label>
                                    <input type="text" class="form-control" id="paymentAmount" name="paymentAmount" readonly="readonly">
                                    <span id="paymentAmountError"><?=form_error('paymentAmount')?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <div class="form-group <?=form_error('payment_method') ? 'has-error' : ''; ?>" id="payment_method_error_div">
                                    <label for="payment_method"><?=$this->lang->line('take_exam_payment_method')?> <span class="text-red">*</span></label>
                                    <?php
                                        $payment_method_array['select'] = $this->lang->line('take_exam_select_payment_method');
                                        if(customCompute($payment_settings)) {
                                            foreach($payment_settings as $payment_setting) {
                                                $payment_method_array[$payment_setting->slug] = $payment_setting->name;
                                            }
                                        }
                                        echo form_dropdown("payment_method", $payment_method_array, set_value("payment_method"), "id='payment_method' class='form-control select2'");
                                    ?>
                                    <span id="payment_method_error"><?=form_error('payment_method')?></span>
                                </div>
                            </div>
                        </div>

                        <?php
                        if(inicompute($payment_settings)) {
                            foreach($payment_settings as $payment_setting) {
                                if($payment_setting->misc != null) {
                                    $misc = json_decode($payment_setting->misc);
                                    if(inicompute($misc->input)) {
                                        foreach($misc->input as $input) {
                                            $this->load->view($input);
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default closeModel" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('close')?></button>
                    <input type="submit" id="add_payment_button" class="btn btn-success" value="<?=$this->lang->line("take_exam_add_payment")?>" />
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$js_gateway     = [];
$submit_gateway = [];
if(inicompute($payment_settings)) {
    foreach($payment_settings as $payment_setting) {
        if($payment_setting->misc != null) {
            $misc = json_decode($payment_setting->misc);
            if(inicompute($misc->js)) {
                foreach($misc->js as $js) {
                    $this->load->view($js);
                }
            }

            if(inicompute($misc->input)) {
                if(isset($misc->input[0])) {
                    $js_gateway[$payment_setting->slug] = isset($misc->input[0]);
                }
            }

            if(inicompute($misc->input)) {
                if(isset($misc->submit) && $misc->submit) {
                    $submit_gateway[$payment_setting->slug] = $misc->submit;
                }
            }
        }
    }
}

$js_gateway     = json_encode($js_gateway);
$submit_gateway = json_encode($submit_gateway);
?>

<script type="text/javascript">
    const gateway = <?=$js_gateway?>;
    const submit_gateway = <?=$submit_gateway?>;
    let form = document.getElementById('paymentAddDataForm');
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        let payment_method = $('#payment_method').val();
        if(payment_method == 'select') {
            $('#payment_method_error').parent().addClass('has-error');
            $('#payment_method_error_div').parent().addClass('has-error');
        }else {
            $('#payment_method_error').parent().removeClass('has-error');
            $('#payment_method_error_div').parent().removeClass('has-error');

            let submit = false;
            for(let item in submit_gateway) {
                if(item == payment_method) {
                    submit = true;
                    window[payment_method + '_payment']();
                    break;
                }
            }

            if(submit == false) {
                form.submit();
            }
        }

    });


    function runner() {
        url = localStorage.getItem('redirect_url');
        if(url) {
            localStorage.clear();
            window.location = url;
        }
        setTimeout(function() {
            runner();
        }, 500);
    }

    $(document).change(function() {
        let payment_method = $('#payment_method').val();
        for(let item in gateway) {
            if(item == payment_method) {
                if(gateway[item]) {
                    $('#' + item + '_div').show();
                }
            } else {
                $('#' + item + '_div').hide();
            }
        }
    });

    function getPaymentAmount() {
        var onlineExamID =  '<?=$onlineExamID?>';
        if(onlineExamID > 0) {
            $('#onlineExamID').val(onlineExamID);
            $.ajax({
                type: 'POST',
                url: "<?=base_url('paymentWebview/get_payment_info')?>",
                data: {'onlineExamID' : onlineExamID},
                dataType: "html",
                success: function(data) {
                    $('#paymentAmount').val('');
                    var response = JSON.parse(data);
                    console.log(response);
                    if(response.status == true) {
                        $('#paymentAmount').val(response.payableamount);
                    } else {
                        $('#paymentAmount').val('0.00');
                    }
                }
            });
        }
    }
    getPaymentAmount();

</script>

<?php if(inicompute($validationErrors)) { ?>
    <script type="application/javascript">
        $(window).load(function() {
            var onlineExamID =  "<?=$validationOnlineExamID?>";
            if(onlineExamID > 0) {
                $('#onlineExamID').val(onlineExamID);
                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('paymentWebview/get_payment_info')?>",
                    data: {'onlineExamID' : onlineExamID},
                    dataType: "html",
                    success: function(data) {
                        $('#paymentAmount').val('');
                        var response = JSON.parse(data);
                        if(response.status == true) {
                            $('#paymentAmount').val(response.payableamount);
                        } else {
                            $('#paymentAmount').val('0.00');
                        }
                    }
                });
            }

            $('#addpayment').modal('show');
            let payment_method = $('#payment_method').val();
            for(let item in gateway) {
                if(item == payment_method) {
                    if(gateway[item]) {
                        $('#' + item + '_div').show();
                    }
                } else {
                    $('#' + item + '_div').hide();
                }
            }
        });
    </script>
<?php } ?>

<script type="text/javascript"  src="<?=base_url('assets/bootstrap/bootstrap.min.js')?>"></script>
<!-- Style js -->
<script type="text/javascript" src="<?=base_url('assets/inilabs/style.js')?>"></script>
<script type="text/javascript" src="<?=base_url('assets/inilabs/inilabs.js')?>"></script>
<script>
    $('#addpayment').modal({backdrop: 'static', keyboard: false}, 'show');
    $('#addpayment').modal('show');

    $('.closeModel').on('click',function(data) {
        location.replace('<?=base_url("paymentWebview/cancel")?>')
    })

</script>

 </body>
</html>
