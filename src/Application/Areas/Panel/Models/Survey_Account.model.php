<?php
class Survey_AccountModel extends ModelBase
{
    public function __construct($db, $iniciar = false)
    {
        $this->name = "survey_account";
        $this->table = "survey_account";
        $this->columns = array (
            "user_id" => new Many2one("User", "account", Constants::$PanelAreaName),
            "entertainment_recommendation" => new Boolean("Recomendación de ocio"),
            "general_recommendation" => new Boolean("Recomendación general"),
            "background_icons_web" => new Boolean("Fondos e iconos para Web"),
            "calendars" => new Boolean("Calendarios"),
            "printables" => new Boolean("Imprimibles"),
            "cliparts" => new Boolean("Clip arts"),
            "photographs" => new Boolean("Fotografías"),
            "wallpapers" => new Boolean("Fondos de escritorio"),
            "bullet_point" => new Boolean("Viñeta"),
        );
        parent::__construct($db, $iniciar);
    }
    
    /**
     * Summary of GetSurveyFields
     * Devuelve los campos que pueden ser votados por el usuario
     */
    public function GetSurveyFields()
    {
        $fields = array();
        
        // Leemos todas las columnas booleanas
        foreach ($this->columns as $field_name => $field_properties)
        {
            if ($field_properties->GetType() != "bool") continue;
            $fields[] = array(
                "field_name" => $field_name,
                "field_title" => $field_properties->GetTitle(),
                "field_value" => 0
            );
        }
        
        return $fields;
    }
}
?>