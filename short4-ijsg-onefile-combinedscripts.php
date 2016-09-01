<?php
/**
 * Making a SPARQL SELECT query
 *
 * This example creates a new SPARQL client, pointing at the
 * dbpedia.org endpoint. It then makes a SELECT query that
 * returns all of the countries in DBpedia along with an
 * english label.
 *
 * Note how the namespace prefix declarations are automatically
 * added to the query.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://unlicense.org/
 */
set_include_path(get_include_path() . PATH_SEPARATOR . './easyrdf-0.9.0/lib/');
require_once "./easyrdf-0.9.0/lib/EasyRdf.php";
//  require_once "../html_tag_helpers.php";
// Setup some additional prefixes for the Drupal Site
EasyRdf_Namespace::set('schema', 'http://schema.org/');
EasyRdf_Namespace::set('content', 'http://purl.org/rss/1.0/modules/content/');
EasyRdf_Namespace::set('dc', 'http://purl.org/dc/terms/');
EasyRdf_Namespace::set('foaf', 'http://xmlns.com/foaf/0.1/');
EasyRdf_Namespace::set('og', 'http://ogp.me/ns#');
EasyRdf_Namespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
EasyRdf_Namespace::set('sioc', 'http://rdfs.org/sioc/ns#');
EasyRdf_Namespace::set('sioct', 'http://rdfs.org/sioc/types#');
EasyRdf_Namespace::set('skos', 'http://www.w3.org/2004/02/skos/core#');
EasyRdf_Namespace::set('xsd', 'http://www.w3.org/2001/XMLSchema#');
EasyRdf_Namespace::set('owl', 'http://www.w3.org/2002/07/owl#');
EasyRdf_Namespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
EasyRdf_Namespace::set('rss', 'http://purl.org/rss/1.0/');
EasyRdf_Namespace::set('site', 'http://localhost/iksce/ns#');
$sparql = new EasyRdf_Sparql_Client('http://investors.ddns.net:8080/marmotta/sparql/');

// Include the requests library  ... see ijsg-onefile-combinedscripts
include('./Requests/library/Requests.php');
Requests::register_autoloader();

// Set debugging
function dbg($level, $message, $dbg) {
  if ($dbg = 1) {
      if ($level = 1) {
          echo $message;
      }
      elseif ($level = 2) {
          print_r($message);
      }
      elseif ($level = 3) {
         var_dump($message);
      }
  }
}
// Set debug to 1 for debugging
$dbg = 1;

// Find all of the predicates for taxonomy terms

// comment ouf for testing...

// Perform SELECT query on RDF store to populate array for all triples with rdfs:seeAlso predicate
 $result_taxpred = $sparql->query(
 'PREFIX dct: <http://purl.org/dc/terms/>
 PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
 PREFIX schema: <http://schema.org/>
 SELECT * {
   SERVICE <https://integratedspaceanalytics.com/new/isp_data_endpoint> {
     SELECT DISTINCT ?p { ?s ?p ?o .
              FILTER regex(?s, "/portal/")
              FILTER regex(?o, "taxonomy") }
   } }
'
 );
// Initialize storage array to hold all of the predicates used for taxonomy terms
 $taxvocabbypredicate = array();
 $taxvocabbyname = array();
 $taxvocabbynamebackup = array('ISP_Column','Tags');
 
 $taxvocabbyname = $taxvocabbynamebackup;
// Populate the storage arrays including the  predicate
 foreach ($result_taxpred as $row) {
     array_push($taxvocabbypredicate, '<'.$row->p.'>');
 }

 foreach($taxvocabbypredicate as $key => $value) {
   echo 'A new vocabby predicate starts here'.$taxvocabbypredicate[$key]."\r\n";

   // Find the terms for each vocabulary...


   // Find the vocabulary name for each vocabulary by doing a sql query


   /// write things to the LDP container, being congnizant of containers already created with the same name


// function get terms...


//$taxvocabbypredicate = '<http://schema.org/isRelatedTo>';
// Perform SELECT query on RDF store to populate array for all triples with schema:isRelatedTo predicate
$result = $sparql->query(
   'PREFIX schema: <http://schema.org/>
    SELECT * {
    SERVICE <https://integratedspaceanalytics.com/new/isp_data_endpoint> {
    SELECT DISTINCT ?o { ?s '.$taxvocabbypredicate[$key].'  ?o .
    FILTER regex(?s, "portal")}
  }
}'
);
// Initialize itermediary storage array for subject, predicate, and object from query with schema:isRelatedTo predicate
//$subarray = array();
//$predarray = array();
$objarray = array();
// Populate the storage arrays including the schema:isRelatedTo predicate (or any predicate you have)
foreach ($result as $row) {
 //   array_push($subarray, $row->s);
 //   array_push($predarray, "schema:isRelatedTo");
    array_push($objarray, $row->o);
}
if ($dbg == 1) {
  echo "Triples with the schema:isRelatedTo predicate".'<br/>';
  foreach($objarray as $i => $value) {
   //  echo $subarray[$i].' '.$predarray[$i].' '.$objarray[$i]."\r\n";
     echo $objarray[$i]."\r\n";
  }
}

echo('new mapping here');
echo("\r\n");
// rewrite the map obj array so that it hopefully is resolvable
foreach($objarray as $i => $value) {
  $new_mapobjarray[$i] = preg_replace('/taxonomy_term/i','taxonomy/term', $objarray[$i]);
  if($dbg == 1) {
    echo($new_mapobjarray[$i]);
    echo("\r\n");
  }
}


// Execute only if true
// if ($newrdftag === "true") {
// Remove the declaration of the array outside of the foreach loop
  $subject = array();
  $predicate = array();
  $object = array();
  $nullobj = array();
  $notnullobj = array();
  $taxnamearray = array();
  $predicatename = array();

  foreach($new_mapobjarray as $i => $value) {
  //  echo $objarray[$i].' this is the object array '.$new_mapobjarray[$i]."\n";
    //  Dereference the taxonomy term URI in mapobjarray and remove markup
     $html = implode('', file($new_mapobjarray[$i]));
     $naked = strip_tags($html);
     if($naked == null) {
     echo 'The null value is'.$new_mapobjarray[$i];
     array_push($nullobj,$new_mapobjarray[$i]);
     //   $localtaxname = gettaxname($new_mapobjarray[$i],$sparql);
     //   echo $localtaxname."\r\n";
     // put a function for the names of the taxonomy terms here (see the bottom of this document)
     }
      if($naked !== null) {
     //   echo 'The null value is'.$new_mapobjarray[$i];
        array_push($notnullobj,$new_mapobjarray[$i]);
        $new_revmapobjarray = preg_replace('/taxonomy\/term/i','taxonomy_term', $new_mapobjarray[$i]);
        echo $new_revmapobjarray."\n"; 
        $localtaxname = gettaxname($new_revmapobjarray,$sparql);
         if($localtaxname == null) {
            echo "I found a null";
         }
         echo $localtaxname."\r\n";
    }

    if ($dbg == 1) {
    //   echo "The html without the tags for ".$mapobjarray[$i]." is:".'<br/>';
    }
     // Locate URI in result
     $pattern = "/URI:.*http:\/\/.* /";
     $input_str = $naked;
     //      $subject = array();
     //      $predicate = array();
     //      $object = array();
     // only execute this code if the uri actually exists:
      if($localtaxname !== null) {
        echo $localtaxname.' is not null '."\n";
          if (preg_match_all($pattern, $input_str, $matches_out)) {
       
       $p = $matches_out[0];
       echo $p."\n";

       $withComma = implode(" ", $p);
       // Select only the URI from the page and remove surrounding whitespaceecho "\r\n";
      $regex = "/URI:&nbsp;/";
 //    echo "\r\n";

      $new_string = preg_replace($regex, "$2 $1", $withComma);
      $new_string = preg_replace('/\s+/i', '', $new_string);
      // Bind result to subject, predicate, and object arrays that together
      // form triples of the form "taxonomy term" rdfs:seeAlso "dbpedia or other referenced URI from the taxonomy page"
      array_push($subject,'<'.$objarray[$i].'>');
 //     array_push($predicate,'<http://www.w3.org/2000/01/rdf-schema#seeAlso>');
      array_push($object,'<'.$new_string.'>');
      array_push($taxnamearray,'\''.$localtaxname.'\'');
 //     array_push($predicatename,'<http://purl.org/dc/terms/title>');

       } else {
       echo $objarray[$i].' this is the object array '.$new_mapobjarray[$i]."\n";

         //     array_push($predicate,'<http://www.w3.org/2000/01/rdf-schema#seeAlso>');
        array_push($object,null);
        // array_push($object,'<'.'null'.'>');
        array_push($subject,'<'.$objarray[$i].'>');
        array_push($taxnamearray,'\''.$localtaxname.'\'');
        //     array_push($predicatename,'<http://purl.org/dc/terms/title>');

      }

    }
  }

  if ($dbg == 1) {

 echo "The created triples are:".'<br/>';
 foreach($subject as $i => $value) {
   // display all taxonomy terms here..not just the ones with related dbpedia terms...(so do a mapping)
     // take the dash out of the taxonomy term...
     $subject[$i] = preg_replace('/taxonomy_term/i','taxonomy/term', $subject[$i]);
 if($object[$i] !== null) {
//  echo $subject[$i].' '.$predicate[$i].' '.$object[$i]."\r\n";
 echo $subject[$i].' '.'<http://www.w3.org/2000/01/rdf-schema#seeAlso>'.' '.$object[$i]."\r\n";
 }
//  echo $subject[$i].' '.$predicatename[$i].' '.$taxnamearray[$i]."\r\n";
 echo $subject[$i].' '.'<http://purl.org/dc/terms/title>'.' '.$taxnamearray[$i]."\r\n";
 }

}

if($dbg == 3) {
echo "\r\n";
echo "The nonexistent URIs are";
echo "\r\n";
foreach($nullobj as $key => $value) {
 echo $nullobj[$key]."\r\n";
 $new_mapobjarray[$key] = preg_replace('/taxonomy\/term/i','taxonomy_term', $nullobj[$key]);
 echo $new_mapobjarray[$key]."\r\n";
 $taxname =  gettaxname($new_mapobjarray[$key],$sparql);
 echo $taxname."\r\n";
}

}


//$taxname = array();
if($dbg == 3) {
echo "\r\n";
echo "The existent URIs are";
echo "\r\n";
foreach($notnullobj as $key => $value) {
 echo $notnullobj[$key]."\r\n";
 $new_mapobjarray[$key] = preg_replace('/taxonomy\/term/i','taxonomy_term', $notnullobj[$key]);
 echo $new_mapobjarray[$key]."\r\n";
 $taxname = gettaxname($new_mapobjarray[$key],$sparql);
 echo $taxname."\r\n";

}
}

// get the name of the vocabulary
//$label = getvocabname($subject[0]);

// comment this out because you are doing this below
// array_push($taxvocabbyname,$taxvocabbynamebackup[$key]);

//array_push($taxvocabbyname,getvocabname($subject[0]));

// use this function call instead if you are pulling the taxonomy vocabulary names with a SQL
// query..
//$taxvocabbyname = getvocabname($subject[0]);


// feed the correct label and subject, predicate and object as variables from the array
// subject, predicate, object
// call the code wdbpedia-multiimplodewkey right here as a function
// call the function for ijsg-wdbpedia-multiimplodewkey
// this function should return the ldp container name
// you should have some code like array_push($taxvocabbyname,taxonomy_termtoldp());


// Fridata 29, April: comment this out for testing
/*
$taxvocabbyname[$key] = taxonomy_termtoldp($subject,$object,$taxnamearray,$taxvocabbynamebackup[$key]);
echo 'Tax vocabby name is'.$taxvocabbyname[$key];
*/

//  array_push($taxvocabbyname,taxonomy_termtoldp($subject,$object,$taxnamearray,$taxvocabbynamebackup[$key]));
 taxonomy_termtoldp($subject,$object,$taxnamearray,$taxvocabbynamebackup[$key]);

// use this function call instead if you are pulling the taxonomy vocabulary names with a SQL
// query..
//taxonomy_termtoldp($subject,$object,$taxnamearray,$taxvocabbyname);


// see ijsldptests.php for the function to feed for LDP write
// once each label for the LDP container has been created for each Tag, it needs to be fed to the
// function to create an LDP container
// for each portal page so I have predicate,label pairs (e.g, schema:isRelatedTo,Tags)

}


 foreach($taxvocabbypredicate as $key => $value) {
    echo $taxvocabbypredicate[$key].' '.$taxvocabbyname[$key];
 }

 foreach($taxvocabbypredicate as $key => $value) {
    echo $taxvocabbyname[$key];
 }

 print_r($taxvocabbypredicate);
 print_r($taxvocabbyname);



function gettaxname($taxonomy_term,$sparql) {
 $taxname = array();
 $subject = '<'.$taxonomy_term.'>';
 $resultnamenot = $sparql->query(
    'PREFIX dct: <http://purl.org/dc/terms/>
     PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
     PREFIX schema: <http://schema.org/>
     SELECT * {
        SERVICE <http://integratedspaceanalytics.com/new/isp_data_endpoint> {
        SELECT DISTINCT ?m {
              '.$subject.' rdfs:label ?m .

         }
     } }'
 );
 foreach ($resultnamenot as $row) {

      return $row->m;
 }

}

// Get the taxonomy label...

//p  $string = $subject[1];

 $string = '<http://localhost/drupal-7.42/taxonomy/term/97>';

// comment this out if you do not have a SQL Database
// getvocabname($string);

function getvocabname($string) {
 preg_match_all('/\/[0-9]*>/',$string,$matches);
 $strip1 = preg_replace('/\//','',$matches[0]);
 $strip2 = preg_replace('/>/','',$strip1[0]);
 $strip2;

// Access control to the database
$user = 'your_username';
$pass = 'your_password';

 try {
     $dbh = new PDO('mysql:host=localhost;dbname=drupal-7.42', $user, $pass);
       foreach($dbh->query("SELECT  `taxonomy_vocabulary`.`name`,  `taxonomy_vocabulary`.`description` ,
`taxonomy_term_data`.`vid` FROM `taxonomy_term_data`
JOIN `taxonomy_vocabulary` ON `taxonomy_term_data`.`vid` = `taxonomy_vocabulary`.`vid`
WHERE `taxonomy_term_data`.`tid` = 24") as $row) {
 //    foreach($dbh->query('SELECT * from `pan_node` LIMIT 10') as $row) {
//          print_r($row);
         if($row['name'] !== NULL) {
         echo($row['name']);
          $label = $row['name'];
       //  array_push($nid_array_for_text_field, $row['nid']);
         }
         if($row['description'] !== NULL) {
         echo($row['description']);
       //  $comment = $row['description'];
        // array_push($content_type_array, $row['type']);
         }
         if($row['vid'] !== NULL) {
         echo($row['vid']);
        // array_push($field_text_field_name_value_array_all, $row[$table_text_field_name_value]);
         }
    }
     $dbh = null;
 } catch (PDOException $e) {
     print "Error!: " . $e->getMessage() . "<br/>";
     die();
 }

echo $label;
return $label;
}

// ijsg-wdbpedia-multiimplodewkey contents below

function taxonomy_termtoldp($subject,$object,$taxnamearray,$label) {
// wrap all of this code in a function so that it can be called in the loop for combined-create-vocabulary...
  foreach($subject as $key => $value) {
  //  if($subject[$key] !== null && $object[$key] !== null) {
  //    if(preg_match_all('/http.*dbpedia\.org/i',$object[$key],$matches) == 1) {
     // echo $subject[$key];
      preg_match_all('/\/[0-9]*>/',$subject[$key],$matches);
      $strip1 = preg_replace('/\//','',$matches[0]);
      $strip2 = preg_replace('/>/','',$strip1[0]);
      $striptotag = '<#'.$strip2.'>';
    //  echo $strip2;
      $subject_taxid[$key] = $striptotag;
    //  echo "\r\n";
    //  }
  //  }
  }

  foreach($subject as $key => $value) {
    // In the end I do not want to write this if I have null values, but I do want to write it if
    // I have a dbpedia term... granted that the subject is unique...
    if($subject[$key] !== null && $object[$key] !== null) {
       if(preg_match_all('/http.*dbpedia\.org/i',$object[$key],$matches) == 1) {

    //   $triplearray[$key] = $subject_taxid[$key].' '.$predicate[$key].' '.$object[$key].' .'."\n".
        $triplearray[$key] = $subject_taxid[$key].' '. '<http://www.w3.org/2000/01/rdf-schema#seeAlso>'.' '.$object[$key].' .'."\n".
       $subject_taxid[$key].'  '.'<http://www.w3.org/2002/07/owl#sameAs>'.' '.$subject[$key].' .'."\n".
    //   $subject_taxid[$key].'  '.'<http://purl.org/dc/terms/title>'.'  '.$objject_taxid[$key].' .';
      // do not display this triple if taxnamearray is an empty string
       $subject_taxid[$key].'  '.'<http://purl.org/dc/terms/title>'.'  '.$taxnamearray[$key].' .';
    //   echo $subject[$i].' '.$predicatename[$i].' '.$taxnamearray[$i]."\r\n";
    //  echo $subject[$key].' '.'rdfs:seeAlso'.' '.$object[$key];

  }
} elseif ($subject[$key] !== null && $object[$key] == null) {

    $triplearray[$key] = $subject_taxid[$key].'  '.'<http://www.w3.org/2002/07/owl#sameAs>'.' '.$subject[$key].' .'."\n".
    // do not display this triple if taxnamearray is an empty string
    $subject_taxid[$key].'  '.'<http://purl.org/dc/terms/title>'.'  '.$taxnamearray[$key].' .';

}
  }



foreach($triplearray as $key => $value) {
 // echo $triplearray[$key];
}

/*
foreach($subject as $key => $value) {
  $skosmembers[$key] = '<http://www.w3.org/2004/02/skos/core#member>'.' '.$subject[$key].' ;';
}
*/

foreach($subject as $key => $value) {
  if($subject[$key] !== null) {
  //   if(preg_match_all('/http.*dbpedia\.org/i',$object[$key],$matches) == 1) {
  $skosmembers[$key] = '<http://www.w3.org/2004/02/skos/core#member>'.' '.$subject_taxid[$key].' ;';
//  }
 }
}


$skosmembersstringpre = implode("\n",$skosmembers);
$skosmembersstring = substr($skosmembersstringpre, 0, -2);

//$label = 'ISP_Column';
$rdfslabel = '\''.$label.'\'';
$comment = '\'Use tags to group articles on similar topics into categories\'';

 $prefixes = array('@prefix skos: <http://www.w3.org/2004/02/skos/core#> .','@prefix rdfs: <http://www.w3.org/2000/01/rdfschema#> .','@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .');
 $prefixesn = implode("\n",$prefixes);
//  echo ($prefixesn);

 $triplesstring = implode("\n",$triplearray);

//$withcomma = $prefixesn."\n".'<>'."\n".$triplesstring;
$data = '<>'.' '.'<http://purl.org/dc/terms/title>'.' '.$rdfslabel.' ;'."\n".'<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>'.' '.'<http://www.w3.org/2004/02/skos/core#Collection>'.' ;'."\n".'<http://www.w3.org/2000/01/rdfschema#comment>'.' '.$comment.' ;'."\n".$skosmembersstring.' .'."\n".$triplesstring;
echo $data;

// Do not create any files, and do not write any to LDP

$filename = $label.'.ttl';
echo 'thefilename is'.$filename.' the ned'."\n";

 $fp = fopen('./'.$filename,'w');
	// echo $withcomma;

 fwrite($fp, $data);
  fclose($fp);

pushandput($filename);

/*
$vocabfortaxonomynames =  pushandput($filename);

return $vocabfortaxonomynames;
*/
}

function pushandput ($inputfile) {

$containertitle = preg_replace('/\.ttl/','',$inputfile);
$url = 'http://investors.ddns.net:8080/marmotta/ldp/contentandportalpages/';
// add a GET request here
$url_two = $url.$containertitle;
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




?>
