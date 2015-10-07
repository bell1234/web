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

			array('category_id', 'required', 'message'=>'请为你的内容分类'),

			array('user_id, create_time', 'required'),		//system will assign it...

			array('user_id, create_time, category_id, up, down, points, comments, type, hide, processed, views, private, thumb_generated', 'numerical', 'integerOnly'=>true),
			array('name, link, thumb_pic', 'length', 'max'=>500),
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
