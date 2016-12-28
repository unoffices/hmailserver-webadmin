<?php
if (!defined('IN_WEBADMIN'))
	exit();

if (hmailGetAdminLevel() != 2)
	hmailHackingAttemp();

$obSettings = $obBaseApp->Settings();
$obAntivirus = $obSettings->AntiVirus();

$action = hmailGetVar("action","");

$str_delete = $obLanguage->String("Remove");

if($action == "save") {
	$obAntivirus->Action = hmailGetVar("avaction",0);
	$obAntivirus->NotifySender = hmailGetVar("avnotifysender",0);
	$obAntivirus->NotifyReceiver = hmailGetVar("avnotifyreceiver",0);
	$obAntivirus->MaximumMessageSize = hmailGetVar("MaximumMessageSize",0);

	$obAntivirus->ClamWinEnabled = hmailGetVar("clamwinenabled",0);
	$obAntivirus->ClamWinExecutable = hmailGetVar("clamwinexecutable",0);
	$obAntivirus->ClamWinDBFolder = hmailGetVar("clamwindbfolder",0);

	$obAntivirus->ClamAVEnabled = hmailGetVar("ClamAVEnabled",0);
	$obAntivirus->ClamAVHost = hmailGetVar("ClamAVHost","");
	$obAntivirus->ClamAVPort = hmailGetVar("ClamAVPort","");

	$obAntivirus->CustomScannerEnabled = hmailGetVar("customscannerenabled",0);
	$obAntivirus->CustomScannerExecutable = hmailGetVar("customscannerexecutable",0);
	$obAntivirus->CustomScannerReturnValue = hmailGetVar("customscannerreturnvalue",0);

	$obAntivirus->EnableAttachmentBlocking = hmailGetVar("EnableAttachmentBlocking",0);
}

$avaction = $obAntivirus->Action;
$avnotifysender = $obAntivirus->NotifySender;
$avnotifyreceiver = $obAntivirus->NotifyReceiver;
$MaximumMessageSize = $obAntivirus->MaximumMessageSize;

$EnableAttachmentBlocking = $obAntivirus->EnableAttachmentBlocking;

$clamwinenabled = $obAntivirus->ClamWinEnabled;
$clamwinexecutable = $obAntivirus->ClamWinExecutable;
$clamwindbfolder = $obAntivirus->ClamWinDBFolder;

$ClamAVEnabled = $obAntivirus->ClamAVEnabled;
$ClamAVHost = $obAntivirus->ClamAVHost;
$ClamAVPort = $obAntivirus->ClamAVPort;

$customscannerenabled = $obAntivirus->CustomScannerEnabled;
$customscannerexecutable = $obAntivirus->CustomScannerExecutable;
$customscannerreturnvalue = $obAntivirus->CustomScannerReturnValue;

$avactiondeletemailchecked = hmailCheckedIf1($avaction == 0);
$avactiondeletattachmentschecked = hmailCheckedIf1($avaction == 1);
?>
<script language="javascript" type="text/javascript">
function testVirusScanner(scannerType) {
	httpObject = getHTTPObject();
	if (httpObject != null) {
		switch (scannerType) {
			case "ClamAV":
				document.getElementById('ClamAVTestResult').innerHTML = "";
				var clamAVHost = document.getElementById('ClamAVHost').value;
				var clamAPort = document.getElementById('ClamAVPort').value;
				var url = "index.php?page=background_ajax_virustest&TestType=ClamAV&Hostname=" + clamAVHost + "&Port=" + clamAPort;
				sendRequest(url, "ClamAVTestResult");
				break;
			case "ClamWin":
				document.getElementById('ClamWinTestResult').innerHTML = "";
				var executable = document.getElementById('clamwinexecutable').value;
				var database = document.getElementById('clamwindbfolder').value;
				var url = "index.php?page=background_ajax_virustest&TestType=ClamWin&Executable=" + executable + "&DatabaseFolder=" + database;
				sendRequest(url, "ClamWinTestResult");
				break;
			case "External":
				document.getElementById('ExternalTestResult').innerHTML = "";
				var executable = document.getElementById('customscannerexecutable').value;
				var returnValue = document.getElementById('customscannerreturnvalue').value;
				var url = "index.php?page=background_ajax_virustest&TestType=External&Executable=" + executable + "&ReturnValue=" + returnValue;
				sendRequest(url, "ExternalTestResult");
				break;
			default:
				alert(scannerType);
				break;
		}
	}
}
function sendRequest(url, responseDiv) {
	httpObject.open("GET", url, true);
	httpObject.send(null);
	httpObject.onreadystatechange = function() {
		printResponse(httpObject, responseDiv);
	};
}
function printResponse(httpObject, elementName) {
	if (httpObject.readyState == 4) {
		if (httpObject.responseText == "1")
			document.getElementById(elementName).innerHTML = "<font color=green>Test succeeded.</font>"
		else
			document.getElementById(elementName).innerHTML = "<font color=red>Test failed.</font>"
	}
}
</script>
    <div class="box medium">
      <h2><?php EchoTranslation("Anti-virus") ?></h2>
      <form action="index.php" method="post" onsubmit="return $(this).validation();" class="cd-form">
<?php
	PrintHidden("page", "smtp_antivirus");
	PrintHidden("action", "save");
?>
        <p><?php EchoTranslation("When a virus is found")?></p>
        <div style="position:relative; display:inline-block;"><input type="radio" name="avaction" id="1" value="0" <?php echo $avactiondeletemailchecked?>><label for="1"><?php EchoTranslation("Delete e-mail")?></label></div>
        <div style="position:relative; display:inline-block;"><input type="radio" name="avaction" id="2" value="1" <?php echo $avactiondeletattachmentschecked?>><label for="2"><?php EchoTranslation("Delete attachments")?></label></div>
<?php
	PrintPropertyEditRow("MaximumMessageSize", "Maximum message size to virus scan (KB)", $MaximumMessageSize, 10, "number", "small");
?>
        <h3><a href="#">ClamWin</a></h3>
        <div class="hidden">
<?php
	PrintCheckboxRow("clamwinenabled", "Enabled", $clamwinenabled);
	PrintPropertyEditRow("clamwinexecutable", "ClamScan executable", $clamwinexecutable, 60);
	PrintPropertyEditRow("clamwindbfolder", "Path to ClamScan database", $clamwindbfolder, 60);
?>
          <p><input type="button" value="<?php EchoTranslation("Test")?>" onclick="testVirusScanner('ClamWin');"></p>
          <p id="ClamWinTestResult"></p>
        </div>
        <h3><a href="#">ClamAV</a></h3>
        <div class="hidden">
<?php
	PrintCheckboxRow("ClamAVEnabled", "Use ClamAV", $ClamAVEnabled);
	PrintPropertyEditRow("ClamAVHost", "Host name", $ClamAVHost);
	PrintPropertyEditRow("ClamAVPort", "TCP/IP port", $ClamAVPort, 5, "number");
?>
          <p><input type="button" value="<?php EchoTranslation("Test")?>" onclick="testVirusScanner('ClamAV');"></p>
          <p id="ClamAVTestResult"></p>
        </div>
        <h3><a href="#"><?php EchoTranslation("External virus scanner")?></a></h3>
        <div class="hidden">
<?php
	PrintCheckboxRow("customscannerenabled", "Enabled", $customscannerenabled);
	PrintPropertyEditRow("customscannerexecutable", "Scanner executable", $customscannerexecutable, 60);
	PrintPropertyEditRow("customscannerreturnvalue", "Return value", $customscannerreturnvalue, 5, "number");
?>
          <p><input type="button" value="<?php EchoTranslation("Test")?>" onclick="testVirusScanner('External');"></p>
          <p id="ExternalTestResult"></p>
        </div>
        <h3><a href="#"><?php EchoTranslation("Block attachments")?></a></h3>
        <div class="hidden">
<?php
	PrintCheckboxRow("EnableAttachmentBlocking", "Block attachments with the following extensions:", $EnableAttachmentBlocking);
?>
        <table>
          <tr>
            <th style="width:30%;"><?php EchoTranslation("Name")?></th>
            <th style="width:60%;"><?php EchoTranslation("Description")?></th>
            <th style="width:10%;">&nbsp;</th>
          </tr>
<?php
$blockedAttachments = $obAntivirus->BlockedAttachments;

for ($i = 0; $i < $blockedAttachments->Count; $i++) {
	$blockedAttachment = $blockedAttachments->Item($i);
	$id = $blockedAttachment->ID;
	$wildcard = $blockedAttachment->Wildcard;
	$description= $blockedAttachment->Description;
?>
          <tr>
            <td><a href="?page=blocked_attachment&action=edit&id=<?php echo $id?>"><?php echo PreprocessOutput($wildcard);?></a></td>
            <td><?php echo PreprocessOutput($description);?></td>
            <td><?php echo "<a href=\"?page=background_blocked_attachment_save&action=delete&id=$id\" class=\"delete\">$str_delete</a>";?></td>
          </tr>
<?php
}
?>
        </table>
        <div class="buttons center"><a href="?page=blocked_attachment&action=add" class="button">Add new extension</a></div>
        </div>
<?php
	PrintSaveButton();
?>
      </form>
    </div>