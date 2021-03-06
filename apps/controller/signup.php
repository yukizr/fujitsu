<?php
class Signup extends SENE_Controller{
    var $status = 'ok';

	public function __construct(){
    parent::__construct();
		$this->lib("SENE_JSON_Engine","lib");
    $this->lib("Sene_Email_Sender","lib");

    $this->load("m_pengguna");
    $this->load("m_notifikasi_email");
    require 'kero/lib/phpmailer/PHPMailerAutoload.php';
	}
  public function index(){
		$sess = $this->getKey();
    $data = array();
    $data['res'] = "";
		$data['sess'] = $sess;
    if (isset($sess['user'])) {
      redir(base_url(""));
      die();
    } else {
      if ($this->input->post("submit")) {
        $name_first = $this->input->post("name_first");
        $name_last = $this->input->post("name_last");
        $kelas = $this->input->post("s_kelas");
        $email = $this->input->post("email");
    		$password = $this->input->post("password");
    		$active = "1";
    		$confirmed = "0";
        function unique_id($l = 20) {
		      return substr(md5(uniqid(mt_rand(), true)), 0, $l);
		    };
        $confirm_code = unique_id();
          if ($this->m_pengguna->check($email) == 0) {
            $id = $this->m_pengguna->set($name_first,$name_last,$email,$password,$kelas,$active,$confirmed,$confirm_code);
            // var_dump($res);
            // die();
            if ($id>0) {

              $user = $this->m_pengguna->getByIdCustomer($id);
      				$user = $user[0];
      				$user_confirm_code = $user->confirm_code;
      				$user_confirm_url = base_url("confirmation/get_code/?ks=").$user_confirm_code;
      				$user_email = $user->email;

              $notif_email = $this->m_notifikasi_email->getByName("customer-account-cofirmation-email","email");
      				$notif_email = $notif_email[0];
              // $this->debug($notif_email);
              // die();

      				$notif_email_content = $notif_email->content;
      				$notif_email_content = str_replace('$name_first$', $user->name_first, $notif_email_content);
      				$notif_email_content = str_replace('$confirmation_code$', $user_confirm_code, $notif_email_content);
      				$notif_email_content = str_replace('$confirmation_url$', $user_confirm_url, $notif_email_content);
              // $this->debug($notif_email_content);
			        // die();

      				// $this->Sene_Email_Sender->from($notif_email->sender,$notif_email->sender);
      				// $this->Sene_Email_Sender->subject("Konfirmasi Pendaftaran");
              // // var_dump($this->Sene_Email_Sender->to($user_email));
              // // die();
      				// $this->Sene_Email_Sender->text($notif_email_content);
      				// $this->Sene_Email_Sender->send();

              $mail = new PHPMailer;
              //Set who the message is to be sent from
              $mail->setFrom('redaksi@japunis.com', 'Japunis');
              //Set an alternative reply-to address
              $mail->addReplyTo('redaksi@japunis.com', 'Japunis');
              //Set who the message is to be sent to
              $mail->addAddress($email, $name_first);
              //Set the subject line
              $mail->Subject = 'Verifikasi dan Aktifkan Akunmu';
              //Read an HTML message body from an external file, convert referenced images to embedded,
              //convert HTML into a basic plain-text alternative body
              $mail->msgHTML($notif_email_content);
              //Replace the plain text body with one created manually
              //$mail->AltBody = 'This is a plain-text message body';
              //Attach an image file
              //$mail->addAttachment('images/phpmailer_mini.png');

              //send the message, check for errors
              if (!$mail->send()) {
                // die('gagal');
                $data["res"] = "gagal";
                $this->view("frontend/__header",$data);
                $this->view("frontend/__nav",$data);
                $this->view("frontend/signup/signup",$data);
                $this->view("frontend/__bottom",$data);
                $this->view("frontend/__footer",$data);
              } else {
                //die('berhasil');
                $data["res"] = "berhasil";
                $this->view("frontend/__header",$data);
                $this->view("frontend/__nav",$data);
                $this->view("frontend/signup/signup",$data);
                $this->view("frontend/__bottom",$data);
                $this->view("frontend/__footer",$data);
              }


            } else {
              $data["res"] = "gagal";
              $this->view("frontend/__header",$data);
        			$this->view("frontend/__nav",$data);
        			$this->view("frontend/signup/signup",$data);
        			$this->view("frontend/__bottom",$data);
        			$this->view("frontend/__footer",$data);
            }
          } else {
            $data['warn'] = 'Email Telah Terdaftar';
      			$this->view("frontend/__header",$data);
      			$this->view("frontend/__nav",$data);
      			$this->view("frontend/signup/signup",$data);
      			$this->view("frontend/__bottom",$data);
      			$this->view("frontend/__footer",$data);
          }
      } else {
        $this->view("frontend/__header",$data);
        $this->view("frontend/__nav",$data);
        $this->view("frontend/signup/signup",$data);
        $this->view("frontend/__bottom",$data);
        $this->view("frontend/__footer",$data);
      }
    }
	}

  private function __out($data){
	   $res = array('status'=>$this->status,'post' => $data);
	   $this->SENE_JSON_Engine->out($res);
	}

}
?>
