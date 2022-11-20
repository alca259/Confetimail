<?php

class Fields
{
    #region Properties
	private $required = false; // Si puede ser nulo
	private $readonly = false; // Si el campo es de solo lectura
	private $title = "unknown"; // Nombre a mostrar
	private $size = false; // Longitud del campo
	private $type = null; // Tipo de campo
	private $decimals = 0; // Indica los decimales del campo flotante
	private $asFunction = false; // Indica si este tipo de campo es funcional
    #endregion
    
    #region Mapping fields
	protected $enum_type_fields = array (
		// Number fields
		"int" => "int",
		"float" => "float",
		"decimal" => "decimal",
		"double" => "double",
		"bool" => "tinyint",

		// Text fields
		"char" => "varchar",
		"littletext" => "text",
		"middletext" => "mediumtext",
		"bigtext" => "longtext",
		"selection" => "varchar",

		// Date fields
		"date" => "date",
		"datetime" => "datetime",

		// Related fields
		"many2one" => "int"
	);
    #endregion

	#region Getters
	public function GetTitle() { return $this->title; }
	public function GetRequired() { return $this->required; }
	public function GetType() { return $this->type; }
	public function IsFunction() { return $this->asFunction; }
	public function IsReadonly() { return $this->readonly; }
	protected function GetDecimals() { return $this->decimals; }
	protected function GetSize() { return $this->size; }
    public function GetDBRequired() { return $this->GetRequired() ? "YES" : "NO"; }
	public function GetDBType()
    {
		if (in_array($this->type, array ('int',	'char', 'selection', 'many2one', 'bool')))
        {
            return sprintf("%s(%s)", $this->enum_type_fields[$this->type], $this->size);
        }
		
        if (in_array($this->type, array ('float', 'decimal', 'double')))
        {
            return sprintf("%s(%s,%s)", $this->enum_type_fields[$this->type], $this->size, $this->decimals);
        }

		return sprintf("%s", $this->enum_type_fields[$this->type]);
	}
    #endregion

	#region Setters
	protected function SetTitle($flag) { $this->title = $flag; }
	protected function SetRequired($flag) { $this->required = $flag; }
	protected function SetType($flag) { $this->type = $flag; }
	protected function SetDecimals($flag) { $this->decimals = $flag; }
    protected function SetAsFunction($flag) { $this->asFunction = $flag; }
	protected function SetReadonly($flag) { $this->readonly = $flag; }
	protected function SetSize($size, $maxSize)
    {
		if ($size <= $maxSize && $size > 0)
        {
			$this->size = $size;
		}
        elseif ($size <= $maxSize && $size <= 0)
        {
			throw new MindException("The field size cannot be less than 0");
		}
        else
        {
			throw new MindException(sprintf("The field size cannot be more than %s", $maxSize));
		}
	}
    #endregion

	#region Public methods
	/**
	 * Return a string with field properties
	 */
	public function GetProperties()
    {
		$property_field = "";

		// We check if this type of field is supported for create in database
		if (array_key_exists($this->GetType(), $this->enum_type_fields))
        {
			// TIPO Todos: Especificamos el tipo de la columna
			$property_field = sprintf("%s", $this->enum_type_fields[$this->getType()]);

			// TIPO NUMERICO: Especificamos longitud y decimales
			if (in_array($this->GetType(), array ('int', 'float', 'decimal', 'double', 'many2one', 'bool')))
            {
                // TIPO NUMERICO: Comprobamos la longitud
				if ($this->GetSize() != false)
                {
					// Comprobamos si tiene decimales
					if ($this->decimals > 0)
                    {
						$property_field .= sprintf(" (%s, %s)", $this->GetSize(), $this->GetDecimals());
					}
                    else
                    {
						$property_field .= sprintf(" (%s)", $this->GetSize());
					}
				}
			}
			elseif (in_array($this->GetType(), array ('char', 'selection')))
            {
                // TIPO TEXTO: Especificamos longitud
				// Comprobamos la longitud
				if ($this->GetSize() != false)
                {
					$property_field .= sprintf(" (%s)", $this->GetSize());
				}
			}

			// TIPO Todos: Comprobamos si es nulo
			if ($this->GetRequired())
            {
				$property_field .= " NOT NULL";
			}
		}

		return $property_field;
	}
    #endregion
}

class Integer extends Fields
{
	public function __construct($title, $size = 11, $required = false, $readonly = false)
    {
		$this->SetRequired($required);
		$this->SetSize($size, 11);
		$this->SetTitle($title);
		$this->SetType("int");
		$this->SetReadonly($readonly);
	}
}

class Float extends Fields
{
	public function __construct($title, $size = 11, $required = false, $readonly = false, $decimals = 2)
    {
		$this->SetRequired($required);
		$this->SetSize($size, 64);
		$this->SetDecimals($decimals);
		$this->SetTitle($title);
		$this->SetType("float");
		$this->SetReadonly($readonly);
	}
}

class Char extends Fields
{
	public function __construct($title, $size = 512, $required = false, $readonly = false)
    {
		$this->SetRequired($required);
		$this->SetSize($size, 2048);
		$this->SetTitle($title);
		$this->SetType("char");
		$this->SetReadonly($readonly);
	}
}

class Date extends Fields
{
	public function __construct($title, $required = false, $readonly = false)
    {
		$this->SetRequired($required);
		$this->SetTitle($title);
		$this->SetType("date");
		$this->SetReadonly($readonly);
	}
}

class Fechahora extends Fields
{
	public function __construct($title, $required = false, $readonly = false)
    {
		$this->SetRequired($required);
		$this->SetTitle($title);
		$this->SetType("datetime");
		$this->SetReadonly($readonly);
	}
}

class Text extends Fields
{
	public function __construct($title, $required = false, $readonly = false)
    {
		$this->SetRequired($required);
		$this->SetTitle($title);
		$this->SetType("littletext");
		$this->SetReadonly($readonly);
	}
}

class Many2one extends Fields
{
	private $relation = false; // Modelo relacionado 1 a muchos
    private $areaName = ""; // Area en la que se encuentra el modelo
	private $fieldRelated = ""; // Campo relacionado
	private $onUpdate = "CASCADE"; // Que hacer al actualizar un registro
	private $onDelete = "CASCADE"; // Que hacer al borrar un registro

	public function __construct($title, $model_related, $areaName, $onupdate = "CASCADE", $ondelete = "CASCADE", $readonly = false)
    {
		$this->SetRequired(true);
		$this->SetTitle($title);
		$this->SetType("many2one");
		$this->SetSize(11, 11);

		$this->SetRelation($model_related);
        $this->SetAreaName($areaName);
		$this->SetRelatedField("id");
		$this->SetOnUpdate($onupdate);
		$this->SetOnDelete($ondelete);
		$this->SetReadonly($readonly);
	}

    #region Getters
	public function GetPropertiesRelation($table_related_name, $table_base_name, $field_base_name)
    {
		$property_field = "";

		// We check if this type of field is supported for create in database
		if (array_key_exists($this->GetType(), $this->enum_type_fields))
        {
			if (in_array($this->GetType(), array ('many2one')))
            {
				$property_field .= sprintf(" `FK_%s_%s_%s`", strtoupper($table_related_name), strtoupper($table_base_name), strtoupper($field_base_name));
				$property_field .= sprintf(" FOREIGN KEY (`%s`) REFERENCES %s (`%s`)", $field_base_name, $table_related_name, $this->fieldRelated);
				$property_field .= sprintf(" ON DELETE %s ON UPDATE %s", $this->onDelete, $this->onUpdate);
			}
		}

		return $property_field;
	}
	public function GetRelation() { return $this->relation; }
    public function GetAreaName() { return $this->areaName; }
    #endregion

    #region Setters
    protected function SetAreaName($flag) { $this->areaName = $flag; }
    protected function SetRelation($flag) { $this->relation = $flag; }
	protected function SetRelatedField($flag) { $this->fieldRelated = $flag; }
	protected function SetOnUpdate($flag) { $this->onUpdate = $flag; }
	protected function SetOnDelete($flag) { $this->onDelete = $flag; }
    #endregion
}

class Funcion extends Fields
{
	public function __construct($title)
    {
		$this->SetTitle($title);
		$this->SetType("function");
		$this->SetAsFunction(true);
		$this->SetReadonly(true);
	}
}

class Selection extends Fields
{
	private $objects = array();
	
	public function __construct($title, $selection_objects = array(), $size = 64, $required = false, $readonly = false)
    {
		$this->SetRequired($required);
		$this->SetTitle($title);
		$this->SetSize($size, 256);
		$this->SetType("selection");
		$this->SetReadonly($readonly);
		$this->SetObjects($selection_objects);
	}

    #region Setters
	protected function SetObjects($flag)
    {
		$this->objects = $flag;
	}
    #endregion

    #region Getters
	public function GetObjects() { return $this->objects; }

	public function GetObjectSelected($object)
    {
		if (array_key_exists($object, $this->objects))
        {
			return $this->objects[$object];
		}

		// No existe
		return false;
	}
    #endregion
}

class Boolean extends Fields
{
	private $grouped = false;

	public function __construct($title, $grouped = false, $size = 1, $required = false, $readonly = false)
    {
		$this->SetRequired($required);
		$this->SetTitle($title);
		$this->SetSize($size, 1);
		$this->SetType("bool");
		$this->SetReadonly($readonly);
		$this->SetGrouped($grouped);
	}

    #region Setters
	protected function SetGrouped($flag)
    {
		$this->grouped = $flag;
	}
    #endregion

    #region Getters
	public function IsGrouped()
    {
		return $this->grouped;
	}
    #endregion
}
?>