<?php

require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->post('/login','login'); /* User login */
$app->post('/signup','signup'); /* User Signup  */
$app->post('/productlist','productlist'); /* Product List  */
$app->post('/productCategory','productCategory'); /* Product Category  */
$app->post('/addtocart','addtocart'); /* addtocart  */
$app->post('/cartproducts','cartproducts'); /* cartproducts  */
$app->post('/quantityupdate','quantityupdate'); /* cartproducts  */
$app->post('/removeproduct','removeproduct'); /* cartproducts  */
$app->post('/shippingaddress','shippingaddress'); /* cartproducts  */
$app->post('/paymentsubmit','paymentsubmit'); /* cartproducts  */
$app->post('/orderhistory','orderhistory'); /* cartproducts  */
$app->post('/productDetails','productDetails'); /* cartproducts  */
$app->post('/getUserAddressList','getUserAddressList'); /* cartproducts  */
$app->post('/changePassword','changePassword'); /* changePassword  */
$app->post('/menuCatSubcatLists','menuCatSubcatLists'); /* menuCatSubcatLists  */




$app->run();

/************************* USER LOGIN *************************************/
/* ### User login ### */
function login() {
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    
    try {
        
        $db = getDB();
        $userData ='';
        $sql = "SELECT * FROM users WHERE email=:email and password=:password ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $data->email, PDO::PARAM_STR);
        $password=hash('sha256',$data->password);
        $stmt->bindParam("password", $password, PDO::PARAM_STR);
        $stmt->execute();
        $mainCount=$stmt->rowCount();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        
        if(!empty($userData))
        {
            $user_id=$userData->user_id;
            $userData->token = apiToken($user_id);
			
			        $sql1 = "SELECT orderNumber FROM tbl_ordertable WHERE user_id='".$user_id."' and orderStatus='Pending'";
					$stmt1 = $db->prepare($sql1);
					$stmt1->execute();
					$order = $stmt1->fetchAll(PDO::FETCH_OBJ);
					foreach($order  as $orderv){
						$userData['orderNumber'] = $orderv->orderNumber;
						array_push($userData, $userData['orderNumber']);
					}
		
			
        }
        
        $db = null;
         if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userOrder . '}';
            } else {
               echo '{"error":{"text":"Please enter correct Email and Password"}}';
            }    
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


/* ### User registration ### */
function signup() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $firstname=$data->firstname;
    $lastname=$data->lastname;
    $mobile=$data->mobile;
    $email=$data->email;
    $password=$data->password;
    
    
    try {

        if (isset($email) && $email!="" && isset($password) && $password!="")
        {
            $db = getDB();
            $userData = '';
            $sql = "SELECT user_id FROM users WHERE email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            if($mainCount==0)
            {
                /*Inserting user values*/
                $sql1="INSERT INTO users(firstname,lastname,mobile,email,password)VALUES(:firstname,:lastname,:mobile,:email,:password)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("firstname", $firstname,PDO::PARAM_STR);
                $stmt1->bindParam("lastname", $lastname,PDO::PARAM_STR);
                $stmt1->bindParam("mobile", $mobile,PDO::PARAM_STR);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $password=hash('sha256',$data->password);
                $stmt1->bindParam("password", $password,PDO::PARAM_STR);
                $stmt1->execute();
                $userData=internalUserDetails($email); 
            }
            
            $db = null;
         
            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Enter valid data1"}}';
            }        
        }
        else{
            echo '{"error":{"text":"Enter valid data2"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function email() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email=$data->email;

    try {
       
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);  
        if (strlen(trim($email))>0 && $email_check>0)
        {
            $db = getDB();
            $userData = '';
            $sql = "SELECT user_id FROM emailUsers WHERE email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            if($mainCount==0)
            {                
                /*Inserting user values*/
                $sql1="INSERT INTO emailUsers(email)VALUES(:email)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $stmt1->execute();               
            }
            $userData=internalEmailDetails($email);
            $db = null;
            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Enter valid data"}}';
            }
        }
        else{
            echo '{"error":{"text":"Enter valid data"}}';
        }
    }
    
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


/* ### internal Username Details ### */
function internalUserDetails($input) {
    
    try {
        $db = getDB();
        $sql = "SELECT * FROM users WHERE email=:input";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("input", $input,PDO::PARAM_STR);
        $stmt->execute();
        $usernameDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $usernameDetails->token = apiToken($usernameDetails->user_id);
        $db = null;
        return $usernameDetails;        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }  
    
}

function productlist(){
    $request = \Slim\Slim::getInstance()->request();   
    $data = json_decode($request->getBody());
    $subcat_id = $data->subCategoryId;
	/*$sort_values = $data->sort_values;
	$sort_parameter = $data->sort_parameter;*/
	
	$sort_values = 'ASC';
	$sort_parameter = 'priceRange';
	
	if (isset($subcat_id) && $subcat_id=="makeup"){
		$subcat_id = 1;
	} else if(isset($subcat_id) && $subcat_id=="skincare"){
		$subcat_id = 2;		
	} else {
		$subcat_id = 1;
	}
	
	if (isset($sort_values) && $sort_values='asc') {
		$sort = "ASC";
	}else if(isset($sort_values) && $sort_values='desc'){
		$sort = "DESC";
	}else {
		$sort = "ASC";
	}
	
	if (isset($sort_parameter) && $sort_parameter='price') {
		$sort_value = "priceRange";
	}else if(isset($sort_parameter) && $sort_parameter='name'){
		$sort_value = "PROD_RGN_NAME";
	}else {
		$sort_value = "id";
	}
    //$subcat_id = 2;
    
	$cartInsertData =array();
	$final_products =array();
	$array_sku = array();
	$usersList_array = array();

    try {
        $productData = '';
        $db = getDB();
        $sql = "SELECT * FROM tbl_products where subCategoryId='".$subcat_id."' ORDER BY ".$sort_value." ".$sort."";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $products_array = $stmt->fetchAll(PDO::FETCH_OBJ);
		foreach($products_array  as $array_values){
			$final_products['id'] = $array_values->id;
			$final_products['PROD_RGN_NAME'] = $array_values->PROD_RGN_NAME;
			$final_products['priceRange'] = $array_values->priceRange;
			$final_products['ATTRIBUTE_DESC_1'] = $array_values->ATTRIBUTE_DESC_1;
			$final_products['DESCRIPTION'] = $array_values->DESCRIPTION;
			$final_products['ATTRIBUTE_CONCERN'] = $array_values->ATTRIBUTE_CONCERN;
			$final_products['ATTRIBUTE_BENEFIT'] = $array_values->ATTRIBUTE_BENEFIT;
			$final_products['ATTRIBUTE_DESC_3'] = $array_values->ATTRIBUTE_DESC_3;
			$final_products['MPP_DESC_2'] = $array_values->MPP_DESC_2;
			$final_products['ATTRIBUTE_DESC_4'] = $array_values->ATTRIBUTE_DESC_4;
			$final_products['ATTRIBUTE_DESC_5'] = $array_values->ATTRIBUTE_DESC_5;
			$final_products['ATTRIBUTE_COLLECTION'] = $array_values->ATTRIBUTE_COLLECTION;
			$final_products['url'] = $array_values->url;
			$final_products['SKIN_CONCERN_ATTR'] = $array_values->SKIN_CONCERN_ATTR;
			$final_products['MPP_DESC_1'] = $array_values->MPP_DESC_1;
			$final_products['ATTRIBUTE_LABEL_4'] = $array_values->ATTRIBUTE_LABEL_4;
			$final_products['MPP_LABEL_2'] = $array_values->MPP_LABEL_2;
			$final_products['shaded'] = $array_values->shaded;
			$final_products['PROD_CAT_DISPLAY_ORDER'] = $array_values->PROD_CAT_DISPLAY_ORDER;
			$final_products['PARENT_CAT_ID'] = $array_values->PARENT_CAT_ID;
			$final_products['PRODUCT_DETAILS_MOBILE'] = $array_values->PRODUCT_DETAILS_MOBILE;
			$final_products['ATTRIBUTE_LABEL_1'] = $array_values->ATTRIBUTE_LABEL_1;
			$final_products['MAKEUP_BENEFIT'] = $array_values->MAKEUP_BENEFIT;
			$final_products['DEFAULT_CAT_ID'] = $array_values->DEFAULT_CAT_ID;
			$final_products['ATTRIBUTE_SKINTYPE'] = $array_values->ATTRIBUTE_SKINTYPE;
			$final_products['ATTRIBUTE_LABEL_3'] = $array_values->ATTRIBUTE_LABEL_3;
			$final_products['sized'] = $array_values->sized;
			$final_products['ATTRIBUTE_LABEL_2'] = $array_values->ATTRIBUTE_LABEL_2;
			$final_products['SKINTYPE_DESC'] = $array_values->SKINTYPE_DESC;
			$final_products['PRODUCT_ID'] = $array_values->PRODUCT_ID;
			$final_products['ATTRIBUTE_LABEL_5'] = $array_values->ATTRIBUTE_LABEL_5;
			$final_products['skus'] = array();
			$final_products['SHORT_DESC'] = $array_values->SHORT_DESC;
			$final_products['SKIN_CONCERN_1'] = $array_values->SKIN_CONCERN_1;
			$final_products['SKIN_CONCERN_3'] = $array_values->SKIN_CONCERN_3;
			$final_products['PROD_RGN_SUBHEADING'] = $array_values->PROD_RGN_SUBHEADING;
			$final_products['RECOMMENDED_PERCENT'] = $array_values->RECOMMENDED_PERCENT;
			$final_products['ATTRIBUTE_DESC_2'] = $array_values->ATTRIBUTE_DESC_2;
			$final_products['PROD_BASE_ID'] = $array_values->PROD_BASE_ID;
			$final_products['defaultSku'] = array();

			$sql1 = "SELECT * FROM `tbl_product_skus` WHERE `PRODUCT_ID`='".$array_values->PRODUCT_ID."'";
			$stmt1 = $db->prepare($sql1);
			$stmt1->execute();
			$products_sku = $stmt1->fetchAll(PDO::FETCH_OBJ);	
			foreach($products_sku  as $array_sku_values){
				$array_sku['id']=$array_sku_values->id;
				$array_sku['PRODUCT_ID']=$array_sku_values->PRODUCT_ID;
				$array_sku['LARGE_SMOOSH']=$array_sku_values->LARGE_SMOOSH;
				$array_sku['isOrderable']=$array_sku_values->isOrderable;
				$array_sku['XS_SMOOSH']=$array_sku_values->XS_SMOOSH;
				$array_sku['SKIN_TYPE']=$array_sku_values->SKIN_TYPE;
				$array_sku['formattedPrice']=$array_sku_values->formattedPrice;
				$array_sku['PRODUCT_SIZE']=$array_sku_values->PRODUCT_SIZE;
				$array_sku['SKU_BASE_ID']=$array_sku_values->SKU_BASE_ID;
				$array_sku['MEDIUM_IMAGE']=$array_sku_values->MEDIUM_IMAGE;
				$array_sku['XL_IMAGE']=$array_sku_values->XL_IMAGE;
				$array_sku['SKU_ID']=$array_sku_values->SKU_ID;
				$array_sku['formattedPrice2']=$array_sku_values->formattedPrice2;
				$array_sku['formattedFuturePrice']=$array_sku_values->formattedFuturePrice;
				$array_sku['XL_SMOOSH']=$array_sku_values->XL_SMOOSH;
				$array_sku['DISPLAY_ORDER']=$array_sku_values->DISPLAY_ORDER;
				$array_sku['HEX_VALUE_STRING']=$array_sku_values->HEX_VALUE_STRING;
				$array_sku['SMALL_IMAGE']=$array_sku_values->SMALL_IMAGE;
				$array_sku['SMOOSH_PATH_STRING']=$array_sku_values->SMOOSH_PATH_STRING;
				$array_sku['LARGE_IMAGE']=$array_sku_values->LARGE_IMAGE;
				$array_sku['ATTRIBUTE_COLOR_FAMILY']=$array_sku_values->ATTRIBUTE_COLOR_FAMILY;
				$array_sku['ATTRIBUTE_FINISH']=$array_sku_values->ATTRIBUTE_FINISH;
				$array_sku['SHADENAME']=$array_sku_values->SHADENAME;
				$array_sku['FUTURE_PRICE']=$array_sku_values->FUTURE_PRICE;
				$array_sku['PRICE']=$array_sku_values->PRICE;
				$array_sku['SHADE_NUMBER']=$array_sku_values->SHADE_NUMBER;
				$array_sku['SHADE_DESCRIPTION']=$array_sku_values->SHADE_DESCRIPTION;
				$array_sku['INVENTORY_STATUS']=$array_sku_values->INVENTORY_STATUS;
				$array_sku['SKIN_TYPE_TEXT']=$array_sku_values->SKIN_TYPE_TEXT;
				array_push($final_products['skus'],$array_sku);				
			}	
            $sql2 = "SELECT * FROM `tbl_product_skus` WHERE `PRODUCT_ID`='".$final_products['PRODUCT_ID']."' limit 1";
			$stmt2 = $db->prepare($sql2);
			$stmt2->execute();
			$products_sku1 = $stmt2->fetchAll(PDO::FETCH_OBJ);
			foreach($products_sku1  as $array_sku_values1){
                $array_sku1['id']=$array_sku_values1->id;
				$array_sku1['PRODUCT_ID']=$array_sku_values1->PRODUCT_ID;
				$array_sku1['LARGE_SMOOSH']=$array_sku_values1->LARGE_SMOOSH;
				$array_sku1['isOrderable']=$array_sku_values1->isOrderable;
				$array_sku1['XS_SMOOSH']=$array_sku_values1->XS_SMOOSH;
				$array_sku1['SKIN_TYPE']=$array_sku_values1->SKIN_TYPE;
				$array_sku1['formattedPrice']=$array_sku_values1->formattedPrice;
				$array_sku1['PRODUCT_SIZE']=$array_sku_values1->PRODUCT_SIZE;
				$array_sku1['SKU_BASE_ID']=$array_sku_values1->SKU_BASE_ID;
				$array_sku1['MEDIUM_IMAGE']=$array_sku_values1->MEDIUM_IMAGE;
				$array_sku1['XL_IMAGE']=$array_sku_values1->XL_IMAGE;
				$array_sku1['SKU_ID']=$array_sku_values1->SKU_ID;
				$array_sku1['formattedPrice2']=$array_sku_values1->formattedPrice2;
				$array_sku1['formattedFuturePrice']=$array_sku_values1->formattedFuturePrice;
				$array_sku1['XL_SMOOSH']=$array_sku_values1->XL_SMOOSH;
				$array_sku1['DISPLAY_ORDER']=$array_sku_values1->DISPLAY_ORDER;
				$array_sku1['HEX_VALUE_STRING']=$array_sku_values1->HEX_VALUE_STRING;
				$array_sku1['SMALL_IMAGE']=$array_sku_values1->SMALL_IMAGE;
				$array_sku1['SMOOSH_PATH_STRING']=$array_sku_values1->SMOOSH_PATH_STRING;
				$array_sku1['LARGE_IMAGE']=$array_sku_values1->LARGE_IMAGE;
				$array_sku1['ATTRIBUTE_COLOR_FAMILY']=$array_sku_values1->ATTRIBUTE_COLOR_FAMILY;
				$array_sku1['ATTRIBUTE_FINISH']=$array_sku_values1->ATTRIBUTE_FINISH;
				$array_sku1['SHADENAME']=$array_sku_values1->SHADENAME;
				$array_sku1['FUTURE_PRICE']=$array_sku_values1->FUTURE_PRICE;
				$array_sku1['PRICE']=$array_sku_values1->PRICE;
				$array_sku1['SHADE_NUMBER']=$array_sku_values1->SHADE_NUMBER;
				$array_sku1['SHADE_DESCRIPTION']=$array_sku_values1->SHADE_DESCRIPTION;
				$array_sku1['INVENTORY_STATUS']=$array_sku_values1->INVENTORY_STATUS;
				$array_sku1['SKIN_TYPE_TEXT']=$array_sku_values1->SKIN_TYPE_TEXT;
				array_push($final_products['defaultSku'],$array_sku1);
			}					
			array_push($usersList_array,$final_products);
		}			
        $db = null;
        if($usersList_array)
            echo '{"productData": ' . json_encode($usersList_array) . '}';
        else
            echo '{"productData": "No Products found"}';        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function productCategory(){
    $request = \Slim\Slim::getInstance()->request();   
    try {
        $productCategory = '';
        $db = getDB();
        $sql = "SELECT * FROM tbl_menu WHERE menuStatus=1 ORDER BY sortOrder asc LIMIT 8";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $productCategory = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        if($productCategory)
            echo '{"productCategory": ' . json_encode($productCategory) . '}';
        else
            echo '{"productCategory": ""}';        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function addtocart(){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
	$cartInsertData =array();
    
    $userId=$data->userId;
    $PRODUCT_ID=$data->PRODUCT_ID;
    $SKU_ID=$data->SKU_ID;
    $Quantity=$data->qty;
	$orderNumber=$data->orderNumb;
	
    
    try {  
        
        if ($PRODUCT_ID!="")
        {
			if (isset($orderNumber) && $orderNumber!="") {
				 $orderNumber = $orderNumber;
			} else {
				$digits_needed=8;
				$random_number=''; // set up a blank string
				$count=0;
				while ( $count < $digits_needed ) {
					$random_digit = mt_rand(0, 9);
					$random_number .= $random_digit;
					$count++;
				}	
				$orderNumber = $random_number;
			}
            
            $db = getDB();
            if (isset($userId) && $userId!="") {     
            $sqlc = "SELECT * FROM tbl_ordertable WHERE user_id=:userId AND orderNumber=:orderNumber";
            $stmtc = $db->prepare($sqlc);
            $stmtc->bindParam("userId", $userId,PDO::PARAM_STR);
            $stmtc->bindParam("orderNumber", $orderNumber,PDO::PARAM_STR);
			$stmtc->execute();
            $orderCnt=$stmtc->rowCount();
            if($orderCnt ==0)
            {
                $sql11="INSERT INTO `tbl_ordertable` (`user_id`, `orderNumber`, `orderStatus`, `paymentMethod`, `paymentStatus`, `cardNumber`, `cardExpMonth`, `cardExpYear`, `cardCvv`) VALUES ('".$userId."', '".$orderNumber."', 'Pending', 'Pending', 'Pending', '', '', '', '')";
                $stmt11 = $db->prepare($sql11);
                $stmt11->execute(); 
			}
            }
			
            $sql = "SELECT * FROM tbl_orderHistory WHERE orderNumber=:orderNumber AND PRODUCT_ID=:PRODUCT_ID and SKU_ID=:SKU_ID";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("orderNumber", $orderNumber,PDO::PARAM_STR);
            $stmt->bindParam("PRODUCT_ID", $PRODUCT_ID,PDO::PARAM_STR);
            $stmt->bindParam("SKU_ID", $SKU_ID,PDO::PARAM_STR);
			$stmt->execute();
            $productCnt=$stmt->rowCount();
            $created=time();
            if($productCnt==0)
            {
                /*Inserting cart values*/
               // $sql1="INSERT INTO tbl_orderHistory(userId,orderNumber,PRODUCT_ID,SKU_ID,Quantity)VALUES(:userId,:orderNumber,:PRODUCT_ID,:SKU_ID,:Quantity)";
                $sql1="INSERT INTO tbl_orderHistory(orderNumber,PRODUCT_ID,SKU_ID,Quantity)VALUES(:orderNumber,:PRODUCT_ID,:SKU_ID,:Quantity)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("orderNumber", $orderNumber,PDO::PARAM_STR);
                $stmt1->bindParam("PRODUCT_ID", $PRODUCT_ID,PDO::PARAM_STR);
                $stmt1->bindParam("SKU_ID", $SKU_ID,PDO::PARAM_STR);
				$stmt1->bindParam("Quantity", $Quantity,PDO::PARAM_STR);
                $stmt1->execute();
				$cartInsertData['msg'] = "Product addedd successfully";
				$cartInsertData['orderNumber'] = $orderNumber;
            }else{
                /*Update cart values*/
                $sql1="UPDATE `tbl_orderHistory` SET Quantity = :Quantity  WHERE orderNumber=:orderNumber AND PRODUCT_ID=:PRODUCT_ID and SKU_ID=:SKU_ID";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("orderNumber", $orderNumber,PDO::PARAM_STR);
                $stmt1->bindParam("PRODUCT_ID", $PRODUCT_ID,PDO::PARAM_STR);
                $stmt1->bindParam("SKU_ID", $SKU_ID,PDO::PARAM_STR);
				$stmt1->bindParam("Quantity", $Quantity,PDO::PARAM_STR);
                $stmt1->execute();
				$cartInsertData['msg'] = "Product updated successfully";
				$cartInsertData['orderNumber'] = $orderNumber;			
			}
            
            $db = null;
         
            if($cartInsertData){
               $cartInsertData = json_encode($cartInsertData);
                echo '{"cartInsertData": ' .$cartInsertData . '}';
            } else {
               echo '{"error":{"text":"Enter valid data"}}';
            }        
        }
        else{
            echo '{"error":{"text":"Enter valid datas"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
	
}


function cartproducts(){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
	$orderNumber=$data->orderNumb;
	    
    try {          
        if ($orderNumber!="")
        {			
            $db = getDB();
            $sql = "SELECT * FROM tbl_orderHistory WHERE orderNumber=:orderNumber";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("orderNumber", $orderNumber,PDO::PARAM_STR);
			$stmt->execute();
            $productCnt=$stmt->rowCount();
            $created=time();
            if($productCnt==0){
				$cartProduct['msg'] = "Your cart is Empty";
            }else{
                /*Update cart values*/
                $sql1="SELECT * FROM tbl_orderhistory a INNER JOIN tbl_product_skus b ON a.SKU_ID = b.SKU_ID INNER JOIN tbl_products c ON a.PRODUCT_ID = c.PRODUCT_ID WHERE a.orderNumber =:orderNumber";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("orderNumber", $orderNumber,PDO::PARAM_STR);
                $stmt1->execute();
				$cartprod = $stmt1->fetchAll(PDO::FETCH_OBJ);				
			}
            
            $db = null;
            if($cartprod){
               $cartprod = json_encode($cartprod);
                echo '{"cartprod": ' .$cartprod. '}';
            } else {
               echo '{"error":{"text":"Your cart is Empty1"}}';
            }        
        }
        else{
            echo '{"error":{"text":"Your cart is Empty2"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
	
}

function quantityupdate(){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $SKU_ID='SKU'.$data->SKU_ID;
    $Quantity=$data->qty;
	$orderNumber=$data->orderNumb;
    
    try {         
        if ($SKU_ID!="")
        {
                $db = getDB();
                /*Update cart values*/
                $sql1="UPDATE `tbl_orderHistory` SET Quantity = '".$Quantity."'  WHERE orderNumber='".$orderNumber."' AND SKU_ID='".$SKU_ID."'";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute();
				$quantityupdate['msg'] = "Product updated successfully";
				$quantityupdate['orderNumber'] = $orderNumber;			
            
                $db = null;
         
            if($quantityupdate){
               $quantityupdate = json_encode($quantityupdate);
                echo '{"quantityupdate": ' .$quantityupdate . '}';
            } else {
               echo '{"error":{"text":"Enter valid data"}}';
            }        
        }
        else{
            echo '{"error":{"text":"Enter valid datas"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }	
}

function removeproduct(){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $SKU_ID='SKU'.$data->SKU_ID;
	$orderNumber=$data->orderNumb;
    
    try {         
        if ($SKU_ID!="")
        {
                $db = getDB();
                /*Update cart values*/
                $sql1="DELETE FROM `tbl_orderhistory` WHERE orderNumber='".$orderNumber."' AND SKU_ID='".$SKU_ID."'";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute();
				$removeproduct['msg'] = "Product removed successfully";
				$removeproduct['orderNumber'] = $orderNumber;			
            
                $db = null;
         
            if($removeproduct){
               $removeproduct = json_encode($removeproduct);
                echo '{"removeproduct": ' .$removeproduct . '}';
            } else {
               echo '{"error":{"text":"Enter valid data"}}';
            }        
        }
        else{
            echo '{"error":{"text":"Enter valid datas"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }	
}

function shippingaddress(){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $firstName=$data->firstName;
    $lastName=$data->lastName;
	$address1=$data->address1;
    $address2=$data->address2;
    $zipcode=$data->zipcode;
    $city=$data->city;
	$state=$data->state;
	$phone=$data->phone;
    
    try {         
        if ($user_id!="")
        {
           $db = getDB();
		   $sql1="INSERT INTO `tbl_useraddress` 
		   (`user_id`, `FirstName`, `LastName`, `Address1`, `Address2`, `Zipcode`, `City`, `State`, `Phone`) VALUES 
		   ('".$user_id."', '".$firstName."', '".$lastName."', '".$address1."', '".$address2."', '".$zipcode."', '".$city."', '".$state."', '".$phone."')";
           $stmt1 = $db->prepare($sql1);
           $stmt1->execute();
		   $shippingaddress['msg'] = "Shipping address added successfully";
		   $shippingaddress['orderNumber'] = $orderNumber;			
            
                $db = null;
         
            if($shippingaddress){
               $shippingaddress = json_encode($shippingaddress);
                echo '{"shippingaddress": ' .$shippingaddress . '}';
            } else {
               echo '{"error":{"text":"Enter valid data"}}';
            }        
        }
        else{
            echo '{"error":{"text":"Enter valid datas"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }	
}

function paymentsubmit(){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $orderNumber=$data->orderNumb;
    $orderStatus='Completed';
	$paymentMethod='CreditCard';
    $paymentStatus='Completed';
    $cardNumber=$data->creditcard;
    $cardExpMonth=$data->month;
    $cardExpYear=$data->year;
    $cardCvv=$data->cvv;
    
    try {         
        if (isset($user_id) && $user_id!="" && isset($orderNumber) && $orderNumber!="")
        {
                $db = getDB();
                
                
            $sql = "SELECT * FROM tbl_ordertable WHERE orderNumber=:orderNumber AND user_id=:user_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("orderNumber", $orderNumber,PDO::PARAM_STR);
            $stmt->bindParam("user_id", $user_id,PDO::PARAM_STR);
			$stmt->execute();
            $orderexistCnt=$stmt->rowCount();
            if($orderexistCnt==0)
            {
                $sql1="INSERT INTO `tbl_ordertable` (`user_id`, `orderNumber`, `orderStatus`, `paymentMethod`, `paymentStatus`, `cardNumber`, `cardExpMonth`, `cardExpYear`, `cardCvv`) VALUES ('".$user_id."', '".$orderNumber."', '".$orderStatus."', '".$paymentMethod."', '".$paymentStatus."', '".$cardNumber."', '".$cardExpMonth."', '".$cardExpYear."', '".$cardCvv."')";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute();
				$paymentsubmit['msg'] = "Ordered successfully";
				$paymentsubmit['orderNumber'] = $orderNumber;	
            }else{
             
                $sql1="UPDATE `tbl_ordertable` SET `orderStatus` = 'Completed', `paymentMethod` = 'Completed', `paymentStatus` = 'Completed', `cardNumber` = '".$cardNumber."', `cardExpMonth` = '".$cardExpMonth."', `cardExpYear` = '".$cardExpYear."', `cardCvv` = '".$cardCvv."' WHERE orderNumber='".$orderNumber."' AND user_id='".$user_id."'";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute();
				$paymentsubmit['msg'] = "Order updated successfully";
				$paymentsubmit['orderNumber'] = $orderNumber;	
            }


                
                $db = null;
         
            if($paymentsubmit){
               $paymentsubmit = json_encode($paymentsubmit);
                echo '{"paymentsubmit": ' .$paymentsubmit . '}';
            } else {
               echo '{"error":{"text":"Enter valid data"}}';
            }        
        }
        else{
            echo '{"error":{"text":"Enter valid datas"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }	
}

function orderhistory(){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
	$user_id=$data->user_id;
    //$user_id= 1;
	    
    try {          
        if ($user_id!="")
        {			
            $db = getDB();
            $sql = "SELECT * FROM tbl_ordertable WHERE user_id=:user_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $user_id,PDO::PARAM_STR);
			$stmt->execute();
            $productCnt=$stmt->rowCount();
            $created=time();
            if($productCnt==0){
				$cartProduct['msg'] = "No orders found";
            }else{
                /*Update cart values*/
                $sql1="SELECT * FROM tbl_ordertable WHERE user_id =:user_id order by id desc";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("user_id", $user_id,PDO::PARAM_STR);
                $stmt1->execute();
				$orderhistory = $stmt1->fetchAll(PDO::FETCH_OBJ);				
			}
            
            $db = null;
            if($orderhistory){
               $orderhistory = json_encode($orderhistory);
                echo '{"orderhistory": ' .$orderhistory. '}';
            } else {
               echo '{"error":{"text":"No orders found"}}';
            }        
        }
        else{
            echo '{"error":{"text":"No orders found"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
	
}

function productDetails(){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
	$PRODUCT_ID=$data->PRODUCT_ID;
    $SKU_ID=$data->SKU_ID;
    /*$PRODUCT_ID= 'PROD57611';
    $SKU_ID= 'SKU92155';*/
	    
    try {          
        if ($PRODUCT_ID !="" && $SKU_ID !="")
        {			
            $db = getDB();
            $sql = "SELECT * FROM tbl_products a INNER JOIN tbl_product_skus b ON a.PRODUCT_ID = b.PRODUCT_ID WHERE b.SKU_ID =:SKU_ID AND b.PRODUCT_ID =:PRODUCT_ID";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("PRODUCT_ID", $PRODUCT_ID,PDO::PARAM_STR);
            $stmt->bindParam("SKU_ID", $SKU_ID,PDO::PARAM_STR);
			$stmt->execute();
            $productCnt=$stmt->rowCount();
            $created=time();
            if($productCnt==0){
				$productDetails = "No products found";
            }else{
                $sql1="SELECT * FROM tbl_products a INNER JOIN tbl_product_skus b ON a.PRODUCT_ID = b.PRODUCT_ID WHERE b.SKU_ID =:SKU_ID";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("SKU_ID", $SKU_ID,PDO::PARAM_STR);
                $stmt1->execute();
				$productDetails = $stmt1->fetchAll(PDO::FETCH_OBJ);				
			}
            
            $db = null;
            if($productDetails){
               $productDetails = json_encode($productDetails);
                echo '{"productDetails": ' .$productDetails. '}';
            } else {
               echo '{"error":{"text":"No products found."}}';
            }        
        }
        else{
            echo '{"error":{"text":"Please pass product_id & sku_id"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
	
}

function getUserAddressList(){
    $request = \Slim\Slim::getInstance()->request();   
    $data = json_decode($request->getBody());
	$user_id=1;

    try {
        $userAddressList = '';
        $db = getDB();
        $sql = "SELECT b.id, a.user_id, a.defaultShippingAddress, a.defaultBillingAddress, b.FirstName, b.LastName, b.Address1, b.Address2, b.Zipcode, b.City, b.State, b.Phone FROM users a INNER JOIN tbl_useraddress b ON a.user_id = b.user_id where a.user_id='".$user_id."' order by b.id desc";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $userAddressArray = $stmt->fetchAll(PDO::FETCH_OBJ);
        $finalAddressList['AddressLists'] = array();
        $finalAddressList['dShippingAddress'] = array();
        $finalAddressList['dBillingAddress'] = array();
            foreach($userAddressArray  as $userAddressArray_Values){
                $addressList['id']     = $userAddressArray_Values->id;  
                $addressList['user_id']      = $userAddressArray_Values->user_id;
                $addressList['FirstName']    = $userAddressArray_Values->FirstName;
                $addressList['LastName']     = $userAddressArray_Values->LastName;
                $addressList['Address1']      = $userAddressArray_Values->Address1;
                $addressList['Address2']    = $userAddressArray_Values->Address2;
                $addressList['Zipcode']     = $userAddressArray_Values->Zipcode;       
                $addressList['City']    = $userAddressArray_Values->City;
                $addressList['State']     = $userAddressArray_Values->State;  
                $addressList['Phone']    = $userAddressArray_Values->Phone;    
                //$addressList['defaultShippingAddress']    = $userAddressArray_Values->defaultShippingAddress;     
                //$addressList['defaultBillingAddress']    = $userAddressArray_Values->defaultBillingAddress;                     
                array_push($finalAddressList['AddressLists'],$addressList);
            }

			$sql1 = "SELECT b.id, b.FirstName, b.LastName, b.Address1, b.Address2, b.Zipcode, b.City, b.State, b.Phone FROM tbl_useraddress b INNER JOIN users a ON a.defaultShippingAddress=b.id where a.user_id='".$user_id."'";
			$stmt1 = $db->prepare($sql1);
			$stmt1->execute();
			$defaultShipping = $stmt1->fetchAll(PDO::FETCH_OBJ);	
			foreach($defaultShipping  as $defaultShippingValues){
                $shippingAddress['id']     = $defaultShippingValues->id;  
                $shippingAddress['FirstName']    = $defaultShippingValues->FirstName;
                $shippingAddress['LastName']     = $defaultShippingValues->LastName;
                $shippingAddress['Address1']      = $defaultShippingValues->Address1;
                $shippingAddress['Address2']    = $defaultShippingValues->Address2;
                $shippingAddress['Zipcode']     = $defaultShippingValues->Zipcode;       
                $shippingAddress['City']    = $defaultShippingValues->City;
                $shippingAddress['State']     = $defaultShippingValues->State;  
                $shippingAddress['Phone']    = $defaultShippingValues->Phone; 
				array_push($finalAddressList['dShippingAddress'],$shippingAddress);   					
			}
            
            $sql2 = "SELECT b.id, b.FirstName, b.LastName, b.Address1, b.Address2, b.Zipcode, b.City, b.State, b.Phone FROM tbl_useraddress b INNER JOIN users a ON a.defaultBillingAddress=b.id where a.user_id='".$user_id."'";
			$stmt2 = $db->prepare($sql2);
			$stmt2->execute();
			$defaultBilling = $stmt2->fetchAll(PDO::FETCH_OBJ);
			foreach($defaultBilling  as $defaultBillingValues){
               $billingAddress['id']     = $defaultBillingValues->id;  
               $billingAddress['FirstName']    = $defaultBillingValues->FirstName;
               $billingAddress['LastName']     = $defaultBillingValues->LastName;
               $billingAddress['Address1']      = $defaultBillingValues->Address1;
               $billingAddress['Address2']    = $defaultBillingValues->Address2;
               $billingAddress['Zipcode']     = $defaultBillingValues->Zipcode;       
               $billingAddress['City']    = $defaultBillingValues->City;
               $billingAddress['State']     = $defaultBillingValues->State;  
               $billingAddress['Phone']    = $defaultBillingValues->Phone;
			   array_push($finalAddressList['dBillingAddress'],$billingAddress);
			}
            
        $db = null;
        if($finalAddressList)
            echo '{"userAddressList": ' . json_encode($finalAddressList) . '}';
        else
            echo '{"userAddressList": "No address found"}';        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function changePassword() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email              = $data->email;
    $password           = $data->old_password;
    $confirm_password   = $data->cpassword;
    
   /* $email              = 'santhosh@css.com';
    $password           = '12345678';
    $confirm_password   = '1234567';
*/
    try {
        if (isset($email) && $email!="" && isset($password) && $password!="") {
        $db = getDB();
        $validateOldPassword ='';
        $sql = "SELECT * FROM users WHERE email=:email and password=:password ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $email, PDO::PARAM_STR);
        $password=hash('sha256',$password);
        $stmt->bindParam("password", $password, PDO::PARAM_STR);
        $stmt->execute();
        $validateOldPassword = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($validateOldPassword) {
                $cpassword = hash('sha256',$confirm_password);
                $sql1="UPDATE `users` SET password = '".$cpassword."' WHERE email='".$email."'";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute();
                $db = null;
				echo '{"error":{"text":"Your password changed successfully","val":1}}';
        } else {
			echo '{"error":{"text":"Sorry!!! Your old password is incorrect","val":2}}';
        }  
        }else{
			echo '{"error":{"text":"Please enter some data","val":3}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function menuCatSubcatLists(){
    $request = \Slim\Slim::getInstance()->request();   
	$menuData1 =array();
	$menuData2 = array();
	$menuData3 = array();
	$final_array = array();
    try {
        $db = getDB();
		$sql = "SELECT * FROM `tbl_menu` where `menuStatus`=1 ORDER BY `sortOrder` ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $menuData1_array = $stmt->fetchAll(PDO::FETCH_OBJ);
		foreach($menuData1_array  as $menuData1_arrayValues){
			$menuData1['id'] 		= $menuData1_arrayValues->id;
			$menuData1['menuName'] = $menuData1_arrayValues->menuName;
			$menuData1['menuUrl'] = $menuData1_arrayValues->menuUrl;
			$menuData1['category'] = array();
			$sql1 = "SELECT * FROM `tbl_category` WHERE `menuId`='".$menuData1_arrayValues->id."' ORDER BY `sortOrder` ASC";
			$stmt1 = $db->prepare($sql1);
			$stmt1->execute();
			$menuData2_array = $stmt1->fetchAll(PDO::FETCH_OBJ);	
			foreach($menuData2_array  as $menuData2_arrayValues){
				$menuData2['id']=$menuData2_arrayValues->id;
				$menuData2['categoryName']=$menuData2_arrayValues->categoryName;
				$menuData2['subCategory'] = array();
				$sql12 = "SELECT * FROM `tbl_subcategory` WHERE `categoryId`='".$menuData2_arrayValues->id."' ORDER BY `sortOrder` ASC";
				$stmt12 = $db->prepare($sql12);
				$stmt12->execute();
				$menuData3_array = $stmt12->fetchAll(PDO::FETCH_OBJ);
				foreach($menuData3_array  as $menuData3_arrayValues){
					$menuData3['id']=$menuData3_arrayValues->id;
					$menuData3['subcategoryName']=$menuData3_arrayValues->subcategoryName;
					array_push($menuData2['subCategory'],$menuData3);		
				}
				array_push($menuData1['category'],$menuData2);				
			}					
			array_push($final_array,$menuData1);
		}			
        $db = null;
        if($final_array)
            echo '{"catMenuLists": ' . json_encode($final_array) . '}';
        else
            echo '{"catMenuLists": ""}';        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

?>
