<?php
App::import('Lib', array('Cart.IExpressCheckoutProcessor', 'Cart.IRefundProcessor'));
/**
 * Authorize.net Payment Processor Component
 * Implements the ExpressCheckout interface to be used with the Cart plugin
 * Implements the Refund interface for the Cart plugin
 *
 */
class AuthnetProcessorComponent extends Object implements IExpressCheckoutProcessor, IRefundProcessor {

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
	public $authnetModel = 'Authnet.AuthnetTransaction';

/**
 * Initializes the component
 *
 * @return void
 * @access public
 */
	public function initialize(Controller $Controller) {
		$this->TransactionModel = ClassRegistry::init($this->authnetModel);
		$this->Controller = $Controller;
	}

/**
 * Step 1 of the Express Checkout - SetExpressCheckout API call
 * Initializes the command on Payment provider side, and redirects to its website. If an error occurred an exception must be thrown
 *
 * @throws LogicException When the cart content is invalid
 * @throws InvalidArgumentException When there are missing parameters to do the checkout
 * @throws RuntimeException When an unexpected error occurs
 * @param string $cart Cart to checkout with exhaustive information (cf Cart::getExhaustiveCartInfo() return value)
 * @param array $options Options for the checkout. Passed values could be:
 * 	- cancelUrl: url the user must be redirected to in case of cancellation
 * @return void
 * @access public
 */
	public function ecInitAndRedirect($cart, $options = array()) {
	}

/**
 * Step 2 of the Express Checkout
 * Retrieve order information and user details from Payment provider website. If an error occurred an exception must be thrown
 * 
 * If the user cancelled or if a permission is missing, the user must be redirected to the url passed in $options['cancelUrl']
 * Otherwise information must be returned in an array.
 * 
 * @throws InvalidArgumentException When there are missing parameters to do the checkout
 * @throws RuntimeException When an unexpected error occurs
 * @param string $cart Cart to checkout with exhaustive information (cf Cart::getExhaustiveCartInfo() return value)
 * @param array $options Options for the checkout. Passed values could be:
 * 	- cancelUrl: url the user must be redirected to in case of cancellation
 * @return array Order Information, with some or all of the following keys:
 * 	- Payment: Payment related data. They will be transmitted "as is" to ecProcessPayment method.
 * 	- ShippingAddress: Shipping address if mentionned on Payment provider website 
 * @access public
 */
	public function ecRetrieveInfo($cart, $options = array()){
		
	}
	
/**
 * Step 3 of the Express Checkout
 * Process to the payment. If an error occurred an exception must be thrown
 * 
 * @throws LogicException When the cart content is invalid
 * @throws InvalidArgumentException When there are incorrect parameters to do the payment
 * @throws RuntimeException When an unexpected error occurs
 * @param string $cart Cart to checkout with exhaustive information (cf Cart::getExhaustiveCartInfo() return value)
 * @param array $options Options for the checkout. Passed values could be:
 * 	- Payment: Payment related data returned by the ecRetrieveInfo method.
 * @return mixed False on error, an array of payment information on success. This array must have the following keys:
 * 	- payment_status: status of the payment made (cf CartOrder->paymentTypes for a list of possible values)
 * 	- payment_reference: internal reference for the transaction
 * @access public
 */
	public function ecProcessPayment($cart, $options = array()){
		
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
		$success = false;

		if ($order['Order']['payment_type'] !== 'Paypal') {
			throw new InvalidArgumentException(__d('paypal', 'You cannot refund via Paypal an Order paid using another system.', true));
		}

		$data = array('PaypalRefund' => array(
			'TRANSACTIONID' => $order['Order']['payment_reference'],
			'NOTE' => $comment
		));
		if ($order['Order']['total_refunds'] == 0 && $amount == $order['Order']['total']) {
			$data['PaypalRefund']['REFUNDTYPE'] = 'Full';
		} else {
			$data['PaypalRefund']['REFUNDTYPE'] = 'Partial';
			$data['PaypalRefund']['AMT'] = $amount;
		}

		$ppResponse = $this->Paypal->refundTransaction($data);
		$success = $ppResponse['REFUNDTRANSACTIONID'];

		return $success;
	}
}