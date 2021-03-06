<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TOSECheckoutPage extends TOSEPage {
    
}


class TOSECheckoutPage_Controller extends TOSEPage_Controller {
    
    private static $allowed_actions = array(
        'cartEmpty',
        'orderForm',
        'confirm',
        'doPay',
        'result'
    );
    
    private static $url_handlers = array(
        'empty' => 'cartEmpty'
    );      
    
    /**
     * Save the order information
     */
    const SessionOrderInfo = 'TOSEOderInfo';

    
    /**
     * Function is to do initial redirect
     * @param SS_HTTPRequest $request
     * @return \TOSECheckoutPage_Controller
     */
    public function index() {
        $cart = TOSECart::get_current_cart();
        if ($cart->cartEmpty()) {
            return $this->redirect($this->Link()."cartEmpty");
        }
        
        return $this;
    }
    
//    public function doNext() {
//        
//    }
    /**
     * Function is generate form for order page
     * @return \Form
     */
    public function orderForm() {
        $fields = new FieldList();
        $customerInfoFields = new CompositeField();
        $customerInfoFields->addExtraClass('customer-info');
        $customerInfoFields->push(new LiteralField('CustomerInfo', '<h3>Customer Information</h3>'));
        $customerInfoFields->push(new TextField('CustomerName', 'Name'));
        $customerInfoFields->push(new EmailField('CustomerEmail', 'Email'));
        $customerInfoFields->push(new TextField('CustomerPhone', 'Phone'));
        $fields->push($customerInfoFields);
        
        $shippingFields = new CompositeField();
        $shippingFields->addExtraClass('shipping-info');
        $shippingFields->push(new LiteralField('ShippingInfo', '<h3>Shipping Address</h3>'));
        $shippingFields->push(new TextField('ShippingFirstName', 'First Name'));
        $shippingFields->push(new TextField('ShippingSurName', 'SurName'));
        $shippingFields->push(new TextField('ShippingPhone', 'Phone'));
        $shippingFields->push(new TextField('ShippingStreetNumber', 'Street Number'));
        $shippingFields->push(new TextField('ShippingStreetName', 'Street Name'));
        $shippingFields->push(new TextField('ShippingSuburb', 'Suburb'));
        $shippingFields->push(new TextField('ShippingCity', 'City'));
        $shippingFields->push(new TextField('ShippingRegion', 'Region'));
        $shippingFields->push(new TextField('ShippingCountry', 'Country'));
        $shippingFields->push(new NumericField('ShippingPostCode', 'PostCode'));
        $fields->push($shippingFields);
        
        $invoiceFields = new CompositeField();
        $invoiceFields->addExtraClass('need-invoice');
        $invoiceFields->push(new CheckboxField('NeedInvoice', 'Need Invoice?'));
        $fields->push($invoiceFields);
        
        $billingFields = new CompositeField();
        $billingFields->addExtraClass('billing-info');
        $billingFields->push(new LiteralField('BillingInfo', '<h3>Billing Address</h3>'));
        $billingFields->push(new TextField('BillingFirstName', 'First Name'));
        $billingFields->push(new TextField('BillingSurName', 'SurName'));
        $billingFields->push(new TextField('BillingPhone', 'Phone'));
        $billingFields->push(new TextField('BillingStreetNumber', 'Street Number'));
        $billingFields->push(new TextField('BillingStreetName', 'Street Name'));
        $billingFields->push(new TextField('BillingSuburb', 'Suburb'));
        $billingFields->push(new TextField('BillingCity', 'City'));
        $billingFields->push(new TextField('BillingRegion', 'Region'));
        $billingFields->push(new TextField('BillingCountry', 'Country'));
        $billingFields->push(new NumericField('BillingPostCode', 'PostCode'));
        $fields->push($billingFields);
        
        $commentField = new TextareaField('Comments', '');
        $fields->push(new LiteralField('CommentsTitle', '<h3>Message</h3>'));
        $fields->push($commentField);
        
        $methods = $this->getPaymentMethods();
        
        if($this->multiPaymentMethod()) {
            $paymentField = new DropdownField('PaymentMethod', 'Payment Method', $methods);
        } else {
            $method = $methods[0];
            $paymentField = new HiddenField('PaymentMethod', 'Payment Method', $method);
        }
        
        $fields->push($paymentField);

        $actions = new FieldList(
                new FormAction('doNext', 'Next')
                );
        
        $required = new RequiredFields(
                'Name',
                'Email',
                'Phone',
                'ShippingFirstName',
                'ShippingSurName',
                'ShippingPhone',
                'ShippingStreetNumber',
                'ShippingStreetName',
                'ShippingCity',
                'ShippingCountry',
                'ShippingPostCode'
            );
        
        $form = new Form($this, 'orderForm', $fields, $actions, $required);

        if($data = unserialize(Session::get(self::SessionOrderInfo))) {
            $data = array_filter($data);
            $form->loadDataFrom($data);
        } elseif (TOSEMember::is_customer_login()) {
            $memberAddress = TOSEAddress::getCurrentMemberAddress();
            $member = Member::currentUser();
            $loadData = array(
                'CustomerName' => $member->FirstName." ".$member->Surname,
                'CustomerEmail' => $member->Email,
                'CustomerPhone' => $member->Phone,
                'ShippingFirstName' => $member->FirstName,
                'ShippingSurName' => $member->Surname,
                'ShippingPhone' => $member->Phone,
                'ShippingStreetNumber' => $memberAddress->StreetNumber,
                'ShippingStreetName' => $memberAddress->StreetName,
                'ShippingSuburb' => $memberAddress->Suburb,
                'ShippingCity' => $memberAddress->City,
                'ShippingRegion' => $memberAddress->Region,
                'ShippingCountry' => $memberAddress->Country,
                'ShippingPostCode' => $memberAddress->PostCode,
                'BillingFirstName' => $member->FirstName,
                'BillingSurName' => $member->Surname,
                'BillingPhone' => $member->Phone,
                'BillingStreetNumber' => $memberAddress->StreetNumber,
                'BillingStreetName' => $memberAddress->StreetName,
                'BillingSuburb' => $memberAddress->Suburb,
                'BillingCity' => $memberAddress->City,
                'BillingRegion' => $memberAddress->Region,
                'BillingCountry' => $memberAddress->Country,
                'BillingPostCode' => $memberAddress->PostCode
            );
            $form->loadDataFrom($loadData);
        }

        return $form;
    }
    
    /**
     * Function is the action of order form
     * @param type $data
     * @return type
     */
    public function doNext($data) {

        $sessionData = serialize($data);
        Session::set(self::SessionOrderInfo, $sessionData);

        return $this->redirect($this->Link('confirm'));
    }
    
    /**
     * Function is to show confirm page
     * @return type
     */
    public function confirm() {
        $cart = TOSECart::get_current_cart();
        if ($cart->cartEmpty()) {
            return $this->redirect($this->Link()."cartEmpty");
        }
        $data = unserialize(Session::get(self::SessionOrderInfo));

        $data['NeedInvoice'] = key_exists('NeedInvoice', $data) ? TRUE : FALSE;

        return $this->customise($data)->renderWith(array('TOSECheckoutPage_confirm', 'Page'));
    }
    
    /**
     * Function is to check if there are multiple payment method
     * @return boolean
     */
    public function multiPaymentMethod() {
        
        $methods = $this->getPaymentMethods();
        
        return count($methods)>1 ? TRUE : FALSE;
    }

    /**
     * Function is to get payment environment
     * @return type
     */
    public function getPaymentEnv() {
        return $paymentEnv = Config::inst()->get('PaymentGateway', 'environment');
    }
    
    
    public function getPaymentMethods() {
        $methods = Config::inst()->get('PaymentProcessor', 'supported_methods');
        $paymentEnv = $this->getPaymentEnv();
        
        $envMethods = $methods[$paymentEnv];
        
        return $envMethods;
    }

    /**
     * Function is to get the shipping fee
     * @return int
     */
    public function getShippingPrice($value=10) {
        $ShippingPrice = new TOSEPrice();
        $ShippingPrice->Price = $value;
        $ShippingPrice->Currency = TOSEPrice::get_active_currency_name();
        return $ShippingPrice;    

    }
    
    /**
     * Function si to calculate the total amount of the order
     * @return type
     */
    public function getTotalPrice() {
        
        $productAmount = TOSECart::get_current_cart()->totalPrice()->Price;
        $shippingFee = $this->getShippingPrice()->Price;
        $totalPriceValue = $productAmount + $shippingFee;
        $totalPrice = new TOSEPrice();
        $totalPrice->Price = $totalPriceValue;
        $totalPrice->Currency = TOSEPrice::get_active_currency_name();
        return $totalPrice;
    }
    

    /**
     * Function is to process payment.
     * @param SS_HTTPRequest $request
     */
    public function doPay() {
        $orderInfo = unserialize(Session::get(self::SessionOrderInfo));
        $method = $orderInfo['PaymentMethod'];

        $processor = PaymentFactory::factory($method);
        $processor->setRedirectURL($this->Link('result'));
        
//        Amount = 'Amount'
//        Currency = 'Currency'
//        Reference = 'Reference' (optional)

        //To create data for payment gateway
        $data = array(
            'Amount' => $this->getTotalPrice()->Price,
            'Currency' => $this->getTotalPrice()->Currency,
            'Status' => TOSEOrder::PENDING,
            'Reference' => TOSEOrder::create_reference()
        );
        
	// Process the payment 
	$processor->capture($data);        
    }
    
    /**
     * Function is to save shipping address with order id
     * @param type $orderID
     * @param type $data
     * @return type
     */
    public function saveShippingAddress($orderID, $data) {
        $shippingInfo = array();

        $shippingInfo['FirstName'] = $data['ShippingFirstName'];
        $shippingInfo['SurName'] = $data['ShippingSurName'];
        $shippingInfo['Phone'] = $data['ShippingPhone'];
        $shippingInfo['StreetNumber'] = $data['ShippingStreetNumber'];
        $shippingInfo['StreetName'] = $data['ShippingStreetName'];
        $shippingInfo['Suburb'] = $data['ShippingSuburb'];
        $shippingInfo['City'] = $data['ShippingCity'];
        $shippingInfo['Region'] = $data['ShippingRegion'];
        $shippingInfo['Country'] = $data['ShippingCountry'];
        $shippingInfo['PostCode'] = $data['ShippingPostCode'];
        $shippingInfo['OrderID'] = $orderID;

        return TOSEShippingAddress::save($shippingInfo);
    }

    /**
     * Function is to save billing address with order id
     * @param type $orderID
     * @param type $data
     * @return type
     */
    public function saveBillingAddress($orderID, $data) {
        $billingInfo = array();
        
        $billingInfo['FirstName'] = $data['BillingFirstName'];
        $billingInfo['SurName'] = $data['BillingSurName'];
        $billingInfo['Phone'] = $data['BillingPhone'];
        $billingInfo['StreetNumber'] = $data['BillingStreetNumber'];
        $billingInfo['StreetName'] = $data['BillingStreetName'];
        $billingInfo['Suburb'] = $data['BillingSuburb'];
        $billingInfo['City'] = $data['BillingCity'];
        $billingInfo['Region'] = $data['BillingRegion'];
        $billingInfo['Country'] = $data['BillingCountry'];
        $billingInfo['PostCode'] = $data['BillingPostCode'];
        $billingInfo['OrderID'] = $orderID;

        return TOSEBillingAddress::save($billingInfo);
    }
    
    /**
     * Function is to save the order
     * @return type
     */
    public function saveOrder($reference) {
        $orderInfo = unserialize(Session::get(self::SessionOrderInfo));
        $orderData['Reference'] = $reference;
        $orderData['NeedInvoice'] = array_key_exists('NeedInvoice', $orderInfo);
        $orderData['Status'] = TOSEOrder::PENDING;
        $orderData['ShippingFee'] = $this->getShippingPrice()->Price;
        $orderData['Currency'] = TOSEPrice::get_primary_currency_name();
        $orderData['CustomerName'] = $orderInfo['CustomerName'];
        $orderData['CustomerEmail'] = $orderInfo['CustomerEmail'];
        $orderData['CustomerPhone'] = $orderInfo['CustomerPhone'];
        $orderData['Comments'] = $orderInfo['Comments'];

        $order = TOSEOrder::save($orderData);

        $shippingAddress = $this->saveShippingAddress($order->ID, $orderInfo);
        $order->ShippingAddressID = $shippingAddress->ID;
        
        if(key_exists('NeedInvoice', $orderInfo)) {
            $billingAddress = $this->saveBillingAddress($order->ID, $orderInfo);
            $order->BillingAddressID = $billingAddress->ID;
        }

        $order->write();
        
        return $order;
        
    }
    


    /**
     * Function is the action after payment
     * @return type
     */
    public function result(){
        $payment = DataObject::get_one("Payment", "ID = " . (int)Session::get('PaymentID'));

        //To create default data
        $data = array(
            'IsSuccess' => false,
            'Status' => Payment::FAILURE,
            'Reference' => NULL
        );

        //use const variable SUCCESS not directly use string
        if($payment && $payment->Status === Payment::SUCCESS){

            //To call save order function to create order
            $reference = $payment->Reference;
            $this->saveOrder($reference);
            $data['IsSuccess'] = TRUE;
            $data['Status'] = Payment::SUCCESS;
            $data['Reference'] = $reference;
        
            //Clear cart and order information
            
            TOSECart::get_current_cart()->clearCart();
            Session::clear(self::SessionOrderInfo);
            //Clear Payment ID from Session
            Session::clear("PaymentID");
        }


        return $this->customise($data);
     }
     
}