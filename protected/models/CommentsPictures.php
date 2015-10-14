<?php

/**
 * This is the model class for table "tbl_comments_pictures".
 *
 * The followings are the available columns in table 'tbl_comments_pictures':
 * @property integer $comment_id
 * @property string $path
 * @property integer $create_time
 */
class CommentsPictures extends CActiveRecord
{

        public $file;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tbl_comments_pictures';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('comment_id', 'numerical', 'integerOnly'=>true),
                       array('path, create_time','safe'),

			array('path', 'length', 'max'=>255),

                    array('file', 'file',
		    'types'=>'jpeg, png, jpg, gif, tiff, bmp',
		    'safe'=>false,
                    'maxSize'=>1024 * 1024 * 5, // 5MB
                    'maxFiles' => 1,
                    'allowEmpty' => true,
                    'tooLarge'=>'There is one file larger than 5MB. Please upload a smaller one.'
),

			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('comment_id, path, create_time', 'safe', 'on'=>'search'),
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
			'comment_id' => 'Comment',
			'path' => 'Path',
			'create_time' => 'Create Time',
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

		$criteria->compare('comment_id',$this->comment_id);
		$criteria->compare('path',$this->path,true);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CommentsPictures the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
