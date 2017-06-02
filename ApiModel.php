<?php
require_once ("Database.php");

//date_default_timezone_set("Australia/Melbourne");

define('SAVE_PHOTO', '../images/photo/');  // save image path
define('DELETE_PHOTO', '../images/photo/');  // delete image path

if (!function_exists('apache_request_headers')) {
    ///
    function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);
                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val)
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return ($arh);
    }

}
$degree = array("1"=>"B.A.", "2"=>"B.S.", "3"=>"J.D.", "4"=>"M.A.", "5"=>"M.B.A.", "6"=>"M.D.", "7"=>"M.S.", "8"=>"PhD");
class ApiModel {

	private $_provider = null;

    private $around = array("1"=>100,"2"=>300,"3"=>500,"4"=>1000,"5"=>2500,"6"=>5000,"7"=>10000);
    private $default_distance = 300;
	private $_host = "https://jobme.co.nz";

	public function __construct() {
		$this -> _provider = new Database();
	}
	public function __destruct() {
		$this -> _provider = null;
	}
	public static function getInstance() {
		return new ApiModel();
	}
	public function provider() {
		return $this -> _provider;
	}
	private function generate_token($length = 8) {
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
		//length:36
		$final_rand = '';
		for ($i = 0; $i < $length; $i++) {
			$final_rand .= $chars[rand(0, strlen($chars) - 1)];
		}
		return $final_rand;
	}
	public function push_test() {

		// iphone2 35:  4a43f9f6e47fb7899ce28f5a6a46da96c2423472cf6d0d3c15c1ae5c5193619b
		// iphone1 34: 416af4a0d0b05af72f65907872a4b8fcc4c7a5653fc21ffb305daa1eb71f47c8
		$result = $this -> push("0721d96ecadeb40f9ff5d622592cccbf5edc7bd54ed0efffda1ccf3b3cd80c16", array("aps" => array("alert" => "VanityDatingApp push test message")));

		if (!$result)
			echo 'Message not delivered' . PHP_EOL;
		else
			echo 'Message successfully delivered' . PHP_EOL;
	}
    public function push_test_android() {
        $message = "test push message for android";
        $device_id3 = array("APA91bHGahHnTqtdmyeVNdAdo9v5SH5gb29iTw55wm5NWNvY41Ct-qEHxQDvzxbcVvpk6VxUvXc6kvRVDfgmgAuJgty2LyuMc4uID_PrGwKg-TE3YpEtAOo1r4-d1KPTUHMG_Mbbr9St");
        $this->android_push($device_id3, array("message" => $message));
        echo "success";exit;
    }
	private function push($deviceToken, $body = array()) {
		// Put your device token here (without spaces):
		// $deviceToken = '98b588c2abf8e93870072ae4c018ebe9dff12930abdba260fa36fcf95adadb6f';

		if ($deviceToken == "")
			return false;

		$deviceToken = strtolower(str_replace(array(" ", "-", "_"), array("", "", ""), $deviceToken));

		// Put your private key's passphrase here:
		$passphrase = '';
		
		// Put your alert message here:
		// $message = 'gajklsdjfklsdajflkjaslkdfjklsadjfklsdaj';

		////////////////////////////////////////////////////////////////////////////////

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck_pro.pem');
//		stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck_dev.pem');

		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		// Open a connection to the APNS server
		//gateway.push.apple.com
		$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
//		$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
		
		if (!$fp) {
			return false;
		}

		// Create the payload body
		/*
		 $body['aps'] = array(
		 'alert' => $message,
		 'sound' => 'default'
		 );
		 *
		 */

		// Encode the payload as JSON
		$payload = json_encode($body);

		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));

		if (!$result)
			return false;

		// Close the connection to the server
		fclose($fp);
		return true;
	}
    public function android_push($regids, $body = array()) {
        $url = 'https://android.googleapis.com/gcm/send';

        $fields = array('registration_ids' => $regids, 'data' => $body);

        $headers = array('Authorization: key=' . $this -> _google_api_key, 'Content-Type: application/json');

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);

        // Close connection
        curl_close($ch);
    }
	private function authentication() {

		$headers = apache_request_headers();
		if (isset($headers['Access-Token']) && isset($headers['Device-Id'])) :
            $sql = "select a.account_id, b.status from device a, account b where a.account_id = b.id and a.access_token = '" . $headers['Access-Token'] . "' and a.device_id='" . $headers['Device-Id'] . "'";
			$info = $this -> provider() -> single($sql);
			if (count($info) > 0) :
				switch($info['status']) :
					case 0 :
						return $info['account_id'];
						break;
					case -1 :
						echo json_encode(array('success' => 'false', 'error' => "Your account is blocked. Please contact us about this."));
						exit ;
						break;
					case 1 :
						echo json_encode(array('success' => 'false', 'error' => "Your account is not active yet. Please confirm your mailbox."));
						exit ;
						break;
				endswitch;
			else :
				echo json_encode(array('success' => 'false', 'error' => "Access denied."));
				exit ;
			endif;
		else :
			echo json_encode(array('success' => 'false', 'error' => "Access denied."));
			exit ;
		endif;
	}
	private  function make_in($arr, $parameter ){
		$str = "";
        $str .= "(";
		if(count($arr) > 0){
			for($i=0; $i < count($arr); $i++){
				if($i == 0) $str .= $arr[$i][$parameter];
				else{
					$str .= " ,".$arr[$i][$parameter];
				}
			}
			$str .= ")";
			return $str;
		}
		else{
			return false;
		}
	}
    private  function get_arr($arr, $parameter ){
        $result = array();
        for($i=0; $i < count($arr); $i++){
            $result[] = $arr[$i][$parameter];
        }
        return $result;
    }
    private  function make_ids($arr, $parameter ){
        $str = "";
        if($arr['num_rows'] != 0 && count($arr) > 0){
            for($i=0; $i < count($arr); $i++){
                if($i == 0) $str .= $arr[$i][$parameter];
                else{
                    $str .= " ,".$arr[$i][$parameter];
                }
            }
            return $str;
        }
        else{
            return "";
        }
    }
    private function convertDateToString($str){
        if($str == "now"){
            return "now";
        }else{
            $exp = explode( "/",$str );
            $year = $exp[0];
            $month = $exp[1];
            $day = 10;
            return mktime(0,0,0, $month, $day, $year);
        }
    }
    private function getUser($user_id){
        $sel_id = $user_id;
        $profile = $this -> provider() -> single("select * from account where id = $sel_id");

        $sql_main = "select a.title from main_cat a where a.id in (".$profile['main_cat_id'].")";
        $profile['job_function'] = $this->provider()->single($sql_main);

        $sql_sub = "select a.title from sub_cat a where a.id in (".$profile['sub_cat_id'].")";
        $profile['industry'] = $this->provider()->single($sql_sub);

        $sql_exp = "select a.* from experience a where a.id in (".$profile['experience_ids'].")";
        if($profile['experience_ids'] == ""){
            $profile['experience'] = array();
        }else{
            $profile['experience'] = $this->provider()->result($sql_exp);
        }

        $sql_education = "select a.* from education a where a.id in (".$profile['education_ids'].")";
        if($profile['education_ids'] == ""){
            $profile['education'] = array();
        }else{
            $profile['education'] = $this->provider()->result($sql_education);
        }

        $sql_skill = "select a.* from skill a where a.id in (".$profile['skill_ids'].")";
        if($profile['skill_ids'] == ""){
            $profile['skills'] = array();
        }else{
            $profile['skills'] = $this->provider()->result($sql_skill);
        }
        return $profile;
    }
    private function update_userInfo($profile){
        $sql_main = "select a.title from main_cat a where a.id in (".$profile['main_cat_id'].")";
        $profile['job_function'] = $this->provider()->single($sql_main);

        $sql_sub = "select a.title from sub_cat a where a.id in (".$profile['sub_cat_id'].")";
        $profile['industry'] = $this->provider()->single($sql_sub);

        $sql_exp = "select a.* from experience a where a.id in (".$profile['experience_ids'].")";
        if($profile['experience_ids'] == ""){
            $profile['experience'] = array();
        }else{
            $profile['experience'] = $this->provider()->result($sql_exp);
        }

        $sql_education = "select a.* from education a where a.id in (".$profile['education_ids'].")";
        if($profile['education_ids'] == ""){
            $profile['education'] = array();
        }else{
            $profile['education'] = $this->provider()->result($sql_education);
        }

        $sql_skill = "select a.* from skill a where a.id in (".$profile['skill_ids'].")";
        if($profile['skill_ids'] == ""){
            $profile['skills'] = array();
        }else{
            $profile['skills'] = $this->provider()->result($sql_skill);
        }
        return $profile;
    }
    public function login() {

        $password = $this -> provider() -> _db -> real_escape_string($_POST['password']);
        $email = $this -> provider() -> _db -> real_escape_string($_POST['email']);
        $device_id = $_POST["device_id"];
        $device_type = $_POST["device_type"];
        $created = time();
        $check = $this -> provider() -> single("select count(*) as count from account where email = '$email'");
        if ($check['count'] > 0) :
            if(count($this -> provider() -> single("select count(*) as count from account where email = '$email' and password = '".md5($password)."'")) > 0){
                $info = $this -> provider() -> single("select * from account where email = '$email' and password = '".md5($password)."'");
                if (count($info) > 0) :
//                if($info['status'] == 0) {
//                    echo json_encode(array("success" => "0", "message" => "your account already deactivated. Please contact to admin"));
//                    exit;
//                }
                    $token = $this -> generate_token(32);
                    $this -> provider() -> execute("delete from device where account_id = " . $info["id"]);
                    if(!$this -> provider() -> execute("insert into device(account_id, device_type, device_id, access_token, created) values('" . $info["id"] . "', '$device_type', '$device_id', '$token', '$created')")){
                        echo json_encode(array("success" => "0", "message" => "sql error : insert device error."));exit;
                    }

                    echo json_encode(array("success" => "1","Access-Token" => $token, "Device-Id" => $device_id, "userinfo" => $info, "avatar" => $info['avatar']));
                else :
                    echo json_encode(array("success" => "0", "message" => "sql error : user not exist."));exit;
                endif;
            }else{
                echo json_encode(array("success" => "0", "message" => "sql error : password is wrong."));exit;
            }
        else :
            echo json_encode(array("success" => "0", "message" => "There's no account associated with this email."));exit;
        endif;
    }
    public function signup() {

        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $company = $_POST["company"];
        $device_id = $_POST["device_id"];
        $device_type = $_POST["device_type"];
        $created = time();
        $token = $this -> generate_token(32);
        $check = $this -> provider() -> single("select count(*) as count from account where email='$email'");
        if ($check["count"] > 0) {
            // exist user
            echo json_encode(array("success" => "0", "message" => "User already exist."));exit;
        } else {
            // new user
            $sql1 = "insert into account( first_name, last_name, email, password, company, experience_ids, education_ids, skill_ids, created)
                values( '$first_name', '$last_name', '$email', '".md5($password)."', '$company', '', '', '', '$created')";
            if(!$this -> provider() -> execute($sql1)){
                echo json_encode(array("success" => "0", "message" => "sql error : user register."));exit;
            }
            echo json_encode(array("success" => "1"));
        }
    }
    public function logout() {

        $account_id = $this -> authentication();
        $this -> provider() -> execute("delete from device where account_id=$account_id");
        echo json_encode(array("success" => "1"));

    }
    public function update_profile() {
        $account_id = $this -> authentication();
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $headline = $_POST['headline'];
        $location = $_POST['location'];
        $lot = $_POST['longitude'];
        $lat = $_POST['latitude'];
        $main_cat_id = $_POST['job_function'];
        $sub_cat_id = $_POST['industry'];
        $salary_min = $_POST['salary_min'];
        $salary_max = $_POST['salary_max'];
        $experience_year = $_POST['experience_year'];
        $skill_ids = $_POST['skill_ids'];
        $experience = $_POST['experience'];
        $education = $_POST['experience'];

        if($this->_provider->result("select *  from account where email = '$email' and id <> $account_id")){
            echo json_encode(array("success" => "0", "message" => "Email already exist."));exit;
        }else{
            $experience_ids = "";
            $education_ids = "";
            if(count($experience)){
                foreach($experience as $row_exp){
                    extract($row_exp);
                    $start_date = convertDateToString($start_date);
                    $end_date = convertDateToString($end_date);
                    if($exp_id == 0){
                        $sql = "insert into experience(account_id, job_title, company, start_date, end_date)
                                      values('$account_id','$headline','$company','$start_date','$end_date')";
                    }else{
                        $sql = "update experience set job_title = '$headline', company='$company', start_date='$start_date', end_date='$end_date' where account_id = $account_id ";
                    }
                    $this->_provider->execute($sql);
                }
            }
            if(count($education)){
                foreach($education as $row_edu){
                    extract($row_edu);
                    $start_date = convertDateToString($start_date);
                    $end_date = convertDateToString($end_date);
                    if($exp_id == 0){
                        $sql = "insert into education(account_id, school_name, field_study, degree, start_date, end_date)
                                      values('$account_id','$school_name','$field_study','$degree','$start_date','$end_date')";
                    }else{
                        $sql = "update experience set job_title = '$headline', company='$company', start_date='$start_date', end_date='$end_date' where account_id = $account_id ";
                    }
                    $this->_provider->execute($sql);
                }
                foreach($education as $row_edu){
                    extract($row_edu);
                    $start_date = convertDateToString($start_date);
                    $end_date = convertDateToString($end_date);
                    if($exp_id == 0){
                        $sql = "insert into education(account_id, school_name, field_study, degree, start_date, end_date)
                                      values('$account_id','$school_name','$field_study','$degree','$start_date','$end_date')";
                    }else{
                        $sql = "update experience set job_title = '$headline', company='$company', start_date='$start_date', end_date='$end_date' where account_id = $account_id ";
                    }
                    $this->_provider->execute($sql);
                }
            }
            $experience_ids = $this->make_ids($this->_provider->single("select id from experience where account_id = $account_id" ), "id");
            $education_ids = $this->make_ids($this->_provider->single("select id from education where account_id = $account_id" ), "id");

            $sql = "update account set first_name='$first_name', last_name='$last_name', email='$email'
                    , headline='$headline', location='$location', lot='$lot', lat='$lat', experience_ids='$experience_ids', education_ids='$education_ids'
                     , main_cat_id='$main_cat_id', sub_cat_id='$sub_cat_id', salary_min='$salary_min', salary_max='$salary_max', experience_year='$experience_year', skill_ids='$skill_ids'
                     where id = $account_id ";
            if($this->_provider->execute($sql)){
                $info = $this->_provider->single("select * from account where id = $account_id ");
                echo json_encode(array("success" => "1", "info" => $info));exit;
            }else{
                echo json_encode(array("success" => "0", "message" => "sql : error account"));exit;
            }
        }
    }
    public function sendMessage() {

        $account_id = $this -> authentication();
        $receiver_id = $_POST['receiver_id'];
        $message = $this -> provider() -> _db -> real_escape_string($_POST['message']);
        $now = time();
        if(!$this -> provider() -> execute("insert into chat(created,sender, receiver, message) values('$now', $account_id, $receiver_id, '$message')")){
            echo json_encode(array("success" => "0", "message" => "sql error : chat runtime."));exit;
        }

        $device = $this -> provider() -> single("select * from device where account_id = $receiver_id");
        $chat_id = $this->provider()->_db->insert_id;
        $token = $device['device_id'];
        $info = $this -> provider() -> single("select * from account where id = $account_id");
        $data = array("aps" => array(
            "alert" => $message,
            "type" => "chat",
            "chat_id" => $chat_id,
            "sender_id" => $account_id,
            "receiver_id" => $receiver_id,
            "username" => $info['first_name']." ".$info['last_name'],
            "photo_url" => $info['avatar'],
            "type" => "message"
        ));
        $data_android = array(
            "message" => $message,
            "type" => "chat",
            "chat_id" => $chat_id,
            "sender_id" => $account_id,
            "sender_name" => $info['first_name']." ".$info['last_name'],
            "sender_photo" => $info['avatar']
        );
        if($device['device_type'] == "ios"){
            $this -> push($token, $data);
        }elseif($device['device_type'] == "android"){
            $this -> android_push(array($token), $data_android);
        }

        echo json_encode(array("success" => "1"));
    }
    public function update_email() {

        $account_id = $this -> authentication();
        $email = $_POST["new_email"];

        if($this->_provider->result("select *  from account where email = '$email' and id <> $account_id")){
            echo json_encode(array("success" => "0", "message" => "Email already exist."));exit;
        }else{
            $sql = "update account set email='$email' where id = $account_id ";
            if($this->_provider->execute($sql)){
                $info = $this->_provider->single("select * from account where id = $account_id ");
                echo json_encode(array("success" => "1", "info" => $info));exit;
            }else{
                echo json_encode(array("success" => "0", "message" => "sql : error account"));exit;
            }
        }
    }
    public function update_password() {

        $account_id = $this -> authentication();
        $password = $_POST["new_password"];
        $sql = "update account set password='".md5($password)."' where id = $account_id ";
        if($this->_provider->execute($sql)){
            $info = $this->_provider->execute("select * from account where id = $account_id ");
            echo json_encode(array("success" => "1"));exit;
        }else{
            echo json_encode(array("success" => "0", "message" => "sql : error account"));exit;
        }
    }
    public function delete_account() {

        $account_id = $this -> authentication();
        $sql = "delete from account where id = $account_id ";
        if($this->_provider->execute($sql)){
            $this->_provider->execute("delete from device where account_id = '$account_id' ");
            $this->_provider->execute("delete from post where user_id = '$account_id' ");
            $this->_provider->execute("delete from chat where sender = '$account_id' or receiver = '$account_id' ");
            $this->_provider->execute("delete from liked where account_id = '$account_id' ");
            echo json_encode(array("success" => "1"));exit;
        }else{
            echo json_encode(array("success" => "0", "message" => "sql : error account"));exit;
        }
    }
    public function social_signup() {

        $first_name = isset($_POST["first_name"]) ? $_POST["first_name"] : "";
        $last_name = isset($_POST["last_name"]) ? $_POST["last_name"] : "";
        $email = $_POST["email"];
        $social_id = $_POST["social_id"];
        $company = $_POST["company"];
        $created = time();

        $token = $this -> generate_token(32);
        $check = $this -> provider() -> single("select count(*) as count from account where social_id='$social_id'");
        if ($check["count"] > 0) {
            // exist user
            echo json_encode(array("success" => "0", "message" => "User already exist."));exit;
        } else {
            // new user
            $sql1 = "insert into account( first_name, last_name, email, social_id,  company, experience_ids, education_ids, skill_ids, created)
                values( '$first_name', '$last_name', '$email', '$social_id', '$company','','','', '$created')";
            if(!$this -> provider() -> execute($sql1)){
                echo json_encode(array("success" => "0", "message" => "sql error : user register."));exit;
            }
            echo json_encode(array("success" => "1"));
        }
    }
    public function social_login() {

        $social_id = $this -> provider() -> _db -> real_escape_string($_POST['social_id']);
        $email = $this -> provider() -> _db -> real_escape_string($_POST['email']);
        $device_id = $_POST["device_id"];
        $device_type = $_POST["device_type"];

        $check = $this -> provider() -> single("select count(*) as count from account where email = '$email'");
        if ($check['count'] > 0) :
            if(count($this -> provider() -> single("select count(*) as count from account where email = '$email' and social_id = '$social_id'")) > 0){
                $info = $this -> provider() -> single("select * from account where email = '$email' and social_id = '$social_id'");
                if (count($info) > 0) :
//                if($info['status'] == 0) {
//                    echo json_encode(array("success" => "0", "message" => "your account already deactivated. Please contact to admin"));
//                    exit;
//                }
                    $created = time();
                    $token = $this -> generate_token(32);
                    $this -> provider() -> execute("delete from device where account_id = " . $info["id"]);
                    if(!$this -> provider() -> execute("insert into device(account_id, device_type, device_id, access_token, created) values('" . $info["id"] . "', '$device_type', '$device_id', '$token', '$created')")){
                        echo json_encode(array("success" => "0", "message" => "sql error : insert device error."));exit;
                    }

                    echo json_encode(array("success" => "1","Access-Token" => $token, "Device-Id" => $device_id, "userinfo" => $info, "avatar" => $info['avatar']));
                else :
                    echo json_encode(array("success" => "0", "message" => "sql error : user not exist."));exit;
                endif;
            }else{
                echo json_encode(array("success" => "0", "message" => "sql error : password is wrong."));exit;
            }
        else :
            echo json_encode(array("success" => "0", "message" => "There's no account associated with this email."));exit;
        endif;
    }
    public function freelancer_like() {
        $account_id = $this -> authentication();
        $post_id = $_POST["post_id"];
        $created = time();
        $chk = $this->provider()->single("select count(*) as ct from liked where post_id = $post_id and account_id = $account_id");
        if($chk['ct'] > 0){
            $sql = "update liked set state = 1  where post_id = $post_id and account_id = $account_id";
        }else{
            $sql = "insert into liked(account_id, post_id, created, state) values('$account_id', '$post_id', '$created', 1) ";
        }

        if($this->provider()->execute($sql)){
            /* notification */
            /*
            $receiver = $this->provider()->single("select user_id from post where user_id = $post_id ");
            $receiver_id = $receiver['user_id'];
            $device = $this -> provider() -> single("select * from device where account_id = $receiver_id");
            $token = $device['device_id'];
            $info = $this -> provider() -> single("select * from account where id = $account_id");
            $data = array("aps" => array(
                "alert" => $info['first_name']." ".$info['last_name']." liked your project.",
                "type" => "like",
                "sender_id" => $account_id,
                "receiver_id" => $receiver_id,
                "username" => $info['first_name']." ".$info['last_name'],
                "photo_url" => $info['avatar']
            ));
            $data_android = array(
                "message" => $info['first_name']." ".$info['last_name']." liked your project.",
                "type" => "dislike",
                "sender_id" => $account_id,
                "sender_name" => $info['first_name']." ".$info['last_name'],
                "receiver_id" => $receiver_id,
                "sender_photo" => $info['avatar']
            );
            if($device['device_type'] == "ios"){
                $this -> push($token, $data);
            }elseif($device['device_type'] == "android"){
                $this -> android_push(array($token), $data_android);
            }*/
            echo json_encode(array("success" => "1"));exit;
        }else{
            echo json_encode(array("success" => "0", "message" => "sql : error liked"));exit;
        }
    }
    public function freelancer_dislike() {
        $account_id = $this -> authentication();
        $post_id = $_POST["post_id"];
        $created = time();
        $chk = $this->provider()->single("select count(*) as ct from liked where post_id = $post_id and account_id = $account_id");
        if($chk['ct'] > 0){
            $sql = "update liked set state = 100  where post_id = $post_id and account_id = $account_id";
        }else{
            $sql = "insert into liked(account_id, post_id, created, state) values('$account_id', '$post_id', '$created', 100) ";
        }

        if($this->provider()->execute($sql)){
            /* notification */
            /*
            $receiver = $this->provider()->single("select user_id from post where user_id = $post_id ");
            $receiver_id = $receiver['user_id'];
            $device = $this -> provider() -> single("select * from device where account_id = $receiver_id");
            $token = $device['device_id'];
            $info = $this -> provider() -> single("select * from account where id = $account_id");
            $data = array("aps" => array(
                "alert" => $info['first_name']." ".$info['last_name']." disliked your project.",
                "type" => "like",
                "sender_id" => $account_id,
                "receiver_id" => $receiver_id,
                "username" => $info['first_name']." ".$info['last_name'],
                "photo_url" => $info['avatar']
            ));
            $data_android = array(
                "message" => $info['first_name']." ".$info['last_name']." disliked your project.",
                "type" => "dislike",
                "sender_id" => $account_id,
                "sender_name" => $info['first_name']." ".$info['last_name'],
                "receiver_id" => $receiver_id,
                "sender_photo" => $info['avatar']
            );
            if($device['device_type'] == "ios"){
                $this -> push($token, $data);
            }elseif($device['device_type'] == "android"){
                $this -> android_push(array($token), $data_android);
            }*/
            echo json_encode(array("success" => "1"));exit;
        }else{
            echo json_encode(array("success" => "0", "message" => "sql : error liked"));exit;
        }
    }
    public function get_profile() {
        $account_id = $this -> authentication();
        $sel_id = $_POST['sel_id'];
        $profile = $this->getUser($sel_id);

        if($profile){
            echo json_encode(array("success" => "1", "user_info"=>$profile));
        }else{
            echo json_encode(array("success" => "0", "message"=>"sql error : account"));
        }
        exit;
    }
    public function client_like() {
        $account_id = $this -> authentication();
        $post_id = $_POST["post_id"];
        $freelancer_id = $_POST["freelancer_id"];
        $created = time();
        $chk =  $this->provider()->execute("select count(*) from matched where client_id = '$account_id' and freelancer_id = '$freelancer_id'");
        if($chk == 0){
            $sql = "insert into matched(client_id, freelancer_id, post_id, state, created) values('$account_id', '$freelancer_id', '$post_id', 100, '$created') ";
        }else{
            $sql = "update matched set  state = 100 where client_id = '$account_id' and freelancer_id = '$freelancer_id' ";
        }
        if($this->provider()->execute($sql)){
            /* notification */
            $receiver = $freelancer_id;
            $receiver_id = $receiver['user_id'];
            $device = $this -> provider() -> single("select * from device where account_id = $receiver_id");
            $token = $device['device_id'];
            $info = $this -> provider() -> single("select * from account where id = $account_id");
            $data = array("aps" => array(
                "alert" => "you matched with ".$info['first_name']." ".$info['last_name'].". ",
                "type" => "like",
                "sender_id" => $account_id,
                "receiver_id" => $receiver_id,
                "username" => $info['first_name']." ".$info['last_name'],
                "photo_url" => $info['avatar']
            ));
            $data_android = array(
                "message" => "you matched with ".$info['first_name']." ".$info['last_name'].".",
                "type" => "like",
                "sender_id" => $account_id,
                "sender_name" => $info['first_name']." ".$info['last_name'],
                "receiver_id" => $receiver_id,
                "sender_photo" => $info['avatar']
            );
            if($device['device_type'] == "ios"){
                $this -> push($token, $data);
            }elseif($device['device_type'] == "android"){
                $this -> android_push(array($token), $data_android);
            }

            echo json_encode(array("success" => "1"));exit;
        }else{
            echo json_encode(array("success" => "0", "message" => "sql : error matched"));exit;
        }

    }
    public function client_dislike() {
        $account_id = $this -> authentication();
        $post_id = $_POST["post_id"];
        $freelancer_id = $_POST["freelancer_id"];
        $created = time();
        $chk =  $this->provider()->execute("select count(*) from matched where client_id = '$account_id' and freelancer_id = '$freelancer_id'");
        if($chk == 0){
            $sql = "insert into matched(client_id, freelancer_id, post_id, state, created) values('$account_id', '$freelancer_id', '$post_id', 100, '$created') ";
        }else{
            $sql = "update matched set  state = 100 where client_id = '$account_id' and freelancer_id = '$freelancer_id' ";
        }

        if($this->provider()->execute($sql)){
            /* notification */
            $receiver = $freelancer_id;
            $receiver_id = $receiver['user_id'];
            $device = $this -> provider() -> single("select * from device where account_id = $receiver_id");
            $token = $device['device_id'];
            $info = $this -> provider() -> single("select * from account where id = $account_id");
            $data = array("aps" => array(
                "alert" => "you matched with ".$info['first_name']." ".$info['last_name'].". ",
                "type" => "like",
                "sender_id" => $account_id,
                "receiver_id" => $receiver_id,
                "username" => $info['first_name']." ".$info['last_name'],
                "photo_url" => $info['avatar']
            ));
            $data_android = array(
                "message" => "you matched with ".$info['first_name']." ".$info['last_name'].".",
                "type" => "like",
                "sender_id" => $account_id,
                "sender_name" => $info['first_name']." ".$info['last_name'],
                "receiver_id" => $receiver_id,
                "sender_photo" => $info['avatar']
            );
            if($device['device_type'] == "ios"){
                $this -> push($token, $data);
            }elseif($device['device_type'] == "android"){
                $this -> android_push(array($token), $data_android);
            }

            echo json_encode(array("success" => "1"));exit;
        }else{
            echo json_encode(array("success" => "0", "message" => "sql : error matched"));exit;
        }

    }
    public function manage() {
        $account_id = $this -> authentication();

        $sql = "SELECT 	a.id, a.post_title, a.location, b.title AS category_title, a.hide_salary, a.salary_min,
                a.experience_year, a.description, a.salary_max, a.skill_ids,
					(SELECT COUNT(c.account_id) FROM `liked` c WHERE c.post_id = a.id) AS like_count
				FROM 	post a, main_cat b
				WHERE 	a.main_cat_id = b.id and a.user_id = $account_id";
        if($posts = $this->provider()->result($sql)){
            echo json_encode(array("success" => "1", "result" => $posts));exit;
        }else{
            echo json_encode(array("success" => "1", "result" => array()));exit;
        }
    }
    public function get_candidate() {
        $account_id = 5;//$this -> authentication();
        $post_id = $_POST['post_id'];
        $sql = "SELECT a.* ,
                (SELECT COUNT(*) FROM matched WHERE client_id = $account_id AND freelancer_id = a.id) AS matched
				FROM account a, `liked` b
				WHERE b.account_id = a.id
					AND b.post_id = '$post_id'
				ORDER BY a.first_name, a.last_name";
        if($candidates = $this->provider()->result($sql)){
            for($i = 0; $i < count($candidates); $i ++){
                $candidates[$i] = $this->update_userInfo($candidates[$i]);
            }
            echo json_encode(array("success" => "1", "candidates" => $candidates));exit;
        }else{
            echo json_encode(array("success" => "1", "candidates" => array()));exit;
        }
    }
    public function get_matches() {
        $account_id = $this -> authentication();

        $sql = "SELECT a.id, a.first_name, a.last_name, a.avatar, a.location, a.company, a.email
				FROM account a, `matched` b
				WHERE b.freelancer_id = a.id
					AND b.client_id = $account_id
				ORDER BY a.first_name, a.last_name";
        if($matches = $this->provider()->result($sql)){
            echo json_encode(array("success" => "1", "matches" => $matches));exit;
        }else{
            echo json_encode(array("success" => "1", "matches" => array()));exit;
        }
    }
    public function get_freelancer_matches() {
        $account_id = $this -> authentication();

        $sql = "SELECT a.id, a.first_name, a.last_name, a.avatar, a.location, a.company, a.email
				FROM account a, `matched` b
				WHERE b.client_id = a.id
					AND b.freelancer_id = $account_id
				ORDER BY a.first_name, a.last_name";
        if($matches = $this->provider()->result($sql)){
            echo json_encode(array("success" => "1", "matches" => $matches));exit;
        }else{
            echo json_encode(array("success" => "1", "matches" => array()));exit;
        }
    }
    public function get_post() {
        $account_id = $this -> authentication();

        $info = $this->provider()->single("select * from account where id = $account_id");
        // get post id by job_function
        $sql_jobFunction = "SELECT id FROM post WHERE main_cat_id = ". $info['main_cat_id'];
        $ids_jobFunction = $this->provider()->result($sql_jobFunction);
        // get post id by location
        $sql_location = "SELECT a.id FROM (SELECT id ,radius, ( 6371 * ACOS( COS( RADIANS(".$info['lat'].") )
                              * COS( RADIANS( lat ) )
                              * COS( RADIANS( lot ) - RADIANS(".$info['lot'].") )
                              + SIN( RADIANS(".$info['lat'].") )
                              * SIN( RADIANS( lat ) ) ) ) AS distance
                        FROM post WHERE status = 0) a WHERE a.distance < a.radius";

        $ids_location = $this->provider()->result($sql_location);
        // get post id by skill
        $arr_skill_ids = explode(",",$info['skill_ids']);
        $sql_skill = "select a.id from post ";
        $sub_where = "";
        for($j = 0; $j < count($arr_skill_ids); $j ++){
            if($j == 0){
                $sub_where .=" where ( find_in_set( ".$arr_skill_ids[$j].", a.skill_ids) > 0 ";
            }else{
                $sub_where .=" OR find_in_set( ".$arr_skill_ids[$j].", a.skill_ids) > 0 ";
            }
        }
        if(count($arr_skill_ids) > 0){
            $sql_skill = "select a.id from post a $sub_where )";
            $ids_skill = $this->provider()->result($sql_skill);
        }else{
            $ids_skill = array();
        }

        // remove post id by liked
        if($ar = $this->provider()->result("SELECT post_id AS id  FROM liked WHERE state > 0")){
            $ids_liked = $ar;
        }else{
            $ids_liked = array();
        }

        // remove post id by my post
        if($ar = $this->provider()->result("SELECT id  FROM post WHERE user_id = $account_id")){
            $ids_posted = $this->get_arr($ar, "id");
        }else{
            $ids_posted = array();
        }

//        echo $account_id;
//        echo "job function";
//
//        print_r($ids_jobFunction);
//        echo "location";
//        print_r($ids_location);
//        echo "skill";
//        print_r($ids_skill);
//        echo "liked";
//        print_r($ids_liked);exit;
        $ids = array_diff( array_merge( $this->get_arr($ids_jobFunction, "id"), $this->get_arr($ids_location, "id"),$this->get_arr($ids_skill, "id")),$this->get_arr($ids_liked, "id"));
        $idss = array_diff($ids, $ids_posted);
        if(count($ids) > 0){
            $sql = "select a.* from post a where id in (".implode(",", $idss).")";
        }else{
            $sql = "select a.* from post a ";
        }
        if($posts = $this->provider()->result($sql)){
            $result = array();
            foreach($posts as $post){
                $temp['id'] = $post['id'];
                $temp['user_id'] = $post['user_id'];
                $temp['post_title'] = $post['post_title'];
                $temp['main_cat_id'] = $post['main_cat_id'];
                $temp['main_cat_id'] = $post['main_cat_id'];
                $temp['location'] = $post['location'];
                $temp['lat'] = $post['lat'];
                $temp['radius'] = $post['radius'];
                $temp['relocate'] = $post['relocate'];
                $temp['remote'] = $post['remote'];
                $temp['experience_year'] = $post['experience_year'];
                $temp['hide_salary'] = $post['hide_salary'];
                $temp['salary_min'] = $post['salary_min'];
                $temp['salary_max'] = $post['salary_max'];
                $temp['skill_ids'] = $post['skill_ids'];
                $temp['post_url'] = $post['post_url'];
                $temp['perks'] = $post['perks'];
                $temp['description'] = $post['description'];
                $temp['qualification'] = $post['qualification'];
                $temp['created'] = $post['created'];
                $temp['client'] = $this->getUser($post['user_id']);
                $result[] = $temp;
            }

        }else{
            echo json_encode(array("success" => "1", "posts" => array()));exit;
        }
        echo json_encode(array("success" => "1", "posts" => $result));exit;
    }


    /************************ ***************************************************/
    /************************ ***************************************************/

}