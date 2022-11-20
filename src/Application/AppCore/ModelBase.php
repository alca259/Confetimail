<?php
class ModelBase implements IModelBase
{
    #region Vars
    public $db = null;
    public $name = null;
    public $table = null;
    public $columns = array();
    public $data = array();
    #endregion

    #region Constructors
    /**
     * Every model needs a database connection, passed to the model
     *
     * @param object $db
     *          A PDO database connection
     */
    public function __construct($db, $iniciar = false)
    {
        try 
        {
            $this->db = $db;
            // Inicializamos las tablas si asi se requiere
            if ($iniciar == true)
            {
                $this->InitModel();
            }
        }
        catch (PDOException $e)
        {
            exit('Database connection could not be established.');
        }
    }
    #endregion

    #region Structure methods
    public function DropTable()
    {
        if (TableExists($this->db, $this->table))
        {
            // La tabla existe, la borramos
            $default_sql = sprintf("DROP TABLE %s;", $this->table);
            $query = $this->db->prepare($default_sql);
            $query->execute();
        }
    }

    /**
     * This function compare the model and table in database
     * If not exists, this create it. If exists, this test if can be updated.
     */
    public function InitModel()
    {
        if (!TableExists($this->db, $this->table))
        {
            // La tabla no existe, la creamos
            $default_sql = sprintf("
                CREATE TABLE %s (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `create_uid` int(11),
                    `create_date` TIMESTAMP DEFAULT 0,
                    `write_uid` int(11),
                    `write_date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`));", $this->table);

            $query = $this->db->prepare($default_sql);
            $query->execute();

            // Creamos un trigger para actualizar la fecha de creación
            $default_trigger_sql = sprintf("
                CREATE TRIGGER TG_BI_%s
                    BEFORE INSERT ON %s
                        FOR EACH ROW
                            SET NEW.create_date = now();", strtoupper($this->table), $this->table);

            $query_trigger = $this->db->prepare($default_trigger_sql);
            $query_trigger->execute();

            // Por cada una de las columnas definidas...
            foreach ($this->columns as $field_name => $field_properties)
            {
                $this->CreateColumn($field_name, $field_properties);
                $this->CreateForeignKey($field_name, $field_properties);
            }
            
            return true;
        }

        // La tabla existe, la modificamos

        // Por cada una de las columnas definidas...
        foreach ($this->columns as $field_name => $field_properties)
        {
            // Si es un campo funcional, no lo generamos en DB
            if ($field_properties->IsFunction())
            {
                continue;
            }
            
            if (!FieldExists($this->db, $this->table, $field_name))
            {
                // El campo no existe en DB, lo creamos
                $this->CreateColumn($field_name, $field_properties);
                $this->CreateForeignKey($field_name, $field_properties);
                continue;
            }

            // El campo existe, obtenemos los datos del campo en BD
            $field_db = GetInfoField($this->db, $this->table, $field_name);

            // Flag para determinar si las propiedades del campo han cambiado
            $flag_field_changed = false;

            // Comprobamos si se han modificado las propiedades de la columna
            if ($field_properties->GetDBType() != $field_db[0]["Type"])
            {
                $flag_field_changed = true;
            }
            elseif ($field_properties->GetDBRequired() == $field_db[0]["Null"])
            {
                $flag_field_changed = true;
            }

            // Si no se ha modificado, continuamos
            if (!$flag_field_changed)
            {
                continue;
            }
            
            // Si se ha modificado, entonces, renombramos el campo anterior y creamos uno nuevo
            try
            {
                // Creamos la transaccion
                $this->db->beginTransaction();

                $field_old_name = $field_db[0]["Field"];
                $field_new_name = $field_name . "_OLD_" . time();

                // Si existia alguna foreign key relacionada, la eliminamos
                $field_fk_assigned = GetConstraintFK($this->db, $this->table, $field_old_name);
                if ($field_fk_assigned != false)
                {
                    // Delete FK
                    $delete_fk_sql = sprintf("ALTER TABLE %s DROP FOREIGN KEY %s", $this->table, $field_fk_assigned[0]["constraint_name"]);
                    $delete_fk_query = $this->db->prepare($delete_fk_sql);
                    $delete_fk_query->execute();
                    $delete_fk_query->closeCursor();
                    
                    // Delete auto-index generated by FK
                    $delete_fk_idx_sql = sprintf("DROP INDEX %s ON %s", $field_fk_assigned[0]["constraint_name"], $this->table);
                    $delete_fk_idx_query = $this->db->prepare($delete_fk_idx_sql);
                    $delete_fk_idx_query->execute();
                    $delete_fk_idx_query->closeCursor();
                }

                // Renombramos el campo viejo
                $rename_sql = sprintf("ALTER TABLE %s CHANGE %s %s %s NULL;", $this->table, $field_old_name, $field_new_name, $field_db[0]["Type"]);

                $rename_query = $this->db->prepare($rename_sql);
                $rename_query->execute();
                $rename_query->closeCursor();

                // Creamos el campo nuevo
                $this->CreateColumn($field_name, $field_properties);

                // Para cada uno de los datos de la columna vieja, hay que migrarlos a la nueva
                $moving_values_sql = sprintf("SELECT id, %s FROM %s ORDER BY id ASC", $field_new_name, $this->table);
                $moving_values_query = $this->db->prepare($moving_values_sql);
                $moving_values_query->execute();

                // Obtenemos los resultados
                $moving_values_results = $moving_values_query->fetchAll(PDO::FETCH_ASSOC);

                // Cerramos el cursor
                $moving_values_query->closeCursor();

                // Migramos los datos de una columna a otra
                foreach ($moving_values_results as $value)
                {
                    $moving_sql = sprintf("UPDATE %s SET %s = %s WHERE id = %s", $this->table, $field_old_name, $field_new_name, $value['id']);
                    $moving_query = $this->db->prepare($moving_sql);
                    $moving_query->execute();
                    $moving_query->closeCursor();
                }
                
                $this->CreateForeignKey($field_name, $field_properties);

                // Finalizamos la transaccion
                $this->db->commit();
            }
            catch (MindException $mex)
            {
                // Volvemos hacia atras
                $this->db->rollback();
                return false;
            }
        }
        
        return true;
    }

    /**
     * This function create a new column in database
     * @param string $field_name
     * @param mixed model $field_properties
     */
    private function CreateColumn($field_name, $field_properties)
    {
        // Comprobamos que no sea un campo funcional
        if (!$field_properties->IsFunction())
        {
            // Creamos las propiedades
            $field_sql = sprintf("ALTER TABLE %s ADD `%s` %s;", $this->table, $field_name, $field_properties->GetProperties());
            //echo $field_sql."<br />";
            $field_query = $this->db->prepare($field_sql);
            $field_query->execute();
            $field_query->closeCursor();
        }
    }
    
    /**
     * This function create a foreign key if the model need it.
     * @param string $field_name
     * @param mixed model $field_properties
     */
    private function CreateForeignKey($field_name, $field_properties)
    {
        // Comprobamos que no sea un campo funcional
        if (!$field_properties->IsFunction())
        {
            // Comprobamos si el tipo de campo que estamos creando es de tipo relacional
            if (in_array($field_properties->GetType(), array ('many2one')))
            {
                $control = new BaseController();
                // Aqui le paso el área en el que está el nombre del modelo..
                $modelRelated = $control->LoadModel($field_properties->GetRelation(), $field_properties->GetAreaName());

                // Comprobamos si existe la tabla con la que se va a relacionar
                if (TableExists($this->db, $modelRelated->table))
                {
                    $fk_data = $field_properties->GetPropertiesRelation($modelRelated->table, $this->table, $field_name);

                    // Creamos las relaciones
                    $field_relation_sql = sprintf("ALTER TABLE %s ADD CONSTRAINT %s;", $this->table, $fk_data);
                    $field_relation_query = $this->db->prepare($field_relation_sql);
                    $field_relation_query->execute();
                    $field_relation_query->closeCursor();
                }
            }
        }
    }
    #endregion

    #region Application security access
    private function CheckAccessRules($uid, $operation)
    {
        if ($uid == ROOT_USER)
        {
            // User root have full access
            return true;
        }

        if (!in_array($operation, array ('read', 'write', 'create', 'unlink')))
        {
            throw new MindException("Invalid access mode");
        }

        // We check if have user rule exists
        $select_user_sql = sprintf("
                SELECT MAX(CASE WHEN perm_%s THEN 1 ELSE 0 END) as Permission
                FROM ir_model_access a
                    JOIN ir_model m ON (m.id = a.model_id)
                WHERE m.name = '%s'
                    AND a.active IS True
                    AND a.user_id = '%s';", $operation, $this->name, $uid);
        $select_user_query = $this->db->prepare($select_user_sql);
        $select_user_query->execute();
        $result_user_query = $select_user_query->fetchAll(PDO::FETCH_ASSOC);
        $select_user_query->closeCursor();

        if ($result_user_query[0]["Permission"] == 0)
        {
            // No tiene privilegios intentamos obtener permisos de anonymous
            $select_anon_sql = sprintf("
                    SELECT MAX(CASE WHEN perm_%s_anon THEN 1 ELSE 0 END) as PermissionAnonymous
                    FROM ir_model_access a
                        JOIN ir_model m ON (m.id = a.model_id)
                    WHERE m.name = '%s'
                        AND a.active IS True
                        AND a.user_id = '%s';", $operation, $this->name, ROOT_USER);
            $select_anon_query = $this->db->prepare($select_anon_sql);
            $select_anon_query->execute();
            $result_anon_query = $select_anon_query->fetchAll(PDO::FETCH_ASSOC);
            $select_anon_query->closeCursor();

            if ($result_anon_query[0]["PermissionAnonymous"] == 0)
            {
                // Si tampoco hay permisos de usuario anonimo,
                // lanzamos una excepción de acceso denegado en función de la operación
                $error_msg = "Access Denied!";

                switch ($operation)
                {
                    case "read":
                        $error_msg = sprintf("%s You cannot read this document. Document type: %s", $error_msg, $this->name);
                        break;
                    case "write":
                        $error_msg = sprintf("%s You cannot modify this document. Document type: %s", $error_msg, $this->name);
                        break;
                    case "create":
                        $error_msg = sprintf("%s You cannot create any document of this type. Document type: %s", $error_msg, $this->name);
                        break;
                    case "unlink":
                        $error_msg = sprintf("%s You cannot delete this document. Document type: %s", $error_msg, $this->name);
                        break;
                    default:
                        $error_msg = sprintf("%s Talk with your Administrator if you think this is an error.");
                }

                throw new MindException($error_msg);
            }
        }

        return true;
    }
    #endregion

    #region Crud Methods
    /**
     * Create a new record in DB from a specified model
     * @param int $uid
     * @param array(objects) $vals
     * @param array(objects) $context
     * @return int Id New Record
     * @throws ORMException if error
     */
    public function Create($uid, $vals = array(), $context = array())
    {
        try
        {
            // Variables
            $new_id = null;

            // Creamos la transaccion
            $this->db->beginTransaction();

            if ($this->CheckAccessRules($uid, "create"))
            {
                // Inicializamos la tabla de creacion
                $sql_fields = "create_uid";
                $sql_values = $uid;

                // Limpieza de caracteres
                $vals = str_replace(array('"'), array("'"), $vals);

                // Por cada una de las columnas definidas...
                foreach ($this->columns as $field_name => $field_properties)
                {
                    $sql_fields = sprintf("%s, %s", $sql_fields, $field_name);
                    
                    // Si existe el valor en vals, lo asignamos, si no, inicializamos nulo
                    if (array_key_exists($field_name, $vals))
                    {
                        $valor = $vals[$field_name];

                        // Prevención de inyección por XSS
                        $valor = htmlspecialchars($valor);

                        // Codificación carácteres
                        $valor = utf8_decode($valor);

                        if ($valor == "true")
                        {
                            $valor = 1;
                        } elseif ($valor == "false") {
                            $valor = 0;
                        }

                        $sql_values = sprintf("%s, \"%s\"", $sql_values, $valor);
                    }
                    else
                    {
                        $sql_values = sprintf("%s, \"%s\"", $sql_values, null);
                    }
                }

                $sql_insert = sprintf("INSERT INTO %s (%s) VALUES (%s);", $this->table, $sql_fields, $sql_values);
                $sql_query = $this->db->prepare($sql_insert);
                $sql_query->execute();
                // Cerramos el cursor
                $sql_query->closeCursor();

                // Guardamos el ID generado
                $new_id = $this->db->lastInsertId();
            }

            // Cerramos la transaccion
            $this->db->commit();

            return $new_id;
        }
        catch (MindException $mex)
        {
            // Exception control
            $this->db->rollback();
            // Tratamiento de excepciones
            throw new ORMException($mex->getMessage());
        }
    }

    /**
     * Modify a record in DB from a specified model
     * @param int $uid
     * @param int $ids
     * @param array(objects) $vals
     * @param array(objects) $context
     * @throws ORMException if error
     */
    public function Write($uid, $ids = array(), $vals = array(), $context = array())
    {
        try
        {
            // Creamos la transaccion
            $this->db->beginTransaction();

            if ($this->CheckAccessRules($uid, "write")) {
                // Inicializamos los valores a escribir
                $sql_update = sprintf("write_uid = \"%s\"", $uid);

                // Limpieza de caracteres
                $vals = str_replace(array('"'), array("'"), $vals);

                // Por cada una de las columnas definidas...
                foreach ($this->columns as $field_name => $field_properties)
                {
                    // Si existe el valor en vals, lo modificamos, si no, lo dejamos como esta
                    if (array_key_exists($field_name, $vals))
                    {
                        $valor = $vals[$field_name];

                        // Prevención de inyección por XSS
                        $valor = htmlspecialchars($valor);

                        // Codificación carácteres
                        $valor = utf8_decode($valor);

                        if ($valor == "true") {
                            $valor = 1;
                        } elseif ($valor == "false") {
                            $valor = 0;
                        }

                        $sql_update = sprintf("%s, %s = \"%s\"", $sql_update, $field_name, $valor);
                    }
                }

                // Preparamos la consulta
                $sql_update = sprintf("UPDATE %s SET %s WHERE id IN (%s);", $this->table, $sql_update, implode(', ', $ids));
                $sql_query = $this->db->prepare($sql_update);
                // Ejecutamos la consulta
                $sql_query->execute();
                // Cerramos el cursor
                $sql_query->closeCursor();
            }

            // Cerramos la transaccion
            $this->db->commit();

            return true;

        }
        catch (MindException $mex)
        {
            // Exception control
            $this->db->rollback();
            // Tratamiento de excepciones
            throw new ORMException($mex->getMessage());
        }
    }

    /**
     * Delete a record in DB from a specified model
     * @param int $uid
     * @param array(int) $ids
     * @param array(objects) $context
     * @throws ORMException if error
     */
    public function Unlink($uid, $ids = array(), $context = array())
    {
        try
        {
            // Creamos la transaccion
            $this->db->beginTransaction();

            if ($this->CheckAccessRules($uid, "unlink"))
            {
                // Preparamos la consulta
                $sql_delete = sprintf("DELETE FROM %s WHERE id IN (%s);", $this->table, implode(', ', $ids));
                $sql_query = $this->db->prepare($sql_delete);
                // Ejecutamos la consulta
                $sql_query->execute();
                // Cerramos el cursor
                $sql_query->closeCursor();
            }

            // Cerramos la transaccion
            $this->db->commit();

            return true;
        }
        catch (MindException $mex)
        {
            // Exception control
            $this->db->rollback();
            // Tratamiento de excepciones
            throw new ORMException($mex->getMessage());
        }
    }

    /**
     * Search IDS from a domain array
     * @param int $uid
     * @param array(string) $domain
     * @param string column $order_by
     * @param int $limitResults
     * @param int $limitStart
     * @return array(int) Ids of results
     * @throws ORMException if error
     */
    public function Search($uid, $domain = array(), $order_by = "", $limitResults = "", $limitStart = "")
    {
        try
        {
            if ($this->CheckAccessRules($uid, "read"))
            {
                // Convertimos las condiciones de busqueda del dominio a lenguaje SQL
                $sql_conditions = buildDomainSQL($domain, $this->columns);

                // Preparamos la consulta
                $sql_statement = sprintf("SELECT id FROM %s%s", $this->table, $sql_conditions);
                // Ordenacion
                if (strlen($order_by)> 0)
                {
                    $sql_statement = sprintf("%s ORDER BY %s", $sql_statement, $order_by);
                }
                // Limitacion de registros, empezando por el registro x
                if (strlen($limitStart) > 0 && strlen($limitResults) > 0)
                {
                    $sql_statement = sprintf("%s LIMIT %s, %s", $sql_statement, $limitStart, $limitResults);
                }
                else if (strlen($limitResults) > 0)
                {
                    $sql_statement = sprintf("%s LIMIT %s", $sql_statement, $limitResults);
                }

                $sql_query = $this->db->prepare($sql_statement);
                // Ejecutamos la consulta
                $sql_query->execute();
                // Obtenemos los resultados
                $resultsQuery = $sql_query->fetchAll(PDO::FETCH_ASSOC);
                // Cerramos el cursor
                $sql_query->closeCursor();

                // Creamos el array a devolver
                $resultsIds = array();

                foreach ($resultsQuery as $value)
                {
                    array_push($resultsIds, $value["id"]);
                }

                return $resultsIds;
            }

            return array();

        }
        catch (MindException $mex)
        {
            // Exception control
            throw new ORMException($mex->getMessage());
        }
    }

    /**
     * Recover array of data from one ids
     * @param int $uid
     * @param array(int) $ids
     * @param string column $order_by
     * @param int $limitResults
     * @param int $limitStart
     * @return array of records
     * @throws ORMException if error
     */
    public function Browse($uid, $ids = array(), $order_by = "", $limitResults = "", $limitStart = "")
    {
        try
        {
            if ($this->CheckAccessRules($uid, "read"))
            {
                // Preparamos la consulta
                $sql_statement = sprintf("SELECT * FROM %s WHERE id IN (%s)", $this->table, implode(', ', $ids));
                
                // Ordenacion
                if (strlen($order_by)> 0)
                {
                    $sql_statement = sprintf("%s ORDER BY %s", $sql_statement, $order_by);
                }
                // Limitacion de registros, empezando por el registro x
                if (strlen($limitStart) > 0 && strlen($limitResults) > 0)
                {
                    $sql_statement = sprintf("%s LIMIT %s, %s", $sql_statement, $limitStart, $limitResults);
                }
                else if (strlen($limitResults) > 0)
                {
                    $sql_statement = sprintf("%s LIMIT %s", $sql_statement, $limitResults);
                }

                $sql_query = $this->db->prepare($sql_statement);
                // Ejecutamos la consulta
                $sql_query->execute();
                // Obtenemos los resultados
                $results = $sql_query->fetchAll(PDO::FETCH_ASSOC);
                // Cerramos el cursor
                $sql_query->closeCursor();

                // Devolvemos un conjunto de modelos
                return $results;
            }

            return array();
        }
        catch (MindException $mex)
        {
            // Exception control
            throw new ORMException($mex->getMessage());
        }
    }

    /**
     * Recover current objects from one ids
     * @param int $uid
     * @param array(int) $ids
     * @param array(objects) $context
     * @return array of objects with data and functions
     * @throws ORMException if error
     */
    public function BrowseRecord($uid, $ids = array(), $order_by = "", $context = array())
    {
        try
        {
            if ($this->CheckAccessRules($uid, "read"))
            {
                // Preparamos la consulta
                if (strlen($order_by)> 0)
                {
                    $sql_statement = sprintf("SELECT * FROM %s WHERE id IN (%s) ORDER BY %s;", $this->table, implode(', ', $ids), $order_by);
                }
                else
                {
                    $sql_statement = sprintf("SELECT * FROM %s WHERE id IN (%s);", $this->table, implode(', ', $ids));
                }

                $sql_query = $this->db->prepare($sql_statement);
                // Ejecutamos la consulta
                $sql_query->execute();
                // Obtenemos los resultados
                $results = $sql_query->fetchAll(PDO::FETCH_ASSOC);
                // Cerramos el cursor
                $sql_query->closeCursor();

                // Creamos el objeto a devolver
                $dataReturned = array();

                // Agregamos la extension Model para cargar la clase
                $model_name = $this->name."Model";

                // Por cada uno de los resultados
                foreach ($results as $raw_result)
                {
                    // Creamos un modelo nuevo
                    $current_model = new $model_name($this->db);
                    $current_model->data = $raw_result;
                    $dataReturned[] = $current_model;
                }

                // Devolvemos un conjunto de modelos
                return $dataReturned;
            }

            return array();
        }
        catch (MindException $mex)
        {
            // Exception control
            throw new ORMException($mex->getMessage());
        }
    }
    
    /**
     * Count records with a domain array
     * @param int $uid
     * @param array(string) $domain
     * @return int Number of results
     * @throws ORMException if error
     */
    public function Count($uid, $domain = array())
    {
        try
        {
            if ($this->CheckAccessRules($uid, "read"))
            {
                // Convertimos las condiciones de busqueda del dominio a lenguaje SQL
                $sql_conditions = buildDomainSQL($domain, $this->columns);
                // Preparamos la consulta
                $sql_statement = sprintf("SELECT count(id) as RecordNumber FROM %s%s", $this->table, $sql_conditions);
                $sql_query = $this->db->prepare($sql_statement);
                // Ejecutamos la consulta
                $sql_query->execute();
                // Obtenemos los resultados
                $resultsQuery = $sql_query->fetchAll(PDO::FETCH_ASSOC);
                // Cerramos el cursor
                $sql_query->closeCursor();

                return $resultsQuery[0]["RecordNumber"];
            }
            
            return 0;
        }
        catch (MindException $mex)
        {
            // Exception control
            throw new ORMException($mex->getMessage());
        }
    }
    #endregion
}
?>