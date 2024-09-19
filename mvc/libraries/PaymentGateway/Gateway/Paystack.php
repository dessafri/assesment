<?php

require_once(dirname(__FILE__, 2) . '/PaymentAbstract.php');
require_once(dirname(__FILE__, 2) . '/Service/PaymentService.php');

class Paystack extends PaymentAbstract
{

    public $session;
    public $params;
    public $url;
    public $paystack_reference;

    public function __construct()
    {
        parent::__construct();
        $this->ci->lang->load('paystack_rules', $this->ci->session->userdata('lang'));
        $paymentWebview= $this->ci->session->userdata('paymentWebview') ?? false;
        $this->url = $paymentWebview ? base_url("paymentWebview/paymentSuccess") : base_url("take_exam/index");
    }

    public function rules() : array
    {
        return [
            [
                'field' => 'payment_type',
                'label' => $this->ci->lang->line("paystack_payment_type"),
                'rules' => 'trim|required'
            ],
            [
                'field' => 'paystack_key',
                'label' => $this->ci->lang->line("paystack_key"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paystack_secret',
                'label' => $this->ci->lang->line("paystack_secret"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paystack_demo',
                'label' => $this->ci->lang->line("paystack_demo"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ],
            [
                'field' => 'paystack_status',
                'label' => $this->ci->lang->line("paystack_status"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ],

            [
                'field' => 'paystack_currency',
                'label' => $this->ci->lang->line("paystack_currency"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ],
        ];
    }

    public function payment_rules() : array
    {
        return [];
    }

    public function status() : bool
    {
        $stripe_status = $this->ci->payment_gateway_m->get_single_payment_gateway(['slug'   => 'paystack',
                                                                                   'status' => 1
        ]);
        return is_object($stripe_status);
    }

    public function cancel()
    {
        redirect($this->url);
    }

    public function fail()
    {
        $this->ci->session->set_flashdata('error', 'The payment is fail');
        redirect($this->url);
    }

    public function payment( $array, $invoice )
    {
        $this->params = [
            'online_exam_id' => $array['onlineExamID'],
            'description'    => $invoice->name,
            'amount'         => (float) $invoice->cost,
            'currency'       => $this->setting->currency_code
        ];

        $this->paystack_reference = $array['paystackReference'];
        $this->response           = $this->paystackVerifyPayment($this->payment_Setting_option['paystack_secret'], $this->paystack_reference);
        $this->success();
    }

    public function success()
    {
        if($this->response['status'] && $this->paystack_reference) {
            $paymentService = new PaymentService($this->paystack_reference);
            $paymentService->add_transaction([
                'online_exam_id' => $this->params['online_exam_id'],
                'amount'         => $this->params['amount'],
                'payment_method' => 'paystack'
            ]);
        } else {
            $this->session->set_flashdata('error', "Payment not success!");
        }
        redirect($this->url);
    }

    private function paystackVerifyPayment( $secretKey, $referenceKey )
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://api.paystack.co/transaction/verify/$referenceKey",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer $secretKey",
                "Cache-Control: no-cache",
            ],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);

        $return = [
            'status'  => false,
            'message' => ''
        ];

        if($err !== '' && $err !== '0') {
            $return['message'] = "cURL Error #:" . $err;
        } else {
            $response = json_decode($response, true);
            if(isset($response['status']) && $response['status']) {
                $return['status'] = true;
            }
            $return['message'] = $response['message'];
        }
        return $return;
    }
}


