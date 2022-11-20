<?php
/**
 * Check if a table exists in the current database.
 *
 * @param PDO $pdo
 *          PDO instance connected to a database.
 * @param string $table
 *          Table to search for.
 * @return bool TRUE if table exists, FALSE if no table found.
 */
function TableExists($db, $table)
{
    // Try a select statement against the table
    // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
    try
    {
        $result = $db->prepare(sprintf("SHOW TABLES LIKE '%s'", $table));
        $result->execute();
    }
    catch (Exception $e)
    {
        // We got an exception == table not found
        return false;
    }

    // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
    // Comprobamos si el resultado es mayor que cero
    if ($result->rowCount() > 0)
    {
        // El campo existe
        return true;
    }

    return false;
}

function FieldExists($db, $table, $field)
{
    try 
    {
        // Ejecutamos la consulta
        $result = $db->prepare(sprintf("SHOW COLUMNS FROM %s WHERE Field = '%s'", $table, $field));
        $result->execute();
        // Comprobamos si el resultado es mayor que cero
        if ($result->rowCount() > 0)
        {
            // El campo existe
            return true;
        }
    }
    catch (Exception $e)
    {
        // Obtenemos una excepcion, el campo no existe
        return false;
    }

    // El campo no existe
    return false;
}

function GetInfoField($db, $table, $field)
{
    try
    {
        // Ejecutamos la consulta
        $result = $db->prepare(sprintf("SHOW COLUMNS FROM %s WHERE Field = '%s'", $table, $field));
        $result->execute();
        // Comprobamos si el resultado es mayor que cero
        if ($result->rowCount() > 0)
        {
            // El campo existe
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    catch (Exception $e)
    {
        // Obtenemos una excepcion, el campo no existe
        return false;
    }

    // El campo no existe
    return false;
}

function GetConstraintFK($db, $table, $column_name)
{
    try
    {
        // Preparamos la consulta
        $result_sql = sprintf("
            SELECT `constraint_name`
            FROM `information_schema`.`KEY_COLUMN_USAGE`
            WHERE `constraint_schema` = SCHEMA()
                AND `table_name` = '%s'
                AND `column_name` = '%s'
                AND `referenced_column_name` IS NOT NULL;", $table, $column_name);
        // Ejecutamos la consulta
        $result = $db->prepare($result_sql);
        $result->execute();
        // Comprobamos si el resultado es mayor que cero
        if ($result->rowCount() > 0)
        {
            // El campo existe
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    catch (Exception $e)
    {
        // Obtenemos una excepcion, el campo no existe
        return false;
    }

    // El campo no existe
    return false;
}

/**
 * PHP Domain example:
 * $domain = array
 * (
 * array("field_name", "=", value),
 * "|",
 * array("field_name", "in", array(value1, value2)),
 * );
 **/
function BuildDomainSQL($domain = array(), $columns = array())
{
    // Inicializamos
    $fix_columns = array(
        "id" => "id",
        "create_uid" => "create_uid",
        "create_date" => "create_date",
        "write_uid" => "write_uid",
        "write_date" => "write_date",
    );

    $columns = array_change_key_case(array_merge($columns, $fix_columns), CASE_LOWER);

    $sql_conditions = "";
    $sql_operators = array(
            "=" => "=",
            "!=" => "!=",
            "<" => "<",
            "<=" => "<=",
            ">" => ">",
            ">=" => ">=",
            "<>" => "<>",
            "in" => "IN",
            "not in" => "NOT IN",
            "like" => "LIKE",
            "not like" => "NOT LIKE",
            "ilike" => "LIKE",
            "not ilike" => "NOT LIKE",
            "starts with" => "LIKE",
            "not starts with" => "NOT LIKE",
            "is" => "IS",
    );
    $is_or = false;

    // Por cada una de las columnas definidas...
    foreach ($domain as $fields_domain)
    {
        // Comprobamos si no es un array, sera una condicion de O.
        if (!is_array($fields_domain))
        {
            // Comprobamos si ya tenemos una condicion previa
            if (strlen($sql_conditions) > 0)
            {
                $sql_conditions .= " OR ";
                $is_or = true;
            }
            
            continue;
        }

        // Es un array
        // Comprobamos si existe el campo
        $field_name = trim(strtolower($fields_domain[0]));
        if (array_key_exists($field_name, $columns))
        {
            // Comprobamos si el operador es válido
            $operador = trim(strtolower($fields_domain[1]));
            if (array_key_exists($operador, $sql_operators))
            {
                // Comprobamos si ya tenemos una condicion previa
                if (strlen($sql_conditions) == 0) {
                    $sql_conditions = " WHERE ";
                } elseif ($is_or == false) {
                    $sql_conditions .= " AND ";
                } else {
                    $is_or = false;
                }

                $values = $fields_domain[2];
                // Creamos la consulta dependiendo del operador
                if (in_array($operador, array("=", "!=", "<=", "<", ">", ">=", "<>", "is"))) {
                    $sql_conditions = sprintf("%s %s %s \"%s\"", $sql_conditions, $field_name, $sql_operators[$operador], $values);
                } elseif (in_array($operador, array("in", "not in"))) {
                    $sql_conditions = sprintf("%s %s %s (%s)", $sql_conditions, $field_name, $sql_operators[$operador], implode(', ', $values));
                } elseif (in_array($operador, array("like", "not like"))) {
                    $sql_conditions = sprintf("%s %s %s \"%%%s%%\"", $sql_conditions, $field_name, $sql_operators[$operador], $values);
                } elseif (in_array($operador, array("ilike", "not ilike"))) {
                    $sql_conditions = sprintf("%s LOWER(%s) %s LOWER(\"%%%s%%\")", $sql_conditions, $field_name, $sql_operators[$operador], $values);
                } elseif (in_array($operador, array("starts with", "not starts with"))) {
                    $sql_conditions = sprintf("%s LOWER(%s) %s LOWER(\"%s%%\")", $sql_conditions, $field_name, $sql_operators[$operador], $values);
                }

            }
        }
    }

    return $sql_conditions;
}
?>