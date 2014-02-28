<?php
/*
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014 Burak USGURLU
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 */

 
//db connection
$dbHost="";
$db="";
$dbUser="";
$dbPassword="";


$table='patients';
$field=array(
		'name'=>'first_name',
		'lastname'=>'last_name',
		'gender'=>'sex',
		'primary_key'=>'id'
		);

$genderValue = array(
		'male'=>'Male',//m,0,...
		'female'=>'Female'//f,1,...
		);
$library='us';

$workInIncrementsOf=1000;

//loadNameFilesToMemory
global $names;
$names = array();
$names['male'] = file("{$library}_male.csv");
$names['female'] = file("{$library}_female.csv");
$names['last'] = file("{$library}_last.csv");


//connect to host
$link = mysql_connect($dbHost, $dbUser, $dbPassword);
if (!$link) {
	die('Not connected : ' . mysql_error());
}

//select db
$db_selected = mysql_select_db($db, $link);
if (!$db_selected) {
	die ('Can\'t use foo : ' . mysql_error());
}

$result = mysql_query("SELECT * FROM {$table} ORDER BY {$field['primary_key']} ASC LIMIT 0,$workInIncrementsOf",$link);
if (!$result) {
	echo "Could not successfully run query from DB: " . mysql_error();
	exit;
}
$count=0;
while(mysql_num_rows($result) != 0){
	printf("\n%4s",$count);
	$rowCount=0;
	while ($row = mysql_fetch_assoc($result)) {
		//get name for gender
		$name = get_line(array_search($row[$field["gender"]], $genderValue));
		$lastname = get_line("last");
		$update = "UPDATE $table SET `{$field['name']}` = '$name',`{$field['lastname']}` = '$lastname' WHERE `$table`.`{$field['primary_key']}` ={$row[$field['primary_key']]};";
		$updated = mysql_query($update,$link);
		if (!$updated) {
			echo "Could not successfully run query from DB: " . mysql_error();
			exit;
		}
		//echo"$update\n";
		echo (($rowCount%10)==0?".":null);
		$rowCount++;
	}
	
	$count++;
	$start = $count*$workInIncrementsOf;
	$result = mysql_query("SELECT * FROM {$table} ORDER BY {$field['primary_key']} ASC LIMIT $start,$workInIncrementsOf");	
}




function get_line($type){
	global $names;
	$line = $names[$type][array_rand($names[$type])];
	return trim($line);
}