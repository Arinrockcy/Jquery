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
	
    

//PAGINATION FUNCTIONS
private function ViewProducts()
	{
		$con='';
	$this->brand_id=NULL;	
		if($_POST['brnd']!=NULL)
		{
			$con=" and product.categoryId = ".$_POST['brnd'];
			$this->brand_id=$_POST['brnd'];
			}	
	
		
		
		$pric='';
		if($_POST['minprice']!=NULL || $_POST['maxprice']!=NULL)
		$pric=" AND Price BETWEEN ".$_POST['minprice']." AND ".$_POST['maxprice'];
				$sor='';
		if($_POST['sorting']!=NULL)
		{
			$sor='ORDER BY product.Price';
			if($_POST['sorting']=='descending')$sor.=' desc';
		    else if($_POST['sorting']=='ascending')$sor.=' ASC';
			}	
		$feilds='product.idProduct AS idProduct,product.ProductName AS ProductName ,product.Price AS Price,product.images AS images, product.description AS description, product.information AS information';
		$table='product INNER JOIN category ON product.categoryId = category.idcategory';
		 $condition="product.ProductName like '%".$this->protection($_POST['key'])."%'".$con.' '.$pric.' '.$sor;
		$result=$this->select_Record($feilds,$table,$condition);
			
		 $this->count=$result['length'];
		$row=($this->count/4)+1;
		$rem=$this->count%4;
		$last=$this->count-$rem;
		if($rem==0)
		{
		    
			$last=$this->count-4;
		}
		$i=1;
		$row1='<a href="#" class="page-item load_Next" next="0">&laquo;</a>';
		$nxt=0;
		while($row>=$i)
		{
			$row1.='<a class="page-item load_Next" next="'.$nxt.'" href="#">'.$i.'</a>';

		    $nxt+=4;
		    $i++;
		}
		 $row1.='<a href="#" class="page-item load_Next" next="'.$last.'">&raquo;</a>';
		 
		$html=$this->PrintReord($result,$this->count);
		$show2=4;
		if($this->count<$show2)
		$show2=$this->count;
		 echo json_encode(array('result'=>$html,'row'=>$row1,'show1'=>1,'show2'=>$show2,'total'=>$this->count));
	}
	private function PagingDisaply()
	{
	    $con='';
		if(($this->brand_id)!=NULL)
		{
			$con=" and Product.Brand_Id = ".$this->brand_id;
			
			}
		$pric='';
		if($_POST['minprice']!=NULL || $_POST['maxprice']!=NULL)
		$pric=" AND Price BETWEEN ".$_POST['minprice']." AND ".$_POST['maxprice'];
				$sor='';
		if($_POST['sorting']!=NULL)
		{
			$sor='ORDER BY product.Price';
			if($_POST['sorting']=='descending')$sor.=' desc';
		    else if($_POST['sorting']=='ascending')$sor.=' ASC';
			}
	    $end=$_POST['start']+4;
		$show2=$end;
	    if($end>$this->count)
		{
	    $end=$end-$this->count;
		$show2=$end-1;
		}
	 	$feilds='product.idProduct AS idProduct,product.ProductName AS ProductName ,product.Price AS Price,product.images AS images, product.description AS description, product.information AS information';
		$table='product INNER JOIN category ON product.categoryId = category.idcategory';
	    $condition="product.ProductName like '%".$this->protection($_POST['key'])."%'".$pric." ".$con." ".$sor." limit ".$_POST['start']." , ".$end;
	    $result=$this->select_Record($feilds,$table,$condition);
	    $html=$this->PrintReord($result,$result['length']);
		 echo json_encode(array('result'=>$html,'show1'=>($_POST['start']+1),'show2'=>$show2));	  
	    
	   	}
		
	private function PrintReord($record,$recl){
	    
	    $j=0;
	    $html="No Record found";
		$len=4;
		if($recl<$len)
		$len=$recl;
		//echo $record['length'];
		if($record['length']!=NULL)
		{
			$html="";
	    while($j<$len)
	    {
			 $image=unserialize($record[$j]['images']);
		    $first_key = key($image);
			
			$card='<div class="card">
				 <a href="?page=productdetails&view='.$record[$j]['idProduct'].'"> <img src="images/product/'.$image[$first_key].'" alt="Avatar" style="width:100%"></a>
				  	<div>
				    <h4><b>'.$record[$j]['ProductName'].'</b></h4> 
				    <p><i class="fa fa-inr" aria-hidden="true"></i> '.$record[$j]['Price'].'.00 &nbsp;<span><i class="fa fa-inr" aria-hidden="true"></i> <strike>700.00</strike></span> <span class="offerdiscount">35% off</span></p>
				    <p><button class="btn btn-success AddCart" for="'.$record[$j]['idProduct'].'">Add to Cart</button> <button class="btn btn-danger">Wishlist</button></p>
				    </div> 
				</div>';
			
	        $html.=$card;
			$j++;
	    }
		}
	    return $html; 
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
