<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TOSEPage extends Page {
    
    /**
     * Save the cart information. For non-login user
     */
    const SessionCart = 'TOSECart';
    /**
     * Save the current currency name
     */
    const SessionCurrencyName = 'TOSEPriceName';
    /**
     * Save the register information
     */
    const SessionRegisterInfo = 'TOSERegisterInfo';
    /**
     * Save the order information
     */
    const SessionOrderInfo = 'TOSEOderInfo';
    
    /**
     * Set the default value if they are not set correctly in config.yml
     * @var type 
     */
    private static $default_page_title = array(
            'TOSEPage' => 'Ecommerce',
            'TOSECategoryPage' => 'Category',
            'TOSEProductPage' => 'Product',
            'TOSECartPage' => 'Cart',
            'TOSECheckoutPage' => 'Checkout',
            'TOSELoginPage' => 'Login',
            'TOSERegisterPage' => 'Register',
            'TOSEAccountPage' => 'Account'
        );

    private static $allowed_children = array(
            'TOSECategoryPage',
            'TOSEProductPage',
            'TOSECartPage',
            'TOSECheckoutPage',
            'TOSELoginPage',
            'TOSERegisterPage',
            'TOSEAccountPage'
        );
    
    /**
     * Function is to generate tosecom pagess
     */
    public function requireDefaultRecords() {
        parent::requireDefaultRecords();
        
        //To create default data
        if(!TOSEDataGenerator::has_initiated()){
            TOSEDataGenerator::start_gen();
        }
        
    }
    
    /**
     * Function is to get the page title for ecommerce pages
     * @param type $pageName
     * @return type
     */
    public static function get_page_title($pageName) {
        $config = Config::inst()->get($pageName, 'pageTitle');
        if(!$config) {
            $defaultTitle = self::$default_page_title[$pageName];
            return $defaultTitle;
        }
        return $config;
    }
    
    /**
     * Function is to get the page URL segment for ecommerce pages
     * @param type $pageName
     * @return type
     */
    public static function get_page_URLSegment($pageName) {
        $config = Config::inst()->get($pageName, 'pageURLSegment');
        if(!$config) {
            $defaultTitle = self::$default_page_title[$pageName];
            $defaultURLSegment = strtolower($defaultTitle);
            return $defaultURLSegment;
        }
        
        return $config;
    }
    
    /**
     * Function is to get page link based on page class name
     * @param type $pageName
     * @return type
     */
    public static function get_page_link($pageName) {
        $page = SiteTree::get_one($pageName);
        
        return $page->Link();
    }  
    /**
     * Function is to check if customer member logged in
     * @return type
     */
    public function isCustomerLogin() {
        return TOSEMember::is_customer_login();
    }
    
    /**
     * Function is to get login or logout string based on member login status
     * @return type
     */
    public function getLogInOut() {
        return $this->isCustomerLogin() ? "logout" : "login";
    }
    
    /**
     * Function is to get the name of login member
     * @return boolean
     */
    public function getMemberName() {
        if (TOSEMember::is_customer_login()) {
           return Member::currentUser()->FirstName; 
        }
        
        return FALSE;
    }
    
    
    /**
     * Function is to get current cart object
     * @return type
     */
    public function getCart() {
        return TOSECart::get_current_cart();
    }
    
    
}


class TOSEPage_Controller extends Page_Controller {
    
    private static $allowed_actions = array(
        'logout'
    );
    
    /**
     * Function is to perform logout action
     * @return type
     */
    public function logout() {
        TOSEMember::logout();
        return $this->redirect('ecommerce/login');
    }  
    
    /**
     * Function is to get the link of ecommerce page
     * @return type
     */
    public function getEcommerceRootPageLink() {
        $page = DataObject::get_one('SiteTree', "ClassName='TOSEPage'");
        return $page->URLSegment;
    }

    /**
     * Function is to get current cart link
     * @return type
     */
    public function getCartLink() {
        $cartPage = DataObject::get_one('SiteTree', "ClassName='TOSECartPage'");
        return $cartPage->Link();
    }
    
    public function getCheckoutLink() {
        $checkoutPage = DataObject::get_one('TOSECheckoutPage');
        return $checkoutPage->Link();
    }
    
}