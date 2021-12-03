<?php
/**
 *  Sypht class
 */
class SyphtClass
{
	public $response 						= [];

	private $auth_token 					= "";
	function __construct(){
		$this->response['code'] 			= 0;
		$this->response['data'] 			= [];
		$this->response['msg'] 				= "";
		$this->response['fileId'] 			= "";
		$this->response['status'] 			= "";

		if( empty( $this->auth_token ) ){
			$this->get_sypht_barrer_token();
		}
	}

	function get_sypht_barrer_token(){
		$curl 							= curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL 					=> "https://login.sypht.com/oauth/token",
			CURLOPT_RETURNTRANSFER 			=> true,
			CURLOPT_ENCODING 				=> "",
			CURLOPT_MAXREDIRS 				=> 10,
			CURLOPT_TIMEOUT 				=> 0,
			CURLOPT_FOLLOWLOCATION 			=> false,
			CURLOPT_HTTP_VERSION 			=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 			=> "POST",
			CURLOPT_POSTFIELDS 				=> '{
				"client_id": "' . SYPHT_CLIENT_KEY . '",
				"client_secret": "' . SYPHT_CLIENT_SECRET . '",
				"audience": "https://api.sypht.com",
				"grant_type": "client_credentials"
			}',
			CURLOPT_HTTPHEADER 				=> array(
				"Accept: application/json",
				"Content-Type: application/json"
			),
		));

		$res 							= curl_exec($curl);
		$err 							= curl_error($curl);

		curl_close($curl);

		if ($err) {
			$this->response['msg'] 		= "cURL Error #:" . $err;
		} else {
			
			$res_arr 					= json_decode( $res, true );

			if( $res_arr && !empty( $res_arr ) && isset( $res_arr['access_token'] ) ){
				$this->auth_token 		= $res_arr['access_token'];
			}

			$this->response['code'] 	= 1;
			$this->response['data'] 	= $res_arr;
		}

		return $this->response;
	}

	function upload_documents( $file_path ){
		$this->response['code'] 			= 0;

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL 					=> "https://api.sypht.com/fileupload",
			CURLOPT_RETURNTRANSFER 			=> true,
			CURLOPT_ENCODING 				=> "",
			CURLOPT_MAXREDIRS 				=> 10,
			CURLOPT_TIMEOUT 				=> 0,
			CURLOPT_FOLLOWLOCATION 			=> false,
			CURLOPT_HTTP_VERSION 			=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 			=> "POST",
			CURLOPT_POSTFIELDS => array('fileToUpload'=> new CURLFILE($file_path),'fieldSets' => '["sypht.invoice","sypht.document","sypht.generic","sypht.bpay","sypht.bill","sypht.bank"]'),
			CURLOPT_HTTPHEADER => array(
			    "Accept: application/json",
			    "Content-Type: multipart/form-data",
			    "Authorization: Bearer " . $this->auth_token
			),
		));

		$res 							= curl_exec($curl);
		$err 							= curl_error($curl);

		curl_close($curl);

		if ( $err ) {
			$this->response['msg'] 		= "cURL Error #:" . $err;
		} else {
			
			$res_arr 					= json_decode( $res, true );

			if( $res_arr && !empty( $res_arr ) && isset( $res_arr['fileId'] ) ){
				$this->response['code'] 		= 1;
				$this->response['fileId'] 		= $res_arr['fileId'];
				$this->response['status'] 		= $res_arr['status'];
			} else if( isset( $res_arr['message'] ) ){
				$this->response['msg'] 			= $res_arr['message'];
			}
			
			$this->response['data'] 	= $res_arr;
		}

		return $this->response;
	}

	function get_document_extract_data( $file_id ){
		$this->response['code'] 			= 0;

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL 					=> "https://api.sypht.com/result/final/" . $file_id,
			CURLOPT_RETURNTRANSFER 			=> true,
			CURLOPT_ENCODING 				=> "",
			CURLOPT_MAXREDIRS 				=> 10,
			CURLOPT_TIMEOUT 				=> 0,
			CURLOPT_FOLLOWLOCATION 			=> false,
			CURLOPT_HTTP_VERSION 			=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 			=> "GET",
			CURLOPT_HTTPHEADER => array(
				"Accept: application/json",
				"Content-Type: application/json",
				"Authorization: Bearer " . $this->auth_token
			),
		));

		$res 							= curl_exec($curl);
		$err 							= curl_error($curl);

		curl_close($curl);

		if ( $err ) {
			$this->response['msg'] 		= "cURL Error #:" . $err;
		} else {
			
			$res_arr 					= json_decode( $res, true );

			if( $res_arr && !empty( $res_arr ) && isset( $res_arr['fileId'] ) ){
				$this->response['code'] 		= 1;
				$this->response['fileId'] 		= $res_arr['fileId'];
				$this->response['status'] 		= $res_arr['status'];
			} else if( isset( $res_arr['message'] ) ){
				$this->response['msg'] 			= $res_arr['message'];
			}
			
			$this->response['data'] 	= $res_arr;
		}

		return $this->response;
	}
}
?>
