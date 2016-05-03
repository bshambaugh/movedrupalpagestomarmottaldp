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
//    require_once "./easyrdf-0.9.0/examples/html_tag_helpers.php";

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

   $sparql = new EasyRdf_Sparql_Client('http://localhost:8080/marmotta/sparql/');
?>

<?php

// Set debug to 1 for debugging
$dbg = 1;
?>

<?php

// Uncomment this to rewrite the predicate without less than or greater than signs
/*
foreach($taxvocabbypredicate as $key => $value) {
  $taxvocabbypredicate[$key] = preg_replace('/[<>]/i','',$taxvocabbypredicate[$key]);
}
*/

// the name and the predicate below are specified for testing purposes, normally they come from
// ijsg-onefile-combinedscripts.php
$taxvocabbypredicate = array('http://schema.org/category','http://schema.org/isRelatedTo');
$taxvocabbyname = array('ISP_Column','Tags');

 // Perform SELECT query on RDF store to populate array for all triples with schema:isRelatedTo predicate
/*
 $result = $sparql->query(
     'SELECT DISTINCT ?s { ?s ?p ?o . }'
 );
// specify the arc2storepath below
 */
 $result = $sparql->query(
     '
    SELECT * {
     SERVICE <your_sparql_endpoint_here> {
     SELECT DISTINCT ?s { ?s ?p ?o .
     FILTER regex(?s, "/portal/") }
   }
 }'
 );

// Initialize itermediary storage array for subject, predicate, and object from query with schema:isRelatedTo predicate
 $subarray = array();
 $secondarray = array();

// Populate the storage arrays including the schema:isRelatedTo predicate
 foreach ($result as $key => $value) {
    array_push($subarray,$value->s);
 }

 $result = $sparql->query(
     '
    SELECT * {
     SERVICE <your_sparql_endpoint_here> {
     SELECT DISTINCT ?s { ?s ?p ?o .
     FILTER regex(?s, "/content/") }
   }
 }'
 );

// Populate the storage arrays including the schema:isRelatedTo predicate
 foreach ($result as $key => $value) {
    array_push($subarray,$value->s);
 }

foreach($subarray as $key => $value) {
    echo $subarray[$key]."\n";
}

// $subarraymatches = array();
// filter to what you want with regex
// $subarraymatches = preg_grep('/http.*portal/i',$subarray);

/*
 foreach($subarraymatches as $k => $value) {
   echo ($subarraymatches[$k]);
 }
*/

// print_r($subarraymatches);

?>


<?php


//$subjechtvar = 'http://localhost/drupal-7.42/content/owen-paterson';
//$subject = '<'.$subjechtvar.'>';
 //$secondarray = array(array());
 $secondarray = array();

foreach($subarray as $k => $value) {

$subjechtvar = strval($subarray[$k]);
$subject = '<'.$subjechtvar.'>';


 // Perform SELECT query on RDF store to populate array for all triples with schema:isRelatedTo predicate
/*
 $result = $sparql->query(
     'SELECT DISTINCT ?p ?o { '.$subject.' ?p ?o . }'
 );
*/

// filter to what you want with regex
$result = $sparql->query(
    'SELECT * {
      SERVICE <your_sparql_endpoint_here> {
      SELECT DISTINCT ?p ?o { '.$subject.' ?p  ?o . }
      }
  }'
);



 $predarray = array();
 $objarray = array();

// Populate the storage arrays including the schema:isRelatedTo predicate
 foreach ($result as $i => $row) {
  //   array_push($subarray, $row->s);
     array_push($predarray, $row->p);
     array_push($objarray, $row->o);
 }

//print_r($objarray);

/*
foreach ($predarray as $i => $value) {
  echo $predarray[$i].' '.$objarray[$i];
}
*/

$predarraymap = array();
$objarraymap = array();

foreach ($predarray as $key => $value) {
  array_push($predarraymap,$predarray[$key]);
}

foreach ($objarray as $key => $value) {
  array_push($objarraymap,$objarray[$key]);
}

//$predarraymap_final = array();
//$objarraymap_final = array();

foreach($taxvocabbypredicate as $i => $value_one) {
  foreach($predarraymap as $j => $value_two) {
    if($taxvocabbypredicate[$i] == $predarraymap[$j]) {
      if($dbg == 1) {
        /*
        echo("We are equal for ".$taxvocabbypredicate[$i]." and ".$predarray[$j]);
        echo("\r\n");
        echo 'The vocabby name for the predicate is '.$taxvocabbyname[$i];
        echo("\r\n");
        echo("\r\n");
        */
        // figure out why this does not work for both
        $marmottatags = 'http://localhost:8080/marmotta/ldp/'.$taxvocabbyname[$i];
        // array_push($predarraymap,$predarray[$j]);
         $objarraymap[$j] = taxtolocal($objarraymap[$j],$marmottatags);
         echo 'we are equal for: '.$objarraymap[$j];
       }
    } elseif ($taxvocabbypredicate[$i] !== $predarray[$j]) {
      if($dbg == 1) {
        /*
       echo("We are not equal for ".$taxvocabbypredicate[$i]." and ".$predarray[$j]);
         echo("\r\n");
         */
        //  array_push($predarraymap,$predarray[$j]);
        //  array_push($objarraymap,$objarray[$j]);
      }
    }
  }
}
// comment this out for testing purposes...
/*
// feed the predicates and tag labels here
$marmottatags = 'http://localhost:8080/marmotta/ldp/Tags-4';
$marmottaispterm = 'http://localhost:8080/marmotta/ldp/ISPterm';


foreach ($predarray as $i => $value) {
  if(preg_match_all('/http.*schema\.org\/isRelatedTo/i',$predarray[$i],$matches) == 1) {
  //  echo $predarray[$i].' '.taxtolocal($objarray[$i],$marmottatags)."\r\n";
    array_push($predarraymap,$predarray[$i]);
    array_push($objarraymap,taxtolocal($objarray[$i],$marmottatags));
//   echo $predarray[$i].' '.$objarray[$i].'matches'.$objarrayws."\r\n";
 } elseif (preg_match_all('/http.*schema\.org\/category/i',$predarray[$i],$matches) == 1) {
  // echo $predarray[$i].' '.taxtolocal($objarray[$i],$marmottaispterm)."\r\n";
   array_push($predarraymap,$predarray[$i]);
   array_push($objarraymap,taxtolocal($objarray[$i],$marmottaispterm));
 }
  else {
  //  echo $predarray[$i].' '.$objarray[$i]."\r\n";
    array_push($predarraymap,$predarray[$i]);
    array_push($objarraymap,$objarray[$i]);
 }
}
*/

// the end

$thirdarray = array();

foreach($predarraymap as $i => $value) {
 // echo($predarray->uri);
  $thirdarray[strval($predarraymap[$i])] = strval($objarraymap[$i]);
}

/*
echo "The third array";
echo("\r\n");
foreach($thirdarray as $i => $value) {
  echo $thirdarray[$i];
}
*/
  //echo "=====\n";
  //var_dump($predarray);
  //echo "=====\n";

  // end of commenting out


  foreach($objarraymap as $i => $value) {
  //  echo strval($subarraymatches[$k]).' '.strval($predarray[$i]).' '.strval($objarray[$i]);
  /*
    echo "k = $k, i = $i\n";
    var_dump(array("s"=>$subarraymatches[$k], "p" => $predarray[$i], "o" => $objarray[$i]));
    echo "\n";
  */
  // echo("\r\n");
  //
  // foreach($taxvocabbypredicate as $m => $value) {
      //    if($taxvocabbypredicate[$m] == $predarraymap[$j]) {
      //    $secondarray[strval($subarray[$k])][strval($predarraymap[$i])] = $objarraymap[$i];
      //    echo 'The predicate is what we want'.$objarraymap[$i];
      //    } else {
          $secondarray[strval($subarray[$k])][strval($predarraymap[$i])] = strval($objarraymap[$i]);
      //    }
  //  }
 }

}

print_r($secondarray);

foreach($objarraymap as $key => $value) {
  echo $objarraymap[$key];
}

function taxtolocal($objarray,$vocabulary) {
  $objarray_enc = '<'.$objarray.'>';
  preg_match_all('/\/[0-9]*>/',$objarray_enc,$matches);
  $strip1 = preg_replace('/\//','',$matches[0]);
  $strip2 = preg_replace('/>/','',$strip1[0]);
  $objarrayws =  $vocabulary.'#'.$strip2;
  //$objarrayws = '<'.$vocabulary.'#'.$strip2.'>';
  return $objarrayws;
}


?>
