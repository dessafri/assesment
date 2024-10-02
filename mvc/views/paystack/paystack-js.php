
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    function paystack_payment()
    {
        let paystackPublicKey = '<?=$payment_options['paystack_key']?>';
        let currency = '<?=$payment_options['paystack_currency']?>';
        $('#payment_method').parent().removeClass('has-error');

        let email  = document.getElementById('email').value;
        let payment_amount  = document.getElementById('paymentAmount').value;
        let amount = Number(payment_amount);

        let error = 0;

        $('#email').parent().removeClass('has-error');
        if(email == '') {
            let error = 1;
            $('#email').parent().addClass('has-error');
        } else if(!isEmail(email)) {
            let error = 1;
            $('#email').parent().addClass('has-error');
        }

        if(payment_amount == '') {
            let error = 1;
            $('#amount').parent().addClass('has-error');
        }

        if(error == 0) {
            let handler = PaystackPop.setup({
                key: paystackPublicKey, // Replace with your public key
                email: email,
                amount: amount * 100, // the amount value is multiplied by 100 to convert to the lowest currency unit
                currency: currency, // Use GHS for Ghana Cedis or USD for US Dollars
                callback: function(response) {
                    if(response.status) {
                        paystackReferenceHandler(response.reference);
                    } else {
                        console.log(response.message);
                    }
                },
                onClose: function() {
                    location.reload();
                },
            });
            handler.openIframe();
        }
    }

    function paystackReferenceHandler(reference) {
        let form = document.getElementById('paymentAddDataForm');
        let hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'paystackReference');
        hiddenInput.setAttribute('value', reference);
        form.appendChild(hiddenInput);
        form.submit();
    }

    function isEmail(email) {
        let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }
</script>