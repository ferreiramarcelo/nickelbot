<?PHP

	class BittrexAdapter extends CryptoBase implements CryptoExchange {

		public function __construct($Exch) {
			$this->exch = $Exch;
		}

		private function get_market_symbol( $market ) {
			$msmn = explode( "-", $market );
			return $msmn[1] . "-" . $msmn[0];
		}

		private function unget_market_symbol( $market ) {
			return $this->get_market_symbol( $market );
		}

		public function get_info() {
			return [];
		}

		public function withdraw( $account = "exchange", $currency = "BTC", $address = "1fsdaa...dsadf", $amount = 1 ) {
			return [];
		}

		public function get_currency_summary( $currency = "BTC" ) {
			return [];
		}
		
		public function get_currency_summaries( $currency = "BTC" ) {
			return [];
		}
		
		public function get_order( $orderid = "1" ) {
			return [];
		}

		public function cancel($orderid="1", $opts = array() ) {
			return $this->exch->market_cancel( array("uuid" => $orderid ) );
		}

		public function get_deposits_withdrawals() {
			$results = [];

			//_____Withdrawals:
			$transactions = $this->exch->account_getwithdrawalhistory( array() );

			foreach( $transactions['result'] as $transaction ) {
				$transaction['exchange'] = "Bittrex";
				$transaction['type'] = 'WITHDRAWAL';
				array_push( $results, $transaction );
			}

			//_____Deposits:
			$transactions = $this->exch->account_getdeposithistory( array() );
			foreach( $transactions['result'] as $transaction ) {
				$transaction['exchange'] = "Bittrex";
				$transaction['type'] = 'DEPOSIT';
				array_push( $results, $transaction );
			}

			$return = [];
			foreach( $results as $result ) {

				if( isset( $result['PaymentUuid'] ) ) {
					$result['id'] = $result['PaymentUuid'];
				} else if ( isset( $result['Id'] ) ) {
					$result['id'] = $result['Id'];
				} else {
					$result['id'] = null;
				}

				$result['currency'] = $result['Currency'];
				$result['method'] = $result['Currency'];
				$result['amount'] = $result['Amount'];
				$result['description'] = $result['Currency'];
				$result['status'] = isset( $result['PendingPayment'] ) ? $result['PendingPayment'] : null;
				$result['fee'] = isset( $result['TxCost'] ) ? $result['TxCost'] : null;
				$result['address'] = isset( $result['CryptoAddress'] ) ? $result['CryptoAddress'] : null;
				$result['fee'] = isset( $result['TxCost'] ) ? $result['TxCost'] : null;

				if( isset( $result['LastUpdated'] ) ) {
					$result['timestamp'] = $result['LastUpdated'];
				} else if ( isset( $result['Opened'] ) ) {
					$result['timestamp'] = $result['Opened'];
				} else {
					$result['timestamp'] = null;
				}

				$result['confirmations'] = isset( $result['Confirmations'] ) ? $result['Confirmations'] : null;

				unset( $result['PaymentUuid'] );
				unset( $result['Currency'] );
				unset( $result['Amount'] );
				unset( $result['Address'] );
				unset( $result['Opened'] );
				unset( $result['Authorized'] );
				unset( $result['PendingPayment'] );
				unset( $result['TxCost'] );
				unset( $result['TxId'] );
				unset( $result['Canceled'] );
				unset( $result['InvalidAddress'] );
				unset( $result['Id'] );
				unset( $result['Confirmations'] );
				unset( $result['LastUpdated'] );
				unset( $result['CryptoAddress'] );

				array_push( $return, $result );
			}

			return $return;
		}

		public function get_deposits() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		public function get_deposit( $deposit_id="1", $opts = array() ) {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		public function get_withdrawals() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		public function cancel_all() {
			$result = $this->get_open_orders();
			$response = array();

			foreach( $result as $order ) {
				array_push($response,$this->cancel($order['id']));
			}

			if( isset( $result['success'] ) )
				return array( 'success' => true, 'error' => false, 'message' => $response );
			else
				return array( 'success' => false, 'error' => true, 'message' => $result );
		}

		public function buy( $pair="LTC-BTC", $amount=0, $price=0, $type="LIMIT", $opts=array() ) {
			$buy = $this->exch->market_buylimit( array( 'market' => $this->unget_market_symbol( $pair ), 'quantity' => $amount, 'rate' => $price ) );
			if( $buy['success'] == 1 )
				unset( $buy['message'] );
			return $buy;
		}
		
		public function sell( $pair="LTC-BTC", $amount=0, $price=0, $type="LIMIT", $opts=array() ) {
			$sell = $this->exch->market_selllimit( array( 'market' => $this->unget_market_symbol( $pair ), 'quantity' => $amount, 'rate' => $price ) );
			if( $sell['success'] == 1 )
				unset( $sell['message'] );
			return $sell;

		}

		public function get_open_orders() {
			if( isset( $this->open_orders ) )
				return $this->open_orders;
			$open_orders = $this->exch->market_getopenorders();
			$this->open_orders = [];
			foreach( $open_orders['result'] as $open_order ) {
				$open_order['id'] = $open_order['OrderUuid'];
				$open_order['market'] = $open_order['Exchange'];
				$open_order['exchange'] = "bittrex";
				$open_order['price'] = $open_order['Limit'];
				$open_order['timestamp_created'] = $open_order['Opened'];
				$open_order['avg_execution_price'] = $open_order['Price'];
				$open_order['side'] = $open_order['OrderType'];
				$open_order['type'] = $open_order['OrderType'];
				$open_order['is_live'] = true;
				$open_order['is_cancelled'] = false;
				$open_order['is_hidden'] = false;
				$open_order['was_forced'] = false;
				$open_order['original_amount'] = null;
				$open_order['remaining_amount'] = null;
				$open_order['executed_amount'] = null;
				$open_order['amount'] = $open_order['Quantity'];

				unset( $open_order['Uuid'] );
				unset( $open_order['OrderUuid'] );
				unset( $open_order['Exchange'] );
				unset( $open_order['OrderType'] );
				unset( $open_order['Quantity'] );
				unset( $open_order['QuantityRemaining'] );
				unset( $open_order['Limit'] );
				unset( $open_order['CommissionPaid'] );
				unset( $open_order['Price'] );
				unset( $open_order['PricePerUnit'] );
				unset( $open_order['Opened'] );
				unset( $open_order['Closed'] );
				unset( $open_order['CancelInitiated'] );
				unset( $open_order['ImmediateOrCancel'] );
				unset( $open_order['IsConditional'] );
				unset( $open_order['Condition'] );
				unset( $open_order['ConditionTarget'] );

				array_push( $this->open_orders, $open_order );
			}
			return $this->open_orders;
		}

		public function get_completed_orders( $market="BTC-USD", $limit = 100 ) {
			if( isset( $this->completed_orders ) )
				return $this->completed_orders;
			$this->completed_orders = [];
			foreach( $this->get_markets() as $market ) {
				$completed_orders = $this->exch->account_getorderhistory( array( 'market' => $market, 'count' => $limit ) );
				foreach( $completed_orders['result'] as $completed_order ) {
					$completed_order['exchange'] = "bittrex";
					$completed_order['market'] = $market;

				    $completed_order['price'] = null;
					$completed_order['amount'] = null;
					$completed_order['timestamp'] = null;
					$completed_order['type'] = null;
					$completed_order['fee_currency'] = null;
					$completed_order['fee_amount'] = null;
					$completed_order['tid'] = null;
					$completed_order['order_id'] = null;
					$completed_order['id'] = null;
					$completed_order['fee'] = null;
					$completed_order['total'] = null;

					unset( $completed_order['OrderUuid'] );
					unset( $completed_order['Exchange'] );
					unset( $completed_order['TimeStamp'] );
					unset( $completed_order['OrderType'] );
					unset( $completed_order['Limit'] );
					unset( $completed_order['Quantity'] );
					unset( $completed_order['QuantityRemaining'] );
					unset( $completed_order['Commission'] );
					unset( $completed_order['Price'] );
					unset( $completed_order['PricePerUnit'] );
					unset( $completed_order['IsConditional'] );
					unset( $completed_order['Condition'] );
					unset( $completed_order['ConditionTarget'] );
					unset( $completed_order['ImmediateOrCancel'] );
					unset( $completed_order['Closed'] );

					array_push( $this->completed_orders, $completed_order );
				}
			}
			return $this->completed_orders;
		}

		public function get_markets() {
			$markets = $this->exch->getmarketsummaries();
			$response = [];
			foreach( $markets['result'] as $market ) {
				array_push( $response, $this->get_market_symbol( $market['MarketName'] ) );
			}
			return $response;
		}

		public function get_currencies() {
			$currencies = $this->exch->getcurrencies();
			$response = [];
			foreach( $currencies['result'] as $currency ) {	
				array_push( $response, $currency['Currency'] );
			}
			return $response;
		}

		public function deposit_address( $currency = "BTC" ){
			if( ! isset( $this->cnt ) )
				$this->cnt = 0;
			if( $this->cnt > 5 )
				return false;
			$address = $this->exch->account_getdepositaddress( array( 'currency' => $currency ) );
			if( $address['message'] == 'CURRENCY_OFFLINE' )
				return FALSE;
			if( $address['success'] == 1 ) {
				if( $address['result']['Address'] == "" ) {
					sleep( 5 );
					$this->cnt++;
					return $this->deposit_address( $currency );
				}
				return $address['result'];
			}
			if( $address['message'] == 'ADDRESS_GENERATING' ) {
				sleep( 5 );
				$this->cnt++;
				return $this->deposit_address( $currency );
			}
			return false;
		}
		
		public function deposit_addresses(){
			$currencies = $this->get_currencies();
			$addresses = [];
			foreach( $currencies as $currency ) {
				$address = $this->deposit_address( $currency );
				if( $address ) {
					$address['wallet_type'] = "exchange";
					$address['currency'] = $address['Currency'];
					$address['address'] = $address['Address'];

					unset( $address['Currency'] );
					unset( $address['Address'] );

					array_push( $addresses, $address );
				}
			}
			return $addresses;
		}

		public function get_balances() {
			/*if( isset( $this->balances ) )//internal cache
				return $this->balances;*/

			$balances = $this->exch->account_getbalances();
			if( $balances['success'] == 1 )
				$balances = $balances['result'];
			else
				return [];

			$response = [];
			foreach( $balances as $balance ) {
				$balance['type'] = "exchange";
				$balance['currency'] = $balance['Currency'];
				$balance['total'] = $balance['Balance'];
				$balance['available'] = $balance['Available'];
				$balance['pending'] = $balance['Pending'];
				$balance['reserved'] = $balance['total'] - $balance['available'];
				$balance['btc_value'] = 0;

				unset( $balance['Currency'] );
				unset( $balance['Balance'] );
				unset( $balance['Available'] );
				unset( $balance['Pending'] );
				unset( $balance['CryptoAddress'] );

				array_push( $response, $balance );
			}

			$this->balances = $response;
			return $this->balances;
		}

		public function get_balance( $currency="BTC" ) {
			$balances = $this->get_balances();
			foreach( $balances as $balance )
				if( $balance['currency'] == $currency )
					return $balance;
		}

		public function get_market_summary( $market="LTC-BTC" ) {
			$market_summary = $this->exch->getmarketsummary( array('market' => $this->unget_market_symbol( $market ) ) );
			$market_summary = $market_summary['result'][0];
			return $this->standardize_market_summary( $market_summary );
		}

		public function get_market_summaries() {
			if( isset( $this->market_summaries ) ) //cache
				return $this->market_summaries;
			
			$market_summaries = $this->exch->getmarketsummaries();
			$market_summaries = $market_summaries['result'];
			$this->market_summaries = [];
			foreach( $market_summaries as $market_summary ) {
				array_push( $this->market_summaries, $this->standardize_market_summary( $market_summary ) );
			}
			return $this->market_summaries;
		}

		//just so I don't have to do this twice in get_market_summary and get_market_summaries...
		private function standardize_market_summary( $market_summary ) {
			$market_summary['exchange'] = "bittrex";
			$market_summary['market'] = $this->get_market_symbol( $market_summary['MarketName'] );
			$market_summary['high'] = $market_summary['High'];
			$market_summary['low'] = $market_summary['Low'];
			$market_summary['base_volume'] = $market_summary['Volume'];
			$market_summary['quote_volume'] = $market_summary['BaseVolume'];
			$market_summary['btc_volume'] = null;
			$market_summary['last_price'] = $market_summary['Last'];
			$market_summary['timestamp'] = $market_summary['TimeStamp'];
			$market_summary['bid'] = is_null( $market_summary['Bid'] ) ? 0 : $market_summary['Bid'];
			$market_summary['ask'] = is_null( $market_summary['Ask'] ) ? 0 : $market_summary['Ask'];
			$market_summary['display_name'] = $market_summary['market'];
			$market_summary['result'] = true;
			$market_summary['created'] = $market_summary['Created'];
			$market_summary['open_buy_orders'] = $market_summary['OpenBuyOrders'];
			$market_summary['open_sell_orders'] = $market_summary['OpenSellOrders'];
			$market_summary['percent_change'] = null;
			$market_summary['frozen'] = null;
			$market_summary['verified_only'] = null;
			$market_summary['expiration'] = null;
			$market_summary['initial_margin'] = null;
			$market_summary['maximum_order_size'] = null;
			$market_summary['mid'] = ( $market_summary['bid'] + $market_summary['ask'] ) / 2;
			$market_summary['minimum_margin'] = null;
			$market_summary['minimum_order_size_quote'] = 0.00050000;
			$market_summary['minimum_order_size_base'] = null;
			$market_summary['price_precision'] = 8;
			$market_summary['vwap'] = null;
			$market_summary['market_id'] = null;

			unset( $market_summary['OpenBuyOrders'] );
			unset( $market_summary['OpenSellOrders'] );
			unset( $market_summary['MarketName'] );
			unset( $market_summary['High'] );
			unset( $market_summary['Low'] );
			unset( $market_summary['Volume'] );
			unset( $market_summary['Last'] );
			unset( $market_summary['BaseVolume'] );
			unset( $market_summary['TimeStamp'] );
			unset( $market_summary['Bid'] );
			unset( $market_summary['Ask'] );
			unset( $market_summary['Created'] );
			unset( $market_summary['PrevDay'] );

			return $market_summary;
		}

		public function get_trades( $market = 'BTC-USD', $opts = array( 'limit' => 10 ) ) {
			$trades = $this->exch->getmarkethistory( array( 'market' => $market, 'count' => $opts['limit'] ) );

			$results = [];
			foreach( $trades['result'] as $trade ) {
				array_push( $results, $trade );
			}

			return $results;
		}

		public function get_orderbook( $market = "BTC-USD", $depth = 10 ) {
			$orderbooks = $this->exch->getorderbook( array( 'market' => $market, 'type' => "both", 'depth' => $depth ) );
			$orderbooks = $orderbooks['result'];
			$n_orderbooks = [];
			$o_orderbooks = [];

			if( isset( $orderbooks['buy'] ) )
				foreach( $orderbooks['buy'] as $orderbook ) {
					array_push( $n_orderbooks, $orderbook );
				}

			if( isset( $orderbooks['sell'] ) )
				foreach( $orderbooks['sell'] as $orderbook ) {
					array_push( $n_orderbooks, $orderbook );
				}

			foreach( $n_orderbooks as $orderbook ) {
				$orderbook['market'] = $market;
				$orderbook['price'] = $orderbook['Rate'];
				$orderbook['amount'] = $orderbook['Quantity'];
				$orderbook['timestamp'] = null;
				$orderbook['exchange'] = null;
				$orderbook['type'] = null;

				unset( $orderbook['Quantity'] );
				unset( $orderbook['Rate'] );
				array_push( $o_orderbooks, $orderbook );
			}

			return $o_orderbooks;
		}

		//Return trollbox data from the exchange, otherwise get forum posts or twitter feed if must...
		public function get_trollbox() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		//Margin trading
		public function margin_history() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}
		public function margin_info() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}
		
		//lending:
		public function loan_offer() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}
		
		public function cancel_loan_offer() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}
		
		public function loan_offer_status() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		public function active_loan_offers() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		//borrowing:

		public function get_positions() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		public function claim_position() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		public function close_position() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		public function active_loan() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

		public function inactive_loan() {
			return array( 'ERROR' => 'METHOD_NOT_AVAILABLE' );
		}

	}

?>