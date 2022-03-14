<?php
/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

require_once __DIR__. '/parse-lib/CLAProcessor.php';
require_once __DIR__. '/parse-lib/Parser.php';

$config = CLAProcessor::getConfig($argc, $argv);
Parser::parseAndPrintXML($config);
exit(ReturnValues::SUCCESS);
?>
