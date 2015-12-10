<?php

/**
 * This is the model class for table "tbl_notification".
 *
 * The followings are the available columns in table 'tbl_notification':
 * @property integer $id
 * @property integer $type_id
 * @property integer $sender
 * @property integer $receiver
 * @property integer $other
 * @property integer $create_time
 * @property integer $read
 */
class Notification extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tbl_notification';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type_id, sender, receiver, create_time', 'required'),
			array('type_id, sender, receiver, other, create_time, read, post_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, type_id, sender, receiver, other, create_time, read', 'safe', 'on'=>'search'),
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
			'senderx' => array(self::BELONGS_TO, 'Users', 'sender'),
			'receiverx' => array(self::BELONGS_TO, 'Users', 'receiver'),
			'post' => array(self::BELONGS_TO, 'Posts', 'post_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'type_id' => 'Type',
			'sender' => 'Sender',
			'receiver' => 'Receiver',
			'other' => 'Other',
			'create_time' => 'Create Time',
			'read' => 'Read',
		);
	}


	public function sendiOSNotification($data){	//pass in ['title'], ['user_id']
		$notifs = DeviceToken::model()->findAllByAttributes(array("user_id"=>$data['user_id'], "device"=>"iOS"));
		foreach($notifs as $notif){
			if ($notif && $notif->token) {
				$unreadMessages = Yii::app()->db->createCommand('SELECT count(*) from tbl_notification where receiver = '.$data['user_id'])->queryScalar();
				$data["token"] = $notif->token;
				$data["unread"] = $unreadMessages;
				$data["type"] = 1;				//to inbox, 2 is to homepage
			 	$url = Yii::app()->params['globalURL'].'/simplepush/iospush.php?'.http_build_query($data);
				$ch  = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //this prevent printing the 200json code
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); //timeout 1s
				curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout 1s
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$result = curl_exec($ch);
				curl_close($ch);
			}
		}
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
		$criteria->compare('type_id',$this->type_id);
		$criteria->compare('sender',$this->sender);
		$criteria->compare('receiver',$this->receiver);
		$criteria->compare('other',$this->other);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('read',$this->read);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Notification the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
