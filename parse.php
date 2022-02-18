<?php
/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

require_once __DIR__. '/CLAProcessor.php';
require_once __DIR__. '/Parser.php';

// ini_set('display_errors', 'stderr');

CLAProcessor::process($argc, $argv);
Parser::parseAndPrintXML();
exit(ReturnValues::SUCCESS);

?>