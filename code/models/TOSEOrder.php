<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TOSEOrder extends DataObject {
    
    const PENDING = "Pending";
    const DELIVERED = "Delivered";
    
    private static $db=array(
        'Reference'=>'Varchar(20)',
        'NeedInvoice' => "Boolean",
        'Status'=>"Enum('Pending, Delivered', 'Pending')",
        'ShippingFee' => 'Currency',
        'Currency' => 'Varchar(10)',
        'CustomerName'=>'Varchar',
        'CustomerEmail'=>'Varchar',
        'CustomerPhone'=>'Varchar',
        'Comments' => "Text"
    );
        
    private static $has_one=array(
        'ShippingAddress'=>'TOSEShippingAddress',
        'BillingAddress'=>'TOSEBillingAddress',
        'Member' => 'Member'
    );
    
    private static $has_many = array(
        'Items' => "TOSEOrderItem"
    );
    
    private static $summary_fields = array(
        'Reference' => 'Reference',
        'getTotalPrice' => 'Total Price',
        'Created' => 'Created',
        'CustomerName' => 'Customer Name',
        'Status' => 'Status'
    );
    
    /**
     * OVERRIDE
     * @return type
     */
    public function getTitle() {
        return $this->Reference;
    }

    /**
     * OVERRIDE
     * @param type $member
     * @return boolean
     */
    public function canDelete($member = null) {
        return FALSE;
    }
    
    
    /**
     * OVERRIDE
     * @param type $member
     * @return boolean
     */
    public function canEdit($member = null) {
        return TRUE;
    }
    
    /**
     * Function is to save order object
     * @param type $data
     * @return \TOSEOrder
     */
    public static function save($data) {
        
        $order = new TOSEOrder();
        $data['MemberID'] = Member::currentUserID();
        $order->update($data);
        $order->write();
        $cartItems = TOSECart::get_current_cart()->getCartItems();
        foreach ($cartItems as $cartItem) {
            $orderItem = array();
            
            $orderItem['OrderID'] = $order->ID;
            $orderItem['Quantity'] = $cartItem->Quantity;
            $orderItem['Name'] = $cartItem->getProduct()->Name;
            $orderItem['Category'] = $cartItem->getProduct()->Category()->Name;
            $orderItem['SKU'] = $cartItem->Spec()->SKU;
            $orderItem['Weight'] = $cartItem->Spec()->Weight;
            $orderItem['Price'] = $cartItem->subTotalPrice()->Price;
            $orderItem['Currency'] = TOSEPrice::get_primary_currency_name();
            $orderItem['ProductID'] = $cartItem->getProduct()->ID;
            $orderItem['SpecID'] = $cartItem->SpecID;

            TOSEOrderItem::save($orderItem);
        }
        
        
        return $order;
    }
    
    /**
     * Function is to create reference number
     * @return type
     */
    public static function create_reference(){
        $date = date("Y-m-d");  
        $time = (int)date("Ymd")*10000;
        
        $amount = DataObject::get("TOSEOrder", "Created Like'{$date}%'")->count();
        $ref = $time + $amount+1;
        
        return $ref;
    }
    
    /**
     * Function is to get total products price
     * @return type
     */
    public function getItemsPrice() {
        $items = $this->Items();
        $productPriceValue = 0;
        foreach ($items as $item) {
            $productPriceValue += $item->Price;
        }
        $productPrice = new TOSEPrice();
        $productPrice->Price = $productPriceValue;
        $productPrice->Currency = TOSEPrice::get_active_currency_name();
        return $productPrice;        
    }

    /**
     * Function is to get total price including shipping fee
     * @return type
     */        
    public function getTotalPrice() {

        $totalPriceValue = $this->getItemsPrice()->Price + $this->ShippingFee;
        $totalPrice = new TOSEPrice();
        $totalPrice->Price = $totalPriceValue;
        $totalPrice->Currency = TOSEPrice::get_active_currency_name();
 
        return $totalPrice;
    }

    /**
     * Function is to update order status
     * @param type $reference
     * @param type $status
     */
//    public function updateOrderStatus($status) {
//        $this->Status = $status;
//        $this->write();
//    }
    
    /**
     * OVERRIDE
     * @return \FieldList
     */
    public function getCMSFields() {
        $fields = new FieldList();
        
        $fields->push(new ReadonlyField('Reference', 'Reference No.'));
        if ($this->Status == self::PENDING) {
            $statusField = new DropdownField('Status', 'Status', array(
                                    self::PENDING => self::PENDING,
                                    self::DELIVERED => self::DELIVERED
                                ));
        } else {
            $statusField = new ReadonlyField('Status', 'Status');
        }
        $fields->push($statusField);
        
        $customerInfoField = new CompositeField();
        $customerInfoField->push(new HeaderField('customerInfoHeader', 'Customer Information'));
        $customerInfoField->push(new ReadonlyField('CustomerName', 'Cutomer Name'));
        $customerInfoField->push(new ReadonlyField('CustomerEmail', 'Customer Email'));
        $customerInfoField->push(new ReadonlyField('CustomerPhone', 'Customer Phone'));
        $customerInfoField->push(new ReadonlyField('Comments', 'Comments'));
        $fields->push($customerInfoField);
        
        $orderInfoField = new CompositeField();
        $orderInfoField->push(new HeaderField('orderInfoHeader', 'Order Information'));
        $orderInfoField->push(new ReadonlyField('Created', 'Created'));
        $orderInfoField->push(new ReadonlyField('NeedInvoiceString', 'Need Invoice?', $this->NeedInvoice ? 'Yes' : 'No'));
        $orderInfoField->push(new ReadonlyField(FALSE, 'Product Price', $this->getItemsPrice()->Nice()));
        $orderInfoField->push(new ReadonlyField(FALSE, 'Shipping fee', $this->Currency." ".  TOSEPrice::get_currency_symbol($this->Currency).$this->ShippingFee));
        $orderInfoField->push(new ReadonlyField(FALSE, 'Total Price', $this->getTotalPrice()->Nice()));
        $fields->push($orderInfoField);
        
        $itemsInfoFields = new CompositeField();
        $itemsInfoFields->push(new HeaderField('itemsInfoHeader', 'Order Items List'));
        $itemsInfoFields->push(new LiteralField('ItemsInfo', $this->getItemsInfo4CMS()));
        $fields->push($itemsInfoFields);
        
        $shippingFields = new CompositeField();
        $shippingFields->push(new HeaderField('shippingHeader', 'Shipping Information'));
        $shippingFields->push(new LiteralField('ShippingInfo', $this->getShippingInfo4CMS()));
        $fields->push($shippingFields);
        
        if($this->NeedInvoice) {
            $billingFields = new CompositeField();
            $billingFields->push(new HeaderField('BillingHeader', 'Billing Information'));
            $billingFields->push(new LiteralField('BillingInfo', $this->getbillingInfo4CMS()));
            $fields->push($billingFields);
        }
        
        return $fields;
    }

    /**
     * Function is to get shipping info for CMS to show order
     * @return string
     */
    public function getShippingInfo4CMS() {
        $shippingAddress = $this->ShippingAddress();
        $info = $shippingAddress->FirstName . " " . $shippingAddress->SurName;
        $info .= "<br>" . $shippingAddress->Phone;
        $info .= "<br>" . $shippingAddress->StreetNumber . " " .$shippingAddress->StreetName . ", " . $shippingAddress->Suburb;
        $info .= "<br>" . $shippingAddress->City . ", " . $shippingAddress->Region . ", " . $shippingAddress->Country . ", " . $shippingAddress->PostCode;
        return $info;
    }
    
    /**
     * Function is to get billing info for CMS to show order
     * @return string
     */
    public function getBillingInfo4CMS() {
        $billingAddress = $this->ShippingAddress();
        $info = $billingAddress->FirstName . " " . $billingAddress->SurName;
        $info .= "<br>" . $billingAddress->Phone;
        $info .= "<br>" . $billingAddress->StreetNumber . " " .$billingAddress->StreetName . ", " . $billingAddress->Suburb;
        $info .= "<br>" . $billingAddress->City . ", " . $billingAddress->Region . ", " . $billingAddress->Country . ", " . $billingAddress->PostCode;
        return $info;
    }
    
    /**
     * Function is to get items HTML codes for cms
     * @return string
     */
    public function getItemsInfo4CMS() {
        $items = $this->items();
        $info = "<table style='border-spacing: 50px 5px; border-collapse:separate'>"
                . "<tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>QTY</th><th>Sub Total</th></tr>";
        foreach ($items as $item) {
            $info .= "<tr><td><img src='".$item->Product()->getDefaultImage()->Filename."' style='width:60px;' ></td>"
                    . "<td>$item->Name</td>"
                    . "<td>$item->Category</td>"
                    . "<td>NZD $$item->Price</td>"
                    . "<td>$item->Quantity</td>"
                    . "<td>$item->Name</td>";
        }
        
        $info .= "</table>";
        
        return $info;
        
    }

    /**
     * Function is to move order from order table to history order table
     */
    public function moveToHistory() {
        $historyOrder = new TOSEHistoryOrder();
        $historyOrder->Reference = $this->Reference;
        $historyOrder->NeedInvoice = $this->NeedInvoice;
        $historyOrder->Status = $this->Status;
        $historyOrder->ShippingFee = $this->ShippingFee;
        $historyOrder->Currency = $this->Currency;
        $historyOrder->CustomerName = $this->CustomerName;
        $historyOrder->CustomerEmail = $this->CustomerEmail;
        $historyOrder->CustomerPhone = $this->CustomerPhone;
        $historyOrder->Comments = $this->Comments;
        $historyOrder->ShippingAddressID = $this->ShippingAddressID;
        $historyOrder->BillingAddressID = $this->BillingAddressID;
        $historyOrder->MemberID = $this->MemberID;
        $historyOrder->write();
        
        // Modify shipping and billing address foreign key
        $shippingAddress = $this->ShippingAddress();
        $shippingAddress->OrderID = 0;
        $shippingAddress->HistoryOrderID = $historyOrder->ID;
        $shippingAddress->write();
        $billingAddress = $this->BillingAddress();
        $billingAddress->OrderID = 0;
        $billingAddress->HistoryOrderID = $historyOrder->ID;
        $billingAddress->write();
        
        $items = $this->Items();
        foreach($items as $item) {
            $item->OrderID = 0;
            $item->HistoryOrderID = $historyOrder->ID;
            $item->write();
        }
        
        $this->delete();
    }
    
    /**
     * Function is to move order to history order if status is latest
     */
    public function onAfterWrite() {
        if($this->Status == self::DELIVERED) {
            $this->moveToHistory();
        }
    }
    
    /**
     * Function is to delete the accessory data
     */
    public function onBeforeDelete() {
        parent::onBeforeDelete();
        $items = $this->Items();
        if ($items->exists()) {
            foreach ($items as $item) {
                if(!$item->HistoryOrderID) {
                    $item->delete();
                }
            }
        }
        $shippingAddress = $this->ShippingAddress();
        if($shippingAddress->ID && !$shippingAddress->HistoryOrderID) {
            $this->ShippingAddress()->delete();
        }
        
        $billingAddress = $this->BillingAddress();
        if($billingAddress->ID && !$billingAddress->HistoryOrderID) {
            $this->BillingAddress()->delete();
        }

    }
//    public function canPay() {
//        if($this->Status == TOSEOrder::PENDING) {
//            return TRUE;
//        }
//        
//        return FALSE;
//    }

}
