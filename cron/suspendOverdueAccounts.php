<?php
include "../../api.almanacmedia.co.uk/classes/settings/settings.php";
include "../../api.almanacmedia.co.uk/classes/email/email.php";
include "../../api.almanacmedia.co.uk/classes/content/content.php";

$content = new content();

$conn = new PDO('mysql:dbname=' . DS_DATABASE_NAME . ';host=' . DS_DATABASE_HOST, DS_DATABASE_USERNAME, DS_DATABASE_PASSWORD);

$now              = date("Y-m-d", time());
$nowPlusThreeDays = date("Y-m-d", strtotime("+3 days"));

$getOverdueInvoices = $conn->prepare("SELECT i.invoiceDate, i.amount, i.venueID, i.id, v.vEmail FROM ds_invoices AS i
									JOIN ds_venues AS v
									ON v.id = i.venueID
									WHERE i.overdue = 1
									AND i.invoiceDate <= DATE_SUB(SYSDATE(), INTERVAL 5 DAY)");
//$getOverdueInvoices->bindParam(":date", $nowPlusThreeDays);
$getOverdueInvoices->execute();

$invoices = $getOverdueInvoices->fetchAll();

foreach($invoices AS $k => $v) {
	$setOD = $conn->prepare("UPDATE ds_venues SET active = 0, suspension = 'OVERDUE' WHERE id = :vid");
	$setOD->bindParam(":vid", $v['venueID']);
	$setOD->execute();
}
}