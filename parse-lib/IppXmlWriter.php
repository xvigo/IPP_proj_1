<?php
/**
 * @package IPP project 2022 - parser.php
 * @author Vilem Gottwald
 */

/**
 * Class for clean working with IPPcode22 XML.
 */
final class IppXmlWriter
{
    /**
     * Private property for XMLWriter.
     */
    private $xw;

    /**
     * Initialize XMLWriter and set its indentation.
     * @param $indent - indentation string (default: '  ')
     */
    function __construct($indent = '  ') 
    {
        $this->xw = new XMLWriter();
        $this->xw->openMemory();

        $this->xw->setIndent(true);
        $this->xw->setIndentString($indent);
    }

    /**
     * Print created XML to standard output.
     */
    function xmlPrint()
    {
        echo $this->xw->outputMemory();
    }

    /**
     * Start XML document - add XML header with version and encoding.
     * @param $version - (default: '1.0')
     * @param $encoding - (default: 'UTF-8')
     */
    function startDocument($version = '1.0', $encoding = 'UTF-8')
    {
        $this->xw->startDocument($version, $encoding);
    }

    /**
     * End XML document.
     */
    function endDocument()
    {
        $this->xw->endDocument();
    }

    /**
     * Start program tag with language attribute.
     * @param $language - language attribute value (default: 'IPPcode22')
     */
    function startProgram($language = 'IPPcode22')
    {
        $this->xw->startElement('program');
        $this->newElementAttribute('language',$language);
    }

    /**
     * End program tag.
     */
    function endProgram()
    {
        $this->xw->endElement();
    }

    /**
     * Private property for storing current instructions order.
     */
    private $instrOrder = 1;
    /**
     * Get value of instruction order and increment it.
     * @return instruction order
     */
    function getInstrOrder()
    {
        return $this->instrOrder++;
    }

    /**
     * Start instruction tag with order and opcode attributes, where order is computed automatically.
     * @param $opcode - opcode attribute value (default: 'IPPcode22')
     */
    function startInstruction($opcode)
    {
        $this->xw->startElement('instruction');
        $this->newElementAttribute('order', $this->getInstrOrder());
        $this->newElementAttribute('opcode', $opcode);

        $this->resetArgOrder(); // set instrustion's argument order back to 1
    }

    /**
     * End instruction tag.
     */
    function endInstruction()
    {
        $this->xw->endElement();
    }

    /**
     * Private property for storing current instruction arguments order.
     */
    private $argOrder = 1;
    
    /**
     * Get value of instructions argument order and increment it.
     * @return instruction order
     */
    function getArgOrder()
    {
        return $this->argOrder++;
    }

    /**
     * Set value of instructions argument order to 1.
     */
    function resetArgOrder()
    {
        $this->argOrder = 1;
    }
    
    /**
     * Add instructoions argument tag with operand type attribute and its content.
     * @param $type - operand type 
     * @param $content - content of arg tag
     */
    function addArgument($type, $content)
    {
        $this->xw->startElement('arg'. $this->getArgOrder());
        $this->newElementAttribute('type', $type);
        $this->xw->text($content);
        $this->xw->endElement();
    }
    /**
     * Add attribute inside a tag.
     * @param $name - name of the attribute
     * @param $value - value of the attribute
     */
    private function newElementAttribute($name, $value)
    {
        $this->xw->startAttribute($name);
        $this->xw->text($value);
        $this->xw->endAttribute();
    }
}
?>
