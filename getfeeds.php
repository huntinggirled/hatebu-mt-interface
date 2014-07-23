<?php
//昨日のはてブのフィードを取得してハンチングブログにEntryする
mb_internal_encoding("utf-8");
$ini = parse_ini_file("config.ini");
$date = (!empty($_GET['date']))?$_GET['date']:date('Ymd');
$atom = file_get_contents("http://b.hatena.ne.jp/".$ini['hatenaid']."/atomfeed?date=".$date);
$atomobj = simplexml_load_string($atom);
$title = "";
$text = "";
$dateCreated = "";
foreach($atomobj->entry as $entrydata) {
  $text .= $entrydata->content;
  $dateCreated = $entrydata->issued;
}
if($text=="") {
  print 'NO BOOKMARK DATE='.$date;
  file_put_contents("log.txt", date("Y-m-d H:i:s")." NO BOOKMARK\n", FILE_APPEND | LOCK_EX);
  return;
}

//trimming
$text = str_replace('<a ', '<a target="_blank" ', $text);
$title = "ブックマーク ".date('Y年n月j日', strtotime($dateCreated));

//センテンス抽出
$sentence = $text;
$sentence = strip_tags($sentence);
$sentence = str_replace("girled", "", $sentence);
$sentence = trim($sentence);
$data = array(
  'sentence' => $sentence,
);

//キーフレーズ抽出、タイトルに付加
$subject_max_length = $ini['subjectmaxlength'];
$appid = $ini['keyphraseappid'];
$url = $ini['keyphraseurl'];
$options = array('http' => array(
  'method' => 'POST',
  'header' => 'User-Agent: Yahoo AppID: '.$appid,
  'content' => http_build_query($data),
));
$contents = file_get_contents($url, false, stream_context_create($options));
//file_put_contents("log.txt", date("Y-m-d H:i:s")." ".$contents."\n", FILE_APPEND | LOCK_EX);
$responsexml = simplexml_load_string($contents);
$result_num = count($responsexml->Result);
if($result_num>0){
  for($i=0; $i<$result_num; $i++){
    $result = $responsexml->Result[$i];
    $keyphrase = htmlspecialchars($result->Keyphrase, ENT_QUOTES);
    if(mb_strlen($title.' '.$keyphrase, 'UTF-8')>$subject_max_length) {
      $title .= '...';
      break;
    }
    $title .= ' '.$keyphrase;
  }
}

$data = array(
  'blogid' => $ini['blogid'],
  'authorid' => $ini['authorid'],
  'title' => $title,
  'text' => $text,
  'status' => ($ini['status'])?$ini['status']:'release',
  'categoryid' => $ini['categoryid'],
);
// POST
$url = $ini['postcgi'];
$options = array('http' => array(
  'method' => 'POST',
  'header' => 'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36',
  'content' => http_build_query($data),
));
$contents = file_get_contents($url, false, stream_context_create($options));
//$text = ereg_replace("\r|\n"," ",$text);
file_put_contents("log.txt", date("Y-m-d H:i:s")." ".$title."\n", FILE_APPEND | LOCK_EX);
print $contents;
?>