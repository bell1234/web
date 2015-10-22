<?php

/**
 * This is the model class for table "tbl_posts".
 *
 * The followings are the available columns in table 'tbl_posts':
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $link
 * @property string $shorturl
 * @property integer $user_id
 * @property integer $create_time
 * @property integer $category_id
 * @property integer $up
 * @property integer $down
 * @property integer $views
 * @property integer $points
 * @property integer $comments
 * @property integer $type
 * @property integer $hide
 * @property integer $processed
 */
class Posts extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tbl_posts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required', 'message'=>'请输入一个吸引人的标题'),
			array('name', 'length','min'=>3, 'tooShort'=>'标题也太短了吧！'),

			array('name', 'length','max'=>90, 'tooLong'=>'标题太长啦！'),

			array('description', 'length', 'min'=>10, 'max'=>'65535', 'tooShort'=>'内容太少啦！', 'tooLong'=>'内容太长啦!'),

			//array('category_id', 'required', 'message'=>'请为你的内容分类'),

			array('user_id, create_time', 'required'),		//system will assign it...

			array('user_id, create_time, category_id, up, down, points, comments, type, hide, processed, views, private, auto', 'numerical', 'integerOnly'=>true),

			array('name, link, thumb_pic, video_html', 'length', 'max'=>500),
			array('shorturl', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, description, link, shorturl, user_id, create_time, category_id, up, down, points, comments, type, hide, processed', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'description' => 'Description',
			'link' => 'Link',
			'views' => 'Views',
			'shorturl' => 'Shorturl',
			'user_id' => 'User',
			'create_time' => 'Create Time',
			'category_id' => 'Category',
			'up' => 'Up',
			'down' => 'Down',
			'points' => 'Points',
			'comments' => 'Comments',
			'type' => 'Type',
			'hide' => 'Hide',
			'processed' => 'Processed',
			'thumb_pic'=>'Thumb Nail',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('link',$this->link,true);
		$criteria->compare('shorturl',$this->shorturl,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('up',$this->up);
		$criteria->compare('views',$this->views);
		$criteria->compare('down',$this->down);
		$criteria->compare('points',$this->points);
		$criteria->compare('comments',$this->comments);
		$criteria->compare('type',$this->type);
		$criteria->compare('hide',$this->hide);
		$criteria->compare('processed',$this->processed);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	//ensure that in php.ini allow_url_fopen is enable
	//s3国内无法使用 以后开始使用又拍云 目前存在服务器上就 http://www.yiiframework.com/extension/upyun/ 

	public function grab_image($url,$saveto){
    		$ch = curl_init ($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    		$raw=curl_exec($ch);
    		curl_close ($ch);
   		if(file_exists($saveto)){
        		unlink($saveto);
    		}
    		$fp = fopen($saveto,'x');
    		fwrite($fp, $raw);
    		fclose($fp);
 		$thumb = new Imagick($saveto);
       		$thumb->setImageFormat("png");
        	$thumb->thumbnailImage(200, 200);
   		if(file_exists($saveto)){
        		unlink($saveto);
    		}
        	$thumb->writeImage($saveto);

		//s3 国内被墙
		//$success = Yii::app()->s3->upload($saveto, $saveto, 'meiliuer');	//from, to, bucket name
		//
   		//if(file_exists($saveto)){
        	//	unlink($saveto);
    		//}
		return $success;
	}


	public function getTitle($url){

		Yii::import('application.extensions.embedly.src.Embedly.Embedly', true);

		// Call with pro (you'll need a real key)
		$pro = new Embedly\Embedly(array(
 		   'key' => '05c0e7529f174ace83e837e28ffc448e',
    		    'user_agent' => 'Mozilla/8.0 (compatible; mytestapp/1.0)'
		));
		$objs = $pro->oembed($url);

		$arr = array();

		if(isset($objs->title)){	//title
			array_push($arr, $objs->title);
		}else{
			array_push($arr, "");		
		}

		if(isset($objs->thumbnail_url)){	//thumbnail

			//$this->grab_image($objs->thumbnail_url, "uploads/posts/".Yii::app()->user->id."/pic_".time().".png");

			array_push($arr, $objs->thumbnail_url);
		}else{
			array_push($arr, "");	
		}

		if(isset($objs->html)){	//video...
			array_push($arr, $objs->html);
		}else{
			array_push($arr, "");	
		}

		return json_encode($arr);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Posts the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
