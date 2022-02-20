<?php
/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

require_once __DIR__. '/ReturnValues.php';

/**
 * Class for command lines argument processing.
 */
final class CLAProcessor
{
    /**
     * Process commmand line arguments.
     *   --help - prints help to standard output
     *   no arguments - does nothing
     *   invalid arguments - parameter error 
     * @param $argc - number of comman line arguments
     * @param $argv - array of command line arguments
     */
    static function process($argc, $argv)
    {
        if ($argc == 1)
        {
            return;
        }
        else if ($argc == 2 && $argv[1] == "--help")
        {
            echo "Usage: php8.1 parse.php \n";
            echo "Reads IPPcode22 source code from standard input, checks its lexical and syntactic correctness and prints its XML representation to standard output.\n";
            exit(ReturnValues::SUCCESS);
        }
        else
        {
            exit(ReturnValues::PARAMETER_ERR);
        }
    }
}
?>
