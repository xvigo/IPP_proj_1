<?php

/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

 /**
  * Class with return values constants.
  */
final class ReturnValues
{
    // general
    public const SUCCESS = 0; // no error
    public const PARAMETER_ERR = 10; // missing script parameter or invalid combination of parameters
    public const INPUT_FILE_ERR = 11; // error while opening input file
    public const OUTPUT_FILE_ERR = 12; // error while opening output file
	public const INTERNAL_ERR = 99; // internal error (e.g. memmory error)

    // parser 
    public const HEADER_ERR = 21; // invalid or missing header in IPPcode22 source code
	public const OPCODE_ERR = 22; // unknown or invalid opcode in IPPcode22 source code
	public const OTHER_ERROR = 23; // other lexical or syntactic error in IPPcode22 source code
}
?>