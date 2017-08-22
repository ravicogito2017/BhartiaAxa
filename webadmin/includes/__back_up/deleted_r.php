<?php

include_once("../utility/config.php");
include_once("../utility/dbclass.php");
include_once("../utility/functions.php");
include_once("new_functions.php");
//print_r($_POST);

set_time_limit(0); 

$pageOwner = "'admin','superadmin'";
chkPageAccess($_SESSION[ROLE_ID], $pageOwner); 


extract($_POST);


// Write functions here



$objDB = new DB();

$where = "WHERE is_deleted=1 ";
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


if(isset($from_date))
	{
		$_SESSION['from_date'] = $from_date;
	}

	if(isset($to_date))
	{
		$_SESSION['to_date'] = $to_date;
	}

	if(isset($_SESSION['from_date']) && $_SESSION['from_date'] != '') 
	{
		$where.= ' AND deposit_date >="'.date('Y-m-d', strtotime($_SESSION['from_date'])).'"';
	}

	if(isset($_SESSION['to_date']) && $_SESSION['to_date'] != '') 
	{
		$where.= ' AND deposit_date <="'.date('Y-m-d', strtotime($_SESSION['to_date'])).'"';
	}


$Query = "select count(id) as CNT from renewal_master  ".$where;
$objDB->setQuery($Query);
$rsTotal = $objDB->select();
$displayTotal=$rsTotal[0]['CNT'];
$extraParam = "&p=".$GLOBALS['p'];

$dpp = true;
$totalRecordCount = $rsTotal[0]['CNT'];
$pageSegmentSize = 15;
include_once("../utility/pagination.php");


$Query = "SELECT * FROM renewal_master ".$where.$OrderBY.$Limit;

#echo $Query;
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

<form name="addForm" id="addForm" action="" method="post">
<TABLE class="table_border" cellSpacing=2 cellPadding=5 width="100%" align=center border=0>
  <tbody>

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
						<td colspan="3" align="center"><input type="submit" name="btnSubmit" value="Search">&nbsp;<?php if(isset($_POST) && (count($_POST)) > 0){ ?><!-- <a href="xml_download.php" style="text-decoration:none;" ><input type="button" name="btnXML" value="Download XML"> --></a><?php } ?></td>
						
					</tr>

    <tr> 
      <td colspan="14">
        <? showMessage(); ?>
      </td>
    </tr>
		<tr> 
      <td colspan="14" align="right">
        <a title=" Export to Excel " href="<?=URL?>deleted_r_excel.php" ><img src="images/excel_icon.gif" border="0" title="Download" /></a>
      </td>
    </tr>
		<tr> 
      <td colspan="14" align="right">
        <? include_once("../utility/pagination_display.php");?>
      </td>
    </tr>
    <tr class="TDHEAD"> 
      <td colspan="14">Transaction Listing</td>
    </tr>
    
    <tr> 
      <td width="8%" align="center" valign="top" class="tbllogin">Application No.</td>
			<!--<td width="8%" align="center" valign="top" class="tbllogin">Folio No.</td>
-->
			<td width="8%" align="center" valign="top" class="tbllogin">Branch Name</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Deposit Date</td>			
			<td width="8%" align="center" valign="top" class="tbllogin">Name</td>
			<!--<td width="8%" align="center" valign="top" class="tbllogin">Middle Name</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Last Name</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Committed Amount</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Tenure (Months)</td>-->
			<td width="8%" align="center" valign="top" class="tbllogin">Amount</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Service Charges</td>
			<!-- <td width="8%" align="center" valign="top" class="tbllogin">Phone</td> -->
			<!--<td width="8%" align="center" valign="top" class="tbllogin">Email</td>
-->
			<!--<td width="8%" align="center" valign="top" class="tbllogin">Receipt No.</td>
			 <td width="8%" align="center" valign="top" class="tbllogin">Duplicate</td> -->
			 <td width="8%" align="center" valign="top" class="tbllogin">Receipt No.</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Receipt Serial No.</td>
			<td width="8%" align="center" valign="top" class="tbllogin">Reason</td>
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
			//$trusted_id = find_customer_id_through_id($rs[$i]['customer_id']);
			$branch_name = find_branch_name($rs[$i]['branch_id']);

			//$selMasterRecord = mysql_query("SELECT customer_id, first_name, middle_name, last_name, phone, email FROM customer_master WHERE id='".$rs[$i]['customer_id']."'");

			//if(mysql_num_rows($selMasterRecord) > 0)
			//{	
			//	$getMasterRecord = mysql_fetch_array($selMasterRecord);
			//}

			//$selFolioRecord = mysql_query("SELECT folio_no, committed_amount, tenure FROM customer_folio_no WHERE id = '".$rs[$i]['folio_no_id']."'");

			//if(mysql_num_rows($selFolioRecord) > 0)
			//{
			//	$getFolioRecord = mysql_fetch_array($selFolioRecord);
			//}
?>
	<tr> 
      <td width="8%" align="center" valign="top" ><?php echo $rs[$i]['policy_no']; ?></td>
			<!--<td width="8%" align="center" valign="top" ><?php echo $getFolioRecord['folio_no']; ?></td>-->
			<td width="8%" align="center" valign="top" ><?php echo $branch_name; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo date('d-m-Y', strtotime($rs[$i]['deposit_date'])); ?></td>			
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['insured_name']; ?></td>
			<!--<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['middle_name']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['last_name']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['last_name']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $getFolioRecord['tenure']; ?></td>
-->
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['amount']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['transaction_charges']; ?></td>
			<!--<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['phone']; ?></td>
			 <td width="8%" align="center" valign="top" ><?php echo $rs[$i]['email']; ?></td>  -->
			
			<!-- <td width="8%" align="center" valign="top" ><a href="javascript:void(0)" onclick="javascript:popUp('<?php echo URL; ?>webadmin/window_duplicate.php?id=<?php echo base64_encode($mysql_id); ?>')">Generate Duplicate</a></td> -->
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['transaction_id']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['receipt_number']; ?></td>
			<td width="8%" align="center" valign="top" ><?php echo $rs[$i]['cancellation_reason'];?></td>
    </tr>


<?php		
		}
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
<!-- Idea by:  Nic Wolfe -->
<!-- This script and many more are available free online at -->
<!-- The JavaScript Source!! http://javascript.internet.com -->

<!-- Begin
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=1,width=800,height=600,left = 112,top = 84');");
}
// End -->
</script>

<?php //$objDB->close(); ?>

