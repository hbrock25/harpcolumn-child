<?php	
/**
 * Download a CSV of users
 * Expects:
 * $l: type of users to show
 * $s: search string
 * $theusers: sql result array
 **/

//begin output
header("Content-type: text/csv");	
if($s && $l)
    header("Content-Disposition: attachment; filename=members_list_" . intval($l) . "_level_" . sanitize_file_name($s) . ".csv");
elseif($s)
header("Content-Disposition: attachment; filename=members_list_" . sanitize_file_name($s) . ".csv");
else
    header("Content-Disposition: attachment; filename=members_list.csv");

$heading = "id,username,firstname,lastname,email,billing firstname,billing lastname,billing company,billing address1,billing address2,billing city,billing state,billing zipcode,billing country,billing phone,shipping firstname,shipping lastname,shipping company,shipping address1,shipping address2,shipping city,shipping state,shipping zipcode,shipping country,membership,term,discount_code_id,discount_code,joined,expires";

$csvoutput = $heading;

//these are the meta_keys for the fields (arrays are object, property. so e.g. $theuser->ID)
$default_columns = array(
    array("theuser", "ID"),
    array("theuser", "user_login"),
    array("metavalues", "first_name"),
    array("metavalues", "last_name"),
    array("theuser", "user_email"),
    array("metavalues", "billing_first_name"),
    array("metavalues", "billing_last_name"),
    array("metavalues", "billing_company"),
    array("metavalues", "billing_address_1"),
    array("metavalues", "billing_address_2"),
    array("metavalues", "billing_city"),
    array("metavalues", "billing_state"),
    array("metavalues", "billing_postcode"),
    array("metavalues", "billing_country"),
    array("metavalues", "shipping_phone"),
    array("metavalues", "shipping_first_name"),
    array("metavalues", "shipping_last_name"),
    array("metavalues", "shipping_company"),
    array("metavalues", "shipping_address_1"),
    array("metavalues", "shipping_address_2"),
    array("metavalues", "shipping_city"),
    array("metavalues", "shipping_state"),
    array("metavalues", "shipping_postcode"),
    array("metavalues", "shipping_country"),
    array("theuser", "membership"),
    array("theuser", "cycle_period"),
    array("discount_code", "id"),
    array("discount_code", "code")
    //joindate and enddate are handled specifically below
);


$csvoutput .= "\n";	

//output
echo $csvoutput;
$csvoutput = "";

if($theusers)
{
    foreach($theusers as $theuser)
    {

	//get meta                                          
	$metavalues = get_userdata($theuser->ID);  
	
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
		if(!empty($$col[0]->$col[1]))
		    $csvoutput .= pmpro_enclose($$col[0]->$col[1]);	//output the value				
	    }
	}
	
	//joindate and enddate
	$csvoutput .= "," . pmpro_enclose(date("Y-m-d", $theuser->joindate)) . ",";
	
	if($theuser->membership_id)
	{
	    if($theuser->enddate)
		$csvoutput .= pmpro_enclose(date("Y-m-d", $theuser->enddate));
	    else
		$csvoutput .= pmpro_enclose("Never");
	}
	else
	    $csvoutput .= "N/A";
	
	$csvoutput .= "\n";
	
	//output
	echo $csvoutput;
	$csvoutput = "";			
    }
}

print $csvoutput;

