<?php

/*
 * A class to represent a connection to the database - includes standard functions
 *
 * This is the replacement for the older database class and this new version uses prepared statements where needed.
 */

//Constants for the database connection details
const ADDRESS = 'localhost';
const USER = 'root';
const PASS = '';
const NAME = 'sgunner-me';

class Database {
    private $conn;

    //Constructor - initialises database connection
    public function __construct() {
        $this->conn = new mysqli(ADDRESS, USER, PASS, NAME);
    }

    //Should be called when the database connection needs to be reformed
    public function onPageArrive() {
        $this->conn = new mysqli(ADDRESS, USER, PASS, NAME);
    }

    //This carries out an insert function into a specific table and returns the ID of the new record.
    //This uses prepared statements to ensure the security of the operation.
    public function insert($tableName, $data) {
        //Begin the different parts of the SQL statement
        $variables = Array();
        $finalVars = null;
        $sqlStarter = "INSERT INTO $tableName ";
        $fieldNamesSQL = "(";
        $dataSQL = "(";
        $types = "";

        //Loop through the data and the keys to make the rest of the SQL statement [INSERT INTO table (fields) VALUES (data)]
        foreach ($data as $k => $v) {
            $fieldNamesSQL .= $k . ",";
            $dataSQL .= "?,"; //This will be set to a value later
            array_push($variables, $v);
            $types .= "s"; //Announce that it is in string form
        }

        $finalVars[] = & $types; //Transform to array of references

        for ($i = 0; $i < count($variables); $i++) { //Cycle through the variables and add them to their final variable array
            $finalVars[] = & $variables[$i];
        }

        $dataSQLLen = strlen($dataSQL);
        $dataSQL = substr($dataSQL, 0, $dataSQLLen - 1); //Chop off the last comma to make it syntactically correct
        $dataSQL .= ")"; //Add the last bracket

        $fieldNamesSQLLen = strlen($fieldNamesSQL);
        $fieldNamesSQL = substr($fieldNamesSQL, 0, $fieldNamesSQLLen - 1); //Chop off the last comma to make it syntactically correct

        //Merge the strings together to get the final SQL statement
        $finalSQL = $sqlStarter . $fieldNamesSQL . ") VALUES " . $dataSQL;

        $statement = $this->conn->prepare($finalSQL); //Create the statement object

        call_user_func_array(Array($statement, "bind_param"), $finalVars); //This calls the bind_param function with all of the parameters that needs to be set - we do not know how many there will be

        $statement->execute(); //Execute the command
        return $statement->insert_id; //Return the ID of the new record
    }

    //This carries out an update function on a specific set of data
    public function newUpdate($tableName, $values, $where) {
        //Form the beginning of the SQL statement
        $sqlStarter = "UPDATE $tableName SET ";
        $types = "";

        //Iteratively add the keys and values to make the SQL statement
        foreach ($values as $k => $v) {
            $sqlStarter .= $k . " = ?,";
            $types .= "s";
        }

        $finalVars[] = & $types; //Convert the types into an array of strings
        $values = array_values($values); //Get only the values from the associative array

        for ($i = 0; $i < count($values); $i++) { //Loop though the variables and add them to the finalValues array
            $finalVars[] = & $values[$i];
        }

        $sqlStarterLen = strlen($sqlStarter);
        $sqlStarter = substr($sqlStarter, 0, $sqlStarterLen - 1); //Chop of the last comma to make it syntactically correct
        $sqlStarter .= " WHERE $where"; //Add the custom WHERE clause

        $statement = $this->conn->prepare($sqlStarter); //Create the new statement object

        call_user_func_array(Array($statement, "bind_param"), $finalVars);

        $statement->execute(); //Execute the command
        return true;
    }

    //Returns all records where a specified field has a specified value
    public function select($tablename, $fieldname, $fieldValue) {
        $sql = "SELECT * FROM $tablename WHERE $fieldname = '$fieldValue'";
        return $this->conn->query($sql);
    }

    //Returns all records where a custom 'WHERE' clause is satisfied.
    public function customSelect($tableName, $where) {
        $sql = "SELECT * FROM $tableName WHERE $where";
        return $this->conn->query($sql);
    }

    //Returns all records within a table
    public function selectAll($tableName) {
        $sql = "SELECT * FROM $tableName";
        return $this->conn->query($sql);
    }

    //A more advanced version of the select function from before
    //This is used in the post list and returns a specified number of posts, ordered by a specified field
    public function selectAllLimitOrder($tableName, $orderBy, $limit = 100) {
        $sql = "SELECT * FROM $tableName ORDER BY $orderBy DESC LIMIT $limit"; //Needs to be in descending order because the time will increase as it becomes more recent.
        return $this->conn->query($sql);
    }

    //This allows for the system to carry out a custom SELECT WHERE LIKE statement
    public function selectLike($tableName, $fields, $values) {
        $sql = "SELECT * FROM $tableName WHERE ";
        $where = "";
        $types = "";

        for ($i = 0; $i < count($fields); $i++) { //Loop through the fields and form the WHERE clause
            if ($i > 0) {
                $where .= " OR ";
            }

            $where .= "$fields[$i] LIKE ?";
            $types .= "s"; //Announce that it is in string form

            $values[$i] = "%" . $values[$i] . "%";
        }

        $finalVars = Array();

        $finalVars[] = & $types;

        for ($i = 0; $i < count($values); $i++) {
            $finalVars[] = & $values[$i];
        }

        $sql .= $where;

        $statement = $this->conn->prepare($sql); //Create the statement object

        call_user_func_array(Array($statement, "bind_param"), $finalVars);

        $statement->execute(); //Execute the command
        $result =  $statement->get_result();

        return $result;
    }

    //Allows for a custom SQL query to be run - susceptible to SQL injection!
    public function customQuery($sql) {
        $result = $this->conn->query($sql);
        return $result;
    }

    //Returns all records where a certain post has been voted on
    public function getPostVote($postID) {
        $sql = "SELECT * FROM votes WHERE postid = $postID AND contentType = '1'"; //Form the SQL statement
        return $this->conn->query($sql);
    }

    //Updates a record where the id field is a certain value
    public function editRecordByID($table, $id, $field, $data) {
        $where = "id = '$id'";
        return $this->newUpdate($table, Array($field => $data), $where); //Execute the command and return the result (Boolean)
    }

    //Deletes all records where a certain condition is met
    public function delete($table, $field, $data) {
        $sql = "DELETE FROM $table WHERE $field = '$data'"; //Form the command
        $this->conn->query($sql); //Execute the command
    }

    //Deletes all records where a custom 'WHERE' clause is satisfied
    public function customDelete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where"; //Form the command
        $this->conn->query($sql);
    }

    //Updates many records at once
    public function massUpdate($table, $field, $value, $data) {
        $where = "$field = '$value'"; //Form the WHERE clause
        return $this->newUpdate($table, $data, $where); //Execute the command and return the result
    }

    //Standard update function to ensure backwards-compatibility with previous implementation of class
    public function update($table, $field, $value, $dataName, $data) {
        $where = "$field = '$value'";
        $dataToUse = Array(
            $dataName => $data
        );

        return $this->newUpdate($table, $dataToUse, $where); //Execute the command
    }

    //This allows for a custom 'WHERE' clause to be used
    public function customUpdate($table, $where, $dataName, $data) {
        $dataToUse = Array(
            $dataName => $data
        );

        return $this->newUpdate($table ,$dataToUse, $where); //Exeute the command and return the result
    }

    //This returns the number of records that meet a specified condition
    public function getOccurences($table, $fieldName, $fieldValue) {
        $sql = "SELECT COUNT(*) FROM $table WHERE $fieldName = '$fieldValue'";
        $result =  $this->conn->query($sql); //Return the row count
        $num_rows = $result->fetch_row()[0];
        return $num_rows;
    }
}