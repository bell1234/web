<?php
/**
 * UserChangePassword class.
 * UserChangePassword is the data structure for keeping
 * user change password form data. It is used by the 'changepassword' action of 'UserController'.
 */
class UserChangePassword extends CFormModel {
	public $oldPassword;
	public $password;
	public $verifyPassword;
	
	public function rules() {
		return  Yii::app()->controller->action->id == 'forgetpassword' ? array(
			array('password, verifyPassword', 'required'),
			array('password, verifyPassword', 'length', 'max'=>128, 'min' => 6,'tooShort' => "密码至少包含6个字符", 'tooLong'=>'密码最多包含128个字符'),
			array('verifyPassword', 'compare', 'compareAttribute'=>'password', 'message' => "两次输入的密码不符"),
		) : array(
			array('oldPassword', 'required', 'message'=>'请输入旧密码'),
			array('oldPassword, password', 'length', 'max'=>128, 'min' => 6,'tooShort' => "新密码至少包含6个字符", 'tooLong'=>'密码最多包含128个字符'),
			array('verifyPassword', 'compare', 'compareAttribute'=>'password', 'message' => "两次输入的密码不符"),
			array('oldPassword', 'verifyOldPassword'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
		);
	}
	
	/**
	 * Verify Old Password
	 */
	 public function verifyOldPassword($attribute, $params)
	 {
		 if (Users::model()->findByPk(Yii::app()->user->id)->password != hash('sha256', $this->oldPassword))
			 $this->addError($attribute, "现有密码输入不正确，请重试");
	 }
}