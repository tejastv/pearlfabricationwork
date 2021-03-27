<?php
/*
* Contact Form Class
*/


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

$admin_email = 'hello@jmsolutions.co.in'; // Your Email
$message_min_length = 5; // Min Message Length


class Contact_Form{
	function __construct($details, $email_admin, $message_min_length){
		
		$this->name = stripslashes($details['name']);
		$this->email = trim($details['email']);
		$this->mobile = trim($details['mobile']);
		$this->subject = 'Inquiry'; // Subject 
		$this->message = stripslashes($details['message']);
	
		$this->email_admin = $email_admin;
		$this->message_min_length = $message_min_length;
		
		$this->response_status = 1;
		$this->response_html = '';
	}


	private function validateEmail(){
		$regex = '/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i';
	
		if($this->email == '') { 
			return false;
		} else {
			$string = preg_replace($regex, '', $this->email);
		}
	
		return empty($string) ? true : false;
	}


	private function validateMobile(){
		$regex = '/[^0-9]+$/';

		if( (strlen($this->mobile)) != 10 ) {
			return false;
		} else {
			$string = preg_match($regex, $this->mobile);
		}
		return empty($string) ? true : false;
	}


	private function validateFields(){
		// Check name
		if(!$this->name)
		{
			$this->response_html .= '<p class="section-descriptionn">Please enter your name</p>';
			$this->response_status = 0;
		}

		// Check Mobile 
		if(!$this->mobile)
		{
			$this->response_html .= '<p class="section-descriptionn">Please enter a mobile number</p>';
			$this->response_status = 0;
		}

		// Check valid mobile 
		if($this->mobile && !$this->validateMobile()) 
		{
			$this->response_html .= '<p class="section-descriptionn">Please enter a valid mobile number</p>';
			$this->response_status = 0;
		}

		// Check email
		if(!$this->email)
		{
			$this->response_html .= '<p class="section-descriptionn">Please enter an e-mail address</p>';
			$this->response_status = 0;
		}
		
		// Check valid email
		if($this->email && !$this->validateEmail())
		{
			$this->response_html .= '<p class="section-descriptionn">Please enter a valid e-mail address</p>';
			$this->response_status = 0;
		}
		
		// Check message length
		if(!$this->message || strlen($this->message) < $this->message_min_length)
		{
			$this->response_html .= '<p class="section-descriptionn">Please enter your message. It should have at least '.$this->message_min_length.' characters</p>';
			$this->response_status = 0;
		}
	}


	private function sendEmail(){
		$mail = mail($this->email_admin, $this->subject, "Phone: " . $this->mobile . "\n\nEmail: " . $this->email . "\n\nMessage: " . $this->message,
			 "From: ".$this->name." <".$this->email.">\r\n"
			."Reply-To: ".$this->email."\r\n"
		."X-Mailer: PHP/" . phpversion());
	
		if($mail)
		{
			$this->response_status = 1;
			$this->response_html = '<p class="section-description-succ">Your message has been sent. Thank you!</p>';
			$this->autoReply();
		}
	}

	private function autoReply(){
		$mail = mail($this->email, "Re: Inquiry", "Dear " . $this->name . ",\n\nGreetings from JM Solutions !!!\n\nThank You for writing to us.\n\nWe'll get back to you shortly.\n\nRegards\nJM Solutions",
			"From: Hello from JMSolutions" . " <".$this->email_admin.">\r\n"
			."Reply-To: ".$this->email_admin."\r\n"
		."X-Mailer: PHP/" . phpversion());
	}


	function sendRequest(){
		$this->validateFields();
		if($this->response_status)
		{
			$this->sendEmail();
		}

		$response = array();
		$response['status'] = $this->response_status;	
		$response['html'] = $this->response_html;
		
		echo json_encode($response);
	}
}


// $contact_form = new Contact_Form($_POST, $admin_email, $message_min_length);
// $contact_form->sendRequest();

//only run when form is submitted
if(isset($_POST['g-recaptcha-response'])) { 
	$secretKey = '6LcvuSoUAAAAAKhD5O5dixTBbLY26Qv_s-4_3jWx';
    $response = $_POST['g-recaptcha-response'];     
    $remoteIp = $_SERVER['REMOTE_ADDR'];


    $reCaptchaValidationUrl = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$response&remoteip=$remoteIp");
    $result = json_decode($reCaptchaValidationUrl, TRUE);

    //get response along side with all results
    // print_r($result);

    if($result['success'] == 1) {
        //True - What happens when user is verified
        // $userMessage = '<div>Success: you\'ve made it :)</div>';
		$contact_form = new Contact_Form($_POST, $admin_email, $message_min_length);
		$contact_form->sendRequest();
    } else {
        //False - What happens when user is not verified
        $userMessage = '<div>Fail: please try again :(</div>';
    }
}

?>