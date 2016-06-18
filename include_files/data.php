<?php
	class data{
		private $size;
		private $noOfFields;
		private $data_set = array();
		private $attributes = array();
		private $class_attribute = array();
		private $dataFileFields = array();
				
		public function __construct(){
			$this->size = 0;
		}
		
		public function getDataSet(){
			return $this->data_set;
		}
		
		public function getDataFields(){
			return $this->dataFileFields;
		}
		
		public function getArraySize(){
			return $this->size;
		}
		
		public function getAttributesArray(){
			return $this->attributes;
		}
		
		public function getClassArray(){
			return $this->class_attribute;
		}
		
		//function reads data from data file
		public function readDataFile($file_name){
			if($fh = fopen($file_name,"r")){ //if file opened	
			  
				//create string to hold contents of entire file
				$file_str = "";
			
				//read first line, remove special characters
				$str = fgets($fh);
				$file_str .= $str;
				$str = str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $str);
				
				$fieldNames = explode(",",$str);
				$noOfFields = sizeof($fieldNames);
	
				//read second line, don't do anything, blank
				$str = fgets($fh);
				$file_str .= $str;
				
				$noEntries = 0;
				//read attributes line by line
				while (!feof($fh)){ 
					//read line, remove special characters
					$str = fgets($fh);
					$file_str .= $str;
					$str = str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $str);
					
					//explode string into temp array
					$temp_array = explode(",",$str );
					
					for($i=0; $i<$noOfFields; $i++){
						$this->data_set[$noEntries][$fieldNames[$i]] = $temp_array[$i];
					}
					$noEntries++;
				} 
				fclose($fh);
				$this->size = $noEntries;
				$this->dataFileFields = $fieldNames;
				
				//return string with file contents
				return $file_str;
				
			}else{
				return "Counld not open file <br />";
			}
		}
		
		//function reads data from names file
		//data is stored in two arrays, an attributes array and a 
		//a class array
		public function readNamesFile($file_name){
			if($fh = fopen($file_name,"r")){ //if file opened	
			
				//read first line. first line contains class values
				$str = fgets($fh);
				$str = str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $str);
	
				//split string. Class Name and class attribute values
				list($class_attribute, $values) = explode(":", $str); 
				$this->class_attribute [$class_attribute] = explode(",", $values);

				//read blank line
				$str = fgets($fh);
			
				//read attributes line by line
				while (!feof($fh)){ 
					//read line from file
					$str = fgets($fh);
					$str = str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $str);
					
					list($attribute, $list_of_att_values) = explode(":", $str);
					$this->attributes[$attribute] = explode(",",$list_of_att_values);
				} 
				fclose($fh);
			}		
		}
		
		//function will return distinct values
		//Input: Array dataset, fieldName is a string
		//Output: returns in array with distinct values for the 
		//		  	given data and fieldName
		public function getDistinct(array &$dataSet,$fieldName){
			//array will hold distinct values for the given field
			$distinct_values = array();
							
			foreach($dataSet as $row){
				//retrieve row item, check if item exists in array
				if(!in_array($row[$fieldName],$distinct_values)){
					$distinct_values [] = $row[$fieldName]; //add item
				}
			}						
			return $distinct_values;
		}
				
		public function countDistinctClasses(array &$dataSet,$fieldName){
			//array will hold distinct values for the given field
			$distinct_values = array();
			
			//get distinct values for given field
			$distinct_values = $this->getDistinct($dataSet,$fieldName);	

			//return number of distinct values
			return sizeof($distinct_values);
		}
		
		public function countNumberInArray(array &$dataSet,$fieldName, $value){
			$count = 0;
			foreach($dataSet as $row){
				if($row[$fieldName] == $value)
					$count++;
			}
			return $count;
		}
		
		/*
			Function: getSubSet
			Input: 	$dataArray = data array to search
					$fieldName = attribute name
					$value = attribute value looking for
			Output: function returns a subset array which contains data
					that matches the $fieldname field and have value $value
		*/
		public function getSubSet(array &$dataSet, $fieldName, $value){
			//sub set will hold matching values 
			$subSet = array();
			
			foreach($dataSet as $dataRow){
				if($dataRow[$fieldName] == $value)
					$subSet[] = $dataRow;
			}			
			return $subSet;
		}
		
		public function convertDataArrayToString(array &$dataSet){
			$data_str = "";
			foreach($dataSet as $row=>$rowValues){
				$tempRow = "";
				foreach($rowValues as $attribute=>$value){
					$tempRow .= $value.",";
				}
				$tempRow = substr($tempRow, 0, strlen($tempRow)-1);
				$data_str .= $tempRow."\n";
			}
			return $data_str;
		}
		
		public function getDataFileFieldsInStrForm(){
			$fields = "";
			foreach($this->dataFileFields as $field)
			{
				$fields .= $field.",";
			}
			//remove trailing comma
			$fields = substr($fields, 0, strlen($fields)-1);
			$fields= str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $fields);
			return $fields;
		}		
	}
?>