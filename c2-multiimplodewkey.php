<?php
include('./Requests/library/Requests.php');
Requests::register_autoloader();

$multiarray = array('key' => array('innerkey' => 'value'),'key2' => array('innerkey2' => 'value2'));

$rootcontainer = 'http://localhost:8080/marmotta/ldp/';
$target_container = 'ispcontent';

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

 pushandput($filename,$rootcontainer,$target_container);

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

function pushandput ($inputfile,$rootcontainer,$target_container) {
// create target_container if it does not exist yet...
$url = $rootcontainer.$target_container;
$url_one = $url;
$headers_one = array('Accept' => 'text/turtle');
$response = Requests::get($url_one,$headers_one);
if($response->status_code == 404) {
  $headers = array('Content-Type' => 'text/turtle','Slug' => $target_container);
  $response = Requests::post($rootcontainer, $headers);
  $string = $response->raw;
  preg_match('/Location: http[:\/a-z0-9-_A-Z]*/',$string,$matches);
  $substring = $matches[0];
  preg_match('/http[:\/a-z0-9-_A-Z]*/',$substring,$matches);
  $url = $matches[0];
}
//echo 'The present url is'.$url;
// This code creates a new ldp containerh
$containertitle = preg_replace('/\.ttl/','',$inputfile);
//This was inherited from above.
//$url = $rootcontainer.$target_container;
//$url = 'http://localhost:8080/marmotta/ldp/';
// add a GET request here
$url_two = $url.'/'.$containertitle;
//echo 'the url with container title is'.$url_two;
$headers_two = array('Accept' => 'text/turtle');
$response = Requests::get($url_two,$headers_two);
if($response->status_code == 404){
  echo "I do not exist";
// do a post to create the container
// Do a post to create the container, and a put to post to it...(do a get to make sure you know what the name of the container is)

$headers = array('Content-Type' => 'text/turtle','Slug' => $containertitle);
$response = Requests::post($url, $headers);
var_dump($response->body);
// find the url of the container created
$string = $response->raw;
preg_match('/Location: http[:\/a-z0-9-_A-Z]*/',$string,$matches);
$substring = $matches[0];
preg_match('/http[:\/a-z0-9-_A-Z]*/',$substring,$matches);
$url = $matches[0];

// function put takes input file and url
// the put request code
putrequest($inputfile,$url);

preg_match('/\/[A-Za-z0-9-_]*$/',$url,$matches);

$matchessub = $matches[0];

preg_match('/[A-Za-z0-9-_]*$/',$matchessub,$itwomatches);

// this returns the label of the ldp container ... using only alphanumeric characters and - and _
return $itwomatches[0];
// return 'duh';

}  else {
  echo "I do exist";
  // do a put to post to the container...(I am not creating a new container with a new name for now..I am trying to overwrite the old)
   putrequest($inputfile,$url_two);

   preg_match('/\/[A-Za-z0-9-_]*$/',$url_two,$matches);

   $matchessub = $matches[0];

   preg_match('/[A-Za-z0-9-_]*$/',$matchessub,$itwomatches);

   // this returns the label of the ldp container ... using only alphanumeric characters and - and _
   return $itwomatches[0];
  //   return 'duh';
  }
  // return the name of the ldp container here...It is whatever the thing is called after the forward slash
}

function putrequest($inputfile,$url) {
  $handle = fopen($inputfile,'r');
  echo($handle);
  $data = fread($handle, filesize($inputfile));
  echo($data);
  //$url = 'http://localhost:8080/marmotta/ldp/'.$containertitle;
  $existingheaders = get_headers($url);
  print_r($existingheaders);
  echo($existingheaders[5]);
  $etag = preg_replace('/ETag: /i','',$existingheaders[5]);
  echo("\n");
  echo($etag);
  echo("\n");
  // do I need the container tag in the header for the put request, it would be easier if I did not need to know ... try it
  //$headers = array('Content-Type' => 'text/turtle','If-Match' => $etag,'Slug' => $containertitle);
  $headers = array('Content-Type' => 'text/turtle','If-Match' => $etag);
  //$headers = array('Content-Type' => 'text/turtle','If-Match' => 'W/"1459004153000"','Slug' => 'Penguins are Awesome');
  $response = Requests::put($url, $headers, $data);
  //$response = Requests:_put($url, $headers, json_encode($data));
  var_dump($response->body);
  fclose($handle);

}
