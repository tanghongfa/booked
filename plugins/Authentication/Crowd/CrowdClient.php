<?php
/**
Copyright 2015 HONGFA TANG (Ericsson)
 */

class CrowdClient {

	private $crowdServerUrl;
	private $appName;
	private $appPassword;
	
	public function __construct($crowdServerUrl, $appName, $appPassword) {
		$this->crowdServerUrl = $crowdServerUrl;
		$this->appName = $appName;
		$this->appPassword = $appPassword;
	}

	/**
	* Simple requests
	*/
	private function _sendHttpRequest($url, $headers = array(), $isPost = false, $postBody = null, $cookies = array()) {
        $ch = $this->_initAndConfigure($url, $headers, $isPost, $postBody, $cookies);
        $result = curl_exec ($ch); 

        $requestOut = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        Log::Debug($requestOut);

        if($result === false) {
            Log::Error("Failed to send POST request to URL:" . $url);
            curl_close ($ch); 
            return false;
        }

        Log::Debug($result);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        $respCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close ($ch); 

        if(200 <= $respCode && 300 > $respCode) {//20x series response code treat as success response
            return array('header' => $header, 'body' => $body);
        } else {
            Log::Error($header);
            return false;
        }
	}    

	public function validateUserLogin($userName, $password) {
		Log::Debug('Calling validateUserLogin: %s, pwd: %s', $userName, $password);

        $headers = array('Content-Type: application/json', 'Accept: application/json');
        $postBody = '{"value": "'. $password .'"}';
        $result = $this->_sendHttpRequest($this->crowdServerUrl . "/rest/usermanagement/latest/authentication?username=" . $userName, $headers, true, $postBody);

        if($result === false) {
            return false;
        } else {
            $responseJson = json_decode($result['body']);
            $user =array('email' => $responseJson->{'email'}, 'firstName' => $responseJson->{'first-name'}, 'lastName' => $responseJson->{'last-name'});            
            return $user;
        }        
	}

    /**
    * @return array for the Group Names
    */    
    public function getUserGroups($userName) {
        Log::Debug('Loading group information for user %s', $userName);

        $headers = array('Content-Type: application/json', 'Accept: application/json');
        
        $result = $this->_sendHttpRequest($this->crowdServerUrl . "/rest/usermanagement/latest/user/group/direct?username=" . $userName, $headers);

        if($result === false) {
            return false;
        } else {
            $responseJson = json_decode($result['body']);
            $groupNames = array();

            foreach ($responseJson->{'groups'} as $crowdGroup) {
                array_push($groupNames, $crowdGroup->{'name'});
            }

            return $groupNames;
        }
    }

    public function createCrowdSession($username) {
        Log::Debug('Loading create Crowd Session for user %s', $userName);

        $headers = array('Content-Type: application/json', 'Accept: application/json');
        $clientIp = $this->_getClientIpAddress();


        $postBody = '{"username":"' . $username . '", "validation-factors" : {"validationFactors" : [{"name" : "remote_address", "value" : "' . $clientIp .'"}]}}';
        
        $result = $this->_sendHttpRequest($this->crowdServerUrl . "/rest/usermanagement/latest/session?validate-password=false", $headers, true, $postBody);

        if($result === false) {
            return false;
        } else {
            $responseJson = json_decode($result['body']);
            return $responseJson->{'token'};
        }
    }

    public function validateCrowdSession($sessionId) {
        Log::Debug('Validate crowd session %s', $sessionId);

        $headers = array('Content-Type: application/json', 'Accept: application/json');

        $result = $this->_sendHttpRequest($this->crowdServerUrl . "/rest/usermanagement/latest/session/" . $sessionId, $headers);

        if($result === false) {
            return false;
        } else {
            $responseJson = json_decode($result['body']);
            $userInfo = $responseJson->{'user'};
            $user =array('email' => $userInfo->{'email'}, 'firstName' => $userInfo->{'first-name'}, 'lastName' => $userInfo->{'last-name'}, 'name' => $userInfo->{'name'});            
            return $user;
        }
    }

    public function deleteCrowdSession($token) {
        Log::Debug('Delete crowd session for token %s', $token);

        $this->_sendRestDeleteRequest($this->crowdServerUrl . "/rest/usermanagement/latest/session/" . $token);        
    }

    private function _initAndConfigure($url, $headers, $isPost, $postBody, $cookies) {

        /*********************************************************
         * initialize the CURL session
        *********************************************************/
        $ch = curl_init($url);        

        /*********************************************************
         * Set SSL configuration -- No verification -- as either it will be using self-signed certification or just HTTP
        *********************************************************/
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        /*********************************************************
         * Configure curl to capture our output.
        *********************************************************/
        // return the CURL output into a variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        /*********************************************************
         * Add cookie headers to our request.
        *********************************************************/
        if (count($cookies)) {
            $cookieStrings = array();
            foreach ($cookies as $name => $val) {
                $cookieStrings[] = $name.'='.$val;
            }
            curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookieStrings));
        }

        /*********************************************************
         * Add any additional headers
        *********************************************************/
        if (count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        /*********************************************************
         * Add HTTP Authentication
        *********************************************************/
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->appName . ":" . $this->appPassword);


        /*********************************************************
         * Flag and Body for POST requests
        *********************************************************/
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
        }

        /*********************************************************
         * Enable to get HEADER_OUT -- debuging purpose
        *********************************************************/
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        return $ch;
    }

    private function _sendRestDeleteRequest($url, $postBody = '') {
        $ch = curl_init();

        /*********************************************************
         * Configure Delete operation
        *********************************************************/
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /*********************************************************
         * Add HTTP Authentication
        *********************************************************/
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->appName . ":" . $this->appPassword);


        $result = curl_exec($ch);
        $result = json_decode($result);
        curl_close($ch);

        return $result;
    }


    private function _getClientIpAddress() {

        $ip = null;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        //Localhost
        if($ip == '::1') {
            $ip = '0:0:0:0:0:0:0:1';
        }

        return $ip;
    }

}
