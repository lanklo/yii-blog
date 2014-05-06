<?php

/**
 * This is the model class for table "{{post}}".
 *
 * The followings are the available columns in table '{{post}}':
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $status
 * @property string $tags
 * @property string $create_time
 * @property string $update_time
 * @property integer $author_id
 */
class Post extends CActiveRecord
{
        const STATUS_DRAFT = 1;
        const STATUS_PUBLISHED = 2;
        const STATUS_ARCHIVED = 3;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{post}}';
	}

        public function defaultScope()
        {
            return array(
                'condition' => "lang='" . Yii::app()->language . "'"
            );
        }

        public function lang($lang)
        {
            $this->getDbCriteria()->mergeWith(array(
                'condition' => "lang='$lang'"
            ));
            return $this;
        }

        /**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
            // NOTE: you should only define rules for those attributes that
            // will receive user inputs.
            return array(
                array('title, content, status', 'required'),
                array('title', 'filter', 'filter' => array('TranslitFilter', 'translitUrl')),
                array('title', 'length', 'max'=>128),
                array('status', 'in', 'range' => array(1, 2, 3)),
                array('tags', 'match', 'pattern' => '/^[\w\s,]+$/',
                    'message' => 'В тегах можно использовать только буквы'),
                array('tags', 'normalizeTags'),
                array('image', 'file', 'types' => 'jpg, gif, png'),
                // The following rule is used by search().
                // @todo Please remove those attributes that should not be searched.
                array('title, status', 'safe', 'on'=>'search'),
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
                    'author' => array(self::BELONGS_TO, 'User', 'author_id'),
                    'comments' => array(self::HAS_MANY, 'Comment', 'post_id',
                        'condition' => 'comments.status='.Comment::STATUS_APPROVED,
                        'order' => 'comments.create_time DESC'),
                    'commentCount' => array(self::STAT, 'Comment', 'post_id',
                        'condition' => 'status='.Comment::STATUS_APPROVED)
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Title',
			'content' => 'Content',
			'status' => 'Status',
			'tags' => 'Tags',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'author_id' => 'Author',
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
		$criteria->compare('title',$this->title,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('tags',$this->tags,true);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('author_id',$this->author_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
        
        public function beforeSave() 
        {
            if(parent::beforeSave()){
                if($this->IsNewRecord){
                    $this->create_time = $this->update_time = time();
                    $this->author_id = Yii::app()->user->id;
                }else 
                    $this->update_time = time();

                if(($this->scenario == 'insert' || $this->scenario == 'update') &&
                    ($image = CUploadedFile::getInstance($this, 'image'))){
                    $this->deleteDocument();

                    $this->image = $image;
                    $this->image->saveAs(
                            Yii::getPathOfAlias('webroot.upload').DIRECTORY_SEPARATOR.$this->image);
                }

                return true;
            }else
                return false;
        }
        
        public function afterSave()
        {
            parent::afterSave();
            Tag::model()->updateFrequency($this->_oldTags, $this->tags);
        }

        protected function beforeDelete(){
            if(!parent::beforeDelete())
                return false;
            $this->deleteDocument(); // удалили модель? удаляем и файл
            return true;
        }

        public function afterDelete()
        {
            parent::afterDelete();
            Comment::model()->deleteAll('post_id=' . $this->id);
            Tag::model()->updateFrequency($this->tags, '');
        }

        public function deleteDocument(){
            $documentPath=Yii::getPathOfAlias('webroot.upload').DIRECTORY_SEPARATOR.
                $this->image;
            if(is_file($documentPath))
                unlink($documentPath);
        }
        
        private $_oldTags;
        
        protected function afterFind() 
        {
            parent::afterFind();
            $this->_oldTags = $this->tags;
        }

        /**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Post the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

        public function normalizeTags($attribute, $params)
        {
            $this->tags = Tag::array2string(array_unique(Tag::string2array($this->tags)));
        }

        public function getUrl()
        {
            return Yii::app()->createUrl('post/view', array(
                'id' => $this->id,
                'title' => $this->title
            ));
        }

        public function addComment($comment)
        {
            if (Yii::app()->params['commentNeedApprtoval'])
                $comment->status = Comment::STATUS_PENDING;
            else
                $comment->status = Comment::STATUS_APPROVED;
            $comment->post_id = $this->id;
            return $comment->save();
        }

}
