<?php

/**
 * This is the model class for table "tbl_users".
 *
 * The followings are the available columns in table 'tbl_users':
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $avatar
 * @property string $activkey
 * @property string $cookie
 * @property integer $create_time
 * @property integer $lastvisit
 * @property integer $lastaction
 * @property integer $status
 * @property string $ip
 * @property integer $logins
 * @property string $country
 * @property string $name_token
 * @property string $city
 */
class Users extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tbl_users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('create_time, lastvisit, lastaction, status, logins', 'numerical', 'integerOnly'=>true),

			array('username', 'length', 'max'=>20, 'tooLong'=>'用户名最多包含20个字符'),
			array('username', 'length', 'min'=>2, 'tooShort'=>'用户名至少包含2个字符'),
			array('username','required','message'=>'请输入用户名'),
			array('username', 'match', 'pattern' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]*$/u','message' =>'用户名只能含有汉字，英文字母，数字和下划线'),
			
			array('email','required','message'=>'请输入电子邮箱地址'),
			array('email', 'unique', 'message' => '此邮箱已经被注册'),
			array('email', 'email','message'=>'请输入您真实的电子邮箱地址'),

			array('password','required','message'=>'请输入密码'),
			array('password', 'length', 'min'=>6, 'tooShort'=>'密码至少包含6个字符'),
			array('password', 'match', 'pattern' => '/^[A-Za-z0-9]*$/','message' =>'密码只能含有英文字母和数字'),

			array('password, email, activkey, cookie, name_token', 'length', 'max'=>128),
			array('avatar', 'length', 'max'=>255),
			array('ip, city', 'length', 'max'=>50),
			array('country', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, username, password, email, avatar, activkey, cookie, create_time, lastvisit, lastaction, status, ip, logins, country, name_token, city', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'username' => 'Username',
			'password' => 'Password',
			'email' => 'Email',
			'avatar' => 'Avatar',
			'activkey' => 'Activkey',
			'cookie' => 'Cookie',
			'create_time' => 'Create Time',
			'lastvisit' => 'Lastvisit',
			'lastaction' => 'Lastaction',
			'status' => 'Status',
			'ip' => 'Ip',
			'logins' => 'Logins',
			'country' => 'Country',
			'name_token' => 'Name Token',
			'city' => 'City',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('avatar',$this->avatar,true);
		$criteria->compare('activkey',$this->activkey,true);
		$criteria->compare('cookie',$this->cookie,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('lastvisit',$this->lastvisit);
		$criteria->compare('lastaction',$this->lastaction);
		$criteria->compare('status',$this->status);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('logins',$this->logins);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('name_token',$this->name_token,true);
		$criteria->compare('city',$this->city,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}



	public function isAdmin() {
		// Returns null if it doesn't find the user in the admins table
		return Admins::model()->findByAttributes(array('user_id'=>$this->id));
	}


	/**
	 * This function save a user's duplicate accounts 
	 * @return null
     	*/

	public function saveDupStats(){

		$model = $this;
		if($model->cookie && !isset($_COOKIE["duc"])){
			setcookie("duc", $model->cookie, time() + 200000000, "/");
		}
	
		if (!$model->cookie):
			if (!isset($_COOKIE["duc"])): //stands for duplicate user check
				$duc = base64_encode($model->activkey);
				setcookie("duc", $duc, time() + 200000000, "/");
			endif;
			
			if (isset($_COOKIE["duc"])) {	//in case cookie disabled.
				$duc = $_COOKIE["duc"];
			} else {
				$duc = base64_encode($model->activkey);
			}
			
			$model->cookie = $duc;
			$model->save(false);

			$dup_cookie = Users::model()->find(array(
				'condition' => 'id != :uid AND cookie = :duc',
				'params' => array(
					':uid' => $model->id,
					':duc' => $duc
				)
			));
			
			$dup_ip = Users::model()->find(array(
				'condition' => 'id != :uid AND ip = :ip',
				'params' => array(
					':uid' => $model->id,
					':ip' => $model->ip
				)
			));
			
			if ($dup_cookie) {
				$su = SameUser::model()->find(array(
					'condition' => '(user1 = :uid1 AND user2 = :uid2) OR (user1 = :uid2 AND user2 = :uid1)',
					'params' => array(
						':uid1' => $model->id,
						':uid2' => $dup_cookie->id
					)
				));
				if (!$su) {
					$su = new SameUser;
				}
				$su->user1 = $dup_cookie->id; //other account always act as user 1
				$su->user2 = $model->id;
				$hex       = 1;
				if ($dup_cookie->ip == $model->ip && $model->ip) {
					$hex += 10;
				}
				if ($dup_cookie->password == $model->password) {
					$hex += 100;
				}
				$su->type_id     = $hex;
				$su->create_time = time();
				$su->save();
			}
		
			if ($dup_ip && $model->ip) { //that you actually have an IP
				$su = SameUser::model()->find(array(
					'condition' => '(user1 = :uid1 AND user2 = :uid2) OR (user1 = :uid2 AND user2 = :uid1)',
					'params' => array(
						':uid1' => $model->id,
						':uid2' => $dup_ip->id
					)
				));
				if (!$su) {
					$su = new SameUser;
				}
				$su->user1 = $dup_ip->id; //other account always act as user 1
				$su->user2 = $model->id;
				$hex       = 10;
				if ($dup_ip->password == $model->password) {
					$hex += 100;
				}
				if ($dup_ip->cookie == $model->cookie && $model->cookie) {
					$hex += 1;
				}
				$su->type_id     = $hex; //cookie
				$su->create_time = time();
				$su->save();
			}
			
		endif;

	}


	/**
	 * This function gets a user's duplicate accounts 
	 * @return null
     	*/

	public function getDuplicateAccount($uid){
	$tree = array();
	$admin = Admins::model()->findByPk($uid);
	if($admin){
		return $tree;	//return empty array for admin.
	}
        $su = SameUser::model()->find(array(
            'condition' => 'user1 = :uid OR user2 = :uid AND (type_id = 1 OR type_id = 11 OR type_id = 111 OR type_id = 101 OR type_id = 110)',
            'params' => array(
                ':uid' => $uid,
            ),
            'order'=>'id ASC'
        ));
        if($su){

            if($uid == $su->user1){
                $other = $su->user2;
            }else{
                $other = $su->user1;
            }
	    $admin = Admins::model()->findByPk($other);
	    if(!$admin){	//not admin
            	array_push($tree, $other);
	    }
        }

        $i = 0;

        while($su){
            $su = SameUser::model()->find(array(
                'condition' => "(user1 = :uid OR user2 = :uid OR user1 = :me OR user2 = :me) AND id > :sid AND (type_id = 1 OR type_id = 11 OR type_id = 111 OR type_id = 101 OR type_id = 110)",
                'params' => array(
                    ':me' => $uid,
                    ':uid' => $other,
                    ':sid' => $su->id
                ),
                'order'=>'id ASC'
            ));

            if($su){
                if($uid == $su->user1){
                    $other = $su->user2;
                }else if($uid == $su->user2){
                    $other = $su->user1;
                }else if($other == $su->user1){	//previous $other
                    $other = $su->user2;
                }else{
                    $other = $su->user1;
                }

		$admin = Admins::model()->findByPk($other);
		if(!$admin){
                	array_push($tree, $other);
		}
            }
        }
	return $tree;
	}



	/**
	 * This function updates the lastaction time in the user model, and also sets
	 * the location city.
	 *
	 * @return null
     	*/
	public function userActed() {
		$this->lastaction = time();

		if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){

			$location = @Yii::app()->citygeoip->lookupLocation($_SERVER['HTTP_CF_CONNECTING_IP']);
			if ($location) {
				$this->city = $location->city;
			}

			$location = Yii::app()->geoip->lookupCountryCode($_SERVER['HTTP_CF_CONNECTING_IP']);
			if ($location) {
				$this->country = $location;
			}
			$this->ip = $_SERVER['HTTP_CF_CONNECTING_IP'];

		} else {

			$location = @Yii::app()->citygeoip->lookupLocation($_SERVER['REMOTE_ADDR']);
			if ($location) {
				$this->city = $location->city;
			}

			$location = Yii::app()->geoip->lookupCountryCode($_SERVER['REMOTE_ADDR']);
			if ($location) {
				$this->country = $location;
			}
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}


		$this->save(false);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Users the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
