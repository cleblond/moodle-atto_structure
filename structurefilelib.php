<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * structure settings.
 *
 * @package   atto_structure
 * @copyright  2014 onwards Carl LeBlond
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;

if (!isset($CFG)) {
    require_once("../../../../../config.php");
}

require_once($CFG->libdir . '/filelib.php');

$datatype  = optional_param('datatype', "", PARAM_TEXT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$courseid  = optional_param('courseid', 0, PARAM_INT);
$moduleid  = optional_param('moduleid', 0, PARAM_INT);
$comp      = optional_param('component', "", PARAM_TEXT);
$farea     = optional_param('filearea', "", PARAM_TEXT);

$itemid     = optional_param('itemid', 0, PARAM_INT); // The id of the module.
$hash       = optional_param('hash', "", PARAM_TEXT); // File or dir hash.
$requestid  = optional_param('requestid', "", PARAM_TEXT); // File or dir hash.
$paramone   = optional_param('paramone', "", PARAM_TEXT); // Nature of value depends on datatype, maybe path.
$paramtwo   = optional_param('paramtwo', "", PARAM_TEXT); // Nature of value depends on datatype, maybe protocol.
$paramthree = optional_param('paramthree', "", PARAM_TEXT); // Nature of value depends on datatype, maybe filearea.

header("Content-type: text/xml");
echo "<?xml version=\"1.0\"?>\n";
$returnxml = uploadfile($paramone, $paramtwo, $paramthree, $requestid, $contextid, $comp, $farea, $itemid);
echo $returnxml;
return;


 // For uploading a file direct from an HTML5.
function uploadfile($filedata, $fileextension, $mediatype, $actionid, $contextid, $comp, $farea, $itemid) {
    global $CFG, $USER;
    // Setup our return object.
    $return = fetch_return_array(true);
    // Make sure nobodyapassed in a bogey file extension.
    switch ($fileextension) {
        case "png":
            break;
        default:
            $fileextension = "mp3";
    }
    $fs       = get_file_storage();
    // Assume a root level filepath.
    $filepath = "/";
    // Make our filerecord.
    $record            = new stdClass();
    $record->filearea  = $farea;
    $record->component = $comp;
    $record->filepath  = $filepath;
    $record->itemid    = $itemid;
    $record->license   = $CFG->sitedefaultlicense;
    $record->author    = 'Moodle User';
    $record->contextid = $contextid;
    $record->userid    = $USER->id;
    $record->source    = '';

    $filenamebase = "upfile_" . $actionid . ".";

    $filename         = $filenamebase . $fileextension;
    $record->filename = $filename;
    // If file already exists, raise an error!
    if ($fs->file_exists($contextid, $comp, $farea, $itemid, $filepath, $filename)) {
        if ($mediatype == 'image') {
            // Delete any existing draft files!
            $file = $fs->get_file($contextid, $comp, $farea, $itemid, $filepath, $filename);
            $file->delete();
            // Check there is no metadata prefixed to the base 64. From OL widgets, none, from JS yes!
            $metapos = strpos($filedata, ",");
            if ($metapos > 10 && $metapos < 30) {
                $filedata = substr($filedata, $metapos + 1);
            }
            // Decode the data and store it!
            $xfiledata   = base64_decode($filedata);
            // Create the file!
            $storedfile = $fs->create_file_from_string($record, $xfiledata);
        } else {
            $storedfile       = false;
            $return['success'] = false;
            array_push($return['messages'], "Already exists, file with filename:" . $filename);
        }
    } else {
        /*check there is no metadata prefixed to the base 64. From OL widgets, none, from JS yes
        if so it will look like this: data:image/png;base64,iVBORw0K
        we remove it, there must be a better way of course ...  */
        $metapos = strpos($filedata, ",");
        if ($metapos > 10 && $metapos < 30) {
            $filedata = substr($filedata, $metapos + 1);
        }
        // Decode the data and store it in memory.
        $xfiledata   = base64_decode($filedata);
        $storedfile = $fs->create_file_from_string($record, $xfiledata);
    }
    // If successful return filename.
    if ($storedfile) {
        array_push($return['messages'], $filename);
        // If unsuccessful, return error!
    } else {
        $return['success'] = false;
        array_push($return['messages'], "unable to save file with filename:" . $filename);
    }
    // We process the result for return to browser.
    $xmloutput = prepare_xml_return($return, $actionid);
    // Return to widget/client the result of our file operation.
    return $xmloutput;
}


// Turns our results array into an xml string for returning to browser.
function prepare_xml_return($resultarray, $requestid) {
    // Set up xml to return.
    $xmloutput = "<result requestid='" . $requestid . "'>";

    if ($resultarray['success']) {
        $xmloutput .= 'success';
        // Not sure how this will impact attachment explorer .. (expects no messages here, but recorder expects..).
        foreach ($resultarray['messages'] as $message) {
            $xmloutput .= "<error>" . $message . "</error>";
        }
    } else {
        $xmloutput .= 'failure';
        foreach ($resultarray['messages'] as $message) {
            $xmloutput .= '<error>' . $message . '</error>';
        }
    }

    $xmloutput .= "</result>";
    return $xmloutput;
}


// This initialises and returns a results array.
function fetch_return_array($initsuccess = false) {
    $return             = array();
    $return['messages'] = array();
    $return['success']  = $initsuccess;
    return $return;
}

