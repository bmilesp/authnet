<?php
App::import('Lib', array('Cart.IDirectCheckoutProcessor', 'Cart.IRefundProcessor'));
/**
 * Authorize.net Payment Processor Component
 * Implements the ExpressCheckout interface to be used with the Cart plugin
 * Implements the Refund interface for the Cart plugin
 *
 */
class AuthnetProcessorComponent extends Object implements IDirectCheckoutProcessor, IRefundProcessor {

/**
 * Current controller
 *
 * @var Controller
 * @access public
 */
	public $Controller;
	
	
	
/**
 *  Used for authnet transactions and datasource
 */	
	public $TransactionModel = null;
	
/**
 * 
 * Modelname used for Authnet transactions
 * 
 * @var authnetModel
 * @access public
 */
	public $authnetModelName = 'Authnet.AuthnetTransaction';
	
/**
 * Initializes the component
 *
 * @return void
 * @access public
 */
	public function initialize(Controller $Controller) {
		$this->TransactionModel = ClassRegistry::init($this->authnetModelName);
		$this->Controller = $Controller;
	}

	
/**
  * Process to the payment. If an error occurred an exception is thrown
 *
 * @throws InvalidArgumentException When there are incorrect parameters to do the payment
 * @throws RuntimeException When an unexpected error occurs
 * @param string $cart Cart to checkout with exhaustive information (cf Cart::getExhaustiveCartInfo() return value)
 * @param array $options Options for the checkout. Passed values could be:
 * 	- Payment: Payment related data returned by the ecRetrieveInfo method
 * @return boolean mixed False on error, an array of payment information on success. This array must have the following keys:
 * @access public
 * @link http://developer.authorize.net/
 */
	public function dcProcessPayment($cart, $options = array()) {
		$_defaults = array();
		$options = array_merge($_defaults, $options);
		if($response = $this->TransactionModel->save($cart)){
			$result = array(
				'payment_reference' => $response['response']['transaction_id'],
				'payment_status' => $response['status'],
			);
		}else{
			return false;
		}

		// Then process recurring payments
		//TODO

		return $result;
	}
	
	public function afterOrder($order_id){
		
		if(!$this->TransactionModel->logModel->saveField('foreign_id', $order_id)){
			return false;
		}
		return true;
	}
	
	/**
 * Process to the refund of a given amount for the passed Order
 *
 * @throws InvalidArgumentException When the initial Order was not paid using Paypal
 * @throws RuntimeException When an unexpected error occurs
 * @param array $order Information about the Order (read from the database)
 * @param float $amount Amount of the refund
 * @param string $comment Comment related to the refund
 * @param string $reference Reference of the refund (can be empty)
 * @return mixed The refund transaction reference on success, false in case of problem.
 * @access public
 */
	public function refund($order, $amount, $comment, $reference) {
		//TODO
	}
	
	
	/**
	 * get's the serialized log record and converts it to an array. 
	 * pass in the field(s) = value(s) as search condition
	 */
	public function unserializeLog($fieldVals = array('field' => 'value')){
		if(!empty($this->TransactionModel->logModel)){
			if($rec = $this->TransactionModel->logModel->find('first',array(
				'conditions' => array($fieldVals),
			)))
			{
				$jsonObj = json_decode($rec[$this->TransactionModel->logModel->alias]['data']);
				return (array) $jsonObj->{$this->TransactionModel->alias};
			}
		}
		return false;
	}
	
}