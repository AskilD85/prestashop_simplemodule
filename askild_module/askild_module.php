<?php
if (!defined('_PS_VERSION_'))
exit;

class Askild_Module extends Module
{
    public function __construct()
    {
        $this->name = 'askild_module';
        $this->tab = 'other';
        $this->version = '1.0.0';
        $this->author = 'Андрей Харитонов';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Тестовый модуль');
        $this->description = $this->l('Описание моего модуля.');
        $this->confirmUninstall = $this->l('Удалить? Уверенны?');
        if (!Configuration::get('ASKILD_MODULE_NAME'))
        $this->warning = $this->l('No name provided');
    }
    public function install()
    {
  if (Shop::isFeatureActive())
    Shop::setContext(Shop::CONTEXT_ALL);
 
  if (!parent::install() ||
    !$this->registerHook('leftColumn') ||
    !$this->registerHook('header') ||
    !$this->registerHook('footer') ||
    !$this->registerHook('home') ||
    !Configuration::updateValue('ASKILD_MODULE_NAME', 'my friend')
  )
    return false;
 
  return true;
}
    public function uninstall()
    {
      if (!parent::uninstall() ||
        !Configuration::deleteByName('ASKILD_MODULE_NAME')
      )
        return false;

      return true;
}
    public function hookFooter() {
        //запрос в БД количество товара 
        $query = "SELECT * FROM ps_product";
        $res = Db::getInstance()->executeS($query);
        
        $price1 = Configuration::get('SIMPL_VAR1');//цена От
        $price2 = Configuration::get('SIMPL_VAR2');//Цена До
        $i = 0;
        foreach ($res AS $row) {
            if($row['price']>=$price1 && $row['price']<=$price2){
                $arr[] =$row['price'];
                ++$i;
                }
        }
        
        $this->context->smarty->assign('mess', count($arr) );
        $this->context->smarty->assign('price1', $price1 );
        $this->context->smarty->assign('price2', $price2 );
        return $this->display(__FILE__, 'askild_module.tpl');
    }
    
    
    public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
 
		// Init Fields form array
		$fields_form[0]['form'] = array(
				'legend' => array(
						'title' => $this->l('Настройка'),
						
				),
				'input' => array(
						array(
                                                    'type' => 'text',
                                                    'label' => $this->l('Цена От'),
                                                    'name' => 'SIMPL_VAR1',
                                                    'size' => 20,
                                                    'required' => true,
                                                    'desc' => $this->l('RUB'),
						),
                                                array(
                                                    'type' => 'text',
                                                    'label' => $this->l('Цена До'),
                                                    'name' => 'SIMPL_VAR2',
                                                    'size' => 10,
                                                    'required' => true,
                                                    'desc' => $this->l('RUB'),
						)
				),
                                
				'submit' => array(
						'title' => $this->l('Сохранить'),
						'class' => 'button'
				)
		);
                $helper = new HelperForm();
                // Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
 
		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
                
                $helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
				'save' =>
				array(
						'desc' => $this->l('Сохранить'),
						'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
						'&token='.Tools::getAdminTokenLite('AdminModules'),
				),
				'back' => array(
						'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
						'desc' => $this->l('Back to list')
				)
		);
                $helper->fields_value['SIMPL_VAR1'] = Configuration::get('SIMPL_VAR1');
                $helper->fields_value['SIMPL_VAR2'] = Configuration::get('SIMPL_VAR2');
                
                return $helper->generateForm($fields_form);
 
	}
   public function getContent()
	{
		$output = null;
 
		if (Tools::isSubmit('submit'.$this->name))
		{
			$simpl_var1 = strval(Tools::getValue('SIMPL_VAR1'));
                        $simpl_var2 = strval(Tools::getValue('SIMPL_VAR2'));
			if (!$simpl_var1  || !Validate::isGenericName($simpl_var1)
                                ||!$simpl_var2
                                || !Validate::isGenericName($simpl_var2) )
				$output .= $this->displayError( $this->l('Проверьте поля!!') );
			else
			{
				Configuration::updateValue('SIMPL_VAR1', $simpl_var1);
                                Configuration::updateValue('SIMPL_VAR2', $simpl_var2);
				$output .= $this->displayConfirmation($this->l('Настройки сохранены'));
			}
		}
		return $output.$this->displayForm();
	}

}