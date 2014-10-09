<?php
/*--------------------------------------------------------------------------------
# com_ijoomeradv_1.5 - iJoomer Advanced
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined('_JEXEC') or die;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="style.css" media="all"/>
	<title>RSA</title>
</head>

<body>

<center>
	<div style="width: 80%; position:absolute; left:10%; top:0%; z-index:1">
		<br/>

		<div class="tabArea" align="center">
			<a class="tab" href="example.php">Example</a>
			<a class="tab" href="about.html">About RSA</a>
			<a class="tab" href="DigitalSignature.html">About Digital Signature</a>
		</div>

		<div class="Paragraph">
			<?php
			include 'MCrypt.php';
			$RSA = new MCrypt();

			$key = "tailoredsolution";

			$message1 = "Hello all";

			$encoded1 = $RSA->encrypt($message1);

			$decoded1 = $RSA->decrypt($encoded1);

			echo "<b>Message:</b> $message1<br />\n";
			echo "<b>Encoded:</b> $encoded1<br />\n";
			echo "<b>Decoded:</b> $decoded1<br />\n";
			exit;

			$message = "هذا نص عربي بتنسيق مجموعة المحارف العالمية";
			$encoded = $RSA->encrypt($message, $keys[1], $keys[0], 5);
			$decoded = $RSA->decrypt($encoded, $keys[2], $keys[0]);

			echo "<b>Message:</b> $message<br />\n";
			echo "<b>Encoded:</b> $encoded<br />\n";
			echo "<b>Decoded:</b> $decoded<br />\n";
			echo "Success: " . (($decoded == $message) ? "True" : "False") . "<hr />\n";

			$message = "عمرو موسى هو سياسي و وزير الخارجية المصري السابق، وأمين عام جامعة الدول العربية. ولد في 1936.تخرج من كليه الحقوق عمل كوزير للخارجية في مصر من 1991 إلى 2001. تم أنتخابه كأمين عام لجامعة الدول العربية في مايو 2001، وما زال قائما بهذا المنصب إلى يومنا هذا.";
			$signature = $RSA->sign($message, $keys[1], $keys[0]);
			echo "<b>Original Message:</b> <div dir=rtl>$message</div><br />\n";
			echo "<b>Message Signature:</b><br /> $signature<br /><br />\n";

			$fake_msg = "عمرو موسى هو سياسي و وزير الخارجية المصري السابق، وأمين عام جامعة الدول العربية. ولد في 1936.تخرج من كليه التجارة والإقتصاد  عمل كوزير للخارجية في مصر من 1991 إلى 2001. تم أنتخابه كأمين عام لجامعة الدول العربية في مايو 2001، وما زال قائما بهذا المنصب إلى يومنا هذا.";
			echo "<b>Fake Message:</b> <div dir=rtl>$fake_msg</div><br />\n";

			echo "<b>Check original message against given signature:</b><br />\n";
			echo "Success: " . (($RSA->prove($message, $signature, $keys[2], $keys[0])) ? "True" : "False") . "<br /><br />\n";

			echo "<b>Check fake message against given signature:</b><br />\n";
			echo "Success: " . (($RSA->prove($fake_msg, $signature, $keys[2], $keys[0])) ? "True" : "False") . "<br /><hr />\n";

			$file = 'about.html';
			$signature = $RSA->signFile($file, $keys[1], $keys[0]);
			echo "<b>Original File:</b> $file<br /><br />\n";
			echo "<b>File Signature:</b><br /> $signature<br /><br />\n";

			$fake_file = 'style.css';
			echo "<b>Fake File:</b> $fake_file<br /><br />\n";

			echo "<b>Check original file against given signature:</b><br />\n";
			echo "Success: " . (($RSA->proveFile($file, $signature, $keys[2], $keys[0])) ? "True" : "False") . "<br /><br />\n";

			echo "<b>Check fake file against given signature:</b><br />\n";
			echo "Success: " . (($RSA->proveFile($fake_file, $signature, $keys[2], $keys[0])) ? "True" : "False") . "<br /><br />\n";

			?>
		</div>
</center>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
	_uacct = "UA-1268287-1";
	urchinTracker();
</script>
</body>
</html>
