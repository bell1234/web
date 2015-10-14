<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 */
class ContactForm extends CFormModel
{
	public $name;
	public $email;
	public $subject;
	public $body;
	public $verifyCode;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			// name, email, subject and body are required
			array('name', 'required', 'message'=>'请输入姓名'),
			array('email', 'required', 'message'=>'请输入您的邮箱'),
			array('subject', 'required', 'message'=>'请输入标题'),
			array('body', 'required', 'message'=>'请输入内容'),
			// email has to be a valid email address
			array('email', 'email', 'message'=>'请输入一个有效的邮件地址，以便我们取得联系'),
			// verifyCode needs to be entered correctly
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements(), 'message'=>'验证码不正确，请重新输入'),
		);
	}

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'verifyCode'=>'Verification Code',
		);
	}
}