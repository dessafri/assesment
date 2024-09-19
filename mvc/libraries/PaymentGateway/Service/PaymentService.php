<?php

class PaymentService
{
    /**
     * @var array<string, bool>
     */
    public $data;
    public $ci;
    public $transaction_id;

    public function __construct($transaction_id)
    {
        $this->ci =& get_instance();
        $this->transaction_id = $transaction_id;
        $this->ci->load->model('online_exam_payment_m');
    }

    public function add_transaction( $array)
    {
        $transaction = $this->ci->online_exam_payment_m->get_single_online_exam_payment(['transactionID' => $this->transaction_id]);
        if(!inicompute($transaction)) {
            $online_exam_payment = [
                'online_examID' => $array['online_exam_id'],
                'usertypeID'    => $this->ci->session->userdata('usertypeID'),
                'userID'        => $this->ci->session->userdata('loginuserID'),
                'paymentamount' => $array['amount'],
                'paymentmethod' => $array['payment_method'],
                'paymentdate'   => date('Y-m-d'),
                'paymentday'    => date('d'),
                'paymentmonth'  => date('m'),
                'paymentyear'   => date('Y'),
                'transactionID' => $this->transaction_id,
                'status'        => 0,
            ];

            $this->ci->online_exam_payment_m->insert_online_exam_payment($online_exam_payment);
            $this->ci->session->set_flashdata('success', 'Payment successful');
            $this->data['ApiPaymentStatus']=true;
        } else {
            $this->data['ApiPaymentStatus']=false;
            $this->ci->session->set_flashdata('error', 'Transaction ID already exist!');
        }
    }
}