<?php

$credentialPath = "./data/app.cred";

require_once("./lib-pcloud/autoload.php");

if (!isset($_GET["folderid"]))
	die("unknown folderid");

if (!isset($_GET["checksum_file_id"]))
	die("unknown checksum_file_id");

$folderid = (int)$_GET["folderid"];
$checksum_file_id = (int)$_GET["checksum_file_id"];

// if (isset($_GET["type"]))
// 	$type = $_GET["type"];
// else
$type = "sha1";

function load_hash_file ($pCloudApp, $checksum_file_id)
{
	$pCloudHashFile = new pCloud\File($pCloudApp);
	$pCloudHashFile->download($checksum_file_id, "/tmp/");

	$checksum_file = "/tmp/checksum.sha1";
	
	$handle = fopen($checksum_file, "r");
	$contents = explode("\n", trim(fread($handle, filesize($checksum_file))));
	fclose($handle);

	$r = array();

	foreach ($contents as $item) {
		$hash2 = substr($item, 0, 40);
		$fname2 = substr($item, 42);

		$r[] = array('fname' => $fname2, 'hash' => $hash2);

		#echo "$fname2 -> $hash2<br>\n";
	}

	return $r;
}

function cmp_flist ($a, $b)
{
    return strcmp($a->name, $b->name);
}

$allgreen = 1;

function cmp_hash_from_file ($hashes, $fname, $hash)
{
	global $allgreen;

	foreach ($hashes as $item) {
		if ($item['fname'] == $fname) {
			if ($item['hash'] == $hash)
				echo "<font color=\"#28AD29\">Hash OK</font>";
			else {
				echo "<font color=\"#F54739\">Hash FAIL</font>";
				$allgreen = 0;
			}
			return;
		}
	}

	echo "<font color=\"#F039F5\">Hash Not Found</font>";
	$allgreen = 0;
}

try {
	$pCloudApp = new pCloud\App();

	$cred = pCloud\Auth::getAuth($credentialPath);

	$access_token = $cred['access_token'];
	$locationid = 1;

	$pCloudApp->setAccessToken($access_token);
	$pCloudApp->setLocationId($locationid);

	$hashes = load_hash_file($pCloudApp, $checksum_file_id);

	$pCloudFolder = new pCloud\Folder($pCloudApp);

	$meta = $pCloudFolder->getMetadata($folderid)->metadata;

	echo "Folder: {$meta->name}<br><br>\n";
	
	$content = $pCloudFolder->getContent($folderid);
	usort($content, "cmp_flist");

	$has_break = 0;
	foreach ($hashes as $hi) {
		$found = 0;

		foreach ($content as $item) {
			if ($item->name == $hi['fname']) {
				$found = 1;
				break;
			}
		}

		if (!$found) {
			$has_break = 1;
			$allgreen = 0;
			echo "<font color=\"#F54739\">File not found in pcloud:</font> ".$hi['fname']."<br>\n";
		}
	}

	if ($has_break) {
		echo "<br><br>\n";
	}
	
	foreach ($content as $item) {
		if (!$item->isfolder) {
			$pCloudFile = new pCloud\File($pCloudApp);
			$info = $pCloudFile->getInfo($item->fileid);
			$mf = $info->metadata;
			
			if ($item->name != "checksum.sha1") {
				echo "{$info->sha1}  {$item->name}  ";
				cmp_hash_from_file($hashes, $item->name, $info->sha1);
				echo "<br>\n";
			}
		}
	}

	echo "<br><br>\n";
	
	if ($allgreen == 0)
		echo "<font color=\"#F54739\"><strong>errors found</strong></font>\n";
	else
		echo "<font color=\"#28AD29\"><strong>no error found</strong></font>\n";
} catch (Exception $e) {
	echo $e->getMessage();
}

?>