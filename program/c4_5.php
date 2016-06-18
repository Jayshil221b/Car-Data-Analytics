<?php
	session_start(); // start up your PHP session!
?>
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
	//session_start(); // start up your PHP session!
	
	//include node class
	include "../include_files/node.class.php";
	include "../include_files/data.php";
	
	//turn debugging on/off
	//$debug = true;
	$debug = false;
	
	//make sure user arrived to this page from data selection page. if not, redirect
	if(!isset($_POST['btnASelected'])){
		header( 'Location: http://jayshil.database.com/program/select_data.php' );
	}else{
		//string holds debug information
		$debug_str = "";
		
		//retrieve array values from previous page
		$attValues = $_REQUEST['serializedAttValues'];
		$attValues = unserialize(stripslashes($attValues)); 
	
		$classValues = $_REQUEST['serializedClassValues'];
		$classValues = unserialize(stripslashes($classValues));
		
		$tDataSet = $_REQUEST['serializedTDataArray'];
		$tDataSet = unserialize(stripslashes($tDataSet));
		
		$vDataSet = $_REQUEST['serializedVDataArray'];
		$vDataSet = unserialize(stripslashes($vDataSet));
		
		$tDataStr = "";
		$tDataStr .=  "<strong>Data Attribute:</strong> <br />";
		
		//save training data in string
		foreach($tDataSet as $dataRow){
			foreach($dataRow as $attribute=>$value){
				$tDataStr .= "[$attribute:$value]";
			}
			$tDataStr .= "<br />";
		}
		
		//get class attribute name
		foreach($classValues as $key=>$value){
			$class_label_attribute = $key;
		}
		unset($key);
		unset($value);
		$debug_str .=  "<strong>Class Attribute:</strong> <br />";
		$debug_str .= "[".$class_label_attribute."]<br /><br />";	
		
		$debug_str .= "<strong>AttValue array:</strong><br />";
		
		//save attValues array
		foreach($attValues as $key=>$value){
			$debug_str .=  "<strong>$key:</strong>";
			foreach($value as $item){
				$debug_str .=  "[$item]";
			}
			$debug_str .= "<br />";
		}
		$debug_str .= "<br />";
		
		//get the list of attributes
		foreach($attValues as $key=>$value){
			$fieldNames [] = $key;
		}
		unset($key);
		unset($value);
		$debug_str .=  "<strong>Field Names:</strong> <br />";
		foreach($fieldNames as $temp){
			$debug_str .= "[".$temp."]";
		}
		$debug_str .=  "<br /><br />";
		
		
		//declare tree root
		$treeRoot = new Node;
		
		?>
				<div id="content-center-header">
		<?php
		//print creating tree
		print "<h3>Creating tree .......................... </h3>";
		?>
				</div>
<?php
		
?>
			<div id="content-center">
<?php
	
		//call the function that is going to build the tree
		$treeRoot = buildDecisionTree(	$fieldNames,
										$attValues,
										$class_label_attribute,
										$classValues,
										$tDataSet);
									
		//register root node session variable and set it equal to the root node
		//session_register('rootNode');
		$_SESSION['rootNode'] = $treeRoot;
		
		//register fieldNames 
		//session_register('fieldNames');
		$_SESSION['fieldNames'] = $fieldNames;
		
		//register fieldNames 
		//session_register('class_label_attribute');
		$_SESSION['class_label_attribute'] = $class_label_attribute;
		
		//session_register('vDataSet');
		$_SESSION['vDataSet'] = $vDataSet;
		
		//session_register('tDataSet');
		$_SESSION['tDataSet'] = $tDataSet;
		
		//session_register('classValues');
		$_SESSION['classValues'] = $classValues;
	
		print "<br /><br />";
		
		
?>
			</div><!-- end #content-center -->
			<div id="content-center-below">
				<form action="../validation/validation.php" method="post">					
					<input type="submit" name="btnASelected2" value="Click to Validate Tree!">
				</form>
			</div>
<?php		
		
		//print debug string
		if($debug)
			print $debug_str;
	} 
?>
			</div><!-- end .content -->
			<div class="footer">
				<p>Copyright &copy; Fullerton CPSC 531. All Rights Reserved</p>
			</div><!-- end .footer -->
		</div><!-- end .container -->
	</body>
</head>
<?php 
/*	This function builds a tree
	Input: this function requires an array of attributes, 
				the class label attribute, and a data set array to be passed in.
				Note: the class label attribute must not exists in the attribute
				array. The class label attribute must be passed in as its own 
				parameter, namely, $class_label_attribute
	Output: this function returns a tree structure
 */
 //maybe pass by reference
function buildDecisionTree(array $attribute_list, array &$attValues, $class_label_attribute, array &$classValues, array $dataSet, $nodeLevel=0){
	//create object to proces names and data files
	$dataObject = new data();
	
	//count the number of distinct classes for the dataset being processed 
	$noOfClasses = $dataObject->countDistinctClasses($dataSet,$class_label_attribute);

	//create a new node
	$node = new node();
	$node->setNodeLevel($nodeLevel);
	
	//format print
	$format_str = "";
	for($i=0; $i<$node->getNodeLevel()*5; $i++){
		if($i % 5 == 0)
			$format_str .= "|";
		$format_str .=  "&nbsp";
	}
	
	//Terminating condition 1: the data tuples all have the same class attribute value.
	//Assign the class attribute value as the nodes name and leaf as the nodes type.
	//Return the node. 
	if($noOfClasses == 1){
		//find distinct class value. Only one value will exists since the number of 
		//distinct values among all tuples is equal to one. 
		$classValue = $dataObject->getDistinct($dataSet,$class_label_attribute);
		$classValue =  $classValue[0];
		
		//set the node name to the distinct class value
		$node->setName($classValue);
		unset($classValue);		
		
		//set nodetype to leaf
		$node->setNodeType("leaf");
		
		print $node->getName();
		print '<img src="../_images/leaf2.jpg" width="12px" height="12px" />';
		
		//return the node
		return $node;
	}
	//Terminating condition 2: the attribute list is empty
	//find the majority class in the tuple set and return the node with the 
	//majority class as its name and leaf as its type.
	if(sizeof($attribute_list) == 0){ //attribute list is empty
		//find majority class in data set
		$majorityClassValue = computeMajorityClass($class_label_attribute,$dataSet);

		//set node name to class value
		$node->setName($majorityClassValue);
		
		//set nodetype to leaf
		$node->setNodeType("leaf");
		
		print $node->getName();
		print '<img src="../_images/leaf2.jpg" width="12px" height="12px" />';
		//print "<br />";
		
		//return node
		return $node;
	}
		
	//find attribute with greatest gain
	$splitAttribute = findMaxAttribute($attribute_list,$class_label_attribute,$dataSet);
	
	//label node with the splitting criterion
	$node->setName($splitAttribute);
	$node->setNodeType("parent");
	
	//remove splitting attribute from list
	$newArrayList = array();
	foreach($attribute_list as $attribute){
		if($attribute != $splitAttribute){
			$newArrayList[] = $attribute;
		}
	}
	
	$splitAttributeValues = $attValues[$node->getName()];	
	$majorityClass = "";
	$star_string = "";
	
	//for each outcome j of splitting criterion
	foreach($splitAttributeValues as $splitValue){
		//find sub data set
		$subDataSet = $dataObject->getSubSet($dataSet, $node->getName(), $splitValue) ;
		if(!is_array($subDataSet))
			print "<h1>Not an array</h1>";
			
		$answer = sizeof($subDataSet);			
		print "<br />";
		print $format_str;
		print '<img src="../_images/bullet.jpg" width="10px" height="10px"/>'.$node->getName()." = "."[".$splitValue."]: ";
		
		//Terminating condition 3:
		//subset Dj is empty (size == 0)
		if($answer == 0){
			//variable will be used to store the name of the majority class
			$majorityClass = computeMajorityClass($class_label_attribute,$dataSet);

			$leafNode = new node();
			$leafNode->setName($majorityClass);
			$leafNode->setNodeType("leaf");
			$leafNode->setNodeLevel($nodeLevel + 1);
			$leafNode->setSplitCriterion($splitValue);
			print $leafNode->getName();
			print '<img src="../_images/leaf2.jpg" width="12px" height="12px" />';
			$node->attachedNodes[] =  $leafNode;
		}else{
			$temp_node = buildDecisionTree($newArrayList, $attValues, $class_label_attribute, $classValues, $subDataSet, $node->getNodeLevel() + 1);
			$temp_node->setSplitCriterion($splitValue);
			$node->attachedNodes [] = $temp_node;
			unset($temp_node);
			unset($splitValue);
		}
	}
	//return node;
	return $node;	
}

//function computes the majority class for a given dataset
function computeMajorityClass($class_label_attribute,array &$dataSet){
	//create object to proces names and data files
	$dataObject = new data();

	//find distinct value(s) for class attribute
	$distinctClasses = $dataObject->getDistinct($dataSet,$class_label_attribute);
	
	//find majority class
	$max = 0;
	$maxValue = "";
	
	//count the number of occurances for each distinct class in the dataset
	foreach($distinctClasses as $class){
		$valueCount = $dataObject->countNumberInArray($dataSet,$class_label_attribute,$class);
		if($valueCount > $max)
			$maxValue = $class;
	}	
	//return majority attribute
	return $maxValue;
}

//function returns the attribute with the greates gain
function findMaxAttribute(array &$fieldNames,$class_label_attribute,array &$dataSet){
	$tempValue = 0;
	
	//variables used to compute max entropy
	$max = 0;
	$maxAttribute = "";

	//compute entropy for each attibute
	foreach($fieldNames as $attribute_name){			
		//compute the information gain for each attribute. save that value in a temp variable.
		$tempValue = computeInformationGain($attribute_name,$class_label_attribute,$dataSet);      
		
		//if temp value is greater than max, update accordingly
		if($tempValue > $max){
			$max = $tempValue;
			$maxAttribute = $attribute_name;
		}
	}
	//return the name of the attribute with the greatest information gain.
	return $maxAttribute;
}

//function computes entropy
function computeInformationGain($attribute_name,$class_label_attribute,array &$dataSet){
	//create object to proces names and data files
	$dataObject = new data();
	
	//find distinct value(s) for class values
	$distinctClassValues = $dataObject->getDistinct($dataSet,$class_label_attribute);
	
	//declare array to save the class name as key and count as value
	$class_attribute_values = array();
	
	//tupleCount saves the number of records in the data set
	$tupleCount = 0;
	
	//count occurance of each attribute value
	foreach($distinctClassValues as $class_value){
		$cValue_Count = $dataObject->countNumberInArray($dataSet,$class_label_attribute,$class_value);
		$class_attribute_values[$class_value] = $cValue_Count;
		
		//increment tuple count
		$tupleCount += $cValue_Count;
	}
	
	$entropyStr = computeInfo_string($class_attribute_values,$tupleCount);
	
	//compute expected information (entropy)
	$entropy = computeInfo($class_attribute_values,$tupleCount);
	
	//declare array to save the attribute names as key and their occurance as value
	$attribute_values = array();
	
	//find distinct value(s) for class values
	$attribute_values = $dataObject->getDistinct($dataSet,$attribute_name);
		
	//compute the info for the attribute
	$calc_string = "";
		
	//count attribute values with class attribute value 
	$av_cl;
	
	//temp string
	$infoString = "";
	$valueShortInfo = "";
	$infoValue = 0;
	
	//find attribute value-class value combinations
	foreach($attribute_values as $value)
	{
		$count = 0;
		$valueInfo = "";
		$info_comp = 0;
		$av_cl[$value] = array();
		$denominator = 0;
		$subArray = $dataObject->getSubSet($dataSet, $attribute_name, $value);
			
		foreach($class_attribute_values as $claValue=>$extra)
		{
			$count = $dataObject->countNumberInArray($subArray,$class_label_attribute,$claValue);
			$denominator += $count;
			$av_cl[$value][$claValue] = $count;			//set condition string equal to attribute value			
		}
		unset($subArray);
		
		//compute value
		$info_comp = computeInfo($av_cl[$value],$denominator);
		$infoValue = $infoValue + ($denominator/$tupleCount) * $info_comp;
	}
	
	$answer = 0;
	$answer = $entropy - $infoValue;
	
	//return information gain for attribute.
	return $answer;
}
/* return info compute string */
function computeInfo_string(array $av_cl, $denominator){
	$info = "";
	foreach($av_cl as $value){
		$info = $info."-(".$value."/$denominator)*log2(".$value."/$denominator)";
	}
	return $info;
}

function computeInfoShortString(array &$av_cl, $denominator){
	$info = "I(";
	foreach( $av_cl as $value)
	{
		$info .= $value.",";
	}
	
	//remove trailing comma
	$info = substr($info, 0, -1);
	$info .= ")";
	return $info;
}

/* function computes local information. Accepts as parameters  */
function computeInfo(array &$av_cl, $denominator){
	$info = 0;
	$temp = 0;
	$Pi = 0;
	
	//value
	foreach($av_cl as $value){
		if($denominator == 0){
			return 0;
		}else{
			$Pi = ($value/$denominator);
			if($Pi == 0){
				//print "Pi equal to zero. <br />";
				return 0;
			}
				
			$temp = -($Pi)*log($Pi,2);
			$info += $temp;
		}	
		if(is_nan($info)){
			print "Invalid Number in function computeInfo.<br />";
		}
	}
	return $info;
}
?>