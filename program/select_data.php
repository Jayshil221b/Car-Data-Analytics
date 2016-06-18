<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>C4.5 Algorithm </title>
		<!-- Link to styles sheet -->
		<link rel="stylesheet" type="text/css" href="../css/main.css" />
	</head>
	
	<body>
		<div class="container">
			<div class="header">
				<div id="header-left">
					<img src="../_images/Tuffy_computer.jpg" alt="Fullerton Logo" />
				</div>
				<div id="header-right">
					<h1>C4.5 Algorithm </h1>
				</div>
				<!-- end .header -->
			</div>
			<div style="clear: both;" ></div>
			
			<div class="content">
			<hr />
<?php
	//include mysql class
	include "../include_files/process_data.php";
	include "../include_files/data.php";
	
	//Declare debug variable. when set to true, debugging information is displayed
	$debug = true;
	$error_Array = array();
	//dropdown table name
	$ddDataSet = 'dataSet_dropdown';
	
	//dropdown list of file names
	$ddNameFiles = 'nameFiles_dropdown';
	//$ddNameFiles = 'car.names';
	
	//ask user to select dataset
	if(!isset($_POST['btn_Submit'])){
	
		
		//print page header
		print "<h1>Choose Data Set & Parameters</h1>";
		print "<p><a href='../index.html'><- Home</a></p>";
		
		/* retrieve list of data sets*/
		//query database for table names
		$dataSetDirectory = opendir("../data");
		while($entryName = readdir($dataSetDirectory)) {
			$dirArray[] = $entryName;
		}
		sort($dirArray); //sort array
		closedir($dataSetDirectory); 	// close directory	
		foreach($dirArray as $item){ //remove hidden files
			if(substr($item,0,1) != "." )
				$dataSet_Files[$item] = $item;
		}
		unset($dirArray);
		
		/* retrieve list of name files */
		// open name file directory directory 
		$myDirectory = opendir("../namesFiles");

		// get each entry
		while($entryName = readdir($myDirectory)) {
			$dirArray[] = $entryName;
		}
		sort($dirArray); //sort array
		closedir($myDirectory); // close directory
		foreach($dirArray as $item){ //remove hidden files
			if(substr($item,0,1) != "." )
				$nameFileNames[$item] = $item;
		}
		
?>
		<form name="myForm" method="POST" action="#">
			<p>Select Data Set: <br />
<?php 
			//$options = $etables;
			$selected = "";
			echo dropdown( $ddDataSet, $dataSet_Files, $selected );
?>
			</p>
			<p>
            <!--Select names files: <br />-->
<?php
			$selected2 = "car.names";
			//$ddNameFiles = "car.names";
			echo dropdown( $ddNameFiles, $nameFileNames, $selected2 );
?>
			</p>
			<p>

				Enter(%) value for training. (Default set to 100): <br />
				<input type="text" name="training_Percent" value="100" />%<br />
			</p>			<p>
				<input type="checkbox" name="cbox_Random" value="1" />Randomize Data<br />
			</p>
			<input type="Submit" name="btn_Submit" value="submit" >
		</form>
<?php	
	} 
	else{
		//global $error_Array;
		//$error_Array = array();
		
		//print header
		print "<h1>Processing Data</h1>";
		print "<p><a href='select_data.php'><- change parameters</a></p>";
		
		//save the name of the table selected in the dropdown menu. 
		//we will use this table for the program
		$data_file = $_POST["$ddDataSet"];
		//$names_file = "car.names";
		$names_file = $_POST["$ddNameFiles"];
		$training_percent = $_POST["training_Percent"];
				
		//print the name of the file selected 
		//print "<p><strong>Names File Selected:</strong> ".$names_file."</p>";
		print "<p><strong>Data File Selected:</strong> ".$data_file."</p>";
							
		//print name files contents
		$names_file_name = "../namesFiles/".$names_file;
		$data_file_name = "../data/".$data_file;	

		//create object to proces names and data files
		$dataObject = new data();
		$dataObject->readNamesFile($names_file_name);
		
		$attArray = $dataObject->getAttributesArray();
		$classArray = $dataObject->getClassArray();
		
		//read data file
		$dataObject->readDataFile($data_file_name);
		$dataSet = $dataObject->getDataSet();
		
		//check percent is valid
		if($training_percent > 100 ||  $training_percent < 1)
		{
			$error_Array[] = "Invalid training percent value: <strong>".$training_percent."</strong> Value must be between 1 and 100 percent.";
		}
		
		//compare data and name file attribute names, save any errors
		$data_file_fields = $dataObject->getDataFields();
		foreach($data_file_fields as $field){
			$found = false;
			if(array_key_exists($field,$classArray)){ //field is class attribute
					$found = true;
			}else{			
				foreach($attArray as $attribute=>$values){
					if($attribute == $field){
						$found = true;
						break;
					}
				}
			}
			if($found == false)
				$error_Array[] = "Attribute <strong> $field </strong> is not defined in names file: <strong> $names_file </strong>";
		}
		unset($found);
		
		//get distinct values for data 
		$dataDistinctValues = array();
		foreach($data_file_fields as $field){
			$dataDistinctValues[$field] = $dataObject->getDistinct($dataSet,$field);
		}		
			
		//get class_attribute
		foreach($classArray as $key=>$value){
			$class_label_attribute = $key;
		}
		unset($key);
		unset($value);
		
		//process data file values. Values must match those declared in the names file.
		if(sizeof($error_Array) == 0){
			foreach($dataDistinctValues as $attribute=>$values){
				//attribute is class attribute
				if(array_key_exists($attribute,$classArray)){
					foreach($values as $index=>$value){
						if(!in_array($value, $classArray[$attribute])){
							$error_Array[] = "Class Value: <strong>$value</strong> is not defined for class attribute <strong>$attribute</strong>. Check names file.";
						}
					}
				}else{
					foreach($values as $value){
						$tempArray = $attArray[$attribute];
						 if(!in_array($value,$tempArray )){
							$error_Array[] = "Attribute: <strong>$value</strong> is not defined for attribute <strong>$attribute</strong> Check names file.";
						}
					}
				}
			}
		}
		
		//no errors encountered. create two arrays, one for training and one for validation.
		if(sizeof($error_Array) == 0){
			print "<p>No errors encountered.</p>";
						
			//if checkbox clicked, shuffle data 
			if(isset($_POST['cbox_Random']) && $_POST['cbox_Random'] == '1'){
				shuffle($dataSet);
				print "<p>Data Randomized!</p>";
			}
			
			//get dataset size
			$dataSetSize = $dataObject->getArraySize();
			
			//convert percent to decimial
			$training_percent = $training_percent / 100.0;
			
			//set training set size
			$tSetSize = round($dataSetSize * $training_percent);
			
			//set validation size
			$vSetSize = $dataSetSize - $tSetSize;
			
			$trainingArray = array_slice($dataSet,0,$tSetSize);
			$validationArray = array_slice($dataSet,$tSetSize,$dataSetSize-1);
			
			//print "<p>Training Set Size: $tSetSize</p>";
			//print "<p>Validation Set Size: $vSetSize</p>";
			
			
			//create a string to save training data
			$TrainingdataSet_str = $dataObject->getDataFileFieldsInStrForm();
			$TrainingdataSet_str .= "\n\n";
			$TrainingdataSet_str .= $dataObject->convertDataArrayToString($trainingArray);
			
			
			//variable used to display or hide testing data. 
			$visibility = "hidden";
			$TestingdataSet_str = "";
			if(sizeof($validationArray) > 0){
				$visibility = "visible";
				$TestingdataSet_str = $dataObject->getDataFileFieldsInStrForm();
				$TestingdataSet_str .= "\n\n";
				$TestingdataSet_str .= $dataObject->convertDataArrayToString($validationArray);
			}
?>

			<div id="content-left-header">
				<?php print "<p>Training Set Size: $tSetSize</p>"; ?>
			</div>
			<div id="content-right-header" style="visibility: <?=$visibility?>;">
				<?php print "<p>Validation Set Size: $vSetSize</p>"; ?>
			</div>
			
			
			<div id="content-left">
				<label class="textareaContainer">
					<textarea readonly name="trainingSet"><?=$TrainingdataSet_str;?> </textarea>
				</label>
			</div>
			
			
			<div id="content-right" style="visibility: <?=$visibility?>;" >
				<label class="textareaContainer"  >
					<textarea readonly name="validationSet" style="visibility: <?=$visibility?>; " ><?=$TestingdataSet_str;?> </textarea>
				</label>
			</div>
			<div style="clear: both;" ></div>
<?php
			//serialize arrays
			$serializedAttValues = serialize($attArray);
			$serializedClassValues = serialize($classArray);
			$serializedTDataArray = serialize($trainingArray);
			$serializedVDataArray = serialize($validationArray);
		
			//create form
?>
			<form action="c4_5.php" method="post">
				<input type="hidden" id="serializedArray1" name="serializedAttValues" 
						value='<?php echo $serializedAttValues;?>'/>
				<input type="hidden" id="serializedArray2" name="serializedClassValues" 
						value='<?php echo $serializedClassValues;?>'/>	
				<input type="hidden" id="serializedArray3" name="serializedTDataArray" 
						value='<?php echo $serializedTDataArray;?>'/>		
				<input type="hidden" id="serializedArray4" name="serializedVDataArray" 
						value='<?php echo $serializedVDataArray;?>'/>
				<br />
				<input type="submit" name="btnASelected" value="Create Classifier!">
			</form>
<?php
		}
	}
	if(sizeof($error_Array) > 0){
		//errors encountered. show errors, ask user to correct before continuing with program
		print "Errors encountered. <br />";
		foreach($error_Array as $error){
			print "---Error: ".$error."<br />";
		}		
		print "<br /><br />";	
?>
		<form action="select_data.php" method="post">			
			<input type="submit" name="btn_processingError" value="Try Again!">
		</form>
<?php
	}
?>
			</div><!-- end .content -->
			<div class="footer">
				<p>Copyright &copy; CSU Fullerton CPSC 531. All Rights Reserved</p>
			</div><!-- end .footer -->
		</div><!-- end .container -->
	</body>
</head>


<?php
//drop down menu
function dropdown($name, array $options, $selected=null ){
    /*** begin the select ***/
    $dropdown = '<select name="'.$name.'" id="'.$name.'">'."\n";

    $selected = $selected;
    /*** loop over the options ***/
    foreach( $options as $key=>$option ){
        /*** assign a selected value ***/
        $select = $selected==$key ? ' selected' : null;

        /*** add each option to the dropdown ***/
        $dropdown .= '<option value="'.$key.'"'.$select.'>'.$option.'</option>'."\n";
    }

    /*** close the select ***/
    $dropdown .= '</select>'."\n";

    /*** and return the completed dropdown ***/
    return $dropdown;
}

function createDataFileString(array &$dataset,$listOfAttributes,$classAttribute,$classValues){

}
?>