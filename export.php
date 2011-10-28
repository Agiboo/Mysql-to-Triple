<?php 

// Defining mysql variables
define('DB_LOCATION','localhost');
define('DB_USERNAME','sakila');
define('DB_PASSWORD','sakila');
define('DB_DATABASE','sakila');

// Defining the root database name
define('DB_NAME','sakila');

// Defining base string of URI
define('DB_STRING', 'http://'.DB_NAME);

// Sending file instead of showing in browser.  Also defining default filename.
header('Content-type: text/plain');
header('Content-Disposition: attachment; filename="'.DB_NAME.'.txt"');

$conn = mysql_connect(DB_LOCATION,DB_USERNAME,DB_PASSWORD);
mysql_select_db(DB_DATABASE);

// Triggering an export for each table in the defined database
$table_result = mysql_query("SHOW TABLES FROM ".DB_NAME);

while($single_table = mysql_fetch_assoc($table_result)){
  $table_name = $single_table['Tables_in_'.DB_NAME];
  // Triggering single table export
  export_table($table_name);
}

/**
 *
 * This function goes over one table and fetches:
 *   * The table description (fields)
 *   * The indexes defined for that table
 *   * The table content
 * and converts it to usable (simplified) triples
 * 
 * @param string $table_name 
 */
function export_table($table_name){
  // Loading table description
  $single_table_result = mysql_query("DESCRIBE $table_name");
  // Defining arrays that are used later on
  $fields = $keys = $fkeys = array();
  
  // This while loop goes over the database info 
  while($row = mysql_fetch_assoc($single_table_result)){
    // If the field has an _id field, store it as a key candidate
    if(substr($row['Field'],-3)== '_id'){
      $keys[] = $row['Field'];
    }
    // Add the field name to a simple array
    array_push($fields,$row['Field']);
  }

  // Get indexes of table:
  $single_table_indexes_result = mysql_query("SHOW INDEXES FROM $table_name");
  while($row = mysql_fetch_assoc($single_table_indexes_result)){
    // Check if a key has the string "_fk_" in it's Key name.  If it has, 
    // this must be a foreign key
    if(preg_match("/_fk_/i", $row['Key_name'])){
      array_push($fkeys,$row['Column_name']);
    }
  }
  
  # Loading the table's results
  $bulk_query = "SELECT * FROM $table_name";
  $bulk_result = mysql_query($bulk_query);
  $primary_key = array_shift($keys);
  // don't use an incrementing key if there's a numerical key in the $keys array
  $inc_pk = false; 
  if(!is_numeric($primary_key)){
    $inc_pk = true;
    $id = 0;
  }
  
  // looping over the table's results
  while($table_row = mysql_fetch_assoc($bulk_result)){
    // Decide whether to use an incrementing ID (if no id is present, such as a connecting table film_actor
    $current_id = ($inc_pk ?  $table_row[$primary_key] : $id) ;
    // Building subject of the triple
    $triple_subject = DB_STRING . "/$table_name/$current_id";
    foreach($fields as $field){
      // Building predicate
      $triple_predicate = DB_STRING . "/$field";
      // This needs clean-up, the whole script needs clean-up!!
      if(in_array($field,$fkeys) && ($table_row[$field] != NULL || $table_row[$field] != "")){
        $triple_object = DB_STRING . "/". substr($field,0,-3)."/".$table_row[$field];
      } else {
        $triple_object = $table_row[$field];
      }      
      echo create_triple($triple_subject,$triple_predicate,$triple_object). "\n";
    }
    // increment if this is an incrementing id
    if($inc_pk){
      $id++;
    }
  }
}


/**
 *
 * This function creates a triple.
 *  
 * @param string $subject
 * @param string $predicate
 * @param string $object
 * @return string 
 */
function create_triple($subject,$predicate,$object){
  if(is_numeric($object)){
    // If numeric, don't use double quotes
    $safe_object = addslashes($object);
  } elseif(is_uri($object)) {
    // If URI, use lt & gt signs
    $safe_object = '<'.$object.'>';
  } else {
    // If other (string) use double quotes
    $safe_object = '"'.addslashes($object).'"';
  }
  return "<$subject> <$predicate> $safe_object.";
}

/**
 *
 * Simple function that checks whether the string is a URI or another string.
 * 
 * @param string $string
 * @return boolean
 */
function is_uri($string){
  $string = substr($string,0,strlen(DB_STRING));
  return ($string == DB_STRING ? true : false );
}
?>
