<?php 
include_once("utility/config.php");

include_once("utility/dbclass.php");

include_once("utility/functions.php");

//include_once("includes/other_functions.php");

include_once("webadmin/includes/new_functions.php");

date_default_timezone_set('Asia/Calcutta');
error_reporting(0);
set_time_limit(0); 

$objDB = new DB();
$pageOwner = "'branch','superadmin','admin','hub'";
chkPageAccess($_SESSION[ROLE_ID], $pageOwner); // $USER_TYPE is coming from index.php


function findLastPayDate($folio_id)
{
	$objDB = new DB();
	$Query = "SELECT DATE_FORMAT(deposit_date, '%d-%m-%Y') AS last_date FROM installment_master WHERE folio_no_id = '".$folio_id."' AND is_deleted=1 ORDER BY id DESC LIMIT 0,1 ";
	$objDB->setQuery($Query);
	$rsu = $objDB->select();
	if(count($rsu) > 0 )
	{
		return $rsu[0]['last_date'];
	}
}

function findCumulativeInstallment($folio_id,$id)
{
	$objDB = new DB();
	$Query = "SELECT SUM(installment) AS cumulative_installment FROM installment_master WHERE folio_no_id = '".$folio_id."' AND is_deleted=1 AND id <= '".$id."'";
	$objDB->setQuery($Query);
	$rsu = $objDB->select();
	if(count($rsu) > 0 )
	{
		return $rsu[0]['cumulative_installment'];
	}
}



$where = '';

#$_SESSION['branch_name'] = isset($_GET['branch_name']) ? $_GET['branch_name'] : '';
#$_SESSION['from_date'] = isset($_GET['from_date']) ? $_GET['from_date'] : '';
#$_SESSION['to_date'] = isset($_GET['to_date']) ? $_GET['to_date'] : '';
#$_SESSION['first_name'] = isset($_GET['first_name']) ? $_GET['first_name'] : '';



if(isset($_SESSION['hub_name_id']) && $_SESSION['hub_name_id'] != '') 
	{
		$where.= ' AND hub_id="'.$_SESSION['hub_name_id'].'"';
	}
	if(isset($_SESSION['branch_name_id']) && $_SESSION['branch_name_id'] != '') 
	{
		$where.= ' AND branch_id="'.$_SESSION['branch_name_id'].'"';
	}

	if(isset($_SESSION['from_date']) && $_SESSION['from_date'] != '') 
	{
		$where.= ' AND deposit_date >="'.date('Y-m-d', strtotime($_SESSION['from_date'])).'"';
	}

	if(isset($_SESSION['to_date']) && $_SESSION['to_date'] != '') 
	{
		$where.= ' AND deposit_date <="'.date('Y-m-d', strtotime($_SESSION['to_date'])).'"';
	}
	if(isset($_SESSION['receipt_number']) && $_SESSION['receipt_number'] != '') 
	{
		$where.= ' AND receipt_number LIKE "%'.$_SESSION['receipt_number'].'%"';
	}

	if(isset($_SESSION['application_no']) && $_SESSION['application_no'] != '') 
	{
		$where.= ' AND application_no LIKE "%'.$_SESSION['application_no'].'%"';
	}

	if(isset($_SESSION['folio_no']) && $_SESSION['folio_no'] != '') 
	{
		$derivedFolioNo = find_folio_id($_SESSION['folio_no']);
		$where.= ' AND folio_no_id = "'.$derivedFolioNo.'"';
	}

	if(isset($_SESSION['customer_id']) && $_SESSION['customer_id'] != '') 
	{
		$derivedCustID = find_id_through_customer_id($_SESSION['customer_id']);
		$where.= ' AND customer_id = "'.$derivedCustID.'"';
	}

	if(isset($_SESSION['first_name']) && $_SESSION['first_name'] != '') 
	{
		$firstNameString = find_id_through_first_name($_SESSION['first_name']);
		#echo $firstNameString;
		if($firstNameString != '')
		{
			$where.= ' AND customer_id IN ( '.$firstNameString.')';
		}
	}

	if(isset($_SESSION['last_name']) && $_SESSION['last_name'] != '') 
	{
		$lastNameString = find_id_through_last_name($_SESSION['last_name']);
		#echo $lastNameString;
		if($lastNameString != '')
		{
			$where.= ' AND customer_id IN ( '.$lastNameString.')';
		}
	}

	if($_SESSION[ROLE_ID] == '3')
		{
			$branchStr = '';
			$branch_user_id = find_branch_user_id($_SESSION[ADMIN_SESSION_VAR]);
			$hub_id = intval($branch_user_id) == 0 ? $_SESSION[ADMIN_SESSION_VAR] : $branch_user_id ;
	
			$selBranches = mysql_query("SELECT id FROM admin WHERE hub_id=".$hub_id."");
			if(mysql_num_rows($selBranches) > 0)
			{
								
				$where.=" AND hub_id='$hub_id'";
			}
	}
else if($_SESSION[ROLE_ID] == '4')
{
	$where.= ' AND branch_id="'.$_SESSION['branch_name'].'"';
}
	if(isset($_SESSION['pis_generated']) && ($_SESSION['pis_generated'] != ''))
	{
		if($_SESSION['pis_generated'] == '0')
		{
		$where.= ' AND pis_id =0';
		}
		else if($_SESSION['pis_generated'] == '1')
		{
		$where.= ' AND pis_id !=0';
		}
		else
		{
		$where.= '';
		}
	}

$sql="SELECT * FROM installment_master_branch WHERE is_deleted=1 ".$where." ORDER BY id DESC";
 //echo $sql;
// exit;
$selTransaction = mysql_query($sql);
//echo "SELECT * FROM installment_master WHERE is_deleted=1 ".$where." ORDER BY id DESC";
$numTransaction = mysql_num_rows($selTransaction);


	//$tbname = $_POST['select1'];
	//$objDB = new DB();
	//$Query = $_SESSION['branch_report_query'];
	//$objDB->setQuery($Query);
	//$rs = $objDB->select();

		
		$header .= "Application No"."\t";
                $header .= "Pre Printed Recipt No"."\t";
                $header .= "Branch Name"."\t";
                
                $header .= "Plan Name."."\t";                
		$header .= "Business Date."."\t";
                $header .= "Applicant Name."."\t";
                
                $header .= "Receive Cash"."\t";
                $header .= "Receive Cheque"."\t";
                $header .= "Receive Draft"."\t"; 
                
                $header .= "Premium Amount."."\t";
                $header .= "Term."."\t";
                $header .= "Sum Assured."."\t";             
                
                
		$header .= "Business Type."."\t";		
		$header .= "Agent Code"."\t";		
		$header .= "Cash Money Recipt."."\t";
                
		$header .= "Cheque Money Recipt"."\t";
		$header .= "Draft Money Recipt"."\t";		
		$header .= "Applicant DOB."."\t";
                
		$header .= "Applicant Age."."\t";
		$header .= "Insured Name."."\t";
		$header .= "Insured DOB."."\t";
                
		$header .= "Insured Age."."\t";
		$header .= "Insured Address."."\t";
		$header .= "Nominee Name."."\t";
                
		$header .= "Nominee Relatioship."."\t";
		$header .= "Nominee DOB."."\t";
		$header .= "Nominee Age."."\t";
                
		$header .= "Appiontee Name."."\t";
		$header .= "Appiontee Relatioship."."\t";
		$header .= "Appiontee DOB."."\t";
                
		$header .= "Appiontee Age."."\t";
		$header .= "Office Name."."\t";
		$header .= "Office Address."."\t";	
		
		
		$header .= "Payment MODE."."\t";		 
		$header .= "Cheque No."."\t";
		$header .= "Cheque Date."."\t";
                
		$header .= "Cheque Bank Name."."\t";
		$header .= "Cheque Branch Name."."\t";		
		$header .= "Draft No."."\t";
                
		$header .= "Draft Date."."\t";
		$header .= "Draft Bank Name."."\t";
		$header .= "Draft Branch Name."."\t";
                
		$header .= "State"."\t";
		$header .= "PIN"."\t";
		$header .= "Telephone"."\t";
                
		$header .= "Occupation"."\t";
		$header .= "Education Qualification"."\t";
		$header .= "Annual Income"."\t";
                
		$header .= "ID Proof"."\t";
		$header .= "Address Proof"."\t";
                
		$header .= "\n";
	
		$search = "/[ \r\n]/";
		if($numTransaction > 0)
		{
			while($getTransaction = mysql_fetch_assoc($selTransaction))
			{  
                            
				$line = '';
                                
                                $line .= "'".$getTransaction['application_no']."\t";
                                $line .= "'".$getTransaction['pre_printed_receipt_no']."\t";
                                $line .= find_branch_name($getTransaction['branch_id'])."\t";
                                
                                
                                $line .= $getTransaction['plan_name']."\t";   
				$business_date = '';
				if(($getTransaction['business_date'] != '0000-00-00')&&($getTransaction['business_date'] != '1970-01-01')){
					$business_date = date('d/m/Y',strtotime($getTransaction['business_date']));
				}
				$line .= $business_date."\t";
                                $line .= $getTransaction['applicant_name']."\t";
                                
                                
                                $line .= $getTransaction['receive_cash']."\t";
                                $line .= $getTransaction['receive_cheque']."\t";
                                $line .= $getTransaction['receive_draft']."\t";
                                
                                
                                $line .= $getTransaction['premium']."\t";
                                $line .= $getTransaction['term']."\t";				
				$line .= $getTransaction['sum_asured']."\t";                       
                                
                                
				$line .= $getTransaction['type_of_business']."\t";				
				$line .= "'".$getTransaction['agent_code']."\t";				
				$line .= "'".$getTransaction['cash_money_receipt']."\t";
                                
                                
				$line .= "'".$getTransaction['cheque_money_receipt']."\t";
				$line .="'". $getTransaction['draft_money_receipt']."\t";				
				$applicant_dob = '';
				if(($getTransaction['applicant_dob'] != '0000-00-00') && ($getTransaction['applicant_dob'] != '1970-01-01')){
					$applicant_dob = date('d/m/Y',strtotime($getTransaction['applicant_dob']));
				}
				$line .= $applicant_dob."\t";
                                
                                
                                
				$line .= $getTransaction['applicant_age']."\t";
				$line .= $getTransaction['insured_name']."\t";
				$insured_dob = '';
				if(($getTransaction['insured_dob'] != '0000-00-00') && ($getTransaction['insured_dob'] != '1970-01-01')){
					$insured_dob = date('d/m/Y',strtotime($getTransaction['insured_dob']));
				}
				$line .= $insured_dob."\t";
                                
                                
				$line .= $getTransaction['insured_age']."\t";
                                $line .= $getTransaction['insured_address1']." ".$getTransaction['insured_address2']." ".$getTransaction['insured_address3']."\t";
				$line .= $getTransaction['nominee_name']."\t";
                                
                                
				$line .= $getTransaction['nominee_relationship']."\t";
				$nominee_dob = '';
				if(($getTransaction['nominee_dob'] != '0000-00-00') && ($getTransaction['nominee_dob'] != '1970-01-01')){
					$nominee_dob = date('d/m/Y',strtotime($getTransaction['nominee_dob']));
				}
				$line .= $nominee_dob."\t";
				$line .= $getTransaction['nominee_age']."\t";
                                
                                
				$line .= $getTransaction['appointee_name']."\t";
				$line .= $getTransaction['appointee_relationship']."\t";
				$appointee_dob = '';
				if(($getTransaction['appointee_dob'] != '0000-00-00') && ($getTransaction['appointee_dob'] != '1970-01-01')){
					$appointee_dob = date('d/m/Y',strtotime($getTransaction['appointee_dob']));
				}
				$line .= $appointee_dob."\t";
                                
				$line .= $getTransaction['appointee_age']."\t";
				$line .= $getTransaction['office_name']."\t";
				$line .= $getTransaction['office_address1']." ".$getTransaction['office_address2']." ".$getTransaction['office_address3']."\t";
				
				                                	
				$line .= $getTransaction['pay_mode']."\t";				
				$line .= "'".$getTransaction['cheque_no']."\t";
				$cheque_date = '';
				if(($getTransaction['cheque_date'] != '0000-00-00') && ($getTransaction['cheque_date'] != '1970-01-01')){
					$cheque_date = date('d/m/Y',strtotime($getTransaction['cheque_date']));
				}
				$line .= $cheque_date."\t";
                                
                                
				$line .= $getTransaction['cheque_bank_name']."\t";
				$line .= $getTransaction['cheque_branch_name']."\t";				
				$line .= "'".$getTransaction['dd_no']."\t";
                                
                                
                                
				$dd_date = '';
				if(($getTransaction['dd_date'] != '0000-00-00') && ($getTransaction['dd_date'] != '1970-01-01')){
					$dd_date = date('d/m/Y',strtotime($getTransaction['dd_date']));
				}
				$line .= $dd_date."\t";
				$line .= $getTransaction['dd_bank_name']."\t";
				$line .= $getTransaction['dd_branch_name']."\t";
                                
                                
				$line .= find_place_name($getTransaction['state_id'])."\t";
				$line .= $getTransaction['pin']."\t";
				$line .= $getTransaction['telephone_no']."\t";
                                
                                
				$line .= $getTransaction['occupation']."\t";
				$line .= $getTransaction['education_qualification']."\t";
				$line .= $getTransaction['anual_income']."\t";
                                
                                
				$line .= find_age_proof($getTransaction['identity_proof'])."\t";
				$line .= find_address_proof($getTransaction['address_proof'])."\t";
                                
                                
				$data .= trim($line)."\n";
				$nominee_address1 = '';
				$nominee_address2 = '';
				$nominee_address3 = '';
				$employers_address1 = '';
				$employers_address2 = '';
				$employers_address3 = '';
			}
				
			//echo "<pre>";
			//print_r($$data);
			
			//exit;
	}
	$data = str_replace("\r", "", $data);
	if ($data == "")
	{
		$data = "\n(0) Records Found!\n";						
	}

	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=SICL_new".'_'.date('m-d-Y_H:i').".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	print "$header\n$data";

?>