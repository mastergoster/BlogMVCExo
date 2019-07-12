<?php
namespace Core\Controller;

class FormController
{

    private $postDatas;

    private $fields = [];

    private $errors = [];

    private $datas = [];

    public function __construct()
    {
        if (count($_POST) > 0) {
            $this->postDatas = $_POST;
        }else{
            $this->errors["post"] = "no-data";
        }
    }

    public function field(string $field, array $constraints = []): void
    {
        //$field = 'mail'    $constraints =  ["require", "verify"]
        $this->fields[$field] =  $constraints;
        
        if(!$this->errors['post']){
            foreach ($constraints as $key => $value) {
                if ($value == "require"){
                    if(!empty($this->postDatas[$field])){
                        $this->datas[$field] = htmlspecialchars($this->postDatas[$field]);
                    }else{
                        unset($this->datas[$field]);
                        $this->errors["$field"] = "le champ {$field} ne peut etre vide";
                    }
                }
                if ($value == "verify"){
                    if(isset($this->postDatas[$field."Verify"])){
                        if($this->postDatas[$field."Verify"] == $this->postDatas[$field]){
                            $this->datas[$field] = htmlspecialchars($this->postDatas[$field]);
                        }else{
                            unset($this->datas[$field]);
                            $this->errors["$field"] = "les champs {$field} doivent correspondre";
                        }
                    }else{
                        unset($this->datas[$field]);
                        $this->errors["$field"] = "le champ {$field} ne peut etre vide";
                    }
                }
                if ($key == "length"){
                    if( strlen($this->postDatas[$field]) >= $value){
                        $this->datas[$field] = htmlspecialchars($this->postDatas[$field]);
                    }else{
                        unset($this->datas[$field]);
                        $this->errors["$field"] = "le champ {$field} doit avoir au minimum {$value} caractères";
                    }
                }
            }
        }

    }
    

    public function hasErrors(): array
    {
        return $this->errors;
    }

    public function getDatas(): array
    {
        return $this->datas;
    }

}
