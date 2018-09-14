<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/third_party/veritrans-php-master/Veritrans.php';

class Veritrans_library
{
	protected $ci;

	#dev
	// public $clientKey = "VT-client-66kQAYrfvuy2CLf2" ;
	// public $serverKey = "VT-server-UJBCJ2sMWJYjHEXuMH7olkr4" ;

	#production
	// public $clientKey = "VT-client-XcKtj7KOcsvYA7ri" ;
	// public $serverKey = "VT-server-Cq9ZHIzrssOFvpAA6X05GASB" ;

	public $clientKey ;
	public $serverKey ;

	private $customer_details = array(
	    'first_name'    => null, //optional
	    'last_name'     => null, //optional
	    'email'         => null, //mandatory
	    'phone'         => null, //mandatory
    );

    private $expiry = array(
		"unit"=> "hour",
		"duration"=> 1
    ) ;

    private $payment_enabled = array(

    ) ;

	public function __construct()
	{
        $this->ci =& get_instance();
        $this->ci->load->model('Book_model', 'book');

        //Set Your server key

		if ($this->ci->session->userdata('sign_in_session_id_user') != null ) {
			$this->ci->db->where('id_user_client', $_SESSION['sign_in_session_id_user']);
			$user_account = $this->ci->db->get('user_client');
			$user_account = $user_account->row_array();

			if ($user_account['dummy_account'] == 1) {
				Veritrans_Config::$isProduction = false;
				// $this->clientKey = "VT-client-KmOsaISr9G5lpCTC" ;
				// $this->serverKey = "VT-server-0ZgJCy63k9Qnuidqgm0HYByt" ;

				$this->clientKey = "VT-client-66kQAYrfvuy2CLf2" ;
				$this->serverKey = "VT-server-UJBCJ2sMWJYjHEXuMH7olkr4" ;

			}else if ($user_account['dummy_account'] == 0) {
				$this->clientKey = "VT-client-XcKtj7KOcsvYA7ri" ;
				$this->serverKey = "VT-server-Cq9ZHIzrssOFvpAA6X05GASB" ;
				Veritrans_Config::$isProduction = true;
			}

			$this->customer_details = array(
				'first_name' => $user_account['full_name'],
				'email' => $user_account['email'],
				'phone' => $user_account['phone_number'],
			) ;

		} else {
			$this->clientKey = "VT-client-XcKtj7KOcsvYA7ri" ;
			$this->serverKey = "VT-server-Cq9ZHIzrssOFvpAA6X05GASB" ;
			Veritrans_Config::$isProduction = true;
		}

		Veritrans_Config::$serverKey = $this->serverKey;

	}

	public function snap_token_down_payment($book_number=null)
	{

		Veritrans_Config::$isSanitized = true;
		Veritrans_Config::$is3ds = true;

		$book_data = $this->ci->book->book_detail($book_number) ;
		$book_data = $book_data->row();

		$transaction = array(
		    'transaction_details' => array(
		        'order_id' => $book_data->book_number."-DP",
		        'gross_amount' => intval($book_data->down_payment) // no decimal allowed
			),
			'customer_details' => $this->customer_details,
			"enabled_payments" => $this->payment_enabled,
			"expiry" => $this->expiry,
			"custom_field1" => 'Uang Muka',
			"custom_field2" => '1',
		);

		$snapToken = Veritrans_Snap::getSnapToken($transaction);

		return $snapToken;
	}

	public function snap_token_full_payment($book_number=null, $assetType='assets')
	{
		Veritrans_Config::$isSanitized = true;
		Veritrans_Config::$is3ds = true;

		if ($assetType == 'assets') {
			$book_data = $this->ci->book->book_detail($book_number) ;
		}else if ($assetType == 'group') {
			$book_data = $this->ci->book->book_detail_group($book_number) ;
		}

		$book_data = $book_data->row();

		$this->customer_details = array(
			'first_name' => $book_data->client_full_name,
			'email' => $book_data->client_email,
			'phone' => $book_data->client_phone_number,
		) ;


		$this->ci->db->where('id_booking', $book_data->id_booking);
		$book_price_perday = $this->ci->db->get('detail_book_price_per_day');

		$full_payment_total = 0 ;

		#TanpaOngkir
		// foreach ($book_price_perday->result() as $value) {
		// 	$full_payment_total += $value->price ;
		// }

		#FullOngkir
		$full_payment_total=intval($book_data->total_price);

		$transaction = array(
		    'transaction_details' => array(
		        'order_id' => $book_data->book_number."-FL",
		        'gross_amount' => intval($full_payment_total) // no decimal allowed
			),
			'customer_details' => $this->customer_details,
			"enabled_payments" => $this->payment_enabled,
			"expiry" => $this->expiry,
			"custom_field1" => 'Pembayaran Lunas',
			"custom_field2" => '3',
		);

		$snapToken = Veritrans_Snap::getSnapToken($transaction);

		return $snapToken;
	}

	public function snap_token_ojek($book_number=null, $assetType='assets')
	{
		Veritrans_Config::$isSanitized = true;
		Veritrans_Config::$is3ds = true;

		if ($assetType == 'assets') {
			$book_data = $this->ci->book->book_detail($book_number) ;
		}else if ($assetType == 'group') {
			$book_data = $this->ci->book->book_detail_group($book_number) ;
		}

		$book_data = $book_data->row();

		$this->customer_details = array(
			'first_name' => $book_data->client_full_name,
			'email' => $book_data->client_email,
			'phone' => $book_data->client_phone_number,
		) ;

		$full_payment_total = $book_data->total_price ;

		$transaction = array(
		    'transaction_details' => array(
		        'order_id' => $book_data->book_number."-OJ",
		        'gross_amount' => intval($full_payment_total) // no decimal allowed
			),
			'customer_details' => $this->customer_details,
			"enabled_payments" => $this->payment_enabled,
			"expiry" => $this->expiry,
			"custom_field1" => 'Pembayaran Lunas Member',
			"custom_field2" => '8',
		);

		$snapToken = Veritrans_Snap::getSnapToken($transaction);

		return $snapToken;
	}

	public function snap_token_repayment($book_number=null)
	{
		Veritrans_Config::$isSanitized = true;
		Veritrans_Config::$is3ds = true;

		$book_data = $this->ci->book->book_detail($book_number) ;
		$book_data = $book_data->row();

		$transaction = array(
		    'transaction_details' => array(
		        'order_id' => $book_data->book_number."-PL",
		        'gross_amount' => intval($book_data->total_price)-intval($book_data->down_payment) // no decimal allowed
			),
			'customer_details' => $this->customer_details,
			"enabled_payments" => $this->payment_enabled,
			"expiry" => $this->expiry,
			"custom_field1" => 'Pelunasan',
			"custom_field2" => '2',
		);

		$snapToken = Veritrans_Snap::getSnapToken($transaction);

		return $snapToken;
	}

	public function snap_token_extend_time($id_extend)
	{
		Veritrans_Config::$isSanitized = true;
		Veritrans_Config::$is3ds = true;

		$this->ci->db->select("e.total_price, b.book_number, e.order_number") ;
		$this->ci->db->where('e.id_extend_time_payment', $id_extend);
		$this->ci->db->join('booking as b', 'b.id_booking = e.id_booking', 'inner');
		$extend_data = $this->ci->db->get('extend_time_payment as e');
		$extend_data = $extend_data->row();

		$book_data = $this->ci->book->book_detail($extend_data->book_number) ;
		$book_data = $book_data->row();

		$this->customer_details = array(
			'first_name' => $book_data->client_full_name,
			'email' => $book_data->client_email,
			'phone' => $book_data->client_phone_number,
		) ;

		$transaction = array(
		    'transaction_details' => array(
		        'order_id' => $extend_data->order_number,
		        'gross_amount' => intval($extend_data->total_price) // no decimal allowed
			),
			'customer_details' => $this->customer_details,
			"enabled_payments" => $this->payment_enabled,
			"expiry" => $this->expiry,
			"custom_field1" => 'Extend Time',
			"custom_field2" => '12',
		);

		$snapToken = Veritrans_Snap::getSnapToken($transaction);

		return $snapToken;
	}

	public function snap_token_topup($no_top_up)
	{
		Veritrans_Config::$isSanitized = false;
		Veritrans_Config::$is3ds = true;

		$this->ci->db->where('no_top_up', $no_top_up);
		$top_up = $this->ci->db->get('top_up_saldo')->row();

		$transaction = array(
		    'transaction_details' => array(
		        'order_id' => $top_up->no_top_up ,
		        'gross_amount' => $top_up->total_price // no decimal allowed
			),
			'item_details' => array(
				array(
				    'price' => $top_up->voucher_point,
				    'quantity' => $top_up->quantity,
				    'name' => "Saldo AMREN.ID Rp. ". number_format($top_up->voucher_point)
				)
			),
			'customer_details' => $this->customer_details,
			// "enabled_payments" => array('bank_transfer'),
			"expiry" => $this->expiry,
			"custom_field1" => 'Top Up Saldo',
			"custom_field2" => '9',
		);

		$snapToken = Veritrans_Snap::getSnapToken($transaction);

		return $snapToken;
	}


	public function transaction_status($book_number=null)
	{
		try {
			$status = Veritrans_Transaction::status($book_number);
			return $status ;
		} catch (Exception $e) {
			return false ;
		}

	}

	public function check_new_status_saldo($no_top_up=null)
	{

		$status = $this->transaction_status($no_top_up);
		

		if ($status != false) {
			$this->ci->db->where('order_id', $no_top_up);
			$this->ci->db->where('payment_type', $status->payment_type);
			$this->ci->db->where('transaction_status', $status->transaction_status);
			$check = $this->ci->db->get('top_up_payment_history');
			if ($check->num_rows() == 0) {
				$this->ci->book->insert_payment_notification_saldo($status) ;
			}
		}



		return true ;
	}

	public function check_new_status_extend($book_number=null)
	{
		$this->ci->db->where('b.book_number', $book_number);
		$this->ci->db->where('e.resumed', 0);
		$this->ci->db->join('booking as b', 'b.id_booking = e.id_booking', 'inner');
		$this->ci->db->order_by('e.id_extend_time_payment', 'desc');
		$extend_data = $this->ci->db->get('extend_time_payment as e') ;


		if ($extend_data->num_rows() > 0) {
			$data = $extend_data->row() ;

			$this->ci->db->order_by('create_date', 'desc');
			$this->ci->db->where('order_id', $data->order_number);
			$payment_history = $this->ci->db->get('payment_history');

			$status = null;

			if ($payment_history->num_rows() > 0) {
				$payment_history = $payment_history->row();
				if ($payment_history->transaction_type == 12) {
					$status = $this->transaction_status($data->order_number) ;
				}else {
					$status = false ;
				}
			}else {
				$status = $this->transaction_status($data->order_number) ;
			}


			if ($status != false) {
				$this->ci->db->where('order_id', $status->order_id);
				$this->ci->db->where('payment_type', $status->payment_type);
				$this->ci->db->where('transaction_status', $status->transaction_status);
				$this->ci->db->where('transaction_type', $status->custom_field2);
				$check = $this->ci->db->get('payment_history');
				if ($check->num_rows() == 0) {
					$this->ci->book->insert_payment_notification_extend($status) ;
				}
			}

		}

	}

	public function check_new_status($book_number=null)
	{
		foreach (array(/*'-DP', '-PL', */'-FL', '-OJ') as $key => $value) {

			$this->ci->db->order_by('create_date', 'desc');
			$this->ci->db->where('order_id', $book_number.$value);
			$check = $this->ci->db->get('payment_history');

			if ($check->num_rows() == 0) {
				$status = $this->transaction_status($book_number.$value);
			} else {
				$check = $check->row() ;
				if ((int) $check->transaction_type == 3 || (int) $check->transaction_type == 8) {
					$status = $this->transaction_status($book_number.$value);
				}else{
					$status = false;
				}
			}


			if ($status != false) {
				$this->ci->db->like('order_id', $book_number, 'after');
				$this->ci->db->where('payment_type', $status->payment_type);
				$this->ci->db->where('transaction_status', $status->transaction_status);
				$this->ci->db->where('transaction_type', $status->custom_field2);
				$check = $this->ci->db->get('payment_history');
				if ($check->num_rows() == 0) {
					$this->ci->book->insert_payment_notification($status) ;
				}
			}
		}

		return true ;
	}


}

/* End of file Veritrans_library.php */
/* Location: ./application/libraries/Veritrans_library.php */
