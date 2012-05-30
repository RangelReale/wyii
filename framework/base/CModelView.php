<?php
/**
 * CModelView class file.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModelView is a model view.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.2
 */
class CModelView extends CFormModel
{
    private $_viewModel;
    private $_views;

	/**
	 * Constructor.
	 * The scenario will be copied from the view model.
	 * @param string name of the scenario that this model is used in.
	 * See {@link CModel::scenario} on how scenario is used by models.
	 */
	public function __construct($viewModel)
	{
        $this->_viewModel = $viewModel;
		$this->setScenario($viewModel->getScenario());
		$this->init();
		$this->attachBehaviors($this->behaviors());
	}

	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking
	 * if the named alias exists.
	 * @param string the property name or the event name
	 * @return boolean whether the property value is null
	 * @since 1.0.1
	 */
	public function __isset($name)
	{
		if(isset($this->getView()->views[$name]))
			return true;
		else
			return parent::__isset($name);
	}

	/**
	 * PHP getter magic method.
	 * This method is overridden to process aliases.
	 * @param string property name
	 * @return mixed property value
	 * @see getAttribute
	 */
	public function __get($name)
	{
		if(isset($this->getView()->views[$name]))
			return $this->getView()->getValue($name);
		else
			return parent::__get($name);
	}

	/**
	 * PHP setter magic method.
	 * This method is overridden to process aliases.
	 * @param string property name
	 * @param mixed property value
	 */
	public function __set($name,$value)
	{
		if(isset($this->getView()->views[$name]))
			return $this->getView()->setValue($name,$value);
		else
			parent::__set($name,$value);
	}

	/**
	 * Returns the mapped attribute names.
	 * @return string attribute names
	 */
	public function attributeNames()
    {
        return CMap::mergeArray(
            parent::attributeNames(),
            array_keys($this->view->views)
        );
    }

	/**
	 * Returns the mapped attribute labels.
	 * @return string attribute labels
	 */
	public function attributeLabels()
	{
		return $this->view->labels;
	}

	/**
	 * @return CModelViews the views for this AR class.
	 */
	public function getView()
	{
		if($this->_views!==null)
			return $this->_views;
		else
        {
            $this->_views=new CModelViews($this);
            $this->_views->refresh();
			return $this->_views;
        }
	}

	/**
	 * Returns the view model
	 * @return CModel the view model
	 */
    public function getViewModel()
    {
        return $this->_viewModel;
    }

    /**
     *
     * @return array views definition
     */
    public function views()
    {
        return array();
    }

	/**
	 * Checks if the validator is allowed to be imported.
	 * @param CValidator validator
	 * @return boolean whether to import the validator
	 */
	public function isValidatorAllowed($validator)
	{
		return (!(
			($validator instanceof CSafeValidator) ||
			($validator instanceof CUnsafeValidator) ||
			($validator instanceof CInlineValidator)
		));
	}

	/**
	 * Imports validators from view model.
	 * @return CList validator list
	 */
	public function createValidators()
    {
        $ret=parent::createValidators();
        foreach ($this->view->views as $vname => $vvalue)
		{
			if (!isset($vvalue[2]['validators']) || $vvalue[2]['validators'])
				foreach ($this->viewModel->getValidators($vvalue[1]) as $validator)
				{
					if ($this->isValidatorAllowed($validator))
					{
						$newValidator = clone $validator;
						$newValidator->attributes=array($vname);
						$ret->add($newValidator);
					}
				}
		}
        return $ret;
    }
}

/**
 * CModelViews is the list view mode views.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.2
 */
class CModelViews
{
	public $labels=array();
	public $views=array();

	private $_model;

	public function __construct($model)
	{
		$this->_model=$model;

		$this->init();
	}

	protected function init()
	{
		$this->views = array();
		// create views
		foreach ($this->_model->views() as $view)
		{
			if(isset($view[0],$view[1]))  // view, attribute
			{
				$this->views[$view[0]]=array($view[0],$view[1],array_slice($view,2),array('value'=>null));
			}
			else
				throw new CDbException(Yii::t('yii','Active record "{class}" has an invalid configuration for view "{view}". It must specify the view and the attribute.',
					array('{class}'=>get_class($model),'{view}'=>$view[0])));
		}
	}

	/**
	 * Returns the view related attribute value.
	 * @param string view name
	 * @param mixed related attribute value
	 */
	public function getValue($name)
	{
		if(!isset($this->views[$name]))
			throw new CDbException(Yii::t('yii','{class} does not have view "{name}".',
				array('{class}'=>get_class($this->_model), '{name}'=>$name)));

        return $this->views[$name][3]['value'];
	}

	/**
	 * Sets the view related attribute value.
	 * @param string view name
	 * @param string value to set the related attribute
	 */
	public function setValue($name,$value)
	{
		if(!isset($this->views[$name]))
			throw new CDbException(Yii::t('yii','{class} does not have view "{name}".',
				array('{class}'=>get_class($this->_model), '{name}'=>$name)));

        $this->views[$name][3]['value'] = $value;
	}

	/**
	 * Load values from view model
	 */
    public function refresh()
    {
		$this->labels=array();
        foreach ($this->views as $vname => $vvalue)
        {
            $this->labels[$vname] = $this->_model->viewModel->getAttributeLabel($vvalue[1]);
            $this->views[$vname][3]['value'] = $this->_model->viewModel->getAttributeValue($vvalue[1]);
        }
    }

	/**
	 * Sets values to view model
	 */
	public function apply()
	{
        foreach ($this->views as $vname => $vvalue)
        {
			if (!isset($vvalue[2]['apply']) || $vvalue[2]['apply'])
				$this->_model->viewModel->setAttributeValue($vvalue[1], $vvalue[3]['value']);
        }
	}
}

?>
