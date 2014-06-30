<?php
if($_POST['key']=='0Y863Z2DQL2VVGLQ'){
	if($_POST['status']=='add'||$_POST['status']=='update') {
		$title = $_POST['title'];
		$comment = $_POST['comment'];
		$comment = preg_replace('/(\[.+?\])/', '', $comment);
		$url = $_POST['url'];
		$body = $comment.' / '.$title;
		$subject = $url;
		// 文字化けする場合は明示的に文字エンコーディングを指定してください
		// $encoding = mb_detect_encoding($body, "SJIS,EUC-JP,JIS,UTF-8");
		// if ($encoding != "JIS") {
		//   //echo $encoding;
		//   $subject = mb_convert_encoding($url, "JIS", $encoding);
		//   $body = mb_convert_encoding($body, "JIS", $encoding);
		// }
		$base64subject = '=?ISO-2022-JP?B?'.base64_encode($subject).'?=';
		//list($content_type, $fullbody) = getAttatchFile($aFilenames, $body);

		$from = 'iftttlink@girled.net';
		$to = 'huntinggirled@gmail.com';
		$content_type = 'text/plain';
		$header = <<< __EOT
From: $from
MIME-Version: 1.0
Content-Type: $content_type
X-Mailer: mail
Content-Transfer-Encoding: 7bit
__EOT;

		mail($to, $base64subject, $body, ereg_replace("\r\n|\r|\n","\n", trim($header)));
		echo "OK ".$body;
	}
}
?>

309701968420