<div id="paystack_div" style="display: none;">
    <div class="col-sm-6">
        <div class="col-sm-12">
            <div class="form-group <?=form_error('email') ? 'has-error' : ''; ?>" >
                <label for="email"><?=$this->lang->line("take_exam_email")?></label> <span class="text-red">*</span>
                <input type="email" class="form-control" id="email" name="email" value="<?=set_value('email', null)?>" >
                <span class="text-red"><?php echo form_error('email'); ?></span>
            </div>
        </div>
    </div>
</div>