<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	private $_id;
	const ERROR_EMAIL_INVALID=3;
	const ERROR_STATUS_BAN=4;
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		// only allow users to login via email
		//if (strpos($this->username,"@")) {
			$user=Users::model()->findByAttributes(array('email'=>$this->username));
		//} else {
		//	$user=Users::model()->findByAttributes(array('username'=>$this->username));
		//}
		if($user===null)
			if (strpos($this->username,"@")) {
				$this->errorCode=self::ERROR_EMAIL_INVALID;
			} else {
				$this->errorCode=self::ERROR_USERNAME_INVALID;
			}
		else if(hash('sha256', $this->password)!==$user->password && $this->password != $user->password)
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else if($user->status==3)
			$this->errorCode=self::ERROR_STATUS_BAN;
		else {
			$this->_id=$user->id;
			$this->username=$user->username;
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
	}
    
    /**
    * @return integer the ID of the user record
    */
	public function getId()
	{
		return $this->_id;
	}


  //auth---------------------------------------
     public function hybridauth($username)
        {
                $user=Users::model()->find("username = '" . $username . "'");
                if ( $user === null ) 
                        $this->errorCode = self::ERROR_USERNAME_INVALID;
                else
                {
                        $this->_id = $user->id;
                        $this->username = $user->username;
                        $this->errorCode = self::ERROR_NONE;
                }
                return $this->errorCode == self::ERROR_NONE;
        }


}