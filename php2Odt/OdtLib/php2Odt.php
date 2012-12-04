<?php

class php2OdtException extends Exception {
    
}


include_once('libPhp/Segment.php');

/**
 * Description of php2Odt
 *
 * Date - $Date: 2012-12-03 13:00:00 +0200 (lun., 03 décembre 2012) $
 * @author Sophien
 */

 /*
 * @copyright  GPL License 2012 - Mehboub Sophien - Badreddine Zeghiche - sociaNova (http://www.socianova.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.fr.html  GPL License
 * @version 1.0
 */
class php2Odt {

    private $dom_manifest;
    private $dom_content;
    private $zip;
    private $config = array('DELIMITER_LEFT' => '{',
        'DELIMITER_RIGHT' => '}',
        'PATH_TO_TMP' => 'C:\Temp');
    private $tmpfile;
    private $images = array();
    private $segments = array();

    public function __construct($filename) {

        if (!file_exists($filename)) {
            throw new php2OdtException("Le fichier odt spécifié n'existe pas");
        }

        $tmp = tempnam($this->config['PATH_TO_TMP'], md5(uniqid()));
        copy($filename, $tmp);
        $this->tmpfile = $tmp;

        $this->zip = new ZipArchive;

        if ($this->zip->open($this->tmpfile, ZipArchive::CREATE) === TRUE) {

            $this->dom_manifest = new DOMDocument;
            $this->dom_content = new DOMDocument;

            if (($this->dom_content->loadXML($this->zip->getFromName('content.xml')))
                    and ($this->dom_manifest->loadXML($this->zip->getFromName('META-INF/manifest.xml'))) == false) {
                throw new php2OdtException("Rien à parser - Vérifier que que le fichier content.xml soit bien formé");
            }
        } else {
            throw new php2OdtException("Erreur Lors de l'ouverture de '$filename' - Vérifiez votre fichier odt");
        }
    }

    public function setVar($key, $value) {
        if (strpos($this->dom_content->saveXML(), $this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT']) === false) {
            throw new php2OdtException("var $key est introuvable dans le document");
        }

        $value = utf8_encode($value);

        $var[$this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT']] = $value;
        foreach ($this->dom_content->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $element) {
            $string = $element->nodeValue;

            if ($element->hasChildNodes()) {
                foreach ($element->childNodes as $elem) {
                    $string = $elem->nodeValue;
                    if (preg_match($this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT'], $string)) {
                        $elem->nodeValue = str_replace(array_keys($var), array_values($var), $string);
                    }
                }
            }
        }
    }

    public function setImage(array $array) {

        if (count($array) > 0) {

            foreach ($array as $key => $value) {

                if (strpos($this->dom_content->saveXML(), $this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT']) === false) {
                    throw new php2OdtException("var $key est introuvable dans le document");
                }


                if (!file_exists($value['file'])) {
                    throw new php2OdtException("L'image spécifié n'existe pas");
                }

                $filename = strtok(strrchr($value['file'], '/'), '/.');
                $file = substr(strrchr($value['file'], '/'), 1);

                foreach ($this->dom_manifest->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:manifest:1.0', 'manifest') as $element) {
                    $elem = $this->dom_manifest->createElementNS('urn:oasis:names:tc:opendocument:xmlns:manifest:1.0', 'manifest:file-entry');
                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:manifest:1.0', 'manifest:media-type', 'image/jpeg');
                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:manifest:1.0', 'manifest:full-path', "Pictures/$file");
                    $element->appendChild($elem);
                }

                $this->images[$value['file']] = $file;
            }

            $replace = $this->config['DELIMITER_LEFT'] . $key . $this->config['DELIMITER_RIGHT'];

            foreach ($this->dom_content->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p') as $element) {

                if (preg_match("/$replace/", $element->nodeValue)) {

                    $element->nodeValue = '';
                    $element->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'style-name', $key . '1');
                    $elem = $this->dom_content->createElementNS('urn:oasis:names:tc:opendocument:xmlns:drawing:1.0', 'draw:frame');

                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:drawing:1.0', 'draw:style-name', $key);
                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:drawing:1.0', 'draw:name', $key);
                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'text:anchor-type', 'text:anchor-type');
                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0', 'svg:width', $value['width'].'cm');
                    $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0', 'svg:height', $value['height'].'cm');

                    $sous_elem = $this->dom_content->createElementNS('urn:oasis:names:tc:opendocument:xmlns:drawing:1.0', 'draw:image');
                    $sous_elem->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', "Pictures/$file");
                    $sous_elem->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:type', "simple");
                    $sous_elem->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:show', "embed");
                    $sous_elem->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:actuate', "onLoad");


                    $elem->appendChild($sous_elem);
                    $element->appendChild($elem);
                }
            }

            foreach ($this->dom_content->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:office:1.0', 'automatic-styles') as $element) {


                $elem = $this->dom_content->createElementNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:style');

                $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:name', $key . '1');
                $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:family', "paragraph");
                $elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:parent-style-name', 'Standard');


                $sous_elem = $this->dom_content->createElementNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:paragraph-properties');
                $sous_elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:style:1.0', 'style:justify-single-word', 'false');

                $sous_elem->setAttributeNS('urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0', 'fo:text-align', $value['align']);


                $elem->appendChild($sous_elem);
                $element->appendChild($elem);
            }
        }
    }

    private function merge() {
        $this->zip->addFromString('META-INF/manifest.xml', $this->dom_manifest->saveXML());
        $this->zip->addFromString('content.xml', $this->dom_content->saveXML());
        foreach ($this->images as $imageKey => $imageValue) {
            $this->zip->addFile($imageKey, 'Pictures/' . $imageValue);
        }
    }

    public function getDom_content() {
        return $this->dom_content;
    }

    public function getTmpfile() {
        return $this->tmpfile;
    }

    public function getConfig($configKey) {
        if (array_key_exists($configKey, $this->config)) {
            return $this->config[$configKey];
        }
    }

    public function setSegment($segment) {
        if (array_key_exists($segment, $this->segments)) {
            return $this->segments[$segment];
        }

        $this->segments[$segment] = new Segment($segment, $this, $this->dom_content->saveXML());
        return $this->segments[$segment];
    }

    public function setOdfHtml() {
        return new html2Odt($this, $this->dom_content->saveXML());
    }

    public function exportAsAttachedFile($name = "") {

        $this->merge();
        if (headers_sent($filename, $linenum)) {
            throw new OdfException("headers already sent ($filename at $linenum)");
        }

        if ($name == "") {
            $name = md5(uniqid()) . ".odt";
        }
        $this->zip->close();
        header('Content-type: application/vnd.oasis.opendocument.text');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        readfile($this->tmpfile);
        unlink($this->tmpfile);
    }

}

?>
