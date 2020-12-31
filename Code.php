<?php
	/*
		$Params[
			'// limit'=>100, // Limit results,
			'start'=>strtotime('first day of this month') // Start looking from here,
			'end'=>strtotime('last day of this month') // End looking here,
		]
	*/

	public function GetTransactions($Params){
		$Options = [];
		/* Config Options */
		if(isset($Params['limit'])){
			$Options['limit'] = $Params['limit'];
		}

		if(isset($Params['start'])){
			$Options['created'] = ['gte'=>$Params['start'], 'lte'=>$Params['end']];
		}

		if(isset($Params['starting_after'])){
			$Options['starting_after'] = $Params['starting_after'];
		}

		/* Call Stripe */
		$StripeQuery = self::$Stripe->charges->all(
			$Options,
			['stripe_account'=>$GLOBALS['USER.COMPANY.STRIPE_TOKEN']]
		);

		/* Return Data */
		if(empty($StripeQuery['data']) && isset($Params['transactions'])){
			return $Params['transactions'];
		}

		/* Get more data or limit */
		if(isset($StripeQuery['data']) && count($StripeQuery['data'])){
			foreach($StripeQuery['data'] as $StripeTransaction){
				$Params['transactions'][] = $StripeTransaction;
			}

			if(isset($Params['limit']) && count($Params['transactions']) <= $Params['limit']){
				return $Params['transactions'];
			}

			$Params['starting_after'] = $StripeQuery['data'][count($StripeQuery['data']) -1 ]['id'];
			return self::GetTransactions($Params);
		}
		/* Return Nothing */
		return [];
	}
