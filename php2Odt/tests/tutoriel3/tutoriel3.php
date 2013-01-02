<?php

/**
 * Description of tutoriel2
 *
 * @author sophien
 */


include_once('../../OdtLib/php2Odt.php');
include_once('../../OdtLib/html2Odt.php');



$t = array('var1',
        'var2',
    'var3');


$odf = new php2Odt('tutoriel3.odt');

$odf->setVar('titre', 'valeur');

$array = array('image' => array(
        'file' => './sociaNOVA.jpg',
        'align' => 'left',
        'width' => 16,
        'height' => 9)
);

$odf->setImage($array);

$sous_sous_segment = $odf->setSegment('Sous-Sous-Segment');

$sous_sous_segment->setvar('textSous-Sous-Segment', 'varSeul');

$sous_segment = $odf->setSegment('Sous-Segment');
 
$sous_segment->setvar('textSous-Segment',$t);

$segment = $odf->setSegment('Segment');

$segment->setvar('textSegment',$t);

// les balises gérées sont : b,i,u,font avec attribut size et/ou color,
//  toutes peuvent etre imbriqué sauf ul li ou ol li

$html = '<font size="20" color="rgb(255,0,0)"><b> 
      Je
    </b></font>
    <i>
       vais
    </i>
    <i>
       vous
    </i><b>
     <i><u>
       expliquer</u>
    </i></b>
    <ol>
    <li>comment ca</li>
    <li>marche</li>
    </ol>';


$odfHtml = $odf->setOdfHtml();

$odfHtml->setVar('html', $html);


$odf->exportAsAttachedFile();

?>
