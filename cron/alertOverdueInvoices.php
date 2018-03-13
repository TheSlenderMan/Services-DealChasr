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
									WHERE i.overdue = 0
									AND i.invoiceDate <= DATE_SUB(SYSDATE(), INTERVAL 3 DAY)");
//$getOverdueInvoices->bindParam(":date", $nowPlusThreeDays);
$getOverdueInvoices->execute();

$invoices = $getOverdueInvoices->fetchAll();

foreach($invoices AS $k => $v) {
	$email = new email($v['vEmail']);
	$email->setBody($content->getContent("OVERDUEINVOICE", array($v['amount'])));
	$email->setSubject("Your DealChasr Invoice is Overdue " . strtoupper(date("M Y", time())));
	$email->executeMail();
	
	$setOD = $conn->prepare("UPDATE ds_invoices SET overdue = 1 WHERE id = :iid AND invoicePaid = 0");
	$setOD->bindParam(":iid", $v['id']);
	$setOD->execute();
}