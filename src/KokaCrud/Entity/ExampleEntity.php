<?php

namespace KokaCrud\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

use KokaCrud\Entity\DataRow;

/**
 * @ORM\Entity
 * @Annotation\Name("ExampleEntity")
 */
class ExampleEntity extends DataRow{
    
    /**
     * @ORM\Column(type="string")
     * @Annotation\Attributes({"type":"text","placeholder":"A string"})
     * @Annotation\Options({"label":"A string:"})
     */
    protected $astring;
    
    /**
     * @ORM\Column(type="integer")
     * @Annotation\Attributes({"type":"text","placeholder":"An integer"})
     * @Annotation\Options({"label":"An integer:"})
     */
    protected $aninteger;
    
    /** 
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"A boolean:", "checked_value":"1", "unchecked_value":"0", "use_hidden_element":true})
     */
    protected $aboolean = 0;
    
    public function getAstring() {
        return $this->astring;
    }

    public function setAstring($astring) {
        $this->astring = $astring;
    }

    public function getAninteger() {
        return $this->aninteger;
    }

    public function setAninteger($aninteger) {
        $this->aninteger = $aninteger;
    }

    public function getAboolean() {
        return $this->aboolean;
    }

    public function setAboolean($aboolean) {
        $this->aboolean = $aboolean;
    }


    
}

?>
