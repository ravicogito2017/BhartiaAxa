<?php
include_once("../utility/config.php");
include_once("../utility/dbclass.php");
include_once("../utility/functions.php");
include_once("new_functions.php");
//print_r($_POST);

set_time_limit(0); 
$objDB = new DB();
$today = date('Y-m-d');

$pageOwner = "'superadmin','admin','hub'";
chkPageAccess($_SESSION[ROLE_ID], $pageOwner); // $USER_TYPE is coming from index.php

#print_r($_POST);
extract($_POST);

if(isset($received) && count($received) > 0)
{
	$receiveStr = implode(',',$received);
	$receivedUpdate = mysql_query("UPDATE installment_master SET hub_received=1, hub_id=".$_SESSION[ADMIN_SESSION_VAR].", hub_receive_date='".$today."' WHERE id IN (".$receiveStr.")");
}
if(isset($scanned) && count($scanned) > 0)
{
	$scanningStr = implode(',',$scanned);
	$scannedUpdate = mysql_query("UPDATE installment_master SET scanning_done=1, hub_id=".$_SESSION[ADMIN_SESSION_VAR].", scanning_date='".$today."' WHERE id IN (".$scanningStr.") ");
}

if(isset($despatched) && count($despatched) > 0)
{
	$despatchStr = implode(',',$despatched);
	//echo $despatchStr;
	//echo "UPDATE installment_master SET hub_despatched=1, hub_despatch_date='".$today."' WHERE id IN (".$despatchStr.") AND hub_id=".$_SESSION[ADMIN_SESSION_VAR]."";
	//exit;
	$despatchedUpdate = mysql_query("UPDATE installment_master SET hub_despatched=1, hub_despatch_date='".$today."' WHERE id IN (".$despatchStr.") AND hub_id=".$_SESSION[ADMIN_SESSION_VAR]."");
}

if(isset($branch_despatched) && count($branch_despatched) > 0)
{
	$branch_despatchedStr = implode(',',$branch_despatched);
	$branch_despatchedUpdate = mysql_query("UPDATE installment_master SET branch_despatched=1, branch_despatch_date='".$today."' WHERE id IN (".$branch_despatchedStr.")");
}

if(isset($admin_received) && count($admin_received) > 0)
{
	$admin_receiveStr = implode(',',$admin_received);
	$admin_receivedUpdate = mysql_query("UPDATE installment_master SET admin_received=1, admin_receive_date='".$today."' WHERE id IN (".$admin_receiveStr.")");
}

/*if(isset($mode) && $mode == 'del') // delete transaction
{
	#echo '<br />'.$transaction_id;
	$premium_array = find_premium_for_this_transaction($transaction_id);
	#print_r($premium_array);

	$this_folio_id = find_folio_id_through_transaction_id($transaction_id);
	#echo '<br />'.$this_folio_id;

	mysql_query("UPDATE customer_folio_no SET total_premium_given= total_premium_given - '".$premium_array['premium_number']."' WHERE id='".$this_folio_id."'"); // roll back number of premiums
	#echo '<br />'."UPDATE customer_folio_no SET total_premium_given= total_premium_given - '".$premium_array['premium_number']."' WHERE id='".$this_folio_id."'";
	
	mysql_query("UPDATE installment_master SET is_deleted=1 WHERE id=".$transaction_id);
	#echo "UPDATE installment_master SET is_deleted=1 WHERE id=".$transaction_id;

	#exit;

	$_SESSION[SUCCESS_MSG] = "Record deleted successfully...";
	header("location: index.php?p=".$_REQUEST['p']."");
	exit();

} */

// Write functions here


function find_premium_for_this_transaction($transaction_id)
{
	$return_array = array();
	#echo "<br /> SELECT amount / comitted_amount as premium, customer_id FROM installment_master WHERE id='".$transaction_id."'";
	$selPremiumNumber = mysql_query("SELECT installment as premium, customer_id FROM installment_master WHERE id='".$transaction_id."'");
	$numPremiumNumber = mysql_num_rows($selPremiumNumber);
	if($numPremiumNumber > 0)
	{
		$getPremiumNumber = mysql_fetch_array($selPremiumNumber);
		$return_array['premium_number'] = $getPremiumNumber['premium'];
		$return_array['customer_id'] = $getPremiumNumber['customer_id'];
	}
	return $return_array;
}


$where = "WHERE is_deleted=0 ";
$OrderBY = " ORDER BY id DESC ";


//=======================================================

#############
//print_r($_POST);

$pid 			= loadVariable('pid',0);
	$showAll 		= loadVariable('all',0);
	$searchField 	= loadVariable('searchField','');
	$searchString 	= outputEscapeString(loadVariable('search',''));
	$sortField 		= loadVariable('sf','name');
	$sortType 		= loadVariable('st','ASC');
	$dataPerPage 	= loadVariable('dpp',25);
	$mid 			= loadVariable('mid',0);
	$mode			= loadVariable('mode','');

	if($showAll == 0)
	{
		loadFromSession('LIST_PAGE','search',$searchString);
		loadFromSession('LIST_PAGE','sf',$sortField);
		loadFromSession('LIST_PAGE','st',$sortType);
		loadFromSession('LIST_PAGE','dpp',$dataPerPage);
		loadFromSession('LIST_PAGE','mid',$mid);
		loadFromSession('LIST_PAGE','pid',$pid);
	}
	
	$searchString 	= outputEscapeString($searchString);

	##############

	##### CODE FOR SEARCHING 

	if(isset($branch_name))
	{
		$_SESSION['branch_name'] = realTrim($branch_name);
	}

	if(isset($from_date))
	{
		$_SESSION['from_date'] = $from_date;
	}

	if(isset($to_date))
	{
		$_SESSION['to_date'] = $to_date;
	}

	if(isset($receipt_number))
	{
		$_SESSION['receipt_number'] = realTrim($receipt_number);
	}

	if(isset($application_no))
	{
		$_SESSION['application_no'] = realTrim($application_no);
	}

	

	if(isset($serial_no))
	{
		$_SESSION['serial_no'] = realTrim($serial_no);
	}
	

	if(isset($first_name))
	{
		$_SESSION['first_name'] = realTrim($first_name);
	}
	if(isset($last_name))
	{
		$_SESSION['last_name'] = realTrim($last_name);
	}

	if(isset($dd_number))
	{
		$_SESSION['dd_number'] = realTrim($dd_number);
	}

	if($_SESSION[ROLE_ID] == '3')
	{
		$branchStr = '';
		$branchStr1  = '';
		$branch_user_id = find_branch_user_id($_SESSION[ADMIN_SESSION_VAR]);
		$hub_id = intval($branch_user_id) == 0 ? $_SESSION[ADMIN_SESSION_VAR] : $branch_user_id ;

		$selBranches = mysql_query("SELECT id FROM admin WHERE hub_id=".$hub_id."");
		if(mysql_num_rows($selBranches) > 0)
		{
			while($getBranches = mysql_fetch_array($selBranches))
			{
				#print_r($getBranches);
				$branchStr.= $getBranches['id'].',';
			}
		}
		$branchStr = trim($branchStr, ',');
		//echo "select * from  `branch_hub_entry` WHERE hub_id=".$hub_id."";
		//exit;
		$selBranches1 = mysql_query("select * from  `branch_hub_entry` WHERE hub_id=".$hub_id."");
		if(mysql_num_rows($selBranches1) > 0)
		{
			while($getBranches1 = mysql_fetch_array($selBranches1))
			{
				//print_r($getBranches1);
				//exit;
				$branchStr1.= $getBranches1['branch_id'].',';
			}
		}

		$branchStr1 = trim($branchStr1, ',');

		$where.=" AND (branch_id IN (".$branchStr.") OR branch_id IN (".$branchStr1.")) AND hub_id=".$hub_id."";
	}
//echo "branch : ".$_SESSION['branch_name'];
		//echo "<br/>";

	if(isset($_SESSION['branch_name']) && $_SESSION['branch_name'] != '') // this is actually branch id
	{
		
		
		$branch_usr_str = find_branch_user_string($_SESSION['branch_name']);
		$branch_usr_str_with_admin = $branch_usr_str.','.$_SESSION['branch_name'];
		$where.= " AND branch_id IN (".trim($branch_usr_str_with_admin,',').")";
	}

	if(isset($_SESSION['from_date']) && $_SESSION['from_date'] != '') 
	{
		$where.= ' AND deposit_date >="'.date('Y-m-d', strtotime($_SESSION['from_date'])).'"';
	}

	if(isset($_SESSION['to_date']) && $_SESSION['to_date'] != '') 
	{
		$where.= ' AND deposit_date <="'.date('Y-m-d', strtotime($_SESSION['to_date'])).'"';
	}

	if(isset($_SESSION['dd_number']) && $_SESSION['dd_number'] != '') 
	{
		$where.= ' AND dd_number = "'.$_SESSION['dd_number'].'"';
	}

	if(isset($_SESSION['receipt_number']) && $_SESSION['receipt_number'] != '') 
	{
		$where.= ' AND transaction_id = "'.$_SESSION['receipt_number'].'"';
	}

	if(isset($_SESSION['application_no']) && $_SESSION['application_no'] != '') 
	{
		$where.= ' AND application_no LIKE "%'.$_SESSION['application_no'].'%"';
	}

	

	if(isset($_SESSION['serial_no']) && $_SESSION['serial_no'] != '') 
	{
		
		$where.= ' AND serial_no = "'.$_SESSION['serial_no'].'"';
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

	##### CODE FOR SEARCHING 




$Query = "select count(id) as CNT from installment_master  ".$where;
#echo $Query;
$objDB->setQuery($Query);
$rsTotal = $objDB->select();
$displayTotal=$rsTotal[0]['CNT'];
$extraParam = "&p=".$GLOBALS['p'];

$dpp = true;
$totalRecordCount = $rsTotal[0]['CNT'];
$pageSegmentSize = 15;
include_once("../utility/pagination.php");

$Query = "SELECT * FROM installment_master ".$where.$OrderBY.$Limit;
#echo $Query;
#exit;
$objDB->setQuery($Query);
$rs = $objDB->select();

$pageRecordCount = count($rs);

//==========================================================

$_SESSION['LIST_PAGE'][$GLOBALS['p']] = array(
'pg' 		=> $currentPage,
'search' 	=> $searchString,
'st' 		=> $sortType,
'sf' 		=> $sortField,
'dpp' 		=> $dataPerPage,
'mid' 		=> $mid,
'pid' 		=> $pid
);

//$selTransaction = mysql_query("SELECT id, branch_id, customer_id, deposit_date, comitted_amount, amount, tenure, transaction_charges, first_name, last_name, phone, email FROM installment_master WHERE is_deleted=0 ORDER BY id DESC");
//$numTransaction = mysql_num_rows($selTransaction);
	

/*
echo "<pre>";
print_r($_POST);
die();
*/



?>


<link type="text/css" rel="stylesheet" href="<?=URL?>dhtmlgoodies_calendar/dhtmlgoodies_calendar.css?random=20051112" media="screen"></LINK>
<script type="text/javascript" src="<?=URL?>dhtmlgoodies_calendar/dhtmlgoodies_calendar.js?random=20060118"></script>

<script type="text/javascript" src="<?=URL?>js/site_scripts.js"></script>
<script type="text/javascript">
<!--
	function confirmDelete(ID)
	{
			if(confirm('Transaction will be deleted.\nAre you sure ? '))
			{
				
				document.sortFrm.mode.value='del';
				document.sortFrm.transaction_id.value=ID;
				document.sortFrm.submit();
			}
	}
	function dppList(dpp)
	{
		//alert(dpp1);
		document.sortFrm.dpp.value = dpp;
		document.sortFrm.pg.value= 1;
		document.sortFrm.submit();
		return true;
	}
//-->
</script>

<form name="addForm" id="addForm" action="" method="post" style="border:0px solid red">
<TABLE class="table_border" cellSpacing=2 cellPadding=5 width="100%" align=center border=0>
  <tbody>
    <tr> 
      <td colspan="30">
        <? showMessage(); ?>
      </td>
    </tr>
		<tr> 
      <td colspan="30" align="right">
        <a title=" Export to Excel " href="<?=URL?>transaction_excel.php" ><img src="images/excel_icon.gif" border="0" title="Download" /></a>
      </td>
    </tr>

	<tr> 
      <td colspan="30" align="left">
				<table>
					<tr>
						<td width="150"><strong>Branch</strong></td>
						<td width="20"><strong>:</strong></td>
						<td width="150">
							<select name="branch_name" id="" class="inplogin_select" style="width:140px;">
								<option value="">Select All</option>
							<?php 
								$selBranch = mysql_query("SELECT branch_name, id FROM admin WHERE branch_user_id=0 AND role_id in (4) ORDER BY branch_name ASC");

								//SELECT id FROM admin WHERE hub_id=".$hub_id.


								/*$selBranches1 = mysql_query("select * from  `branch_hub_entry` WHERE hub_id=".$hub_id."");
									if(mysql_num_rows($selBranches1) > 0)
									{
										while($getBranches1 = mysql_fetch_array($selBranches1))
										{
											//print_r($getBranches1);
											//exit;
											$branchStr1.= $getBranches1['branch_id'].',';
										}
									}

									$branchStr1 = trim($branchStr1, ',');
									*/
		


								while($getBranch = mysql_fetch_array($selBranch))
								{					
							?>
								<option value="<?php echo $getBranch['id'];?>" <?php echo (isset($_SESSION['branch_name']) && ($_SESSION['branch_name']  == $getBranch['id']) ? 'selected' : ''); ?>><?php echo $getBranch['branch_name'];?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td width="150"><strong>From Date</strong></td>
						<td width="20"><strong>:</strong></td>
						<td width="150">
							<input name="from_date" id="from_date" type="text" class="inplogin" value="<?php echo (isset($_SESSION['from_date']) ? $_SESSION['from_date'] : ''); ?>" maxlength="20" readonly /> &nbsp;<img src="images/cal.gif" alt="" style="border: 0pt none ; cursor: pointer; position: absolute;" onclick="displayCalendar(document.addForm.from_date,'dd-mm-yyyy',this)" width="20" height="18">&nbsp;&nbsp;<a href="javascript:void(0)" onclick="javascript:document.addForm.from_date.value=''">Clear</a>
						</td>
					</tr>
					<tr>
						<td width="150"><strong>To Date</strong></td>
						<td width="20"><strong>:</strong></td>
						<td width="150">
							<input name="to_date" id="to_date" type="text" class="inplogin" value="<?php echo (isset($_SESSION['to_date']) ? $_SESSION['to_date'] : ''); ?>" maxlength="20" readonly /> &nbsp;<img src="images/cal.gif" alt="" style="border: 0pt none ; cursor: pointer; position: absolute;" onclick="displayCalendar(document.addForm.to_date,'dd-mm-yyyy',this)" width="20" height="18">&nbsp;&nbsp;<a href="javascript:void(0)" onclick="javascript:document.addForm.to_date.value=''">Clear</a>
						</td>
					</tr>
					<tr>
						<td width="150"><strong>Application Number</strong></td>
						<td width="20"><strong>:</strong></td>
						<td width="150">
							<input name="application_no" id="application_no" type="text" class="inplogin" value="<?php echo (isset($_SESSION['application_no']) ? $_SESSION['application_no'] : ''); ?>" maxlength="100" /> 
						</td>
					</tr>
					
					<tr>
						<td width="150"><strong>Cheque No.</strong></td>
						<td width="20"><strong>:</strong></td>
						<td width="150">
							<input name="dd_number" id="dd_number" type="text" class="inplogin" value="<?php echo (isset($_SESSION['dd_number']) ? $_SESSION['dd_number'] : ''); ?>" maxlength="100" /> 
						</td>
					</tr>

					<tr>
						<td width="150"><strong>Receipt Number</strong></td>
						<td width="20"><strong>:</strong></td>
						<td width="150">
							<input name="receipt_number" id="receipt_number" type="text" class="inplogin" value="<?php echo (isset($_SESSION['receipt_number']) ? $_SESSION['receipt_number'] : ''); ?>" maxlength="50" /> 
						</td>
					</tr>
					<tr>
						<td width="150"><strong>Receipt Serial Number</strong></td>
						<td width="20"><strong>:</strong></td>
						<td width="150">
							<input name="serial_no" id="serial_no" type="text" class="inplogin" value="<?php echo (isset($_SESSION['serial_no']) ? $_SESSION['serial_no'] : ''); ?>" maxlength="100" /> 
						</td>
					</tr>
					<tr>
						<td width="150"><strong>Applicant Name</strong></td>
						<td width="20"><strong>:</strong></td>
						<td width="150">
							<input name="first_name" id="first_name" type="text" class="inplogin" value="<?php echo (isset($_SESSION['first_name']) ? $_SESSION['first_name'] : ''); ?>" maxlength="255" /> 
						</td>
					</tr>
					

					<tr>
						<td colspan="3" align="center"><input type="submit" name="btnSubmit" value="Search">&nbsp;<?php if(isset($_POST) && (count($_POST)) > 0){ ?><!-- <a href="xml_download.php" style="text-decoration:none;" ><input type="button" name="btnXML" value="Download XML"></a> --><?php } ?></td>
						
					</tr>
				</table>
        
      </td>
    </tr>

		<tr> 
      <td colspan="30" align="right">
        <? include_once("../utility/pagination_display.php");?>
      </td>
    </tr>
    <tr class="TDHEAD"> 
      <td colspan="30">Transaction Listing</td>
    </tr>
		<tr>
			<td colspan="30"><input type="submit" name="btnUpdate" value="Update" class="inplogin"></td>
		</tr>
    
    <tr> 
		<td width="8%" align="center" valign="top" class="tbllogin">PIS Generated</td>
		<?php
			if(($_SESSION[ROLE_ID] == '1') || ($_SESSION[ROLE_ID] == '2')) // FOR SUPERADMIN, ADMIN	
				{
		?>
			<td width="8%" align="center" valign="top" class="tbllogin">Admin Received</td>
		<?php
				}	
		?>
		<?php
		if(intval($_SESSION[ROLE_ID]) != 4) // NOT FOR BRANCH
			{ 
	?>
      <td width="8%" align="center" valign="top" class="tbllogin">Hub Received</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Scanning Done</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Despatched To INS.CO.</td>
			
	<?php 
			}
	?>
	<?php
		if(intval($_SESSION[ROLE_ID]) == 3) // NOT FOR BRANCH
			{ 
	?>
			<td width="8%" align="center" valign="top" class="tbllogin">Branch Despatched</td>
	<?php
			}	
	?>
      
			<!--<td width="8%" align="center" valign="top" class="tbllogin">Customer ID</td>-->
			<td width="8%" align="center" valign="top" class="tbllogin">Application Number</td>
			<!--<td width="8%" align="center" valign="top" class="tbllogin">Folio Number</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Receipt Number</td>
-->
			<td width="8%" align="center" valign="top" class="tbllogin">Receipt Serial Number</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Receipt Number</td>

			<td width="8%" align="center" valign="top" class="tbllogin">Branch Name</td>
			<td width="8%" align="center" valign="top" class="tbllogin">HUB Name</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Deposit Date</td>			
			<td width="8%" align="center" valign="top" class="tbllogin">Applicant Name</td>
			
			<td width="8%" align="center" valign="top" class="tbllogin">Nominee Name</td>
			<td width="8%" align="center" valign="top" class="tbllogin">SUM ASSURED</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Tenure (Years)</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Amount</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Service Tax</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Total Amount</td> 

			<td width="8%" align="center" valign="top" class="tbllogin">Tax</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Payment Type</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Branch Despatch Date</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Hub Receive Date</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Hub Despatch Date</td>
			
			<!-- <td width="8%" align="center" valign="top" class="tbllogin">Edit</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Employee Code</td> -->
			<td width="8%" align="center" valign="top" class="tbllogin">Phone</td>
			<!-- <td width="8%" align="center" valign="top" class="tbllogin">Email</td> -->
			<!-- <td width="8%" align="center" valign="top" class="tbllogin">Subsequent Payment</td> -->
			<!-- <td width="8%" align="center" valign="top" class="tbllogin">Receipt</td> -->
			<!-- <td width="8%" align="center" valign="top" class="tbllogin">New Plan With Existing Customers</td> -->
			<td width="8%" align="center" valign="top" class="tbllogin">Status</td>
			<!--<td width="8%" align="center" valign="top" class="tbllogin">Signature and Application Form Image</td>-->
			
    </tr>
<?php
	if($pageRecordCount > 0)
	{
		for($i=0;$i<count($rs);$i++) { 
		 if(($i % 2)==0)
		 {
			$cls="text5";
		 }
		 else
		 {
			$cls="text6";
		 }
			$mysql_id = $rs[$i]['id'];
			#echo '<br />'.$rs[$i]['customer_id']; // this is autoincrement id of the customer_master table

			//$selMasterRecord = mysql_query("SELECT customer_id, first_name, middle_name, last_name, phone, email FROM customer_master WHERE id='".$rs[$i]['customer_id']."'");

			//if(mysql_num_rows($selMasterRecord) > 0)
			//{	
			//	$getMasterRecord = mysql_fetch_array($selMasterRecord);
			//}

		//	$selFolioRecord = mysql_query("SELECT folio_no, committed_amount, tenure, nominee_name FROM customer_folio_no WHERE id = '".$rs[$i]['folio_no_id']."'");

			//if(mysql_num_rows($selFolioRecord) > 0)
			//{
			//	$getFolioRecord = mysql_fetch_array($selFolioRecord);
			//}

			$branch_name = find_branch_name($rs[$i]['branch_id']);
			$payment_mode_service = $rs[$i]['payment_mode'];

			//$kyc_ok = kyc_ok($rs[$i]['customer_id']);
?>
	<tr> 
	<td width="8%" align="center" valign="top" ><?php echo (intval($rs[$i]['pis_id']) != 0 ? 'Yes' : 'No'); ?></td>
			<?php
		if(($_SESSION[ROLE_ID] == '1') || ($_SESSION[ROLE_ID] == '2')) // FOR SUPERADMIN, ADMIN	
			{
	?>
			<td width="8%" align="center" valign="top" ><?php if(intval($rs[$i]['pis_id']) != 0){ ?><input type="checkbox" name="admin_received[]" value="<?php echo $mysql_id; ?>" <?php echo ($rs[$i]['admin_received'] == '1' ? 'checked' : '')?>> <?php } ?></td>
	<?php
			}	
	?>
	<?php
		if(intval($_SESSION[ROLE_ID]) != 4) // NOT FOR BRANCH
			{ 
	?>
			<td width="8%" align="center" valign="top" ><?php if(intval($rs[$i]['pis_id']) != 0){ 
				if($rs[$i]['branch_despatched'] == '1')	{
		?><input type="checkbox" name="received[]" value="<?php echo $mysql_id; ?>" 
		<?php echo ($rs[$i]['hub_received'] == '1' ? 'checked' : '')?>
		<?php echo ($rs[$i]['hub_received'] == '1' ? 'disabled="disabled"' : '')?>
		> <?php }} ?></td>


			<td width="8%" align="center" valign="top" ><?php if(intval($rs[$i]['pis_id']) != 0){ 
				if($rs[$i]['hub_received'] == '1')	{
			?><input type="checkbox" name="scanned[]" value="<?php echo $mysql_id; ?>" 
			<?php echo ($rs[$i]['scanning_done'] == '1' ? 'checked' : '')?>
			<?php echo ($rs[$i]['scanning_done'] == '1' ? 'disabled="disabled"' : '')?>
			><?php }} ?> </td>


			<td width="8%" align="center" valign="top" ><?php if(intval($rs[$i]['pis_id']) != 0){ 
					if($rs[$i]['scanning_done'] == '1')	{
				?><input type="checkbox" name="despatched[]" value="<?php echo $mysql_id; ?>" 
				<?php echo ($rs[$i]['hub_despatched'] == '1' ? 'checked' : '')?>
				<?php echo ($rs[$i]['hub_despatched'] == '1' ? 'disabled="disabled"' : '')?>
				> <?php }} ?></td>
	

			

	<?php
			}	
	?>
	<?php
		if(intval($_SESSION[ROLE_ID]) == 3) // FOR BRANCH
			{ 
	?>

			<td width="8%" align="center" valign="top" >
			<?php if(intval($rs[$i]['pis_id']) != 0){ 
				if($rs[$i]['branch_despatched'] == '1')	{
			?><input type="checkbox" name="branch_despatched[]" value="<?php echo $mysql_id; ?>" <?php echo ($rs[$i]['branch_despatched'] == '1' ? 'checked' : '')?>
			disabled="disabled"><?php } } ?> </td>
	<?php
			}			
	?>

      
			<!--<td width="8%" align="center" valign="top" ><?php echo $rs['customer_id']; ?></td>-->
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['application_no']; ?></td>
			<!--<td width="8%" align="center" valign="top" ><?php echo $rs['folio_no']; ?></td>-->
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['serial_no']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['transaction_id']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $branch_name; ?></td>
			<td width="8%" align="center" valign="top" >
				<?php echo find_branch_name($rs[$i]['hub_id']); ?>
			</td><!-- hub -->
			<td width="8%" align="center" valign="top" ><?php echo date('d-m-Y', strtotime($rs[$i]['deposit_date'])); ?></td>			
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['first_name']; ?></td>
			
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['nominee_name']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['sum_assured']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['tenure']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['amount']; ?></td>
<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['service_tax']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['total_value']; ?></td>

			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['transaction_charges']; ?>
				<!-- <br /><a href="javascript:void(0)" onclick="javascript:popUp('<?php echo URL; ?>webadmin/window_service_charge_edit.php?id=<?php echo base64_encode($mysql_id); ?>')">Edit</a> -->
			</td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['payment_mode']; ?></td>
			<td width="8%" align="center" valign="top" >
				<?php 
					$branchdespatchDate = $rs[$i]['branch_despatch_date'] == '0000-00-00' ? 'Not Despatched' : date('d/m/Y', strtotime($rs[$i]['branch_despatch_date']));
					echo $branchdespatchDate; 
				?>
			</td>
			<td width="8%" align="center" valign="top" >
				<?php 
					$receiveDate = $rs[$i]['hub_receive_date'] == '0000-00-00' ? 'Not Received' : date('d/m/Y', strtotime($rs[$i]['hub_receive_date']));
					echo $receiveDate; 
				?>
			</td>
			<td width="8%" align="center" valign="top" >
				<?php 
					$despatchDate = $rs[$i]['hub_despatch_date'] == '0000-00-00' ? 'Not Despatched' : date('d/m/Y', strtotime($rs[$i]['hub_despatch_date']));
					echo $despatchDate; 
				?>
			</td>
			
			<!--<td width="8%" align="center" valign="top" >
			<?php if($rs[$i]['payment_type'] == 'INITIAL PAYMENT'){ 
				if(intval($_SESSION[ROLE_ID]) == 3)	
					{
			?>
				<a href="javascript:void(0)" onclick="javascript:popUp('<?php echo URL; ?>webadmin/window_edit_branch.php?id=<?php echo base64_encode($mysql_id); ?>')">Edit</a>
			<?php 
					}
					else
					{
			?>
				<a href="javascript:void(0)" onclick="javascript:popUp('<?php echo URL; ?>webadmin/window_edit.php?id=<?php echo base64_encode($mysql_id); ?>')">Edit</a>
			<?php
					}
				}
			?>
			</td>-->
			
			<!-- <td width="8%" align="center" valign="top" ><?php echo $employee_code; ?></td> -->
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['phone']; ?></td>
			<!-- <td width="8%" align="center" valign="top" ><?php echo $getMasterRecord['email']; ?></td>  -->
			<!-- <td width="8%" align="center" valign="top" >
			<?php if(intval($kyc_ok) == 1) { ?>
			<a href="javascript:void(0)" onclick="javascript:popUp('<?php echo URL; ?>webadmin/subsequent_payment.php?id=<?php echo base64_encode($rs[$i]['folio_no_id']); ?>')">Pay Now
			<?php } ?></td>	 -->
			<!-- <td width="8%" align="center" valign="top" >			
				<a href="javascript:void(0)" onclick="javascript:popUp('<?php echo URL; ?>webadmin/window.php?id=<?php echo base64_encode($mysql_id); ?>')">Generate</a>		
			</td> -->
			<?php
		$status = "Inside Branch";
		if($rs[$i]['branch_despatched'] == 1) {$status="Despatched from Branch"; }
		if($rs[$i]['hub_received'] == 1) {$status="Received by Hub"; }
		if($rs[$i]['hub_despatched'] == 1) {$status="Despatched from Hub"; }
		if($rs[$i]['admin_received'] == 1) {$status="Received by Admin"; }
	?>
			<td width="8%" align="center" valign="top" ><?= $status;?></td>
			<!-- <td width="8%" align="center" valign="top" ><a title="Delete" href="#" onclick="javascript:confirmDelete(<?=$mysql_id; ?>)"><img src="images/delete_icon.gif" border="0" /></a></td> 
			<td width="8%" align="center" valign="top" ><a href="javascript:void(0)" onclick="javascript:popUp('<?php echo URL; ?>webadmin/window_upload.php?id=<?php echo base64_encode($rs[$i]['id']); ?>')">View or Upload</td>-->
    </tr>

<?php		
		}
?>
		<tr>
			<td colspan="30" style="border:0px solid red"><input type="submit" name="btnUpdate" value="Update" class="inplogin"></td>
		</tr>
<?php
	}
?>
    

  </tbody>
</table>
</form>
<form name="sortFrm" action="" method="post">
	<input type="hidden" name="transaction_id" value="">
	<input type="hidden" name="mode" value="">

	<input type="hidden" name="user_id" value="">
	<input type="hidden" name="st" id="st" value="<?=$sortType?>" >
	<input type="hidden" name="sf" id="sf" value="<?=$sortField?>" >
	<input type="hidden" name="pg" id="pg" value="<?=$currentPage?>" >
	<input type="hidden" name="p" id="p" value="<?=$GLOBALS['p']?>" >
	<input type="hidden" name="mid" id="mid" value="<?=$mid?>" >
	<input type="hidden" name="dpp" id="dpp" value="<?=$dataPerPage?>" >
	<INPUT type="hidden" name="search" id="search" value="<?=stripslashes($searchString)?>">  
	<INPUT type="hidden" name="searchField" id="searchField" value="<?=$searchField?>">

</form>
<form name="frm_opts" action="<?=$_SERVER['PHP_SELF'];?>?p=<?=$_REQUEST['p']?>" method="post" >
	<input type="hidden" name="mode" value="">
	<input type="hidden" name="user_id" value="">
	<input type="hidden" name="st" id="st" value="<?=$sortType?>" >
	<input type="hidden" name="sf" id="sf" value="<?=$sortField?>" >
	<input type="hidden" name="pg" id="pg" value="<?=$currentPage?>" >
	<input type="hidden" name="p" id="p" value="<?=$GLOBALS['p']?>" >
	<input type="hidden" name="mid" id="mid" value="<?=$mid?>" >
	<input type="hidden" name="dpp" id="dpp" value="<?=$dataPerPage?>" >
	<INPUT type="hidden" name="search" id="search" value="<?=stripslashes($searchString)?>">
	<INPUT type="hidden" name="searchField" id="searchField" value="<?=$searchField?>"> 
</form>

<SCRIPT LANGUAGE="JavaScript">

<!-- Begin
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=1,width=800,height=600,left = 112,top = 84');");
}
// End -->
</script>

<?php //$objDB->close(); ?>

