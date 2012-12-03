<?php

/**
 * Description of html2Odt
 *
 * Date - $Date: 2012-12-03 12:00:00 +0200 (lun., 03 décembre 2012) $
 * @author Sophien
 */

 /*
 * @copyright  GPL License 2012 - Mehboub Sophien - sociaNova (http://www.socianova.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.fr.html  GPL License
 * @version 1.0
 */
include_once('libHtml/config.php');

class html2Odt {

    private $dom_content;
    private $config = array('DELIMITER_LEFT' => '{',
        'DELIMITER_RIGHT' => '}',
        'PATH_TO_TMP' => 'C:\Temp');
    private $xml;
    private $xsl;
    private $odf;

    public function __construct($odf, $xml) {
        $this->xsl = config::$xsl;
        $this->odf = $odf;

        $this->dom_content = new DOMDocument;

        $this->dom_content->loadXML($xml);
    }

    private function transform($xml, $xsl) {
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet(new SimpleXMLElement($xsl));
        return $xslt->transformToDoc(new SimpleXMLElement($xml));
    }

    public function setVar($key, $value) {
        $value = '<html>' . $value . '</html>';
        $this->xml = $value;
        $value = $this->transform($value, $this->xsl);

        foreach ($this->dom_content->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $element) {
            $string = $element->nodeValue;

            if (preg_match($this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT'], $string)) {

                $elem1 = $this->dom_content->createElementNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'text:span');

                $parts = explode($this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT'], $element->nodeValue);

                $elem1->nodeValue = $parts[1];

                $element->nodeValue = '';
                if (!($parts[0] == '')) {
                    $elem = $this->dom_content->createElementNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'text:span');
                    $elem->nodeValue = $parts[0];
                    $element->appendChild($elem);
                }
                $element->appendChild($elem1);

                foreach ($value->documentElement->childNodes as $v) {
                    $node = $this->dom_content->importNode($v, true);

                    $element->insertBefore($node, $elem1);
                }
            }
        }
        $this->style();
    }

    public function style() {

        $array = config::$style;

        include_once('libHtml/parseHtml.php');

        $tidy = tidy_parse_string($this->xml);

        $tidy->cleanRepair();

        $parse = new parseHtml();

        $parse->get_nodes($tidy->html());


        foreach ($parse->getArray() as $matches) {

            $id = '';
            foreach ($matches as $key => $value) {
                $vAttr = '';
                if (count($value) > 0) {
                    $vAttr = implode('', array_values($value));
                }
                $vAttr = strtolower($vAttr);
                $id = $id . $key . $vAttr;
            }
            $flag = false;
            foreach ($this->dom_content->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style') as $element) {
                if ($element->hasAttributes()) {
                    $attributes = $element->attributes;
                    if (!is_null($attributes)) {
                        foreach ($attributes as $attr) {
                            if ($attr->name == 'name') {
                                if ($attr->value == $id) {
                                    $flag = true;
                                }
                            }
                        }
                    }
                }
            }
            if ($flag == false) {
                foreach ($this->dom_content->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:office:1.0', 'automatic-styles') as $element) {

                    $elem = $this->dom_content->createElementNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:style');

                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:name', $id);
                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:family', 'text');

                    $elem1 = $this->dom_content->createElementNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:text-properties');

                    foreach ($matches as $key => $value) {
                        if (count($value) > 0) {
                            foreach ($value as $k => $v) {

                                if (isset($array[$key . '-' . $k])) {
                                    include_once('libHtml/xColor.php');
                                    foreach ($array[$key . '-' . $k] as $val) {
                                        if ($key . '-' . $k == 'font-color') {
                                            if (preg_match('/rgb/', $v)) {
                                                $v = explode(',', str_replace(array('rgb(', ')', ' '), '', $v));
                                                $v = xColor::rgb2hex($v);
                                            } elseif (!preg_match('/#/', $v)) {
                                                $v = xColor::color_name_to_hex($v);
                                            } else {
                                                $v = strtolower($v);
                                            }
                                            $elem1->setAttribute($val, $v);
                                        }
                                        if ($key . '-' . $k == 'font-size') {

                                            $elem1->setAttribute($val, $v . 'pt');
                                        } else {
                                            $elem1->setAttribute($val, $v);
                                        }
                                    }
                                }
                            }
                        }
                        if (isset($array[$key])) {
                            foreach ($array[$key] as $k => $val) {
                                $elem1->setAttribute($k, $val);
                            }
                        }
                        $elem->appendChild($elem1);
                        $element->appendChild($elem);
                    }
                }
            }
        }
        $this->odf->getDom_content()->loadXML($this->dom_content->saveXML());
    }

}

?>
