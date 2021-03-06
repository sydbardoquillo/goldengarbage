<?php
	/**
	* GGU_service.php
	*
	* File to handle all API requests.
	* Accepts POST requests.
	* Each request will be identified by a tag.
	* Response will be JSON data.
	*
	* @category   GoldenGarbageServer
	* @package    com.spectrum.ecoapp.goldengabage
	* @author     Arreglo, Charlie Ahl F. <arreglo.charlieahl@live.com>
	* @author     Sotto, Antonio Jr. O. <antoniosottojr@gmail.com> 	
	* @copyright  Team Spectrum
	* @version    2.2.0.1
	*/

	if (isset($_GET['tag']) && $_GET['tag'] != '') {
	    $tag = $_GET['tag'];
		require_once __DIR__ . '/GGU_functions.php';
	    $DBFunction = new DB_Functions();
	    $response = array("ErrorStatus" => FALSE);

	    if ($tag == 'requestLogin') {
	   		$username = $_GET['username'];
	   		$password = $_GET['password'];
	   		$reply = $DBFunction->RequestLogin($username, $password);
	   		if ($reply == FALSE) {
	            $response["ErrorStatus"] = TRUE;
	            $response["ErrorMessage"] = "Incorrect username or password.";
	        } else {
	        	$response["AccountID"] = $reply["us_ID"];
	        	$response["AccountName"] = $reply["us_firstname"];
	        }
	        echo json_encode($response);
	    }

	    else if ($tag == 'requestRegister') {
	    	$firstname = $_GET['firstname'];
	   		$lastname = $_GET['lastname'];
	   		$username = $_GET['username'];
	   		$address = $_GET['address'];
	   		$password = $_GET['password'];
	   		if ($DBFunction->IsUsernameExisted($username)) {
				$response["ErrorStatus"] = TRUE;
	            $response["ErrorMessage"] = "Sorry, username has already been taken. Please try another.";
	   		} else {
	   			$reply = $DBFunction->RequestRegister($firstname, $lastname, $username, $address, $password);
	   			if ($reply == FALSE) {
	            $response["ErrorStatus"] = TRUE;
	            $response["ErrorMessage"] = "Sorry, there's a problem within our service. Please, try again later.\n(Error: 4RE-G04)";
		        } else {
		        	$response["AccountID"] = $reply["us_ID"];
		        	$response["AccountName"] = $reply["us_firstname"];
	        	}
	        }
	        echo json_encode($response);	        	
	    }

	    else if ($tag == 'requestJunkshopList') {
	    	$junkshopList = $DBFunction->RequestJunkshopList();
	    	if ($junkshopList == FALSE) 
	    	{
	    		$response["ErrorStatus"] = TRUE;
	            $response["ErrorMessage"] = "Sorry, there's a problem within our service. Please, try again later.\n(Error: 4JK-L04)";
	    	}
	    	else
	    	{
				for($i = 0; $i < count($junkshopList); $i++) {
				    $junkshopListData[] = array('JunkshopName' => $junkshopList[$i]->js_name, 
		    			'Location' => $junkshopList[$i]->js_address, 
		    			'Latitude' => $junkshopList[$i]->js_lat, 
		    			'Longitude' => $junkshopList[$i]->js_log);
				}
			    $response["JunkshopList"] = $junkshopListData;
	    	}
	    	echo json_encode($response);
	    }

	    else if ($tag == 'requestJunkshopInfo') {
			$junkshopName = $_GET['junkshopName'];
	    	$junkshopID = $DBFunction->RequestJunkshopID($junkshopName);
	    	if($junkshopID == FALSE)
	    	{
	    		$response["ErrorStatus"] = TRUE;
	            $response["ErrorMessage"] = "Sorry, there's a problem within our service. Please, try again later.\n(Error: 4JK-ID4)";
	    	}
	    	else
	    	{
	    		$junkshopInfo = $DBFunction->RequestJunkshopInfo($junkshopID);
	    		if($junkshopID == FALSE)
		    	{
		    		$response["ErrorStatus"] = TRUE;
		            $response["ErrorMessage"] = "Sorry, there's a problem within our service. Please, try again later.\n(Error: 4JK-IF4)";
		    	}
		    	else
		    	{
		    		$response["JunkshopOwner"] = $junkshopInfo["js_owner"];
		    		$response["JunkshopContact"] = $junkshopInfo["js_contact_number"];
		    		$response["JunkshopPickUp"] = $junkshopInfo["js_pickup_flag"];

		    		$junkshopItems = $DBFunction->RequestJunkshopItems($junkshopID);
		    		if($junkshopItems == FALSE)
		    		{
		    			$response["JunkshopItems"] = array();
		    		}
		    		else 
		    		{
				    	for($i = 0; $i < count($junkshopItems); $i++) {
				    		$junkshopItemData[] = array('JunkshopItemName' => $junkshopItems[$i]->js_item_name, 
				    			'JunkshopItemPrice' => $junkshopItems[$i]->js_item_price);
				    	}
				    	$response["JunkshopItems"] = $junkshopItemData;
			    	}
			    	

		    		$junkshopComments = $DBFunction->RequestJunkshopComments($junkshopID);
		    		if($junkshopComments == FALSE)
		    		{
		    			$response["JunkshopComments"] = array();
		    		}
		    		else 
		    		{
				    	for($i = 0; $i < count($junkshopComments); $i++) {
				    		$junkshopCommentData[] = array('CommentFullName' => $junkshopComments[$i]->js_fullname, 
				    			'CommentStarRating' => $junkshopComments[$i]->js_star,
				    			'CommentContent' => $junkshopComments[$i]->js_comment);
				    	}
				    	$response["JunkshopComments"] = $junkshopCommentData;
			    	}
		    	}
	    	}
	    	echo json_encode($response);
		}

		else if ($tag == 'requestPostReview') {
	    	$userID = $_GET['userID'];
	    	$junkshopName = $_GET['junkshopName'];
	    	$userComment = $_GET['userComment'];
	    	$userRating = $_GET['userRating'];
			if ($DBFunction->RequestPostReview($userID, $junkshopName, $userComment, $userRating))
			{
				$response["ErrorStatus"] = FALSE;
				$response["RespondMessage"] = "Comment has been added sucessfully.";
			}
			else
			{
				$response["ErrorStatus"] = TRUE;
		        $response["ErrorMessage"] = "Sorry, there's a problem within our service. Please, try again later.\n(Error: 4JK-PR4)";
			}
			echo json_encode($response);
		}

		else if ($tag == 'requestSearchItem') {
	    	$itemName = $_GET['itemName'];
	    	$junkshopSearchItems = $DBFunction->requestSearchItem($itemName);
	    	if($junkshopSearchItems == FALSE)
		    {
		    	$response["ErrorStatus"] = TRUE;
		        $response["ErrorMessage"] = "No Items Found";
		    }
		    else
		    {
		    	for($i = 0; $i < count($junkshopSearchItems); $i++) {
				    $junkshopSearchListData[] = array('ItemName' => $junkshopSearchItems[$i]->js_item_name,
				    	'ItemPrice' => $junkshopSearchItems[$i]->js_item_price,
				    	'JunkshopName' => $junkshopSearchItems[$i]->js_name,
				    	'JunkshopAddress' => $junkshopSearchItems[$i]->js_address,
				    	'JunkshopPickUp' => $junkshopSearchItems[$i]->js_pickup_flag);
				}
			    $response["SearchResult"] = $junkshopSearchListData;
		    }
			echo json_encode($response);
		}

		else if($tag == 'requestUserInfo'){
			$userID = $_GET['userID'];
			$userInfo = $DBFunction->RequestUserInfo($userID);
			if($userInfo == FALSE)
			{
				$response["ErrorStatus"] = TRUE;
		        $response["ErrorMessage"] = "User not found";
			}
			else
			{
				$response["FirstName"] = $userInfo["us_firstname"];
				$response["LastName"] = $userInfo["us_lastname"];
				$response["Address"] = $userInfo["us_address"];
				   
			}
			echo json_encode($response);
		}

		else if($tag == 'requestUserUpdate'){
			$userID = $_GET['userID'];
			$us_firstname  = $_GET['us_firstname'];
			$us_lastname = $_GET['us_lastname'];
			$us_address = $_GET['us_address'];
			$userUpdate = $DBFunction->RequestUserUpdate($userID, $us_firstname, $us_lastname, $us_address);
			if($userUpdate == FALSE)
			{
				$response["ErrorStatus"] = TRUE;
		        $response["ErrorMessage"] = "Update Error. User does not exist. \n(Error: 4US-UUF)"; /*UserID was not found*/
			}
			else
			{

				$response["ErrorStatus"] = FALSE;
				$response["RespondMessage"] = "Profile updated successfully.";
			}
			echo json_encode($response);
		}
		else if($tag == 'requestPasswordUpdate'){
			$userID = $_GET['userID'];
			$password = $_GET['password'];
			$newPassword = $_GET['new_password'];
			$passUpdate = $DBFunction->RequestPasswordUpdate($userID, $password, $newPassword);
			if($passUpdate == FALSE)
			{
				$response["ErrorStatus"] = TRUE;
		        $response["ErrorMessage"] = "Password update failed. \n(Error: 4US-UPF)";/*UserID and Password mismatch*/
			}
			else
			{

				$response["ErrorStatus"] = FALSE;
				$response["RespondMessage"] = "Password changed.";
			}
			echo json_encode($response);
		}
		else if($tag == 'requestPostAuction'){
			
		}

	    else {
	    	$error_msg = "FATAL WARNING: Unknown request";
	   		echo $error_msg;
	    }

	} else {
	   $error_msg = "FATAL WARNING: Requirements not found.";
	   echo $error_msg;
	}
?>