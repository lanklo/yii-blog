<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

        protected function beforeAction($action)
        {
            parent::beforeAction($action);

            $cs = Yii::app()->clientScript;
            $cs->registerCssFile(Yii::app()->baseUrl . '/css/screen.css', 'screen, projection');
            $cs->registerCssFile(Yii::app()->baseUrl . '/css/print.css', 'print');
            $cs->registerCssFile(Yii::app()->baseUrl . '/css/main.css');
            $cs->registerCssFile(Yii::app()->baseUrl . '/css/form.css');

            $cs->scriptMap = array(
                'jquery.js' => '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js',
                'jquery.ajaxqueue.js' => false,
                'jquery.metadata.js' => false,
            );

            return true;
        }
}