<?php

/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

require_once __DIR__. '/IppXmlWriter.php';
require_once __DIR__. '/ReturnValues.php';
require_once __DIR__. '/Stats.php';


/**
 * Class for checking lexical and syntactic correctness of IPPcode22 
 * given on standard output and printing its XML representation to standard output.
 * If program isn't correct, program is terminated with coresponding return value (see ReturnValues.php).
 */
final class Parser
{
    private static $xml;
    private static $stats;
    /**
     * Performs syntax and lexical analysis of IPPcode22 given on standard input 
     * and prints its XML representation to standard output.
     */
    static function parseAndPrintXML($config)
    {
        Self::$xml = new IppXmlWriter();
        Self::$stats = new Stats($config);
        $headerFound = false;

        while($line = fgets(STDIN))
        {
            $commentSplitted = explode("#", $line, 2); // separate comment from code
            if(strlen($commentSplitted[0]) < strlen($line))
            { // if line had comment
                Self::$stats->addComment();
            }
            $line = $commentSplitted[0]; // save code without comment

            $OpcodeAndOperands = preg_split("/[\n\t ]+/", $line, 0, PREG_SPLIT_NO_EMPTY); // separate opcode and operands
            
            if(empty($OpcodeAndOperands))
            {
                continue;
            }

            if (!$headerFound)
            {
                if(strcasecmp($OpcodeAndOperands[0], ".IPPcode22") == 0)
                {
                    $headerFound = true;
                    Self::$xml->startDocument();
                    Self::$xml->startProgram();
                    continue;
                }
                else
                {
                    exit(ReturnValues::HEADER_ERR);
                }
            }

            $OpcodeAndOperands[0] = strtoupper($OpcodeAndOperands[0]);
            Self::parseCode($OpcodeAndOperands);
        }
        Self::$stats->endProgram();
        if($config)
        {
            Self::$stats->printStats();
        }

        Self::$xml->endProgram();
        Self::$xml->endDocument();
        Self::$xml->xmlPrint();
    }

    /**
     * Parses instruction code given as opcode and operands in array
     * @param array $codeArr instruction and operands stored in array
     * on lexical or syntax error exits with corresponding error code
     */
    private static function parseCode($codeArr)
    {
        Self::$stats->addInstruction();
        switch($codeArr[0])
        {
            // no operand
            case "RETURN":
                Self::check_operands_count($codeArr, 1);
                Self::$stats->addReturn();
                Self::$xml->startInstruction($codeArr[0]);
                Self::$xml->endInstruction();
                break;

            case "CREATEFRAME":
            case "PUSHFRAME":
            case "POPFRAME":
            case "BREAK":
                Self::check_operands_count($codeArr, 1);
                Self::$xml->startInstruction($codeArr[0]);
                Self::$xml->endInstruction();
                break;
            
            // <var>
            case "DEFVAR":
            case "POPS":
                Self::check_operands_count($codeArr, 2);
                Self::$xml->startInstruction($codeArr[0]);
                Self::parse_VAR($codeArr[1]);
                Self::$xml->endInstruction();
                break;
            
            // <label>
            case "LABEL":
                Self::check_operands_count($codeArr, 2);
                Self::$stats->addLabel($codeArr[1]);
                Self::$xml->startInstruction($codeArr[0]);
                Self::parse_LABEL($codeArr[1]);
                Self::$xml->endInstruction();
                break;

            case "CALL":
            case "JUMP":
                Self::check_operands_count($codeArr, 2);
                Self::$stats->addJump($codeArr[1]);
                Self::$xml->startInstruction($codeArr[0]);
                Self::parse_LABEL($codeArr[1]);
                Self::$xml->endInstruction();
                break;
            
            // <symb>
            case "PUSHS":
            case "WRITE":
            case "EXIT":
            case "DPRINT":
                Self::check_operands_count($codeArr, 2);
                Self::$xml->startInstruction($codeArr[0]);
                Self::parse_SYMB($codeArr[1]);
                Self::$xml->endInstruction();
                break;
            
            // <var> <symb>
            case "MOVE":
            case "INT2CHAR":
            case "STRLEN":
            case "TYPE":
            case "NOT":
                Self::check_operands_count($codeArr, 3);
                Self::$xml->startInstruction($codeArr[0]);
                Self::parse_VAR($codeArr[1]);
                Self::parse_SYMB($codeArr[2]);
                Self::$xml->endInstruction();
                break;

            // <var> <type>
            case "READ":
                Self::check_operands_count($codeArr, 3);
                Self::$xml->startInstruction($codeArr[0]);
                Self::parse_VAR($codeArr[1]);
                Self::parse_TYPE($codeArr[2]);
                Self::$xml->endInstruction();
                break;

            // <var> <symb1> <symb2>
            case "ADD":
            case "SUB":
            case "MUL":
            case "IDIV":
            case "LT":
            case "GT":
            case "EQ":
            case "AND":
            case "OR":
            case "STRI2INT":
            case "CONCAT":
            case "GETCHAR":
            case "SETCHAR":
                Self::check_operands_count($codeArr, 4);
                Self::$xml->startInstruction($codeArr[0]);
                Self::parse_VAR($codeArr[1]);
                Self::parse_SYMB($codeArr[2]);
                Self::parse_SYMB($codeArr[3]);
                Self::$xml->endInstruction();
                break;

            // <label> <symb1> <symb2>
            case "JUMPIFEQ":
            case "JUMPIFNEQ":
                Self::check_operands_count($codeArr, 4);
                Self::$stats->addJump($codeArr[1]);
                Self::$xml->startInstruction($codeArr[0]);
                Self::parse_LABEL($codeArr[1]);
                Self::parse_SYMB($codeArr[2]);
                Self::parse_SYMB($codeArr[3]);
                Self::$xml->endInstruction();
                break;
            // invalid command
            default:
                exit(ReturnValues::OPCODE_ERR);
        }    
    }   
    // OPERANDS REGEXES
    private const O_VAR = '/^(LF|TF|GF)@[a-zA-Z_\-$&%\*!\?][0-9a-zA-Z_\-$&%\*!\?]*$/';
    private const O_LABEL = "/^[a-zA-Z_\-$&%\*!\?][0-9a-zA-Z_\-$&%\*!\?]*$/";
    
    // VARIABLE TYPES REGEXES
    private const T_INT = "/^int@[+-]?[0-9]+$/";
    private const T_STRING = '/^string@(\\\\[0-9]{3}|[^\s#\\\\])*$/u';
    private const T_BOOL = "/^bool@(true|false)$/";
    private const T_NIL = "/^nil@nil$/";
    
    /**
     * Check lexical and syntax corectness of variable operand and add its XML representation into XMLWriter.
     * @param $operand - string which should represent variable
     */
    private static function parse_VAR($operand)
    {
        if (preg_match(Self::O_VAR, $operand))
        {
            Self::$xml->addArgument('var', $operand);
        }
        else
        {
            exit(ReturnValues::OTHER_ERR);
        }
    }

    /**
     * Check lexical and syntax corectness of symbol operand and add its XML representation into XMLWriter.
     * @param $operand - string which should represent symbol
     */
    private static function parse_SYMB($operand)
    {
        if (preg_match(Self::T_INT, $operand))
        {
            $separated = explode('@', $operand, 2);
            Self::$xml->addArgument('int', $separated[1]);
        }
        elseif (preg_match(Self::T_STRING, $operand))
        {
            $separated = explode('@', $operand, 2);
            Self::$xml->addArgument('string', $separated[1]);
        }
        elseif (preg_match(Self::T_BOOL, $operand))
        {
            $separated = explode('@', $operand, 2);
            Self::$xml->addArgument('bool', $separated[1]);
        }
        elseif (preg_match(Self::T_NIL, $operand))
        {
            $separated = explode('@', $operand, 2);
            Self::$xml->addArgument('nil', $separated[1]);
        }
        elseif (preg_match(Self::O_VAR, $operand))
        {
            Self::$xml->addArgument('var', $operand);
        }
        else
        {
            exit(ReturnValues::OTHER_ERR);
        }
    }
    
    /**
     * Check lexical and syntax corectness of label operand and add its XML representation into XMLWriter.
     * @param $operand - string which should represent label
     */
    private static function parse_LABEL($operand)
    {
        if (preg_match(Self::O_LABEL, $operand))
        {
            Self::$xml->addArgument('label', $operand);
        }
        else
        {
            exit(ReturnValues::OTHER_ERR);
        }
    }

    /**
     * Check lexical and syntax corectness of type operand and add its XML representation into XMLWriter.
     * @param $operand - string which should represent variable
     */
    private static function parse_TYPE($operand)
    {
        switch($operand)
        {
            case "int":
            case "string":
            case "bool":
                Self::$xml->addArgument("type", $operand);
                break;
                
            default:
                exit(ReturnValues::OTHER_ERR);
        }
    }

    /**
     * Check number of operands in array, if it doesn't match exit with OPCODE_ERR return value.
     * @param operands - array containing all instructions operands
     * @param count - number of excpected operands
     */
    private static function check_operands_count($operands, $count)
    {
        if (count($operands) != $count)
        {
            exit(ReturnValues::OTHER_ERR);
        }
    }
}
?>
