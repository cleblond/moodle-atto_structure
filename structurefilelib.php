<?php

/**
* internal library of functions and constants for Poodll modules
* accessed directly by poodll flash wdgets on web pages.
* @package mod-poodllpairwork
* @category mod
* @author Justin Hunt
*
*/


/**
* Includes and requires
*/
//ob_start();
global $CFG;

//we need to do this, because when called from a widet, cfg is not set
//but the relative path fails from a quiz but it has already been set in that case
//, so we check before we call it, to cover both bases

if(!isset($CFG)){
require_once("../../../../../config.php");
}

require_once($CFG->libdir . '/filelib.php');

	$datatype = optional_param('datatype', "", PARAM_TEXT);    // Type of action/data we are requesting
	$contextid  = optional_param('contextid', 0, PARAM_INT);  // the id of the course 
	$courseid  = optional_param('courseid', 0, PARAM_INT);  // the id of the course 
	$moduleid  = optional_param('moduleid', 0, PARAM_INT);  // the id of the module 
	//added justin 20120803 careful here, I think $component is a php keyword or something
	//it screwed the whole world
	$comp = optional_param('component', "", PARAM_TEXT);  // the component
	$farea = optional_param('filearea', "", PARAM_TEXT);  // the filearea
	
	$itemid  = optional_param('itemid', 0, PARAM_INT);  // the id of the module
	$hash  = optional_param('hash', "", PARAM_TEXT);  // file or dir hash
	$requestid  = optional_param('requestid', "", PARAM_TEXT);  // file or dir hash
	$paramone  = optional_param('paramone', "", PARAM_TEXT);  // nature of value depends on datatype, maybe path
	$paramtwo  = optional_param('paramtwo', "", PARAM_TEXT);  // nature of value depends on datatype, maybe protocol
	$paramthree  = optional_param('paramthree', "", PARAM_TEXT);  // nature of value depends on datatype, maybe filearea

	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\"?>\n";
	//uploadfile filedata(base64), fileextension (needs to be cleaned), blah blah 
	//paramone is the file data, paramtwo is the file extension, paramthree is the mediatype (audio,video, image)
	//requestid is the actionid
	$returnxml = uploadfile($paramone,$paramtwo, $paramthree, $requestid,$contextid, $comp, $farea,$itemid);

	echo $returnxml;
	return;


//For uploading a file diorect from an HTML5 or SWF widget
function uploadfile($filedata,  $fileextension, $mediatype, $actionid,$contextid, $comp, $farea,$itemid){
	global $CFG,$USER;
	

	//setup our return object
	$return=fetchReturnArray(true);
	
	//make sure nobodyapassed in a bogey file extension
	switch($fileextension){
		case "mp3": 
		case "flv":
		case "jpg":
		case "png":
		case "xml":
		case "mov":
		case "mp4":
			break;
		default: $fileextension="mp3";
	}
	
	//init our fs object
	$fs = get_file_storage();
	//assume a root level filepath
	$filepath="/";
	

		
	
	//make our filerecord
	 $record = new stdClass();
     $record->filearea = $farea;
    $record->component = $comp;
    $record->filepath = $filepath;
    $record->itemid   = $itemid;
    $record->license  = $CFG->sitedefaultlicense;
    $record->author   = 'Moodle User';
	$record->contextid = $contextid;
    $record->userid    = $USER->id;
    $record->source    = '';
        
  
	//make filename and set it
	//we are trying to remove useless junk in the draft area here
	//when we know its stable, we will do the same for non images too

		$filenamebase = "upfile_" . $actionid . "." ;

	$filename = $filenamebase . $fileextension;
	$record->filename = $filename;
	
	
	//if file already exists, raise an error
	if($fs->file_exists($contextid,$comp,$farea,$itemid,$filepath,$filename)){
		if($mediatype=='image'){
			//delete any existing draft files.
			$file = $fs->get_file($contextid,$comp,$farea,$itemid,$filepath,$filename);
			$file->delete();
			
			//check there is no metadata prefixed to the base 64. From OL widgets, none, from JS yes
			$metapos = strPos($filedata,",");
			if($metapos >10 && $metapos <30){
				$filedata = substr($filedata,$metapos+1);
			}
	
			//decode the data and store it 
			$xfiledata = base64_decode($filedata);
			//create the file
			$stored_file = $fs->create_file_from_string($record, $xfiledata);

		}else{
			$stored_file = false;
			$return['success']=false;
			array_push($return['messages'],"Already exists, file with filename:" . $filename );
		}
	}else{
		
		//check there is no metadata prefixed to the base 64. From OL widgets, none, from JS yes
		//if so it will look like this: data:image/png;base64,iVBORw0K
		//we remove it, there must be a better way of course ...
		//$metapos = strPos($filedata,";base64,");
		$metapos = strPos($filedata,",");
		if($metapos >10 && $metapos <30){
			//$trunced = substr($filedata,0,$metapos+8);
			$filedata = substr($filedata,$metapos+1);
		
		}
	
		//decode the data and store it in memory
		$xfiledata = base64_decode($filedata);
		$stored_file = $fs->create_file_from_string($record, $xfiledata);
		//}
	
	}
	
	//if successful return filename
	if($stored_file){
		array_push($return['messages'],$filename );

	//if unsuccessful, return error
	}else{
		$return['success']=false;
		array_push($return['messages'],"unable to save file with filename:" . $filename );
	}
		
	//we process the result for return to browser
	$xml_output=prepareXMLReturn($return, $actionid);	
	
	//we return to widget/client the result of our file operation
	return $xml_output;
}


//this turns our results array into an xml string for returning to browser
function prepareXMLReturn($resultArray, $requestid){
	//set up xml to return	
	$xml_output = "<result requestid='" . $requestid . "'>";

		if($resultArray['success']){
			$xml_output .= 'success';
			//not sure how this will impact attachment explorer .. (expects no messages here, but recorder expects..)
			foreach ($resultArray['messages'] as $message) {
				$xml_output .= '<error>' . $message . '</error>';
			}
		}else{
			$xml_output .= 'failure';
			foreach ($resultArray['messages'] as $message) {
				$xml_output .= '<error>' . $message . '</error>';
			}
		}
	
	
	//close off xml to return	
	$xml_output .= "</result>";	
	return $xml_output;
}


//this initialises and returns a results array
function fetchReturnArray($initsuccess=false){
	//new filearray
	$return = array();
	$return['messages'] = array();
	$return['success'] = $initsuccess;
	return $return;
}

