<?php	
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("pmpro_orders_csv")))
	{
		die(__("You do not have permissions to perform this action.", "pmpro"));
	}	
	
	global $wpdb;	
	
	//get users	
	if(isset($_REQUEST['s']))
		$s = $_REQUEST['s'];
	else
		$s = "";
	
	if(isset($_REQUEST['l']))
		$l = $_REQUEST['l'];
	else
		$l = false;
		
	if(isset($_REQUEST['start-month']))
		$start_month = $_REQUEST['start-month'];
	else
		$start_month = "1";
		
	if(isset($_REQUEST['start-day']))
		$start_day = $_REQUEST['start-day'];
	else
		$start_day = "1";
		
	if(isset($_REQUEST['start-year']))
		$start_year = $_REQUEST['start-year'];
	else
		$start_year = date("Y");
		
	if(isset($_REQUEST['end-month']))
		$end_month = $_REQUEST['end-month'];
	else
		$end_month = date("n");
		
	if(isset($_REQUEST['end-day']))
		$end_day = $_REQUEST['end-day'];
	else
		$end_day = date("j");
		
	if(isset($_REQUEST['end-year']))
		$end_year = $_REQUEST['end-year'];
	else
		$end_year = date("Y");	
	
	if(isset($_REQUEST['predefined-date']))
		$predefined_date = $_REQUEST['predefined-date'];
	else
		$predefined_date = "This Month";		
			
	if(isset($_REQUEST['status']))
		$status = $_REQUEST['status'];
	else
		$status = "";
	
	if(isset($_REQUEST['filter']))
		$filter = sanitize_text_field($_REQUEST['filter']);
	else
		$filter = "all";	
	
	if(isset($_REQUEST['starting-order-id']))
		$starting_order_id = $_REQUEST['starting-order-id'];
	else
		$starting_order_id = "";	
        
	if(isset($_REQUEST['report-type']))
		$report_type = sanitize_text_field($_REQUEST['report-type']);
	else
		$report_type = "";	

	//some vars for the search
	if(!empty($_REQUEST['pn']))
		$pn = $_REQUEST['pn'];
	else
		$pn = 1;
	
	if(!empty($_REQUEST['limit']))
		$limit = $_REQUEST['limit'];
	else
		$limit = false;
		
	if($limit)
	{	
		$end = $pn * $limit;
		$start = $end - $limit;		
	}
	else
	{
		$end = NULL;
		$start = NULL;
	}
		
	//filters
	if($filter == "all" || !$filter)
			$condition = "1=1";
	elseif($filter == "within-a-date-range")
	{	
		$start_date = $start_year."-".$start_month."-".$start_day;
		$end_date = $end_year."-".$end_month."-".$end_day;
		
		//add times to dates
		$start_date =  $start_date . " 00:00:00";
		$end_date =  $end_date . " 23:59:59";
		
		$condition = "timestamp BETWEEN '".$start_date."' AND '".$end_date."'";
	}
	elseif($filter == "predefined-date-range")
	{	
		if($predefined_date == "Last Month")
		{
			$start_date = date("Y-m-d", strtotime("first day of last month", current_time("timestamp")));
			$end_date   = date("Y-m-d", strtotime("last day of last month", current_time("timestamp")));
		}
		elseif($predefined_date == "This Month")
		{
			$start_date = date("Y-m-d", strtotime("first day of this month", current_time("timestamp")));
			$end_date   = date("Y-m-d", strtotime("last day of this month", current_time("timestamp")));
		}
		elseif($predefined_date == "This Year")
		{
			$year = date('Y');
			$start_date = date("Y-m-d", strtotime("first day of January $year", current_time("timestamp")));
			$end_date   = date("Y-m-d", strtotime("last day of December $year", current_time("timestamp")));
		}
		
		elseif($predefined_date == "Last Year")
		{
			$year = date('Y') - 1;
			$start_date = date("Y-m-d", strtotime("first day of January $year", current_time("timestamp")));
			$end_date   = date("Y-m-d", strtotime("last day of December $year", current_time("timestamp")));
		}
	
		//add times to dates
		$start_date =  $start_date . " 00:00:00";
		$end_date =  $end_date . " 23:59:59";
	
		$condition = "timestamp BETWEEN '".$start_date."' AND '".$end_date."'";
	}			
	elseif($filter == "within-a-level")
	{
		$condition = "membership_id = $l";
	}			
	elseif($filter == "within-a-status")
	{
		$condition = "status = '$status' ";
	}		
	
        elseif($filter == "starting-from-order-id")
        {
                $condition = "o.id >= $starting_order_id AND o.status = 'success' ";
        }		
	//string search
	if($s)
	{
		$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS o.id FROM $wpdb->pmpro_membership_orders o LEFT JOIN $wpdb->users u ON o.user_id = u.ID LEFT JOIN $wpdb->pmpro_membership_levels l ON o.membership_id = l.id ";
		
		$join_with_usermeta = apply_filters("pmpro_orders_search_usermeta", false);
		if($join_with_usermeta)
			$sqlQuery .= "LEFT JOIN $wpdb->usermeta um ON o.user_id = um.user_id ";
		
		$sqlQuery .= "WHERE (1=2 ";
		
		$fields = array("o.id", "o.code", "o.billing_name", "o.billing_street", "o.billing_city", "o.billing_state", "o.billing_zip", "o.billing_phone", "o.payment_type", "o.cardtype", "o.accountnumber", "o.status", "o.gateway", "o.gateway_environment", "o.payment_transaction_id", "o.subscription_transaction_id", "u.user_login", "u.user_email", "u.display_name", "l.name");
		
		if($join_with_usermeta)
			$fields[] = "um.meta_value";
		
		$fields = apply_filters("pmpro_orders_search_fields", $fields);
		
		foreach($fields as $field)
			$sqlQuery .= " OR " . $field . " LIKE '%" . esc_sql($s) . "%' ";
		$sqlQuery .= ") ";
		
		$sqlQuery .= "AND " . $condition . " ";
		
		$sqlQuery .= "GROUP BY o.id ORDER BY o.id DESC, o.timestamp DESC ";
	}
	else
	{
		$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS id FROM $wpdb->pmpro_membership_orders o WHERE ".$condition." ORDER BY o.id DESC, o.timestamp DESC ";
	}
	
	if(!empty($start) && !empty($limit))
		$sqlQuery .= "LIMIT $start, $limit";
		
	$order_ids = $wpdb->get_col($sqlQuery);	
		
	//begin output
	header("Content-type: text/csv");	
	
        if($report_type == "peachtree-customers") {
            // This is the peachtree-customers export

            header("Content-Disposition: attachment; filename=peachtree-customers.csv");		
	
	    $csvoutput = "customer-id,customer-name,bill-to-address-line-one,bill-to-city,bill-to-state,bill-to-zip,bill-to-country,gl-sales";
	
	    //these are the meta_keys for the fields (arrays are object, property. so e.g. $theuser->ID)
	    $default_columns = array(
		array("user", "user_login"),
		array("order", "billing", "name"),
		array("order", "billing", "street"),
		array("order", "billing", "city"),
		array("order", "billing", "state"),
		array("order", "billing", "zip"),
		array("order", "billing", "country"),
	    );
        } elseif($report_type == "peachtree-orders") {
            // The peachtree-orders export

            header("Content-Disposition: attachment; filename=peachtree-orders.csv");		
	    $csvoutput = "customer-id,customer-name,reference,cash-amount,amount,deposit-ticket-id,date,number-of-distributions,invoice-paid,quantity,item-id,description,gl-account,cash-account";
	
	    //these are the meta_keys for the fields (arrays are object, property. so e.g. $theuser->ID)
	    $default_columns = array(
		array("user", "user_login"),
		array("order", "billing", "name"),
                array("order", "payment_transaction_id"),
		array("order", "total"),
	    );


        } else { 
            // The standard export
            header("Content-Disposition: attachment; filename=orders.csv");		
	    $csvoutput = "id,user_id,user_login,first_name,last_name,user_email,billing_name,billing_street,billing_city,billing_state,billing_zip,billing_country,billing_phone,membership_id,level_name,subtotal,tax,couponamount,total,payment_type,cardtype,accountnumber,expirationmonth,expirationyear,status,gateway,gateway_environment,payment_transaction_id,subscription_transaction_id,discount_code_id,discount_code,timestamp";
	
	    //these are the meta_keys for the fields (arrays are object, property. so e.g. $theuser->ID)
	    $default_columns = array(
		array("order", "id"),
		array("user", "ID"),
		array("user", "user_login"),
		array("user", "first_name"),
		array("user", "last_name"),
		array("user", "user_email"),
		array("order", "billing", "name"),
		array("order", "billing", "street"),
		array("order", "billing", "city"),
		array("order", "billing", "state"),
		array("order", "billing", "zip"),
		array("order", "billing", "country"),
		array("order", "billing", "phone"),
		array("order", "membership_id"),
		array("level", "name"),
		array("order", "subtotal"),
		array("order", "tax"),		
		array("order", "couponamount"),
		array("order", "total"),
		array("order", "payment_type"),
		array("order", "cardtype"),
		array("order", "accountnumber"),
		array("order", "expirationmonth"),
		array("order", "expirationyear"),
		array("order", "status"),
		array("order", "gateway"),
		array("order", "gateway_environment"),
		array("order", "payment_transaction_id"),
		array("order", "subscription_transactiond_id"),
		array("discount_code", "id"),
		array("discount_code", "code")
	    );

        }
	
	//any extra columns
	$extra_columns = apply_filters("pmpro_orders_csv_extra_columns", array());
	if(!empty($extra_columns))
	{
		foreach($extra_columns as $heading => $callback)
		{
			$csvoutput .= "," . $heading;
		}
	}
	
	$csvoutput .= "\n";	
	
	//output
	echo $csvoutput;
	$csvoutput = "";
	
	if($order_ids)
	{
		foreach($order_ids as $order_id)
		{
			$order = new MemberOrder();
			$order->nogateway = true;
			$order->getMemberOrderByID($order_id);
			$user = get_userdata($order->user_id);
			$level = $order->getMembershipLevel();
			$sqlQuery = "SELECT c.id, c.code FROM $wpdb->pmpro_discount_codes_uses cu LEFT JOIN $wpdb->pmpro_discount_codes c ON cu.code_id = c.id WHERE cu.order_id = '" . $order_id . "' LIMIT 1";
			$discount_code = $wpdb->get_row($sqlQuery);
			
			//default columns			
			if(!empty($default_columns))
			{
				$count = 0;
				foreach($default_columns as $col)
				{
					//add comma after the first item
					$count++;
					if($count > 1)
						$csvoutput .= ",";
						
					//checking $object->property. note the double $$					
					if(!empty($col[2]) && isset($$col[0]->$col[1]->$col[2]))
						$csvoutput .= pmpro_enclose($$col[0]->$col[1]->$col[2]);	//output the value				
					elseif(!empty($$col[0]->$col[1]))
						$csvoutput .= pmpro_enclose($$col[0]->$col[1]);	//output the value				
				}
			}
									
         // peachtree special fields
         if($report_type == "peachtree-customers") {
           // gl-sales
           $csvoutput .= "," . "20530";
         } elseif($report_type == "peachtree-orders") { 
           // amount, deposit-ticket-id,date,number-of-distributions,invoice-paid,quantity,item-id,
           // description,gl-account,cash-account
           // deposit-ticket-id takes some trickery -- group by date, but separate checks from cred cards
	   $csvoutput .= ",-" . $order->total;
           if($order->gateway == "check") {
             $csvoutput .= ",C" . date("dmy", $order->timestamp);
           } else {
             // web credit card order
             $csvoutput .= ",W" . date("dmy", $order->timestamp);
           }
           // date
	   $csvoutput .= "," . pmpro_enclose(date("n/j/Y", $order->timestamp));
           // number-of-distributions, invoice paid, quantity
           $csvoutput .= ",1,,1";
           // item-id, description
           $csvoutput .= ",HCSUB" . $order->membership_id . "," . pmpro_enclose($level->name);
           // gl-account, cash-account
           $csvoutput .= ",20530,10200";
         } else {
	   //timestamp
	   $csvoutput .= "," . pmpro_enclose(date(get_option("date_format"), $order->timestamp));
	 }					
			//any extra columns			
			if(!empty($extra_columns))
			{
				foreach($extra_columns as $heading => $callback)
				{
					$csvoutput .= "," . pmpro_enclose(call_user_func($callback, $order));
				}
			}
				
			$csvoutput .= "\n";
			
			//output
			echo $csvoutput;
			$csvoutput = "";			
		}
	}				
	
	print $csvoutput;
	
	function pmpro_enclose($s)
	{	
		return "\"" . str_replace("\"", "\\\"", $s) . "\"";
	}
