<?php

require_once(dirname(__FILE__, 2) . '/PaymentAbstract.php');
require_once(dirname(__FILE__, 2) . '/Service/PaymentService.php');
require_once(FCPATH . 'vendor/autoload.php');


use Omnipay\Omnipay;

class Stripe extends PaymentAbstract
{

    public $session;
    public $params;
    public $url;

    public function __construct()
    {
        parent::__construct();
        $this->ci->lang->load('stripe_rules', $this->ci->session->userdata('lang'));
        $paymentWebview= $this->ci->session->userdata('paymentWebview') ?? false;
        $this->url = $paymentWebview ? base_url("paymentWebview/paymentSuccess") : base_url("take_exam/index");
        $this->gateway = Omnipay::create('Stripe');
        $this->gateway->setApiKey($this->payment_Setting_option['stripe_secret']);
        $this->gateway->setTestMode((bool)$this->payment_Setting_option['stripe_demo']);
    }

    public function rules() : array //done
    {
        return [
            [
                'field' => 'payment_type',
                'label' => $this->ci->lang->line("stripe_payment_type"),
                'rules' => 'trim|required'
            ],
            [
                'field' => 'stripe_key',
                'label' => $this->ci->lang->line("stripe_key"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'stripe_secret',
                'label' => $this->ci->lang->line("stripe_secret"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'stripe_demo',
                'label' => $this->ci->lang->line("stripe_demo"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ],
            [
                'field' => 'stripe_status',
                'label' => $this->ci->lang->line("stripe_status"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ]
        ];
    }

    public function payment_rules() : array
    {
        return [
            [
                'field' => 'stripeToken',
                'label' => $this->ci->lang->line("stripe_token"),
                'rules' => 'trim|required|xss_clean'
            ]
        ];
    }

    public function status() : bool //done
    {
        $stripe_status = $this->ci->payment_gateway_m->get_single_payment_gateway(['slug' => 'stripe', 'status' => 1]);
        return is_object($stripe_status);
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
            'online_exam_id' => $array['onlineExamID'],
            'description'    => $invoice->name,
            'amount'         => number_format((float)($invoice->cost), 2, '.', ''),
            'currency'       => $this->setting->currency_code,
            'token'          => $array['stripeToken']
        ];

        $this->response = $this->gateway->purchase($this->params)->send();
        $this->success();
    }

    public function success() //done
    {
        if($this->response->isSuccessful()) {
            if($this->response->getData()['status'] === "succeeded") {
                $transaction_id = $this->response->getData()['id'];
                if($transaction_id) {
                    $paymentService = new PaymentService($transaction_id);
                    $paymentService->add_transaction([
                        'online_exam_id' => $this->params['online_exam_id'],
                        'amount'         => $this->params['amount'],
                        'payment_method' => 'stripe'
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
        } elseif($this->response->isRedirect()) {
            $this->response->redirect();
        } else {
            $this->session->set_flashdata('error', "Something went wrong!");
            redirect($this->url);
        }
    }
}
