<?php

/***
DISCLAIMER:
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTOR "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE
*/

class Booklink_Vm_Model_Payment extends Mage_Payment_Model_Method_Cc
{
    const CGI_URL = 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/processxml.do';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canUseCheckout = true;
    protected $_canCapture = true;
    protected $_canUseInternal = true;
    protected $_canRefund = true; 
    protected $_canSaveCc = true;
	protected $_canCapturePartial = true;
	protected $_canRefundInvoicePartial = true;

    protected $_code = "vm";
    protected $_formBlockType = 'vm/payment_form_vm';
    protected $_order;
    
    const REQUEST_TYPE_AUTH_CAPTURE = 'CCSALE';
    const REQUEST_TYPE_AUTH_ONLY    = 'CCAUTHONLY';
    const REQUEST_TYPE_CREDIT       = 'CCCREDIT';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'CCFORCE';
    const STATUS_APPROVAL           = 'APPROVAL';
	
    public function authorize(Varien_Object $payment, $amount)
    {
        $minOrderTotal = $this->getConfigData('min_order_total');
        $maxOrderTotal = $this->getConfigData('max_order_total');
		
		if(!empty($minOrderTotal))
		{
        	if ($amount < $minOrderTotal)
        	{
            	Mage::throwException(Mage::helper('vm')->__('Invalid amount for authorization.'));
			}
		}
		if(!empty($maxOrderTotal))
		{
        	if ($amount > $maxOrderTotal)
        	{
            	Mage::throwException(Mage::helper('vm')->__('Invalid amount for authorization.'));
			}
		}
		
        $orderId = $payment->getOrder()->getIncrementId();
        $billing = $this->getOrder()->getBillingAddress();
        try 
        {
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY);
            
			$payment->setAmount($amount);
            
			$xmlData = $this->generateXML($payment,$amount);
			
            $data = $this->postCurlRequest($xmlData);
			
        } 
        catch (Exception $e) 
        {
            $payment->setStatus(self::STATUS_ERROR);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
            $this->setStore($payment->getOrder()->getStoreId());
		    Mage::throwException("Unable to process: This could be due to an invalid gateway url " . $e->getMessage());
        }
		
		try 
        {
			$xmlResponse = new SimpleXmlElement($data); 
			
			$isApproval = $xmlResponse->ssl_result_message == self::STATUS_APPROVAL;
			$isApproved = $xmlResponse->ssl_result_message == self::STATUS_APPROVED;
			
		}
		catch (Exception $e) 
		{
			Mage::throwException("Error Code: " . $xmlResponse->errorCode . "\n". $e->getMessage());
		}
		
        if ($isApproved OR $isApproval) 
        {
            $this->setStore($payment->getOrder()->getStoreId());
            $payment->setStatus(self::STATUS_APPROVED);
            $payment->setCcApproval($xmlResponse->ssl_approval_code);
            $payment->setAmount($xmlResponse->ssl_amount);
            $payment->setIsTransactionClosed(0);
            $payment->setCcTransId($xmlResponse->ssl_txn_id);
            $payment->setCcAvsStatus($xmlResponse->ssl_avs_response);
            $payment->setLastTransId($xmlResponse->ssl_txn_id);
            $payment->setTransactionId($xmlResponse->ssl_txn_id);
        } 
        else 
        {
            $this->setStore($payment->getOrder()->getStoreId());
            $payment->setStatus(self::STATUS_ERROR);
			$response_code = $xmlResponse->errorCode; 
			//$response_code = trim($response_code);
			if(empty($response_code))
				$response_code = "6002";
			Mage::throwException("Credit card was not approved, please try a different credit card. \n" . "Response Code: " . $response_code);
			
			
        }
        return $this;
    }
    
	public function capture(Varien_Object $payment, $amount)
    {
		error_log("capture");
		try 
        {
			
			$minOrderTotal = $this->getConfigData('min_order_total');
			$maxOrderTotal = $this->getConfigData('max_order_total');
			
			if(!empty($minOrderTotal))
			{
				if ($amount < $minOrderTotal)
				{
					Mage::throwException(Mage::helper('vm')->__('Invalid amount for authorization.'));
				}
			}
			if(!empty($maxOrderTotal))
			{
				if ($amount > $maxOrderTotal)
				{
					Mage::throwException(Mage::helper('vm')->__('Invalid amount for authorization.'));
				}
			}
			
			if ($payment->getCcTransId()) 
			{
				$payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
			} 
			else 
			{
				$payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
			}
			
			$payment->setAmount($amount);
			$orderId = $payment->getOrder()->getIncrementId();
			$billing = $this->getOrder()->getBillingAddress();
			$xmlData = $this->generateXML($payment,$amount); 
			Mage::log("xmlData: " . $xmlData);
			$data = $this->postCurlRequest($xmlData);
			
        } 
        catch (Exception $e) 
        {
			
            $payment->setStatus(self::STATUS_ERROR);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
            $this->setStore($payment->getOrder()->getStoreId());
            Mage::throwException("Unable to process: This could be due to an invalid gateway url " . $e->getMessage());
			
        }
        
        $xmlResponse = new SimpleXmlElement($data); 
		
		
		$isApproval = $xmlResponse->ssl_result_message == self::STATUS_APPROVAL;
        $isApproved = $xmlResponse->ssl_result_message == self::STATUS_APPROVED;
        if ($isApproved OR $isApproval) 
        {
            $this->setStore($payment->getOrder()->getStoreId());
            $payment->setStatus(self::STATUS_APPROVED);
            $payment->setCcApproval($xmlResponse->ssl_approval_code);
            $payment->setAmount($xmlResponse->ssl_amount);
            $payment->setIsTransactionClosed(0);
            $payment->setCcTransId($xmlResponse->ssl_txn_id);
            $payment->setCcAvsStatus($xmlResponse->ssl_avs_response);
            $payment->setLastTransId($xmlResponse->ssl_txn_id);
            if (!$payment->getParentTransactionId() || $xmlResponse->ssl_txn_id != $payment->getParentTransactionId()) {
                $payment->setTransactionId($xmlResponse->ssl_txn_id);
            }
        } 
        else 
        {
            $this->setStore($payment->getOrder()->getStoreId());
			
			$payment->setStatus(self::STATUS_ERROR);
			$response_code = $xmlResponse->errorCode . " " . $xmlResponse->ssl_approval_code;
			$response_code = trim($response_code);
			if(empty($response_code))
				$response_code = "6002";
			Mage::throwException("Credit card was not approved, please try a different credit card. \n" . "Response Code: " . $response_code);
        }
        return $this;
    }
    
    public function generateXML(Varien_Object $payment,$amount)
    {
		//$transType = $payment->getAnetTransType(); 
        $orderId = $payment->getOrder()->getIncrementId();
        $billing = $this->getOrder()->getBillingAddress();
		$shipping = $this->getOrder()->getShippingAddress();
        $info = $this->getInfoInstance();
        
		
		try
		{
			$doc = new DOMDocument();
			$doc->formatOutput = false;
			
			$r = $doc->createElement( "txn" );
			$doc->appendChild( $r );
		  
			$name = $doc->createElement( "ssl_merchant_ID" );
			$name->appendChild(  $doc->createTextNode($info->decrypt($this->getConfigData('login'))));
			$r->appendChild( $name );
		
			$name1 = $doc->createElement( "ssl_user_id" );
			$name1->appendChild($doc->createTextNode($info->decrypt($this->getConfigData('userlogin'))));
			$r->appendChild( $name1 );   
		  
			$name2 = $doc->createElement( "ssl_pin" );
			$name2->appendChild($doc->createTextNode($info->decrypt($this->getConfigData('password'))));
			$r->appendChild( $name2 );
				
			$name21 = $doc->createElement( "ssl_test_mode" );
			$name21->appendChild($doc->createTextNode($this->getConfigData('test') ? 'TRUE' : 'FALSE'));
			$r->appendChild( $name21 );
		  
			$name22 = $doc->createElement( "ssl_transaction_type" );
			$name22->appendChild($doc->createTextNode($payment->getAnetTransType()));
			$r->appendChild( $name22 );
			
			
			$ssl_description = $doc->createElement( "ssl_description" );
			$ssl_description->appendChild($doc->createTextNode("ecommerce"));
			$r->appendChild( $ssl_description );
			
			
			$ssl_inv_num = $doc->createElement( "ssl_invoice_number" );
			//$ssl_inv_num->appendChild($doc->createTextNode($payment->getOrder()->getIncrementId() ));
			$ssl_inv_num->appendChild($doc->createTextNode($orderId));
			$r->appendChild( $ssl_inv_num );
			
			
			switch ($payment->getAnetTransType()) 
			{
				case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
					$approvalCode = $doc->createElement( "ssl_approval_code" );
					$approvalCode->appendChild($doc->createTextNode($payment->getCcApproval()));
					$r->appendChild($approvalCode);
		
					$txnId = $doc->createElement( "ssl_txn_id" );
					$txnId->appendChild($doc->createTextNode($payment->getCcTransId()));
					$r->appendChild( $txnId);
					break;
				case self::REQUEST_TYPE_CREDIT:
					$txnId = $doc->createElement( "ssl_txn_id" );
					$txnId->appendChild($doc->createTextNode($payment->getCcTransId()));
					$r->appendChild( $txnId);
					break;

			}
			
			$name3 = $doc->createElement( "ssl_card_number" );
			$name3->appendChild($doc->createTextNode( $payment->getCcNumber()));
			$r->appendChild( $name3 );
			  
			$name4 = $doc->createElement( "ssl_exp_date" );
			$name4->appendChild($doc->createTextNode(sprintf('%02d',$payment->getCcExpMonth()).substr($payment->getCcExpYear(),2,2)));
			$r->appendChild( $name4 );

			if($this->getConfigData('useccv'))
			{
				switch ($payment->getAnetTransType())
				{ 
					case self::REQUEST_TYPE_AUTH_ONLY:
					case self::REQUEST_TYPE_AUTH_CAPTURE:  
						$name6 = $doc->createElement( "ssl_cvv2cvc2_indicator" );
						$name6->appendChild($doc->createTextNode( "1"));
						$r->appendChild( $name6 );
			
						$name7 = $doc->createElement( "ssl_cvv2cvc2");
						$name7->appendChild($doc->createTextNode( $info->getCcCid()));   //$info->setCcType($data->getCcType())  setCcCid($data->getCcCid())
						$r->appendChild( $name7 );
						
						break;
				}
			}

			
			
			$tax_charge = $this->getOrder()->getTaxAmount();
			if(empty($tax_charge))
				$tax_charge = 0.00;
				
			$salestax = $doc->createElement( "ssl_salestax" );
			$salestax->appendChild($doc->createTextNode($tax_charge));
			$r->appendChild( $salestax );

			
			$name5 = $doc->createElement( "ssl_amount" );
			$name5->appendChild($doc->createTextNode($amount));
			$r->appendChild( $name5 );

			$special_char = array("&", "<", ">");
			
			$company_name = str_replace($special_char, " ", $billing->getCompany());
			$fname = str_replace($special_char, " ", $billing->getFirstname());
			$lname = str_replace($special_char, " ", $billing->getLastname());
			if(empty($company_name))		
				$company_name =  Mage::helper('core')->removeAccents($fname). ' '.  Mage::helper('core')->removeAccents($lname);	 
			
			$company_name = substr($company_name, 0, 50);
			$fname = substr($fname, 0, 20);
			$lname = substr($lname, 0, 30);
			
			$compname = $doc->createElement( "ssl_company" );
			
			$compname->appendChild($doc->createTextNode($company_name));
			$r->appendChild( $compname );
			
			$cust_id = $this->getOrder()->getCustomerId();
			if($cust_id == '' || empty($cust_id))
			{
				$cust_id = "NOT-REGISTERED";
			}
			$customer_code = $doc->createElement( "ssl_customer_code" );
			$customer_code->appendChild($doc->createTextNode($cust_id));
			$r->appendChild( $customer_code );
			
			
			
			$name8 = $doc->createElement( "ssl_first_name" );
			$name8->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($fname)));
			$r->appendChild( $name8 );
			
			
			$name81 = $doc->createElement( "ssl_last_name" );
			$name81->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($lname)));
			$r->appendChild( $name81 );
		
			$address1 = str_replace($special_char, " ", $billing->getStreet(1));
			$address2 = str_replace($special_char, " ", $billing->getStreet(2));
			$name9 = $doc->createElement( "ssl_avs_address" );
			$ssl_avs_address = Mage::helper('core')->removeAccents($address1);
			$ssl_avs_address_new = $ssl_avs_address;
			$ssl_avs_address_remain = "";
				
			if(strlen($ssl_avs_address)>30)
			{
				//Mage::throwException("Address1 cannot be more than 30 characters");
				$ssl_avs_address_new = "";
				$ssl_avs_address_remain = "";
				
				$new_address = wordwrap($ssl_avs_address, 30, "^");
				$addr_pieces = explode("^", $new_address);
				$ssl_avs_address_new = $addr_pieces[0];
				$ssl_avs_address_remain = $addr_pieces[1];
			}
			$name9->appendChild($doc->createTextNode($ssl_avs_address_new));
			$r->appendChild( $name9 );
			
			$name9_address2 = $doc->createElement( "ssl_address2" );
			$ssl_address2 = Mage::helper('core')->removeAccents($address2);
			
			$ssl_address2 = $ssl_avs_address_remain . " ". $ssl_address2;
			$ssl_address2 = trim($ssl_address2);
			
			if(strlen($ssl_address2)>30)		
			{
				$ssl_address2 = substr($ssl_address2, 0, 30);
			}
				
			$name9_address2->appendChild($doc->createTextNode($ssl_address2));
			$r->appendChild( $name9_address2 );
		  
			$bill_city = str_replace($special_char, " ", $billing->getCity());
			$bill_city = substr($bill_city, 0, 30);
			$name10 = $doc->createElement( "ssl_city" );
			$name10->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($bill_city)));
			$r->appendChild( $name10 );
		  
			$name11 = $doc->createElement( "ssl_state" );
			$bill_state_val = $billing->getRegionCode();
			$name11->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($bill_state_val)));
			$r->appendChild( $name11 );
		  
			$name12 = $doc->createElement( "ssl_avs_zip" );
			$name12->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents(substr($billing->getpostcode(), 0, 9))));
			$r->appendChild( $name12 );  
				 
			$name13 = $doc->createElement( "ssl_country" );
			$name13->appendChild($doc->createTextNode($billing->getCountry()));
			$r->appendChild( $name13 );
			
			$bill_phone = $doc->createElement( "ssl_phone" );
			$bill_phone->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents(substr($billing->getTelephone(), 0, 20))));
			$r->appendChild( $bill_phone );		
			
		 
			$name23 = $doc->createElement( "ssl_email" );
			$name23->appendChild($doc->createTextNode($payment->getOrder()->getCustomerEmail()));
			$r->appendChild( $name23 );
			
			
			if($shipping!="")
			{
				$ship_fname = $doc->createElement( "ssl_ship_to_first_name" );
				$ship_fname->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($fname)));
				$r->appendChild( $ship_fname );
				
				$ship_lname = $doc->createElement( "ssl_ship_to_last_name" );
				$ship_lname->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($lname)));
				$r->appendChild( $ship_lname );
				
				$ship_addr1 = $doc->createElement( "ssl_ship_to_address1" );
				$ssl_ship_address = Mage::helper('core')->removeAccents($address1);
				$ssl_ship_address_new = $ssl_ship_address;
				$ssl_ship_address_remain = "";
					
				if(strlen($ssl_ship_address)>30)
				{
					$ssl_ship_address_new = "";
					$ssl_ship_address_remain = "";
					
					$new_address = wordwrap($ssl_ship_address, 30, "^");
					$addr_pieces = explode("^", $new_address);
					$ssl_ship_address_new = $addr_pieces[0];
					$ssl_ship_address_remain = $addr_pieces[1];
				}
				
				$ship_addr1->appendChild($doc->createTextNode($ssl_ship_address_new));
				$r->appendChild( $ship_addr1 );
				
				$ship_addr2 = $doc->createElement( "ssl_ship_to_address2" );
				
				$ssl_ship_address2 = Mage::helper('core')->removeAccents($address2);
				
				$ssl_ship_address2 = $ssl_ship_address_remain . " ". $ssl_ship_address2;
				$ssl_ship_address2 = trim($ssl_ship_address2);
				
				if(strlen($ssl_ship_address2)>30)		
				{
					$ssl_ship_address2 = substr($ssl_ship_address2, 0, 30);
				}
				$ship_addr2->appendChild($doc->createTextNode($ssl_ship_address2));
				$r->appendChild( $ship_addr2 );
				
				$ship_city = $doc->createElement( "ssl_ship_to_city" );
				$ship_city->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($bill_city)));
				$r->appendChild( $ship_city );
				
				$ship_state = $doc->createElement( "ssl_ship_to_state" );
				$ship_state_val = $shipping->getRegionCode();
				
				$ship_state->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($ship_state_val)));
				$r->appendChild( $ship_state );
				
				$ship_zip = $doc->createElement( "ssl_ship_to_zip" );
				$ship_zip->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents(substr($shipping->getpostcode(), 0, 9))));
				$r->appendChild( $ship_zip );
				
				$ship_country = $doc->createElement( "ssl_ship_to_country" );
				$ship_country->appendChild($doc->createTextNode(substr($shipping->getCountry(), 0, 3)));
				$r->appendChild( $ship_country );
			}
			
			//$ship_phone = $doc->createElement( "ssl_ship_to_phone" );
			//$ship_phone->appendChild($doc->createTextNode(Mage::helper('core')->removeAccents($shipping->getTelephone())));
			//$r->appendChild( $ship_phone );		
			
			
			
			$xml_string =  $doc->saveXML();
			$xml_string = substr($xml_string,22,strlen($xml_string)-1);
			$xml_string = "xmldata=" .$xml_string;
			//$urlToPost = $urlToPost. "?xmldata=" .$xml_string;
			return $xml_string;
		}
		catch (Exception $e) 
		{
			Mage::log("General XML Exception: " . $e->getMessage());
		}
    }
     
    public function getOrder()
    {
		if (!$this->_order) {
			$this->_order = $this->getInfoInstance()->getOrder();
		}
		return $this->_order;
    }
    
    
    public function refund(Varien_Object $payment, $amount)
    {
        if ($payment->getRefundTransactionId() && $amount > 0)
        { 
            $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
            $payment->setAmount($amount);
            
            try 
            {
                $xmlData = $this->generateXML($payment,$amount);
                $data = $this->postCurlRequest($xmlData);
            } 
            catch (Exception $e) 
            {
                $payment->setStatus(self::STATUS_ERROR);
                $payment->setAmount($amount);
                $payment->setLastTransId($orderId);
                $this->setStore($payment->getOrder()->getStoreId());
                Mage::throwException($e->getMessage());
            }
            $xmlResponse = new SimpleXmlElement($data); 
			$isApproval = $xmlResponse->ssl_result_message == self::STATUS_APPROVAL;
			$isApproved = $xmlResponse->ssl_result_message == self::STATUS_APPROVED;
			if ($isApproved OR $isApproval) 
			{
				$payment->setCcApproval($xmlResponse->ssl_approval_code);
				$payment->setCcTransId($xmlResponse->ssl_txn_id);
				if (!$payment->getParentTransactionId() || $xmlResponse->ssl_txn_id != $payment->getParentTransactionId()) {
					$payment->setTransactionId($xmlResponse->ssl_txn_id);
				}
				$payment->setStatus(self::STATUS_APPROVED);
                return $this;
            } 
            else 
            {
                $errorCode = "";
				$errorMsg = "";
				if($xmlResponse->errorCode!="")
					$errorCode = "Error Code: <" . $xmlResponse->errorCode . "> ";
				if($xmlResponse->errorMessage!="")
					$errorMsg = $xmlResponse->errorMessage;
				else
					$errorMsg = "Refund not approved. There was an error in refunding the payment.";
					
                Mage::throwException($errorCode . $errorMsg);
            }
        }
        Mage::throwException("Unable to process refund for this payment online.");
    }
    
    protected function _postRequest($request)
    {
        $client = new Varien_Http_Client();

        $uri = $this->getConfigData('cgi_url');
        $client->setUri($uri ? $uri : self::CGI_URL);
        $client->setConfig(array(
            'maxredirects'=>0,
            'timeout'=>180,
        ));
        $client->setParameterPost($request);
        $client->setMethod(Zend_Http_Client::POST);

        try 
        {
            $response = $client->request();
        }
        catch (Exception $e) 
        {
            Mage::throwException($this->_wrapGatewayError($e->getMessage()));
        }

        return $response;
    }
   
    protected function postCurlRequest($request)
    {
            $url = $this->getConfigData('cgi_url');
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($ch); 
            if (!$data) {
                throw new Exception(curl_error($ch));
            }
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode && substr($httpcode, 0, 2) != "20") 
            { 
                Mage::throwException("Returned HTTP CODE: " . $httpcode . " for this URL: " . $urlToPost);
            }
            curl_close($ch);
            return $data;
     }
}

