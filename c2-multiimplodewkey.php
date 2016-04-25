<?php
include('./Requests/library/Requests.php');
Requests::register_autoloader();

$multiarray = array('key' => array('innerkey' => 'value'),'key2' => array('innerkey2' => 'value2'));



/*
 $subarray[0] = $multiarray[0][1];
 $subarray[1] = $multiarray[1][1];

 print_r($subarray);
*/

foreach($secondarray as $a => $b) {
  $subarray = array();
   $subarraymulti = array();
  $astring = strval($a).'.ttl';
  preg_match('/([^\\/])*ttl/',$astring,$matches);
  $filename = $matches[0];
  $containername = preg_replace('/\.ttl/','',$filename);
  //echo $containername;
  echo($filename);
  // get the slug from the $filename by subtracting .ttl for postrequest.php here
//  echo("\r\n");

// I am going to assume I do not need to create a file by commenting out below..
  $fp = fopen('./'.$filename,'w');

  // echo("\r\n");
 foreach($b as $c => $d) {
// echo($multiarray[$a][$c]);
//echo(strval($c).' '.strval($secondarray[$a][$c]));
//echo strval($c);
      if(preg_match('/dc\/terms\/date/', strval($c)) || preg_match('/dc\/terms\/created/', strval($c)) || preg_match('/dc\/terms\/modified/', strval($c)) == 1) {
      //  echo "I have a date or I am created or modified";
      } else {
        if(preg_match('/rss\/1.0\/modules\/content\/encoded/', strval($c)) == 1) {
          $string = strval($secondarray[$a][$c]);
          $stringproc = preg_replace('/</','&lt;',$string);
          $stringproc2 = preg_replace('/>/','&gt;',$stringproc);
          $stringproc3 = preg_replace('/\'/','\\\'',$stringproc2);
          $subarraymulti[$c] = httpmatched(strval($c)).' '.httpmatched($stringproc3).' ;';
        } elseif (preg_match('/rss\/1.0\/modules\/content\/encoded/', strval($c)) != 1) {
           if(preg_match('/schema\.org\/name/', strval($c)) == 1) {
             $subarraymulti[$c] = httpmatched(strval($c)).' '.httpmatched(strval($secondarray[$a][$c])).' ;'."\n"
             .'<http://purl.org/dc/terms/title>'.' '.httpmatched(strval($secondarray[$a][$c])).' ;';
           } if(preg_match('/schema\.org\/name/', strval($c)) != 1) {
          $subarraymulti[$c] = httpmatched(strval($c)).' '.httpmatched(strval($secondarray[$a][$c])).' ;';
           }
        }

      }
//echo(httpmatched(strval($c)));
//echo("\r\n");
//$subarraymulti[$c] = strval($c).' '.strval($secondarray[$a][$c]);

//echo("\r\n");
// $subarray[$c] = $secondarray[$a][$c];
// echo strval($subarray[$c]);
 //$subarraymulti[$c] = strval($c).' '.strval($subarray[$c]);
 //echo($subarraymulti[$c]);
 }
 $withcommapre = implode("\n",$subarraymulti);
 // http://stackoverflow.com/questions/5592994/remove-the-last-character-from-string
 $withcommamid = substr($withcommapre, 0, -1);
 $withcomma = '<>'."\n".$withcommamid.'.';
 echo $withcomma;
/*
 echo($withcomma);
 echo $containername;
 echo('-------');
 echo("\r\n");
*/

// $withcomma = implode("\n",$subarraymulti);
// I am going to assume I do not need to create a file by commenting out below..
 fwrite($fp, $withcomma);


 // see if this works...otherwise close and reopen file and put the function somewhere else...
// postandputtoldp($containername,$withcomma);
 //run the putrequest2.php file contents here...
 // I am going to assume I do not need to create a file by commenting out below..
  fclose($fp);

//rm pushandput($filename);

}

//print_r($subarray);



//echo($withcomma);

function httpmatched($string) {
  $matched = preg_match('/^http/', $string);
  if ($matched == 1) {
    $newstring = '<'.$string.'>';
  } elseif ($matched == 0) {
    $newstring = '\''.$string.'\'';
  }
  return $newstring;
}

/*
function postandputtoldp($containertitle,$data) {
$url = 'http://localhost:8080/marmotta/ldp/drupalsite2/';
$headers = array('Content-Type' => 'text/turtle','Slug' => $containertitle);
$response = Requests::post($url, $headers);

$url = $url.$containertitle;
//echo $url;

$existingheaders = get_headers($url);
//echo $existingheaders;

$etag = preg_replace('/ETag: /i','',$existingheaders[5]);
echo $etag;

$headers = array('Content-Type' => 'text/turtle',
                 'If-Match' => $etag ,
                  'Slug' => $containertitle);

echo($data);

$response = Requests::put($url,$headers,$data);
}
*/

//$inputfile = "joe-smith.ttl";

//$containertitle = 'Let-us-build-a-Boat';

//pushandput($inputfile);

function pushandput ($inputfile) {

$containertitle = preg_replace('/\.ttl/','',$inputfile);
// change the portal page name to an appropriate name
$url = 'http://localhost:8080/marmotta/ldp/isaportal3/';
$headers = array('Content-Type' => 'text/turtle','Slug' => $containertitle);
$response = Requests::post($url, $headers);

var_dump($response->body);

?>

<?php

$handle = fopen($inputfile,'r');
echo($handle);
$data = fread($handle, filesize($inputfile));

echo($data);
$url = 'http://localhost:8080/marmotta/ldp/isaportal3/'.$containertitle;
$existingheaders = get_headers($url);
print_r($existingheaders);
echo($existingheaders[5]);
$etag = preg_replace('/ETag: /i','',$existingheaders[5]);
echo("\n");
echo($etag);
echo("\n");
$headers = array('Content-Type' => 'text/turtle','If-Match' => $etag,'Slug' => $containertitle);
//$headers = array('Content-Type' => 'text/turtle','If-Match' => 'W/"1459004153000"','Slug' => 'Penguins are Awesome');
$response = Requests::put($url, $headers, $data);
//$response = Requests:_put($url, $headers, json_encode($data));
var_dump($response->body);
fclose($handle);
}
