<?php

//some xml functions


function parse_xml_file($source) {
	if (file_exists($source)) {
	    $xml = simplexml_load_file($source);
	    return $xml;
	} else {
	    exit('Failed to open test.xml.');
	}
}


?>