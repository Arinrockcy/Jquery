<?php 
if(isset($_POST) && $_SERVER['REQUEST_METHOD'] == "POST")
{
	require_once('../includes/config/Config.inc.php');
	require_once ('PHPMAIL/class.phpmailer.php');
	$obj= new dy_service();
}
class dy_service
{
	private $last_id;
	function __construct()
	{
	$Check=$this->array_clean();
	if($function_name=$this->protection($_POST['to']))
	$this->$function_name();
	}
	
    private function login()
	{
		$table_name='Admin';
		$feilds='*';
		$userName=$this->protection($_POST['key']);
		$condition="userName = '".$userName."'";
		$result=$this->select_Record($feilds,$table_name,$condition);
		$msg='Check The User Name';
		if($result!=NULL)
		if($result['length']>=1)
		{
			$password=$_POST['key1'];
			if($result[0]['status']!=0)
			{
			$password = hash('sha512', $password . $result[0]['salt']);	
			if($password==$result[0]['password'])	
			{
			 setcookie('BId', serialize(array(0=>$result[0]['IdAdmin'])), time() + (864 * 30), "/");
			$browserAgent = $_SERVER['HTTP_USER_AGENT'];
			$se=hash('sha512',$userName.$browserAgent);
			setcookie('uid', $se, time() + (864 * 30), "/");
			$msg=1;
			}
				else{$msg='Sorry! Wrong Password';}
			}
			else{$msg='Sorry! Your Account Has been locked';}
		}
		
		echo json_encode(array('Permission'=>$msg));
	}
private function logout(){
		       setcookie('uid', '', time()-1000, '/');
        setcookie('BId', '', time()-1000, '/');
    
	echo json_encode(array('result'=>1));
		}	
	function checkLogin()
	{
		$id=unserialize($_COOKIE['uid']);
			$browserAgent = $_SERVER['HTTP_USER_AGENT'];
			$se=hash('sha512',$userName.$browserAgent);
		}
	
		
	//admin work
	private function CheckMail(){
		$headers = getallheaders();
		$d=new drived();
		$email= $this->protection($headers["Verify-Key"]);
		
		$condition="email = '".$email."'";
		$result=$this->select_Record('*','Admin',$condition);
		$messgae='Wrong Email';
		if($email==$result[0]['email'])
		{
			$option['crt']='verify';
			$messgae=$d->create_msg_Admin($option);
			$d->sendphpMail($email,$messgae);
			$messgae='Please check the mail for Email';
			setcookie('BrowserId', $result[0]['IdAdmin'], time() + (8640 * 30), "/");
			
			}
		echo json_encode(array('Result'=>$messgae));	
		}
	private function checkCode()
	{
		$headers = getallheaders();
		
		$rn= $this->protection($headers["Verify-Key"]);
		$code=md5(website_code.$rn);
		$message='Please Check code';
		if($code==$_COOKIE['browserCode'])
		{
			$message=1;
			}
		echo json_encode(array('Result'=>$message));		
		}	

	private function resetpassword()
	{
		$clienId=unserialize($_COOKIE['BId']);
		$condition="IdAdmin = ".$clienId[0];
		$password=$this->protection($_POST['oldPwd']);
		$result=$this->select_Record('IdAdmin, password, salt','admin',$condition);
		$password = hash('sha512', $password . $result[0]['salt']);	
		$msg='Sorry! Check Old Password';
		if($password==$result[0]['password'])	
		{
		$password=$this->protection($_POST['NewPwd']);
		$salt=$this->salt();
		$password=hash('sha512',$password.$salt);
		$condition="password = '".$password."' , salt='".$salt."' where IdAdmin=".$this->protection($clienId[0]);
		$update=$this->update_Record('admin',$condition);
		if($update)
			$msg=1;
		else
			$msg=0;
		}
	echo json_encode(array('Result'=>$msg));
	}
private function changePassword()
{
	$headers = getallheaders();
		$clienId=unserialize($_COOKIE['BId']);		
		$password= $this->protection($headers["Verify-Key"]);
		$salt=$this->salt();
		$password=hash('sha512',$password.$salt);
		$condition="password = '".$password."' , salt='".$salt."' where IdAdmin=".$this->protection($clienId[0]);
		$update=$this->update_Record('admin',$condition);
		if($update)
			$msg=1;
		else
			$msg=0;
	echo json_encode(array('Result'=>$msg));		
	}
	private function salt()
	{
	     $browserAgent = $_SERVER['HTTP_USER_AGENT'];
		 return hash('sha512',$browserAgent);
		
	}
		
	//delete functions
	private function Deleteclient()
	{
		$table='client';
		$condition='iduser='.$this->protection($_POST['key']);
		$result=$this->delete($table,$condition);
		if($result)
			$msg=1;
		else
			$msg=0;
		echo json_encode(array('Result'=>$msg));
	}
	//Insert functions
	private function add_product()
	{
	     $data=$_POST;
		$qty=$data['qty'];
	    unset($data['to']);
		unset($data['qty']);	
	    unset($data['file[]']);
	    $array_key=array_keys($data);
	    $columns= stripslashes(implode(", ",$array_key));
		
	    $vas = array();
	    foreach($data as $val) {
	        if (ctype_digit($val))
	            $vas[]=$val;
	        else
	            $vas[]="'".$val."'";
	    }
	    $vas=$this->array_protect($vas);
	    $columns.=', date';
	     $values= stripslashes(implode(", ",$vas));
	     $values.=", '".date('d-m-Y')."'";
	 $result=$this->insert_Recorde('product',$columns,$values);
	 $type='error';
	 $message='Soory! file to Insert Record';
	 if($result)
	 {
		 $column='qty, IdProduct, date';
		 $productId=$this->last_id;
		$value="'".$qty."',".$productId.",'".date('m-d-Y')."'";
	  $result1=$this->insert_Recorde('stock',$column,$value);
	  $type='error';
	     $message='Soory! file to Upload Images';
	     $INames = array();
	     $resultI = array();
	     $locations='../../Images/Product/';
	     for($i=0;$i<count($_FILES['file']['name']);$i++)
	     {
	     $INames[$i]= $_FILES['file']['name'][$i];
	     $tmp_name=$_FILES["file"]['tmp_name'][$i];
	     $message="";
	     $resultI= $this->fileupload($tmp_name,$locations,$INames[$i]);
	     if($resultI==0)
	     {
	         $type='error';
	         unset($INames[$i]);
	         $message.=$INames[$i]."Failed to upload";
	     }
	     }
	     
	     $INames=serialize($INames);
	      $id=$this->last_id('idProduct','product');
	     
	     $condtion=" images='".$INames."' where idProduct=".$id;
	     $uRes=$this->update_Record('product',$condtion);
	     
	     if($uRes==1)
	     {
	         $type='success';
	         $message.="  Record Updated";
	     }
	     else
	     {
	         $type='error';
	         $message.="  Faild to  Updated";
	 }
	 }
	 echo json_encode(array('result'=>$message,'type'=>$type));
	 } 
	 private function UploadProductImage()
	 {
	     $locations='../../Images/Product/';
	     for($i=0;$i<count($_FILES['file']['name']);$i++)
	     {
	     $INames[$i]= $_FILES['file']['name'][$i];
	         $tmp_name=$_FILES["file"]['tmp_name'][$i];
	             $message="";
	         $resultI= $this->fileupload($tmp_name,$locations,$INames[$i]);
	         if($resultI==0)
	         {
	         $type='error';
	             unset($INames[$i]);
	             $message.=$INames[$i]."Failed to upload";
	     }
	     }
	     
	           $id=$_POST['id'];
	           $con=" idProduct =  ".$id;
	           $result=$this->select_Record('images','product',$con);
 	           $imagearray=unserialize($result[0]['images']);
 	           $img_array=implode(",",array_merge($INames,$imagearray));
 	        $INames=serialize(array_merge($INames,$imagearray));
 	        
	     	    $condtion=" images='".$INames."' where idProduct=".$id;
	     	    $uRes=$this->update_Record('product',$condtion);
	     	    if($uRes==1)
	     	    {
	     	        $type='success';
	     	        $message.="  Record Updated";
	     	    }
	     	    else
	     	    {
	     	        $type='error';
	     	        $message.="  Faild to  Updated";
	     	    }
	     	    echo json_encode(array('result'=>$message,'type'=>$type,'img_array'=>$img_array,'key'=>$id));
	     	    }
	private function NewCoupon()
	{
		unset($_POST['to']);
		
		$column="CouponCode,Coupon_Name,Coupon_Description,Coupon_Currancy,IdType,Buyqty,Date,";
		$values="'".$this->CouponCode($_POST['Coupon_Name'])."','".$_POST['Coupon_Name']."','".$_POST['Coupon_Description']."','".$_POST['Coupon_Currancy']."',".$_POST['IdType'].",".$_POST['Buyqty'].",'".date('d-m-Y')."',";
		if($_POST['IdType']==1)
		{
			$column.="Coupon_Value";
			$values.="'".$_POST['Coupon_Value']."'";
			}
		else if($_POST['IdType']==2)
		{
			$column.="getQty";
			$values.=$_POST['getQty'];
			}	
			$result=$this->insert_Recorde('coupon',$column,$values);
			echo json_encode(array('result'=>$result));	
		}
	private function CouponCode($name)
	{
		$obj1=new drived();
		
		 return $name=substr($name, 0, 3).$obj1->RandomKey(3);
		
		}
	private function NewAdmin()
	{
		$column='userName, password, salt, email, status, Type_2, pwdFlag, date';
		$salt=$this->salt();
		$password = hash('sha512', 'admin'.$salt);
		$values="'".$_POST['uname']."', '".$password."', '".$salt."', '".$_POST['email']."', ".$_POST['clientstatus_idclientstatus'].", ".$_POST['Type_2'].", 0, '".date('d-m-Y')."'";
		$result=$this->insert_Recorde('admin',$column,$values);
		echo json_encode(array('result'=>$result));
		}		     	   
	 //View Functions
	private function view_users()
	{
		$table='client INNER JOIN clientstatus ON client.staus = clientstatus.idclientstatus INNER JOIN admintype ON admintype.idAdminType=client.Type_2';
		$feilds=' client.iduser,client.name,client.email,client.Type_2,client.date,clientstatus.Clientstatus,admintype.type';
	$result=$this->select_Record($feilds,$table,"client.name like '%%'");
		$i=0;
	$html="<thead>
	
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Type</th>
        <th>date</th>
		<th>Action</th>
      </tr>
    </thead>
	<tfoot>
            <tr>
         <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Type</th>
        <th>date</th>
		<th>Action</th>
            </tr>
        </tfoot>
	 <tbody>";
	while($i<$result['length'])
	{
		$html.='<tr for="'.$result[$i]['iduser'].'"><td>'.($i+1).'</td><td>'.$result[$i]['name'].'</td><td>'.$result[$i]['email'].'</td><td>'.$result[$i]['Clientstatus'].'</td><td>'.$result[$i]['type'].'</td><td>'.$result[$i]['date'].'</td><td><button type="Button" class="btn btn-info updateModel" data-toggle="modal" data-target="#myModal">Edit</button></td></tr>';
		
		$i++;}
		echo json_encode(array('result'=>$html));
	}
	private function view_Admin()
	{
		$table='admin INNER JOIN clientstatus ON admin.status = clientstatus.idclientstatus INNER JOIN admintype ON admintype.idAdminType=admin.Type_2';
		$feilds=' admin.IdAdmin,admin.userName,admin.email,admin.Type_2,admin.date,clientstatus.Clientstatus,admintype.type';
	$result=$this->select_Record($feilds,$table,"admin.userName like '%%'");
		$i=0;
	$html="<thead>
	
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Type</th>
        <th>date</th>
		<th>Action</th>
      </tr>
    </thead>
	<tfoot>
            <tr>
         <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Type</th>
        <th>date</th>
		<th>Action</th>
            </tr>
        </tfoot>
	 <tbody>";
	while($i<$result['length'])
	{
		$html.='<tr for="'.$result[$i]['IdAdmin'].'"><td>'.($i+1).'</td><td>'.$result[$i]['userName'].'</td><td>'.$result[$i]['email'].'</td><td>'.$result[$i]['Clientstatus'].'</td><td>'.$result[$i]['type'].'</td><td>'.$result[$i]['date'].'</td><td><button type="Button" class="btn btn-info updateModel" data-toggle="modal" data-target="#myModal">Edit</button></td></tr>';
		
		$i++;}
		echo json_encode(array('result'=>$html));
	}
	
	private function view_user()
	{
		$feilds="client.*, Status AS clientstatus.Clientstatus";
	$tabel="client INNER JOIN clientstatus ON client.iduser=clientstatus.idclientstatus";
	$condition="client.iduser = '".$_POST['key']."'";
		$result=$this->select_Record($feilds,$table,$condition);
		$i=0;
	$html="";
	while($i<sizeof($result))
	{$html.="<div><img src='../Images/userProfile/".$result[$i]['pic']."'/>".$result[$i]['name']."</div>";$i++;}
		echo json_encode(array('result'=>$html));
	}
	 
	private function ViewProducts()
	{
	   $table='product INNER JOIN subcategory ON product.Scategory_id = subcategory.idScategory INNER JOIN brand ON product.Brand_Id=brand.IdBrand INNER JOIN category ON product.category_Id=category.idcategory INNER JOIN style ON product.Idstyle=style.IdStyle INNER JOIN stock ON product.idProduct=stock.IdProduct';
	    $feilds=' product.*,subcategory.name As SCName,brand.IdBrand,brand.Brand,category.Name AS Category, style.Style, stock.qty AS stock, stock.IdStock';
	    $result=$this->select_Record($feilds,$table,"product.name like '%%'");
	    $i=0;
	    $html="<thead>
	    <tr>
        <th>SL.No.</th>
        <th>Product Name</th>
        <th>Brand</th>
        <th>Sub Categories</th>
        <th>categories</th>
        <th>Style</th>
		<th>Price</th>
	    <th>Quantity</th>    
	    
		<th>#</th>           
      </tr>
    </thead>
	<tfoot>
            <tr>
         <th>SL.No.</th>
        <th>Product Name</th>
        <th>Brand</th>
        <th>Sub Categories</th>
        <th>categories</th>
        <th>Style</th>
		<th>Price</th>
	    <th>Quantity</th>    
	    
		<th>#</th>
            </tr>
        </tfoot>
	 <tbody>";
	    while($i<$result['length'])
	    {
	        
	        $img_array=implode(",",unserialize($result[$i]['images']));
	        $id=$result[$i]['idProduct'];
	        $html.='<tr for="'.$id.'"><td><input type="radio" class="selected-row" name="key-edit-delete"></td><td>'.$result[$i]['name'].'</td><td>'.$result[$i]['Brand'].'</td><td>'.$result[$i]['SCName'].'</td><td>'.$result[$i]['Category'].'</td><td>'.$result[$i]['Style'].'</td><td>'.$result[$i]['Price'].'</td><td for="'.$result[$i]['IdStock'].'">'.$result[$i]['stock'].'</td><td><input type="hidden" id="specification'.$id.'" value="'.$result[$i]['specifications'].'"><input type="hidden" id="infomation'.$id.'" value="'.$result[$i]['infomation'].'"><input type="hidden" id="images'.$id.'" value="'.$img_array.'"> <button type="Button" class="btn btn-default Edit-image"  data-toggle="modal" data-target="#updateimage">Edit Image</button></td></tr>';
	    
	        $i++;}
	        echo json_encode(array('result'=>$html,'len'=>$result['length']));
	}
	private function viewProduct()
	{
		$table='product INNER JOIN subcategory ON product.Scategory_id = subcategory.idScategory INNER JOIN brand ON product.Brand_Id=brand.IdBrand INNER JOIN category ON product.category_Id=category.idcategory INNER JOIN style ON product.Idstyle=style.IdStyle INNER JOIN stock ON product.idProduct=stock.IdProduct';
	    $feilds=' product.*,subcategory.name As SCName,brand.IdBrand,brand.Brand,category.Name AS Category, style.Style, stock.qty AS stock, stock.IdStock';
		$cond="product.idProduct = ".$this->protection($_POST['key']);
	    $result=$this->select_Record($feilds,$table,$cond);
	    $i=0;
		$img_array=unserialize($result[0]['images']);
		$html="<label>Product Code: ".$result[0]['ProductCode']."</label><br><img class='img-thumbnail img-responsive' src='../Images/Product/".$img_array[0]."' style='width:200px;height:200px;'><br><label>Name: ".$result[0]['name']."</label><br><label>In Stack: ".$result[0]['stock']."</label>";
		echo json_encode(array('result'=>$html));
		
		}
	private function GenrateImageName($name)
	{
		$key=substr($name, -1);
		$name=substr($string, 0, -1);
		return $name.=($key+1);
		    }
	//order Management
	private function orders()
	{
		$feilds="OrderDetails.*, OrderDetails.Date AS OrderDate,client.name AS Name,client.email AS Email,orderstatus.Status_Order AS Status,shipment_status.delivery_status,shipment_status.idshipment_status";
	$tabel="OrderDetails INNER JOIN client ON OrderDetails.iduser=client.iduser INNER JOIN orderstatus ON OrderDetails.idOrderStatus=orderstatus.idOrderStatus INNER JOIN shipment ON OrderDetails.idOrderDetails=shipment.idOrderDetails INNER JOIN shipment_status ON shipment.stauts_id=shipment_status.idshipment_status";
	$condition="client.name like'%%'";
		$result=$this->select_Record($feilds,$tabel,$condition);
		$i=0;
	$html="<thead>
	    <tr>
        <th>SL.No.</th>
        <th>Client Name</th>
        <th>Quantity</th>
        <th>Total Prize</th>
        <th>Status</th>
        <th>Order Details</th>
		<th>Date</th>
	              
      </tr>
    </thead>
	<tfoot>
            <tr>
         <th>SL.No.</th>
        <th>Client Name</th>
        <th>Quantity</th>
        <th>Total Prize</th>
        <th>Status</th>
        <th>Order Details</th>
		<th>Date</th>
	    
            </tr>
        </tfoot>
	 <tbody>";
	while($i<$result['length'])
	{
		$id=$result[$i]['idOrderDetails'];
			$OrderStatus=$result[$i]['Status'];
			if($result[$i]['idshipment_status']!=0)
			$OrderStatus=$result[$i]['delivery_status'];
			
		$html.='<tr for="'.$id.'"><td><input for="'.$result[$i]['Email'].'" type="radio" class="selected-row" name="order-edit-delete"></td><td>'.$result[$i]['Name'].'</td><td>'.$result[$i]['qty'].'</td><td>'.$result[$i]['Price'].'</td><td>'.$OrderStatus.'</td><td><a class="view-order" href="#ViewOrder?vf='.$result[$i]['Name'].'">View Products</a></td><td>'.$result[$i]['OrderDate'].'</td></tr>';
	$i++;}
		echo json_encode(array('result'=>$html));
	}
	private function orders_id()
	{
$feilds="OrderDetails.*,client.name AS Name, orderta.IdProduct AS OrderPID,orderta.qty AS qty,orderta.Price AS TPrice,shipment_status.delivery_status,shipment_status.idshipment_status,shipment.date AS ShDate,shipment.ShippingAddress AS Address,product.name AS Pname,product.idProduct AS PId, product.Price AS Pprice,product.MRP AS MRP,stock.qty AS InStock, orderstatus.Status_Order AS Status,orderstatus.idOrderStatus AS OrdStatusID,shipment_type.Name AS shipment_typeName ,shipment_type.DCost AS DCost, shipment.RecpName";
$tabel="OrderDetails INNER JOIN client ON OrderDetails.iduser=client.iduser LEFT JOIN orderta ON OrderDetails.idOrderDetails=orderta.IdorderDetail INNER JOIN shipment ON OrderDetails.idOrderDetails=shipment.idOrderDetails INNER JOIN shipment_status ON shipment.stauts_id=shipment_status.idshipment_status LEFT JOIN product ON orderta.IdProduct=product.idProduct INNER JOIN stock on product.idProduct=stock.IdProduct INNER JOIN orderstatus ON OrderDetails.idOrderStatus=orderstatus.idOrderStatus INNER JOIN shipment_type ON shipment.idShipment_type=shipment_type.idShipment_type";
	$condition="OrderDetails.idOrderDetails = ".$this->protection($_POST['key']);
		$result=$this->select_Record($feilds,$tabel,$condition);
		$i=0;
	$orderInfo="";
	$ProductsInfo='';
	$cust='';
	$shippingSta='';
	$total=NULL;
	$mrp='';
	$price='';
	$qty='';
	$subt='';
	$subta=0;
	$gtota=0;	
	$ordTo='';
	
	while($i<$result['length'])
	{
		if($i==0)
		{
			$OrderStatus=$result[$i]['Status'];
			if($result[$i]['idshipment_status']!=0)
			$OrderStatus=$result[$i]['delivery_status'];
			$gtota=$result[$i]['DCost'];

			$shippingSta.="<p>".$result[$i]['shipment_typeName']."</p><p>₹".$result[$i]['DCost']."</p>";	
			$orderInfo.="<p>".$result[$i]['OrderId']."</p><p>".$result[$i]['Date']."</p><p>".$OrderStatus."</p>";
			$cust.="<p>".$result[$i]['Name']."</p><p>".$result[$i]['RecpName']."</p><p>".$result[$i]['Address']."</p>";
			$shipsta=$result[$i]['delivery_status'];	
			$shipdate=$result[$i]['ShDate'];
			$Invsta=$result[$i]['Status'];	
			$Invsdate=$result[$i]['Date'];		
			
			}
		//ProductView
		
		$ProductsInfo.="<p>".$result[$i]['Pname']."</p><p><a for='".$result[$i]['PId']."' data-toggle='modal' data-target='#ProductView' class='ViewProduct_single' href='#ViewSingle'>&nbsp;view Product | Details</a></p>";
		$mrp.="<p>₹".$result[$i]['MRP']."</p>";	
		$price.="<p>₹".$result[$i]['Pprice']."</p>";		
		$qty.="<p>".$result[$i]['qty']."</p>";
		$subta+=$result[$i]['TPrice'];
		$subt.="<p>₹".$result[$i]['TPrice']."</p>";			
		$i++;}
		
		$ordTo.="<p>₹".$subta."</p><p>₹".$gtota."</p><p>₹".($gtota+$subta)."</p><p>₹".($gtota+$subta)."</p>";
		echo json_encode(array('ProductsInfo'=>$ProductsInfo,'orderInfo'=>$orderInfo,'cust'=>$cust,'shippingSta'=>$shippingSta,'price'=>$price,'price'=>$price,'mrp'=>$mrp,'qty'=>$qty,'subt'=>$subt,'ordTo'=>$ordTo,'shipsta'=>$shipsta,'shipdate'=>$shipdate,'Invsta'=>$Invsta,'Invsdate'=>$Invsdate));
	}

	private function order_status(){
		$table='OrderDetails';
		$d=new drived();
		$key=$this->protection($_POST['key']);
		if($_POST['tab']=='idOrderStatus')
		{
			$table='OrderDetails';
		$condition=' idOrderStatus = '.$this->protection($_POST['key1']).' where idOrderDetails='.$key;
		}
		else if($_POST['tab']=='idshipment_status')
		{
			$table='shipment';
		$condition=' stauts_id = '.$this->protection($_POST['key1']).' where idOrderDetails='.$key;
		}
		$result=$this->update_Record($table,$condition);
		if($result)
		{
			$email=$this->protection($_POST['key2']);
			$messgae=$d->create_message($this->protection($_POST['msg']));
			$d->sendphpMail($email,$messgae);
			$msg=1;
		}
		else
			$msg=0;
			
		echo json_encode(array('Result'=>$msg));
		
	}
private function ViewCoupon()
{
	$feilds='coupon.*,coupon_type.Type AS CType,coupon_type.IdCoupon_Type';
	$tabel="coupon INNER JOIN coupon_type ON coupon.IdType=coupon_type.IdCoupon_Type";
	$condition="coupon.Coupon_Name like'%%'";
		$result=$this->select_Record($feilds,$tabel,$condition);
		$i=0;
	$html="<thead>
	    <tr>
        <th>SL.No.</th>
        <th>Coupon Name</th>
        <th>Coupon Type</th>
        <th>Coupon Description</th>
        <th>Coupon Value</th>
        <th>Coupon Currancy</th>
		<th>Date</th>
	              
      </tr>
    </thead>
	<tfoot>
            <tr>
          <th>SL.No.</th>
        <th>Coupon Name</th>
        <th>Coupon Type</th>
        <th>Coupon Description</th>
        <th>Coupon Value</th>
        <th>Coupon Currancy</th>
		<th>Date</th>
	    
            </tr>
        </tfoot>
	 <tbody>";
	while($i<$result['length'])
	{
		$id=$result[$i]['Idcoupon'];
		if($result[$i]['IdType']==1)
		{
			$buy=$result[$i]['Buyqty'];
		$get=$result[$i]['Coupon_Value'];	
	$value_co="Buy ".$buy." Get ".$get." Off <input type='hidden' value='".$get."' id='get".$id."'> <input type='hidden' value='".$buy."' id='buy".$id."'>";}
		else if($result[$i]['IdType']==2)
		{
			$buy=$result[$i]['Buyqty'];
		$get=$result[$i]['getQty'];
		$value_co="Buy ".$buy." Get ".$get." Free <input type='hidden' value='".$get."' id='get".$id."'> <input type='hidden' value='".$buy."' id='buy".$id."'>";
		}
		$html.="<tr for='".$id."'><td><input  type='radio' class='selected-row' name='order-edit-delete'></td><td>".$result[$i]['Coupon_Name']."</td><td>".$result[$i]['CType']."</td><td>".$result[$i]['Coupon_Description']."</td><td> ".$value_co."</td><td>".$result[$i]['Coupon_Currancy']."</td><td>".$result[$i]['Date']."</td></tr>";
		$i++;
	}
	echo json_encode(array('result'=>$html));
	}
	
	
	//load functions
	private function loadOrderSatus()
	{
		
		$result=$this->select_Record('*','orderstatus',"Status_Order like '%%'");
		$i=0;
	$html="<option value=''> Select</option>";
	while($i<$result['length'])
	{
		$html.='<option value="'.$result[$i]['idOrderStatus'].'"> '.$result[$i]['Status_Order'].'</option>';
		$i++;
		}
		
		echo json_encode(array('result'=>$html));
		}
	private function loadOrderSatusAdmin()
	{
		
		$result=$this->select_Record('*','orderstatus',"Status_Order like '%%'");
		$i=0;
	$html="<option value=''> Select</option>";
	while($i<$result['length'])
	{
		$html.='<option class="idOrderStatus" value="'.$result[$i]['idOrderStatus'].'"> '.$result[$i]['Status_Order'].'</option>';
		$i++;
		}
	$result=$this->select_Record('*','shipment_status',"delivery_status like '%%'");
		$i=1;
	
	while($i<$result['length'])
	{
		$html.='<option class="idshipment_status" value="'.$result[$i]['idshipment_status'].'"> '.$result[$i]['delivery_status'].'</option>';
		$i++;
		}	
		
		echo json_encode(array('result'=>$html));
		}	
	private function loadCoupons()
	{
		
		$result=$this->select_Record('*','coupon',"Coupon_Name like '%%'");
		$i=0;
	$html="<option value=''> Select coupon</option>";
	while($i<$result['length'])
	{
		$html.='<option value="'.$result[$i]['Idcoupon'].'"> '.$result[$i]['Coupon_Name'].'</option>';
		$i++;
		}
		
		echo json_encode(array('result'=>$html));
		}	
	private function loadCoupon()
	{
		$feilds='coupon.*,coupon_type.Type AS CType,coupon_type.IdCoupon_Type';
	$tabel="coupon INNER JOIN coupon_type ON coupon.IdType=coupon_type.IdCoupon_Type";
		$cond=' Idcoupon = '.$this->protection($_POST['key']);
		$result=$this->select_Record($feilds,$tabel,$cond);
		$i=0;
	$coupon='';
	while($i<$result['length'])
	{
		if($result[$i]['IdType']==1)
		{
			$buy=$result[$i]['Buyqty'];
		$get=$result[$i]['Coupon_Value'];	
	$value_co="Buy ".$buy." Get ".$get." Off ";}
		else if($result[$i]['IdType']==2)
		{
			$buy=$result[$i]['Buyqty'];
		$get=$result[$i]['getQty'];
		$value_co="Buy ".$buy." Get ".$get." Free ";
		}
		$coupon.='<input type="hidden" name="coid" value="'.$result[$i]['Idcoupon'].'"><p>'.$result[$i]['CouponCode'].'</p><p>'.$result[$i]['Coupon_Name'].'</p></p><p>'.$value_co.'</p><p>'.$result[$i]['CType'].'</p>';
		
		$i++;
		}
		$feilds='coupon.Idcoupon,couponex_cat.IdCategory AS IDCAT ,couponex_cat.IdCouponEx_cat';
	$tabel="coupon INNER JOIN couponex_cat ON coupon.Idcoupon=couponex_cat.Idcoupon";
		$cond='coupon.Idcoupon = '.$this->protection($_POST['key']);
		$result=$this->select_Record($feilds,$tabel,$cond);
		$i=0;
		$couId=NULL;
		$catId=NULL;
	while($i<$result['length'])
	{
	$couId[]=$result[$i]['IdCouponEx_cat'];
	$catId[]=$result[$i]['IDCAT'];	
	$i++;	
	}
	$feilds='coupon.Idcoupon,couponex_cus.IdUser AS IDCAT ,couponex_cus.IdCouponEx_Cus';
	$tabel="coupon INNER JOIN couponex_cus ON coupon.Idcoupon=couponex_cus.Idcoupon";
	$cond='coupon.Idcoupon = '.$this->protection($_POST['key']);
		$result=$this->select_Record($feilds,$tabel,$cond);
		$i=0;
		$couId1=NULL;
		$catId1=NULL;
	while($i<$result['length'])
	{
	$couId1[]=$result[$i]['IdCouponEx_Cus'];
	$catId1[]=$result[$i]['IDCAT'];	
	$i++;	
	}
	$feilds='coupon.Idcoupon,couponex_pro.IdProduct AS IDCAT ,couponex_pro.IdCouponEx_Pro';
	$tabel="coupon INNER JOIN couponex_pro ON coupon.Idcoupon=couponex_pro.Idcoupon";
	$cond='coupon.Idcoupon = '.$this->protection($_POST['key']);
		$result=$this->select_Record($feilds,$tabel,$cond);
		$i=0;
		$couId2=NULL;
		$catId2=NULL;
	while($i<$result['length'])
	{
	$couId2[]=$result[$i]['IdCouponEx_Pro'];
	$catId2[]=$result[$i]['IDCAT'];	
	$i++;	
	}	
		echo json_encode(array('coupon'=>$coupon,'couId'=>$couId,'catId'=>$catId,'cousId'=>$couId1,'cusId'=>$catId1,'procId'=>$couId1,'proId'=>$catId1));
		}	
			
	private function loadSatus()
	{
		$result=$this->select_Record('*','clientstatus',"Clientstatus like '%%'");
		$i=0;
		$html='<option value="">User Permission</option>';
	while($i<$result['length'])
	{
		$html.='<option value="'.$result[$i]['idclientstatus'].'">'.$result[$i]['Clientstatus'].'</option>';
		$i++;
		}
				echo json_encode(array('result'=>$html));
		}
	private function autoProduct()
	{
		$con="name like '%".$_POST['key']."%' || ProductCode like '%".$_POST['key']."%' ";
		$result=$this->select_Record('*','product',$con);
		$i=0;
		$html='';
	while($i<$result['length'])
	{
		$image=unserialize($result[$i]['images']);
$first_key = key($image);
		$html.='<li value="'.$result[$i]['idProduct'].'" for="'.$image[$first_key].'" class="list-group-item">'.$result[$i]['name'].' , '.$result[$i]['ProductCode'].'</li>';
		$i++;
		}
		
		
		echo json_encode(array('result'=>$html));
		}	
	private function autoUser()
	{
		$con="name like '%".$_POST['key']."%' || email like '%".$_POST['key']."%' ";
		$result=$this->select_Record('*','client',$con);
		$i=0;
		$html='';
	while($i<$result['length'])
	{


		$html.='<li value="'.$result[$i]['iduser'].'"  class="list-group-item">'.$result[$i]['name'].' , '.$result[$i]['email'].'</li>';
		$i++;
		}
		
		
		echo json_encode(array('result'=>$html));
		}	
	
	private function loadType()
	{
		$result=$this->select_Record('*','admintype',"type like '%%'");
		$i=0;
		$html='<option value="">User Type</option>';
	while($i<$result['length'])
	{
		$html.='<option value="'.$result[$i]['idAdminType'].'">'.$result[$i]['type'].'</option>';
		$i++;
		}
				echo json_encode(array('result'=>$html));
		}
	private function loadCopType()
	{
		$result=$this->select_Record('*','coupon_type',"Type like '%%'");
		$i=0;
		$html='<option value="">Coupon Type</option>';
	while($i<$result['length'])
	{
		$html.='<option value="'.$result[$i]['IdCoupon_Type'].'">'.$result[$i]['Type'].'</option>';
		$i++;
		}
				echo json_encode(array('result'=>$html));
		}
	//update functions
	private function UpdateCoupon()
	{
		$id=$_POST['couponId'];
		
		unset($_POST['to']);
		unset($_POST['couponId']);
		$array_key=array_keys($_POST);
		$columns= stripslashes(implode(", ",$array_key));
		$i=0;
		$condition="";
		foreach($_POST as $val) {
			if (ctype_digit($val))
		$condition.=$array_key[$i]." = ".$val.", ";
				else
		$condition.=$array_key[$i]." = '".$val."', ";
		$i++;
				 }
		$condition=substr($condition, 0, -2);		 
		 $condition.=' where Idcoupon = '.$id;		 
		$res=$this->update_Record('coupon',$condition);
		if($res)
			$msg=1;
		else
			$msg=0;
		echo json_encode(array('result'=>$res));
		
		}
	
	private function UserUpdateStatus()
	{
		$table='client';
		$condtion="stauts=".$this->protection($_POST['staus']);
		$result=$this->update_Record($table,$condtion);
		if($result)
			$msg=1;
		else
			$msg=0;
		echo json_encode(array('Result'=>$msg));
	}
	private function UpdateUser()
	{
		$id=$_POST['id'];
		unset($_POST['id']);
		unset($_POST['to']);
		$condition="";
		$array_key=array_keys($_POST);

		$columns= stripslashes(implode(", ",$array_key));
		$i=0;
		$condition="";
		foreach($_POST as $val) {
			if (ctype_digit($val))
		$condition.=$array_key[$i]." = ".$val.", ";
				else
		$condition.=$array_key[$i]." = '".$val."', ";
		$i++;
				 }
		$condition=substr($condition, 0, -2);		 
		 $condition.=' where iduser = '.$id;		 
		$res=$this->update_Record('client',$condition);
		if($res)
			$msg=1;
		else
			$msg=0;
		echo json_encode(array('result'=>$res));
		}
	private function UpdateBanner()
	{
		
		$location='../../Images/Banner/';
		$tmp_name=$_FILES["file"]['tmp_name'];
		$fileName=$_FILES["file"]['name'];
		$deletename='../';
		$deletename.=$_POST['ImageName'];
		
		$result=$this->deletefile($deletename);
		$message='Failed to Replace'.$_POST['ImageName'];
		if($result==1)
		{
		$message=$this->fileupload($tmp_name,$location,$fileName);
		$condtion="Banner ='".$fileName."'  where idBanner= ".$_POST['id'];
		$this->update_Record('banner',$condtion);
		
		}
		$imagename='../Images/Banner/'.$fileName;
		echo json_encode(array('Result'=>$message,'key'=>$result,'img'=>$imagename));
		
	}

	private function UpdateProductImg()
	{
	
	     $location='../../Images/Product/';
	    $tmp_name=$_FILES["file"]['tmp_name'];
	    $fileName=$_FILES["file"]['name'];
	    $deletename='../';
	    $deletename.=$_POST['ImageName'];
	if($_POST['ImageName']!='default.jpg')
	    {$result=$this->deletefile($deletename);}
	    $message='Failed to Replace'.$_POST['ImageName'];
	    if($result==1)
	    {
	        $message=$this->fileupload($tmp_name,$location,$fileName);
	        $con=" idProduct =  ".$_POST['id'];
	        $result=$this->select_Record('images','product',$con);
 	        $imagearray=unserialize($result[0]['images']);
 	        $key = array_search(substr($_POST['ImageName'], 18), $imagearray);
 	        $imagearray[$key]=$fileName;
 	        $img_array=implode(",",$imagearray);
 	        $condtion="images ='".serialize($imagearray)."'  where idProduct= ".$_POST['id'];
	        $this->update_Record('product',$condtion);
	
	    }
	    $id=$_POST['id'];
	    echo json_encode(array('Result'=>$message,'img_array'=>$img_array,'key'=>$id,'img'=>$fileName));
	
	}	
	private function UpdateScategory()
	{
		$id=$_POST['old'];
		unset($_POST['old']);
		unset($_POST['to']);
		$condition="";
		$array_key=array_keys($_POST);
		
		$i=0;
		$condition="";
		foreach($_POST as $val) {
			if (ctype_digit($val))
		$condition.=$array_key[$i]." = ".$val.", ";
				else
		$condition.=$array_key[$i]." = '".$val."', ";
		$i++;
				 }
		$condition=substr($condition, 0, -2);		 
		 $condition.=' where idScategory = '.$id;		 
		$res=$this->update_Record('subcategory',$condition);
		echo json_encode(array('result'=>$res));
		}
	private function Updatecategory()
	{
		$id=$_POST['idcategory'];
		unset($_POST['idcategory']);
		unset($_POST['to']);
		$condition="";
		$array_key=array_keys($_POST);

		$i=0;
		$condition="";
		foreach($_POST as $val) {
			if (ctype_digit($val))
		$condition.=$array_key[$i]." = ".$val.", ";
				else
		$condition.=$array_key[$i]." = '".$val."', ";
		$i++;
				 }
		$condition=substr($condition, 0, -2);		 
		 $condition.=' where idcategory = '.$id;		 
		$res=$this->update_Record('category',$condition);
		echo json_encode(array('result'=>$res));
		}
private function ApplyCoupon()
{
	$id=$this->protection($_POST['coid']);
	
	$res=$this->jsondecode($_POST['exCategories']);
	if($res['len']>1)
	{
		$val=$res['val'];
		$val[0]=$id;
		$column='Idcoupon, IdCategory, date';
		$this->updateApplycoupon($res['len'],'couponex_cat',$val,$column);
		}
	$res1=$this->jsondecode($_POST['exProducts']);
	$res2=$this->jsondecode($_POST['exUser']);	
	if($res1['len']>1)
	{
		$val=$res1['val'];
		$val[0]=$id;
		$column='Idcoupon, IdProduct, date';
		$this->updateApplycoupon($res1['len'],'couponex_pro',$val,$column);
		}
	if($res2['len']>1)
	{
		$val=$res2['val'];
		$val[0]=$id;
		$column='Idcoupon, IdUser, date';
		$this->updateApplycoupon($res2['len'],'couponex_cus',$val,$column);
		}	
	}
private function updateApplycoupon($len,$table,$val,$column)
{
	$i=1;
		while($len>$i)
		{
			 $valu=$val[0]." , ".$val[$i].", '".date("d-m-Y H:i:s")."'";
			
		echo $result=$this->insert_Recorde($table,$column,$valu);
			$i++;
			}
	}	
private function jsondecode($data)
{
	
	$ca=stripslashes($data);
	$result['val']=json_decode($ca,true);
	$result['len']=count($result['val']);
	return $result; 
	}			
	//thirdParty lib management
	private function PaymentEdit()
	{
		
		}
	private function ViewPayment()
	{
		
		}
	//select boxs
	private function load_categories(){
	$result=$this->select_Record('*','category',"Name like '%%'");	
		$i=0;
	$html="<option value=''>Select category</option>";
	while($i<sizeof($result)-1)
	{$html.="<option value='".$result[$i]['idcategory']."'>".$result[$i]['Name']."</option>";$i++;}
		echo json_encode(array('result'=>$html));
	}
	private function load_brand(){
	    $result=$this->select_Record('*','brand',"Brand like '%%'");
	    $i=0;
	    $html="<option value=''>Select Brand</option>";
	    while($i<sizeof($result)-1)
	    {$html.="<option value='".$result[$i]['IdBrand']."'>".$result[$i]['Brand']."</option>";$i++;}
	    echo json_encode(array('result'=>$html));
	}
	private function load_Style(){
	    $result=$this->select_Record('*','style',"Style like '%%'");
	    $i=0;
	    $html="<option value=''>Select Style</option>";
	    while($i<sizeof($result)-1)
	    {$html.="<option value='".$result[$i]['IdStyle']."'>".$result[$i]['Style']."</option>";$i++;}
	    echo json_encode(array('result'=>$html));
	}
	private function load_subcategory(){
	$result=$this->select_Record('*','subcategory',"name like '%%'");	
	$i=0;
	$html="<option value=''>Select Sub Category</option>";
	while($i<sizeof($result)-1)
	{$html.="<option value='".$result[$i]['idScategory']."' for='".$result[$i]['idcategory']."'>".$result[$i]['name']."</option>";$i++;}
		echo json_encode(array('result'=>$html));	
	}
	
		
	//Banner Images
	private function uploadBanner()
	{
		$location='../../Images/Banner/';
		$tmp_name=$_FILES["file"]['tmp_name'];
		$fileName=$_FILES["file"]['name'];
		$result=$this->fileupload($tmp_name,$location,$fileName);
		if($result==1)
		{
		    $table='banner';
		    $columns="Banner, date";
		    $values="'".$fileName."','".date('d-m-Y')."'";
		    $result=$this->insert_Recorde($table,$columns,$values);
		    
		}
		echo json_encode(array('Result'=>$result));
	}
	private function DeleteBanner()
	{
		$deletename='../';
		$deletename.=$_POST['ImageName'];
		$result=$this->deletefile($deletename);
		$condtion="idBanner = ".$_POST['id'];
		$this->delete('banner',$condtion);
		$message='Faild to Delete File'.$_POST['ImageName'];
		if($result==1)
		$message=$_POST['ImageName'].'File Sucessfully Deleted';
		echo json_encode(array('Result'=>$message));
	}
	private function DeleteProductImage()
	{
	    $deletename='../';
	    $deletename.=$_POST['ImageName'];
	    $resulti=$this->deletefile($deletename);
	    if($resulti==1)
	    {
	    $id=$_POST['id'];
	    $condtion="idProduct = ".$id;
	    $con=" idProduct =  ".$id;
	    $result=$this->select_Record('images','product',$con);
	    $imagearray=unserialize($result[0]['images']);
	    $fileName=substr($_POST['ImageName'], 18);
	    $key = array_search($fileName, $imagearray);
	   unset($imagearray[$key]); 
	    $img_array=implode(",",$imagearray);
	    $condtion="images ='".serialize($imagearray)."'  where idProduct= ".$id;
	    $res=$this->update_Record('product',$condtion);
	    }
	    echo json_encode(array('Result'=>$resulti,'img_array'=>$img_array,'key'=>$id,'img'=>$fileName));
	}
	
	private function loadBanner()
	{
	    
	    $result=$this->select_Record('*','banner'," Banner like '%%' ");
	    $i=0;
	    $hrml=null;
	    while($i<$result['length'])
	    {
	       $hrml.='<div> <img src="../Images/Banner/'.$result[$i]['Banner'].'" id="'.$result[$i]['idBanner'].' " class="img-responsive update_image" alt="Cinque Terre" width="304" height="236"> <button type="Button" class="btn btn-danger deleteBanner">Delete</button></div>';
$i++;
	    }
	    echo json_encode(array('result'=>$hrml));
	}
	//dynamic functions
	private function SaveData()
	{
		$array_data=$_POST;
		
		unset($array_data['to']);
		unset($array_data['i_to']);
		$array_key=array_keys($array_data);
		$columns= stripslashes(implode(", ",$array_key));
		$vas = array();
		foreach($array_data as $val) {
			if (ctype_digit($val))
		$vas[]=$val;
				else
		$vas[]="'".$val."'";
				 }
		$vas=$this->array_protect($vas);	
	 $values= stripslashes(implode(", ",$vas));
		$table=$_POST['i_to'];
		$columns.=', date';
		$values.=", '".date('d-m-Y')."'";
		$result=$this->insert_Recorde($table,$columns,$values);
		echo json_encode(array('result'=>$result));
		
	}
	private function UpdateData()
	{
	    unset($_POST['to']);
	    $array_key=array_keys($_POST);
	    $tableName=$_POST['i_t'];
	    unset($_POST['i_t']);
	    $key_id=$array_key[0];
	    $id=$_POST[$key_id];
	    unset($_POST[$key_id]);
	    $condition="";
	    $array_key=array_keys($_POST);
	    
	    $i=0;
	    $condition="";
	    foreach($_POST as $val) {
	        if (ctype_digit($val))
	            $condition.=$array_key[$i]." = ".$val.", ";
	        else
	            $condition.=$array_key[$i]." = '".$val."', ";
	        $i++;
	    }
	    $condition=substr($condition, 0, -2);
	    $condition.=' where '.$key_id.' = '.$id;
	    $res=$this->update_Record($tableName,$condition);
	    echo json_encode(array('result'=>$res));
	}
	private function deletefile($target_file){
		if (file_exists($target_file)) 
		{
        $msg=0;
        if(unlink($target_file))
		$msg=1;
						}
		return $msg;
	}
	private function fileupload($tmp_name,$target_dir,$fileName)
	{
		//$filename .='.'. pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
    $msg="";
    $target_file=$target_dir.$fileName;
    $uploadOk = 1;
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
        $msg.= "Directory ".$target_dir." created. ";
        }
        // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $msg .= "File not uploaded.";
        } 
		else {
        if (move_uploaded_file($tmp_name, $target_file)) {
            $msg = 1;
            $msg_type = "success";
	                } 
			else {
            $msg = 0;
            $msg_type = "error";
        }
    }
	return $msg;	
	}
	private function mailPhp($message)
	{
	$text=$obj2->create_message($message);	
	$obj2->sendphpMail($mailID ,$text);
			}	
//CMS Functions
private function ListNames()
{
    $html='<option value="">Select Page</option>';
    
    foreach(glob('../pages/*.*') as $filename){
        $html.='<option value="'.substr($filename,9).'">'.substr($filename,9).'</option>';
    }
    echo json_encode(array('result'=>$html));
}	
private function UploadCSV()
{
 $location='../csv/';
	    $tmp_name=$_FILES["file"]['tmp_name'];
	    $fileName='default.csv';
	    $message=$this->fileupload($tmp_name,$location,$fileName);
		$this->csvInsert();
		    
}
private function csvInsert()
{
$row = 1;
$image=serialize(array(0=>'default.jpg'));
 $columns='ProductCode,name,infomation,Size,Unit,specifications,MRP,Price,category_Id,Scategory_id,Brand_Id,Idstyle,Images';
 $column='qty, IdProduct, date';
$row_a=array();
$idcategory=NULL;
if (($handle = fopen("../CSV/default.csv","r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
	
    if($row==1)
	{
		for ($c=0; $c < $num; $c++) {
		$row_a[$c]=$data[$c];
		}
		
		}
	if($row!=1)
	{
		$values='';
    for ($c=0; $c < $num; $c++) {
		$val=$data[$c];
		if($row_a[$c]=='Category')
		{
			$con=" Name like '%".$val."%'";
			$idcategory=$this->getId('idcategory','category',$con);
		$values.=$idcategory.', ';
		}
		else if($row_a[$c]=='Sub Category')
		{
			$con=" name like '%".$val."%' and idcategory=".$idcategory;
		$values.=$this->getId('idScategory','subcategory',$con).', ';
			}
		else if($row_a[$c]=='Brand')
		{
			$con=" Brand like '%".$val."%'";
		$values.=$this->getId('IdBrand','brand',$con).', ';
			}
		else if($row_a[$c]=='Quantity in Stock')
		{
			$qty=(int)$val;
		
			}		
		else
		{
		   if (ctype_digit($val))
	            $values.=$val.', ';
	        else
	            $values.="'".$val."', ";
		}
      
    }
	
	$values.="'".$image."'";
	$res=$this->insert_Recorde('product',$columns,$values);
	$log=0;
	if($res)
	 {
		 
	//update the
	$productId=$this->last_id;
		 $value="'".$qty."',".$productId.",'".date('m-d-Y')."'";
	  $result1=$this->insert_Recorde('stock',$column,$value);
	  if($result1)
			$log=1; 
	 
	
	 }
	
	}
		$row++;
  }
  fclose($handle);
}
echo json_encode(array('result'=>$log));
}
//database function
	
	private function getId($id,$table,$condition)
	{
		$query="select ".$id." from ".$table." where ".$condition;
		 $reslt=$this->query_exc($query);
		 if($reslt->num_rows>=1)
		 {
		 while($row=$reslt->fetch_assoc()){$val[]=$row;}
		 return $val[0][$id];
		 }
	}
	private function last_id($id,$table)
	{
		
		$result="SELECT ".$id." FROM ".$table." ORDER BY ".$id." DESC LIMIT 1";
		 $reslt=$this->query_exc($result);
		 if($reslt->num_rows>=1)
		 {
		 while($row=$reslt->fetch_assoc()){$val[]=$row;}
		 return $val[0][$id];
		 }
		}
		
	private function insert_Recorde($table,$columns,$values)
		{
		 $query="insert into ".$table." (".$columns.") values (".$values.")";
		return $reslt=$this->query_exc($query);
					}
		private function select_Record($feilds,$table_name,$condition)
		{
$query="select ".$feilds." from ".$table_name." where ".$condition;
		$reslt=	$this->query_exc($query);
		$val=NULL;
		if($reslt->num_rows >=1)
		{
		    $val['length']=$reslt->num_rows;
		    while($row=$reslt->fetch_assoc()){$val[]=$row;}}
		return $val;
			}
		private	function update_Record($table,$condtion)
		{
		$query="update ".$table." set ".$condtion;
		return $reslt=	$this->query_exc($query);
					}
		private function delete($table,$condtion)
		{
			$query="delete from ".$table." where ".$condtion;
			return $reslt=	$this->query_exc($query);
		}
		private function query_exc($query)//for material database
		{
		$link=$this->connect_db();
		$reslt=	$link->query($query);
		
		if(!$reslt){echo json_encode(array('result'=> mysqli_error($link)));}
		else {
		$this->last_id= $link->insert_id;
			return $reslt;
			} 	 		
				}	
		private static function connect_db()
		{	
		try
		{
		static $conn;
		$conn = new mysqli(SQL_HOST,SQL_USER,SQL_PASS,SQL_DB);
		if ($conn) {return ($conn);}
		}
		catch (Exception $e){die('Opps something Gone Wrong');}
		}			
		private function array_clean()
		{
		$link=$this->connect_db();
		array_walk($_POST, function(&$string) use ($link) { 
  		$string = mysqli_real_escape_string($link, $string);});
		return 1;
		}
		private function array_protect($array)
		{
		$link=$this->connect_db();
		array_walk($array, function(&$string) use ($link) { 
  		$string = mysqli_real_escape_string($link, $string);});
		return $array;
		}
		private function protection($string)
		{
			if($string==NULL || $string=='')
			{
				echo json_encode(array('erorr'=>'1'));
				}
				else
			{	
			$link=$this->connect_db();
			return mysqli_real_escape_string($link, $string);
			}
			}
}
class drived {
	function get_month($date)
	
		{	
			$reult=substr($this->month_name($date),0, 3)." ".substr($date,8, 9);
			return $reult;
						}
		function month_name($date)
		{
			$d=date("m", strtotime($date));
			 $dateObj   = DateTime::createFromFormat('!m', $d);
			return $dateObj->format('F');
			}
		function client_IP()
		{
			$ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
			}
	function RandomALKey($length){return substr( "abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ" ,mt_rand( 0 ,50 ) ,1 ) .substr( md5( time() ), 1);}						
	function RandomKey($length){$result = '';for($i = 0; $i < $length; $i++) {$result .= mt_rand(1, 9);}return $result;}
	function sendphpMail($send,$message)
	{
		$this->mail = new PHPMailer(true);
		$this->mail->IsSMTP(); 							// telling the class to use SMTP
	    $this->mail->Host       = MailHost;            // sets the SMTP server
	    $this->mail->SMTPAuth   = true;                // enable SMTP authentication
	    $this->mail->Port       = MailPort;            // set the SMTP port for the Mail server
	    $this->mail->Username   = MailUserName;            // SMTP account username
	    $this->mail->Password   = MailPassword;            // SMTP account password
		$this->mail->AddAddress($send);
		$this->mail->SetFrom(Admin_Mail,Admin_Mail_Name);
	    $this->mail->Subject = $message[0];
		$this->mail->MsgHTML($message[1]);
		$this->mail->Send();
	    $this->mail->ClearAddresses();
	    		}	
	function create_message($message)
	{
		$messageA[1]='Your Product Status:'.$message;
		$messageA[0]='Daily Website';
		return $messageA;	
		}
	function create_msg_Admin($content)
	{
	if($content['crt']=='verify')
	{
		$messageA[0]='Verification-Code';
		$rn=$this->RandomKey(5);
		$code=md5(website_code.$rn);
		setcookie('browserCode', $code, time() + (864 * 30), "/");
		$link="<a href='http://localhost/local/Cpanel/index.php?token=".$code."&ce=".$rn."&tc=".$code."£#ChekAuthKey'>Here</a>";
		$messageA[1]='please Follow this link:'.$link;
		
		}	
	return $messageA;
	}
	
	function date_align($date)
	{
		$date=str_replace(",","",$date);
		$date=str_replace(" ","-",$date);
		return $newDate = date("Y-m-d", strtotime($date));
		}	
	
	}
	


?>