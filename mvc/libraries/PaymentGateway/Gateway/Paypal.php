<?php

require_once(dirname(__FILE__, 2) . '/PaymentAbstract.php');
require_once(dirname(__FILE__, 2) . '/Service/PaymentService.php');
require_once(FCPATH . 'vendor/autoload.php');

use Omnipay\Omnipay;

class Paypal extends PaymentAbstract
{

    public $session;
    public $params;
    public $url;

    public function __construct()
    {
        parent::__construct();
        $this->ci->lang->load('paypal_rules', $this->ci->session->userdata('lang'));
        $paymentWebview= $this->ci->session->userdata('paymentWebview') ?? false;
        $this->url = $paymentWebview ? base_url("paymentWebview/paymentSuccess") : base_url("take_exam/index");
        $this->gateway = Omnipay::create('PayPal_Express');
        $this->gateway->setUsername($this->payment_Setting_option['paypal_username']);
        $this->gateway->setPassword($this->payment_Setting_option['paypal_password']);
        $this->gateway->setSignature($this->payment_Setting_option['paypal_signature']);
        $this->gateway->setTestMode((bool)$this->payment_Setting_option['paypal_demo']);
    }

    public function rules() : array //done
    {
        return [
            [
                'field' => 'payment_type',
                'label' => $this->ci->lang->line("paypal_payment_type"),
                'rules' => 'trim|required'
            ],
            [
                'field' => 'paypal_username',
                'label' => $this->ci->lang->line("paypal_username"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paypal_password',
                'label' => $this->ci->lang->line("paypal_password"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paypal_signature',
                'label' => $this->ci->lang->line("paypal_signature"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paypal_email',
                'label' => $this->ci->lang->line("paypal_email"),
                'rules' => 'trim|xss_clean|max_length[255]|valid_email|callback_unique_field'
            ],
            [
                'field' => 'paypal_demo',
                'label' => $this->ci->lang->line("paypal_demo"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ],
            [
                'field' => 'paypal_status',
                'label' => $this->ci->lang->line("paypal_status"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ]
        ];
    }

    public function payment_rules() : array
    {
        return [];
    }

    public function status() : bool //done
    {
        $paypal_status = $this->ci->payment_gateway_m->get_single_payment_gateway(['slug' => 'paypal', 'status' => 1]);
        return is_object($paypal_status);
    }

    public function cancel() //done
    {
        redirect($this->url);
    }

    public function fail() //done
    {
        $this->ci->session->set_flashdata('error', 'The payment is fail');
        redirect($this->url);
    }

    public function payment( $array, $invoice ) //done
    {
        $this->params = [
            'cancelUrl'      => base_url('take_exam/cancel/paypal'),
            'returnUrl'      => base_url('take_exam/success/paypal'),
            'online_exam_id' => $array['onlineExamID'],
            'description'    => $invoice->name,
            'amount'         => (float) $invoice->cost,
            'currency'       => $this->setting->currency_code,
        ];

        $this->ci->session->set_userdata("params", $this->params);
        $this->response = $this->gateway->purchase($this->params)->send();
        if($this->response->isSuccessful()) {
            // payment was successful: update database
        } elseif($this->response->isRedirect()) {
            $this->response->redirect();
        } else {
            echo $this->response->getMessage();
        }
    }

    public function success() //done
    {
        $params         = $this->ci->session->userdata('params');
        $this->response = $this->gateway->completePurchase($params)->send();
        $this->response = $this->response->getData();
        $purchase_id    = $_GET['PayerID'];
        if(isset($this->response['PAYMENTINFO_0_ACK']) && $this->response['PAYMENTINFO_0_ACK'] === 'Success') {
            if($purchase_id) {
                $transaction_id = $this->response['PAYMENTINFO_0_TRANSACTIONID'];
                $paymentService = new PaymentService($transaction_id);
                $paymentService->add_transaction([
                    'online_exam_id' => $params['online_exam_id'],
                    'amount'         => $params['amount'],
                    'payment_method' => 'paypal'
                ]);
                redirect($this->url);
            } else {
                $this->session->set_flashdata('error', 'Payer id not found!');
                redirect($this->url);
            }
        } else {
            $this->session->set_flashdata('error', 'Payment not success!');
            redirect($this->url);
        }
    }
}
