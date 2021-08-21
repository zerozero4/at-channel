<?php

    class Database {
        protected $Connection = null;
        protected $Query = null;
        private $QueryClosed = true;

        public function __construct($HostName, $Username, $Password, $DataBase) {
            $this->Connection = new mysqli($HostName, $Username, $Password, $DataBase);
            if($this->Connection->connect_error)
                exit('Failed to initialize Database : ' . $this->Connection->connect_error);
        }


        public function Query($Statement) {
            /**
             * If the previous query is not closed, close the query.
             */
            if(!$this->QueryClosed)
                $this->Query->close();
            
            /**
             * Try to prepare the given query statement.
             */
            if($this->Query = $this->Connection->prepare($Statement)) {
                /**
                 * If arguments where provided for the query statement
                 */
                if(func_num_args() > 1) {
                    $Arguments = func_get_args();
                    $Arguments = array_slice($Arguments, 1);
                    $Types = "";
                    $ArgumentReference = array();

                    /**
                     * For each argument in the associative array,
                     * 
                     * If the element is an array itself, iterate it
                     * else add type info to arrayReference.
                     */
                    foreach($Arguments as $Key => &$Value) {
                        if(is_array($Arguments[$Key])) {
                            foreach($Arguments[$Key] as $Index => &$Element) {
                                $Types .= $this->GetType($Arguments[$Key][$Index]);
                                $ArgumentReference[] = &$Element;
                            }
                        } else {
                            $Types .= $this->GetType($Arguments[$Key]);
                            $ArgumentReference[] = &$Value;
                        }
                    }

                    array_unshift($ArgumentReference, $Types);
                    call_user_func_array( array($this->Query, 'bind_param'), $ArgumentReference );
                }

                /**
                 * Execute the query and capture error if any.
                 */
                $this->Query->execute();
                $this->Query->store_result();

                if($this->Query->error)
                    exit("Unable to process MySQL Query (Response From mysqli : " . $this->Query->error . ").");
                
                $this->QueryClosed = false;
            } else {
                exit("Unable to prepare MySQL Query statement [ $Statement ] (Response from mysqli : " . $this->Connection->error . ").");
            }

            return $this;
        }

        public function NumRows()      {    return $this->Query->num_rows;         }
        public function AffectedRows() {    return $this->Query->affected_rows;    }

        public function AsArray() {
            $Params = array();
            $Row = array();

            /**
             * Acquire the type metadata which is required for bind_param.
             */
            $Meta = $this->Query->result_metadata();

            /**
             * Store the types (a.k.a a value coresponding to the type).
             */
            while ($Field = $Meta->fetch_field()) 
                $Params[] = &$Row[$Field->name];
            
            /**
             * Call the bind_param function.
             */
            call_user_func_array(array($this->Query, 'bind_result'), $Params);

            $Result = array();

            /**
             * Insert into the resultant array roe by row.
             */
            while ($this->Query->fetch()) {
                $R = array();
                foreach ($Row as $Key => $Value)
                    $R[$Key] = $Value;

                $Result[] = $R;
            }
            return $Result;
        }
    

        private function GetType($Variable) {
            if(is_string($Variable))    return "s";
            if(is_float($Variable))     return "d";
            if(is_int($Variable))       return "i";
            return "b";
        }
    };
?>