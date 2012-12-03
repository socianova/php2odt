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


$odf = new php2Odt('tutoriel2.odt');

$odf->setVar('valeur', 'titre');

$array = array('image' => array(
        'file' => './sociaNOVA.jpg',
        'align' => 'right',
        'width' => 10,
        'height' => 2)
);

$odf->setImage($array);


// les balises gérées sont : b,i,u,font avec attribut size et/ou color,
//  toutes peuvent etre imbriqué sauf ul li ou ol li

$html = '<font size="15" color="#ff0000"><b> 
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

$html2 = '<font size="8" color="blue"><u> 
      Je
    </u></font>
    <i>
       vais
    </i>
    <i>
       vous
    </i><b>
     <i><u>
       expliquer</u>
    </i></b>
    <ul>
    <li>comment ca</li>
    <li>marche</li>
    </ul>';


$odfHtml = $odf->setOdfHtml();

$odfHtml->setVar('html', $html);

$odfHtml->setVar('test', $html2);


$odf->exportAsAttachedFile();

?>
