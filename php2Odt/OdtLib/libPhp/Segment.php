<?php

/**
 * Description of Segment
 * 
 * Date - $Date: 2012-12-03 13:00:00 +0200 (lun., 03 décembre 2012) $
 * @author Sophien
 */

 /*
 * @copyright  GPL License 2012 - Mehboub Sophien - Badreddine Zeghiche - sociaNova (http://www.socianova.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.fr.html  GPL License
 * @version 1.0
 */

class Segment {

    private $_children = array();
    private $_childs = array();
    private $dom;
    private $elements;
    private $name;
    private $odf;

    public function __construct($name, $odf, $xml) {
        $this->odf = $odf;
        $this->name = $name;

        $this->dom = new DOMDocument; 
        $this->dom->loadXML($xml);

        $replace_d = "[!-- BEGIN $name --]";
        $replace_f = "[!-- END $name --]";
        $depart = false;

        foreach ($this->dom->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $element) {
            if ($depart) {
                $this->elements[] = $element;
            }
            if (preg_match($replace_f, $element->nodeValue)) {
                $depart = false;
            }
            if (preg_match($replace_d, $element->nodeValue)) {
                $depart = true;
            }
        }


        foreach ($this->dom->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $element) {
            if (preg_match($replace_f, $element->nodeValue)) {
                $element->nodeValue = preg_replace("#\[!--\sEND\s($name)\s--\]#sm", ' ', $element->nodeValue);
            }
            if (preg_match($replace_d, $element->nodeValue)) {
                $element->nodeValue = preg_replace("#\[!--\sBEGIN\s($name)\s--\]#sm", ' ', $element->nodeValue);
                ;
            }
        }
        $newdoc = new DOMDocument();
        $new = $newdoc->createElement('test');
        foreach ($this->elements as $element) {

            $cloned = $element->cloneNode(TRUE);
            $new->appendChild($newdoc->importNode($cloned, TRUE));
        }
        $newdoc->appendChild($new);
        $this->doc = $newdoc->saveXML();
        $this->analyseChildren();
    }

    public function analyseChildren($string = '[\S]*') {
        $i = 0;
        foreach ($this->elements as $key => $element) {

            $reg = "#\[!--\sBEGIN\s([\S]*)\s--\]#sm";

            if (preg_match($reg, $element->textContent)) {
                preg_match_all($reg, $element->textContent, $this->_childs[$i++]);
            }
        }
        foreach ($this->_childs as $value) {
            $this->_children[] = $value[1][0];
        }
        $this->_children = array_reverse($this->_children);
        $this->_children[] = $this->name;
    }

    private function copie($nb) {
        $value = new DOMDocument;
        $value->loadXML($this->doc);
        $i = 1;

        $elements = array();
        foreach ($value->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $val) {
            $elements[] = $val;
        }
        $elements = array_reverse($elements);
        for ($i = 1; $i < $nb; $i++) {
            foreach ($elements as $val) {
                $node = $this->dom->importNode($val, true);
                $this->elements[0]->parentNode->insertBefore($node, $this->elements[count($this->elements) - 1]->nextSibling);
            }
        }
    }

    public function setvar($key ,$value = '') {
        $config = array('DELIMITER_LEFT' => '{',
            'DELIMITER_RIGHT' => '}');
        $test = false;
        
        if (is_array($key)) {$value = $key;$key = '';}
        
        if (!$key == '') {
            if (strpos($this->dom->saveXML(), $config['DELIMITER_LEFT'] . $key . $config['DELIMITER_RIGHT']) === false) {
                throw new php2OdtException("var $key est introuvable dans  document");
            }
        }else {

        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $test = true;
                $nbcopy = count($v);
            if (strpos($this->dom->saveXML(), $config['DELIMITER_LEFT'] . $k . $config['DELIMITER_RIGHT']) === false) {
                throw new php2OdtException("var $k est introuvable dans  document");
            }   
            }  else {
                $test = false;
            }
            
        }
        }
        
        if ($test) {
            
            $this->copie($nbcopy);
            
            foreach ($value as $k => $val) {

            foreach ($val as $v) {
                foreach ($this->dom->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $element) {
                    if ($element->hasChildNodes()) {
                        foreach ($element->childNodes as $elem) {
                            $string = $elem->nodeValue;
                            if (preg_match($config['DELIMITER_LEFT'] . $k . $config['DELIMITER_RIGHT'], $string)) {
                                $elem->nodeValue = str_replace($config['DELIMITER_LEFT'] . $k . $config['DELIMITER_RIGHT'], $v, $string);
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        }elseif (is_array($value)) {
            $this->copie(count($value));

            foreach ($value as $v) {
                foreach ($this->dom->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $element) {
                    if ($element->hasChildNodes()) {
                        foreach ($element->childNodes as $elem) {
                            $string = $elem->nodeValue;
                            if (preg_match($config['DELIMITER_LEFT'] . $key . $config['DELIMITER_RIGHT'], $string)) {
                                $elem->nodeValue = str_replace($config['DELIMITER_LEFT'] . $key . $config['DELIMITER_RIGHT'], $v, $string);
                                break 2;
                            }
                        }
                    }
                }
            }
        } else {
            $this->copie(1);
            $var[$config['DELIMITER_LEFT'] . $key . $config['DELIMITER_RIGHT']] = $value;
            foreach ($this->dom->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $element) {
                if ($element->hasChildNodes()) {
                    foreach ($element->childNodes as $elem) {
                        $string = $elem->nodeValue;
                        if (preg_match($config['DELIMITER_LEFT'] . $key . $config['DELIMITER_RIGHT'], $string)) {
                            $elem->nodeValue = str_replace(array_keys($var), array_values($var), $string);
                        }
                    }
                }
            }
        }
        $this->odf->getDom_content()->loadXML($this->dom->saveXML());
    }

}

?>
