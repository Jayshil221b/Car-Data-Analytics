<?php	class process{		private $fileName;		private $tableName;		private $connObj;		private $class_attribute;		private $attValues = array();		private $class_values = array();		private $table_fields = array();				public function __construct($fileName,$tableName,$connObj){			$this->fileName = $fileName;			$this->tableName = $tableName;			$this->connObj = $connObj;		}				public function setFileName($fileName){			$this->fileName = $fileName;		}		public function getFileName(){			return $fileName;		}				public function setTableName($tableName){			$this->tableName = $tableName;		}		public function getTableName(){			return $tableName;		}		  		public function setConnObj($connObj){			$this->connObj = $connObj;		}		public function getConnObj(){			return $connObj;		}				public function getAttValues(){			return $this->attValues;		}		public function getClass_values(){			return $this->class_values;		}		public function getFileString(){			return $this->file_content_str;		}						public function getTableFields(){			return $this->table_fields;		}				/*			function opens file and saves file contents into array		*/		public function processNameFile(){			//print name files contents			$file_name = $this->fileName;					//variable will hold the content of the entire file 			$this->file_content_str = "";					 if($fh = fopen($file_name,"r")){				//read first line. first line contains class values				$str = fgets($fh);								//append line to variable				$this->file_content_str .= $str."<br />";								//split string. Class Name and class attribute values				list($this->class_attribute, $values) = split(":", $str);				$temp_Array_Spaces  = explode(",", $values);								//remove spaces				foreach($temp_Array_Spaces as $temp){					$temp_Array_No_Spaces[] = removeSpaces($temp);				}															$this->class_values[$this->class_attribute] = $temp_Array_No_Spaces;				unset($temp_Array_Spaces);					unset($temp_Array_No_Spaces);									//read blank line				$str = fgets($fh);								//append blank line to variable				$this->file_content_str .= $str."<br />";								//read attributes line by line				while (!feof($fh)){ 					//read line from file					$str = fgets($fh);										//append line to variable					$this->file_content_str .= $str."<br />";					list($attribute, $list_of_att_values) = split(":", $str);							//remove spaces from attribute values					$tempArraySpaces = explode(",", $list_of_att_values);					foreach($tempArraySpaces as $values){						$tempArrayNoSpaces[] = removeSpaces($values);					}										$this->attValues[$attribute] = $tempArrayNoSpaces;					unset($tempArrayNoSpaces);					unset($tempArraySpaces);				} 				fclose($fh);			}				}				public function processSelectedTable(){					$counter = 0;			$query = "SELECT column_name									FROM information_schema.columns									WHERE table_name = '".$this->tableName."'									ORDER BY ordinal_position";						//get table attribute names			$this->connObj->query($query);						$tempArray = array();			//save attribute names in array			while($row=$this->connObj->fetchArray()){				//don't include the first attribute. the first attribute is an auto				//increment key (not relevent to the data)				if($counter != 0){					$fieldName = $row[0];										$temp_query = "SELECT DISTINCT $fieldName FROM $this->tableName";					$tempArray[$fieldName] = $temp_query;				}				$counter++;							}							foreach($tempArray as $key => $tempQuery){				$this->connObj->query($tempQuery);				while($row=$this->connObj->fetchArray()){					 $this->table_fields[$key][] = $row[$key];				}			}		}					//function used to compare tableArray to fileArray		function compare(){			//declare array to hold erros			$error_Array = array();						$File_class_attribute = $this->class_attribute;						foreach($this->table_fields as $Attribute => $Attribute_Values){				if($Attribute == $File_class_attribute){					foreach($Attribute_Values as $value){						if(in_array($value,$this->class_values[$Attribute])){							continue;						}else{							$error_Array [] = "Class Value: $value has not been defined in 												names file. Check spelling.";						}						}				}elseif(array_key_exists($Attribute,$this->attValues)){						//now we need to check if the values defined in the table data are 					//defined in the names file					foreach($Attribute_Values as $value ){						if(in_array($value,$this->attValues[$Attribute])){							continue;						}else{							$error_Array [] = "Value: $value is not defined for attribute 												$Attribute in the names file!";						}									}				}else{	//table attribute does not exists in file, save error					$error_Array [] = "Attribute: <strong> $Attribute </strong> 										exists in table definition but is not 										defined in the names file. If class attribute,										make sure you spell it correctly.";				}			}			return $error_Array;		}				/*			function prints processed input		*/		public function printFileProcessedInput(){			print "<strong>Processed Input from file:</strong> <br />";			print "Class attribute: ".$this->class_attribute."<br />";			print "Class values: ";			foreach($this->class_values as $value){				foreach($value as $item){					print "[".$item."]";				}			}			unset($key);			unset($value);			unset($item);			print "<br />";						foreach($this->attValues as $key=>$attribute){				print $key.":";				foreach($attribute as $value){					print "[".$value."]";				}						print "<br />";			}		}	}?>