<?php

class PostController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView()
	{
            $post = $this->loadModel();
            $comment = $this->_newComment($post);
            
            $this->render('view',array(
                'model' => $post,
                'comment' => $comment,
            ));
	}

        protected function _newComment($post)
        {
            $comment = new Comment;

            if (isset($_POST['ajax']) && $_POST['ajax'] == 'comment-form') {
                echo CActiveForm::validate($comment);
                Yii::app()->end();
            }

            if (isset($_POST['Comment'])) {
                $comment->attributes = $_POST['Comment'];
                if ($post->addComment($comment)) {
                    if ($comment->status == Comment::STATUS_PENDING)
                        Yii::app()->user->setFlash('commentSubmitted', 'Thank you for your comment.
                        Your comment will be posted once it is approved.');
                    $this->refresh();
                }
            }
            return $comment;
        }

        /**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Post;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Post'])){
                    $model->attributes = $_POST['Post'];
                    $model->image = CUploadedFile::getInstance($model, 'image');
                    if($model->save()){
                        $model->image->saveAs('upload/' . '123.jpg');
                        $this->redirect(array('view','id'=>$model->id));
                    }
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate1($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Post']))
		{
			$model->attributes=$_POST['Post'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

        public function actionUpdate($id = null)
	{
            if($id === null)
                $model = new Post();
	    else if(!$model=Post::model()->findByPk($id))
                throw new CHttpException(404);

            if(isset($_POST['Post'])){
                $model->attributes = $_POST['Post'];
                if($model->save())
                    $this->refresh();
            }

            $this->render('update',array(
                'model'=>$model,
            ));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{

            $criteria = new CDbCriteria(array(
                'condition' => 'status=' . Post::STATUS_PUBLISHED,
                'order' => 'update_time DESC',
                'with' => 'commentCount'
            ));
            if (isset($_GET['tag']))
                $criteria->addSearchCondition('tags', $_GET['tag']);

            $dataProvider = new CActiveDataProvider('Post', array(
                'pagination' => array(
                    'pageSize' => 5,
                ),
                'criteria' => $criteria
            ));

            $this->render('index',array(
                'dataProvider' => $dataProvider,
            ));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Post('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Post']))
			$model->attributes=$_GET['Post'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

        private $_model;
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Post the loaded model
	 * @throws CHttpException
	 */
	public function loadModel()
	{
            if ($this->_model === null) {
                if (isset($_GET['id'])) {
                    if (Yii::app()->user->isGuest)
                        $condition = 'status=' . Post::STATUS_PUBLISHED
                            . ' OR status=' . Post::STATUS_ARCHIVED;

                    else $condition = '';
                    $this->_model = Post::model()->findByPk($_GET['id'], $condition);
                }
                if ($this->_model === null)
                    throw new CHttpException(404, 'Запрашиваемая страница не существует.');
            }
            return $this->_model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Post $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='post-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

        public function actionTestmembers()
        {
            $team = new Team();
            $team->name = 'Team name';

            $member1 = new Member();
            $member1->name = 'Member1 name';

            $member2 = new Member();
            $member2->name = 'Member2 name';

            $team->members[] = $member1;
            $team->members[] = $member2;

            $team->save();
        }
        
        public function actionFilm()
	{
            $films = Film::model()->findAll();
            foreach($films as $item){
                echo '<pre>';
                print_r($item->attributes);
                echo '</pre>';

            }
            exit();
	}

        const RATING_USERS_PER_ITARATION = 100;
        
        public function actionIterations()
        {
            Yii::import('application.models.User', true);
            $letters='abcdefghijklmnopqrstuvwxyz';
            $results=array();

            for($i=0; $i<strlen($letters); $i++){
                $letter = $letters[$i];
                echo "Working on '$letter' letter...\n<br/>";

                $dataProvider = new CActiveDataProvider('User', array(
                    'criteria' => array(
                        'select' => 't.rating',
                        'order' => 't.username',
                        'condition' => 't.username LIKE :username',
                        'params' => array(':username' => $letter.'%')
                    )
                ));

                $iterator = new CDataProviderIterator($dataProvider,
                    self::RATING_USERS_PER_ITARATION);

                $result = 0;
                foreach ($iterator as $user)
                    $result += $user->rating/($iterator->totalItemCount) * 100000;

                $results[$letter] = $result / 100000;
                echo "Letter '$letter' is done!<br/>";

            }
        }
}
