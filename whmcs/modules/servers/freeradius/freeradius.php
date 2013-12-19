<?php
# Author: zhfall@gmail.com	Date: 2013-12-20
# Copyright: 2011-2013

function freeradius_ConfigOptions() {

	# Should return an array of the module options for each product - maximum of 24

    $configarray = array(
		"plan" => array(
			"FriendlyName" => "Plan",
			"Type" => "dropdown",
			"Options" => "Trial,Starter,Standard,Advanced,Ultimate"
		)
	);

	return $configarray;

}

function freeradius_CreateAccount($params) {

    # ** The variables listed below are passed into all module functions **

    $serviceid = $params["serviceid"]; # Unique ID of the product/service in the WHMCS Database
    $pid = $params["pid"]; # Product/Service ID
    $producttype = $params["producttype"]; # Product Type: hostingaccount, reselleraccount, server or other
    $domain = $params["domain"];
	#$username = $params["username"];
	#$password = $params["password"];
    $clientsdetails = $params["clientsdetails"]; # Array of clients details - firstname, lastname, email, country, etc...
    $customfields = $params["customfields"]; # Array of custom field values for the product
    $configoptions = $params["configoptions"]; # Array of configurable option values for the product
	$username = $customfields["UserID"];
	$password = $customfields["Password"];
    # Product module option settings from ConfigOptions array above
    $configoption1 = $params["configoption1"];

    # Additional variables if the product/service is linked to a server
    $server = $params["server"]; # True if linked to a server
    $serverid = $params["serverid"];
    $serverip = $params["serverip"];
    $serverusername = $params["serverusername"];
    $serverpassword = $params["serverpassword"];
    $serveraccesshash = $params["serveraccesshash"];
    $serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config
 
	$successful = true;
	$result = "success";
 
	$mysqli = new mysqli($serverip, $serverusername, $serverpassword, $serveraccesshash);
	if ($mysqli->connect_error) {
		$result = $mysqli->connect_error;
		return $result;
	}

	$strseq = "";
	$i = 0;
	$oldname = $username;
	do {
		$username = $oldname .$strseq;
		if ($ret = $mysqli->query("SELECT value FROM radcheck WHERE username = '$username'"))
			$exists = $ret->num_rows;
		else
			return $mysqli->connect_error;
		$i = $i + 1;
		$strseq = (string)$i;
	} while ($exists > 0); 
	
	$command = 'encryptpassword';
	$adminuser = "admin";
	$values["password2"] = $password;
	$results = localAPI($command,$values,$adminuser);
	$encryptpassword = $results["password"];

	$table = "tblhosting";
	$update = array("username"=>$username, "password"=>$encryptpassword);
	$where = array("id"=>$serviceid);
	update_query($table,$update,$where);

	$mysqli->autocommit(false);

	if (!$mysqli->query("INSERT INTO radcheck(username,attribute,op,value) VALUES('$username','Cleartext-Password',':=','$password')")) {
		$result = $mysqli->error;
		$successful = false;
	}

	if (!$mysqli->query("INSERT INTO radusergroup(groupname,username) VALUES('$configoption1','$username')")) {
		$result = $mysqli->error;
		$successful = false;
	}

	if ($successful)
		$mysqli->commit();
	else
		$mysqli->rollback();

	$mysqli->close();
	
	return $result;
}

function freeradius_TerminateAccount($params) {

    $serviceid = $params["serviceid"]; # Unique ID of the product/service in the WHMCS Database
    $pid = $params["pid"]; # Product/Service ID
    $producttype = $params["producttype"]; # Product Type: hostingaccount, reselleraccount, server or other
    $domain = $params["domain"];
	$username = $params["username"];
	$password = $params["password"];
    $clientsdetails = $params["clientsdetails"]; # Array of clients details - firstname, lastname, email, country, etc...
    $customfields = $params["customfields"]; # Array of custom field values for the product
    $configoptions = $params["configoptions"]; # Array of configurable option values for the product

    # Product module option settings from ConfigOptions array above
    $configoption1 = $params["configoption1"];

    # Additional variables if the product/service is linked to a server
    $server = $params["server"]; # True if linked to a server
    $serverid = $params["serverid"];
    $serverip = $params["serverip"];
    $serverusername = $params["serverusername"];
    $serverpassword = $params["serverpassword"];
    $serveraccesshash = $params["serveraccesshash"];
    $serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config
 
	$successful = true;
	$result = "success";
 
	$mysqli = new mysqli($serverip, $serverusername, $serverpassword, $serveraccesshash);
	if ($mysqli->connect_error) {
		$result = $mysqli->connect_error;
		return $result;
	}

	$mysqli->autocommit(false);

	if (!$mysqli->query("DELETE FROM radcheck WHERE username = '$username'")) {
		$result = $mysqli->error;
		$successful = false;
	}

	if (!$mysqli->query("DELETE FROM radusergroup WHERE username = '$username'")) {
		$result = $mysqli->error;
		$successful = false;
	}

	if ($successful)
		$mysqli->commit();
	else
		$mysqli->rollback();
	
	$mysqli->close();
	return $result;
}

function freeradius_ChangePassword($params) {

    $serviceid = $params["serviceid"]; # Unique ID of the product/service in the WHMCS Database
    $pid = $params["pid"]; # Product/Service ID
    $producttype = $params["producttype"]; # Product Type: hostingaccount, reselleraccount, server or other
    $domain = $params["domain"];
	$username = $params["username"];
	$password = $params["password"];
    $clientsdetails = $params["clientsdetails"]; # Array of clients details - firstname, lastname, email, country, etc...
    $customfields = $params["customfields"]; # Array of custom field values for the product
    $configoptions = $params["configoptions"]; # Array of configurable option values for the product

    # Product module option settings from ConfigOptions array above
    $configoption1 = $params["configoption1"];

    # Additional variables if the product/service is linked to a server
    $server = $params["server"]; # True if linked to a server
    $serverid = $params["serverid"];
    $serverip = $params["serverip"];
    $serverusername = $params["serverusername"];
    $serverpassword = $params["serverpassword"];
    $serveraccesshash = $params["serveraccesshash"];
    $serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config

	$successful = true;
	$result = "success";
 
	$mysqli = new mysqli($serverip, $serverusername, $serverpassword, $serveraccesshash);
	if ($mysqli->connect_error) {
		$result = $mysqli->connect_error;
		return $result;
	}

	if (!$mysqli->query("UPDATE radcheck set value = '$password' WHERE username = '$username'")) {
		$result = $mysqli->error;
		$successful = false;
	}

	$mysqli->close();
	
	return $result;

}

function freeradius_ChangePackage($params) {

    $serviceid = $params["serviceid"]; # Unique ID of the product/service in the WHMCS Database
    $pid = $params["pid"]; # Product/Service ID
    $producttype = $params["producttype"]; # Product Type: hostingaccount, reselleraccount, server or other
    $domain = $params["domain"];
	$username = $params["username"];
	$password = $params["password"];
    $clientsdetails = $params["clientsdetails"]; # Array of clients details - firstname, lastname, email, country, etc...
    $customfields = $params["customfields"]; # Array of custom field values for the product
    $configoptions = $params["configoptions"]; # Array of configurable option values for the product

    # Product module option settings from ConfigOptions array above
    $configoption1 = $params["configoption1"];

    # Additional variables if the product/service is linked to a server
    $server = $params["server"]; # True if linked to a server
    $serverid = $params["serverid"];
    $serverip = $params["serverip"];
    $serverusername = $params["serverusername"];
    $serverpassword = $params["serverpassword"];
    $serveraccesshash = $params["serveraccesshash"];
    $serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config
 
	$successful = true;
	$result = "success";
 
	$mysqli = new mysqli($serverip, $serverusername, $serverpassword, $serveraccesshash);
	if ($mysqli->connect_error) {
		$result = $mysqli->connect_error;
		return $result;
	}

	if (!$mysqli->query("UPDATE radusergroup SET groupname = '$configoption1' WHERE username = '$username'")) {
		$result = $mysqli->error;
		$successful = false;
	}

	$mysqli->close();
	
	return $result;

}

function freeradius_ClientArea($params) {

	$username = $params["username"];
	$password = $params["password"];
    $clientdetails = $params["clientdetails"];
	$server = $params["server"]; # True if linked to a server
    $serverid = $params["serverid"];
    $serverip = $params["serverip"];
    $serverusername = $params["serverusername"];
    $serverpassword = $params["serverpassword"];
    $serveraccesshash = $params["serveraccesshash"];
    $serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config
 
	$url = "http://" .$serverip ."/vpn/count.php"; # URL to WHMCS API file
  
	$successful = True;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_GET, 1);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, $username .":" .$password);
	curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
	$data = curl_exec($ch);
	if (curl_error($ch)) {
		$successful = False;
		$result = "Error Message(user): " .curl_errno($ch) ."- " .curl_error($ch);
	} else {
		$ret = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
		if ( $ret != 200 ) {
			$successful = False;
			$result = "HTTP Return Code(user): " .$ret;
		}
	}
	curl_close($ch);

	if ( $successful ) {
		$code = $data;
	} else {
		$code = $result;
	}
	return $code;

}

function freeradius_UsageUpdate($params) {

	$serverid = $params['serverid'];
	$serverhostname = $params['serverhostname'];
	$serverip = $params['serverip'];
	$serverusername = $params['serverusername'];
	$serverpassword = $params['serverpassword'];
	$serveraccesshash = $params['serveraccesshash'];
	$serversecure = $params['serversecure'];

	# Run connection to retrieve usage for all domains/accounts on $serverid

	# Now loop through results and update DB

	foreach ($results AS $domain=>$values) {
        update_query("tblhosting",array(
         "diskused"=>$values['diskusage'],
         "dislimit"=>$values['disklimit'],
         "bwused"=>$values['bwusage'],
         "bwlimit"=>$values['bwlimit'],
         "lastupdate"=>"now()",
        ),array("server"=>$serverid,"domain"=>$values['domain']));
    }

}

?>
