<?php
	include "../include_files/node.class.php";
	session_start(); 
	
	if(isset($_POST['btnASelected2'])){
		$vDataSet =  $_SESSION['vDataSet'];
		$tDataSet = $_SESSION['tDataSet'];
		
		$totalRecords = sizeof($vDataSet) + sizeof($tDataSet );
		print "Total Records: $totalRecords <br />";
		
		//retrieve session variables
		$rootNode = $_SESSION['rootNode'];
		$fieldNames = $_SESSION['fieldNames'];
		$class_label_attribute =  $_SESSION['class_label_attribute'];
		$classValues = $_SESSION['classValues'];
	
		$tree_str = $rootNode->printNodesDepthFirstFile();
			
		$myFile = "../programOutput/tree.txt";
		$fh = fopen($myFile, 'w') or die("can't open file");
	
		fwrite($fh, $tree_str);
		fclose($fh);
			
		//create training confusion matrix
		$tCMatrix = array();
		$testingCMatrix = array();
		
		//save class values in array
		$cv = $classValues[$class_label_attribute];
		
		foreach($cv as $row){
			foreach($cv as $column){
				$tCMatrix [$row][$column] = 0;
				$testingCMatrix [$row][$column] = 0;
			}
		}
		//2 confusion matrices created. values set to 0.
	
		$tcorrect = 0;
		$tincorrect = 0;
		$tcalcString = "";
		
		//validate data, one row at a time for training set 
		for($i=0; $i<sizeof($tDataSet); $i++){
			$tcalcString .= "Row $i from dataset: <br />";
			foreach($tDataSet [$i] as $att =>$val ){
				$tcalcString .= "[$att:$val]";
			}
			$tcalcString .= "<br />";
			
			//classify data 
			$tree_classifier = "Not Set";
			$unimportant_treeResult = $rootNode->findNode($tDataSet[$i],$tree_classifier);
			
			//save tree classification value, this value is obtained from the second parameter passed to the
			//function findNode. 
			$treeClassification = $tree_classifier;
			
			//make sure item was classifed by tree. Print not found if item is not found in tree. 
			//this should not happen
			if($treeClassification == "Item not in tree!"){
				print "Could Not Find: ";
				print_r($tDataSet[$i]);
				print " Check spelling in names file.<br /><br />";
			}
			
			//get the actual class value from the dataset
			$realCValue = $tDataSet[$i][$class_label_attribute];
			
			//add classification to confusion matrix
			$tCMatrix [$realCValue][$treeClassification] += 1; 
			
			//compare tree value to actual value 
			if($realCValue == $treeClassification){
				$tcorrect++;
			}else{
				$tincorrect++;
			}

			$tcalcString .= "Tree Classified row $i as: <strong>".$treeClassification."</strong><br />";
			$tcalcString .= "Row $i is actually: <strong>".$realCValue."</strong><br /><br/>";
		}
		unset($unimportant_treeResult);
		unset($i);
		
		//print "Calc String: <br /> $tcalcString";
		//compute accuracy for training set
		$sizeOfTestingSet = sizeof($tDataSet);
		$trainingAcc = ($tcorrect / $sizeOfTestingSet) * 100.0;
				
		print "<strong>Accuracy based on training set: </strong><br />";
		print "----<strong>Training Set Size: </strong>$sizeOfTestingSet <br />";
		print "----<strong>Total Data Size: </strong>$totalRecords<br />";
		print "----<strong>Correctly Classified: </strong>$tcorrect <br />";
		print "----<strong>Incorrectly Classifeied: </strong>$tincorrect <br />";
		print "----<strong>Accuracy: </strong>$trainingAcc%<br /><br />";
		
		
		//start the table
		$trainingTableHTML = "<table border='1'>";
		$rCounter = 0;
		$cCounter = 0;
		
		//create confusion matrix table string for training values
		foreach($tCMatrix as $classValue => $rows){
			$cCounter = 0;	
			$trainingTableHTML .= "<tr>"; //start row
			if($rCounter == 0){ //print header
				foreach($tCMatrix as $columnHeader=>$crows){
						$trainingTableHTML .= "<td><strong> $columnHeader </strong></td>";
				}
				$trainingTableHTML .= "<td></td>";
				$trainingTableHTML .= "</tr>"; //end row
				$trainingTableHTML .= "<tr>"; //start row
			}
			
			foreach($rows as $ClassifiedValue => $value ){
				$trainingTableHTML .= "<td>".$tCMatrix[$classValue][$ClassifiedValue]."</td>";
			}
			$trainingTableHTML .= "<td> <strong>$classValue</strong></td>";
			
			$trainingTableHTML .= "</tr>"; //end row
			$rCounter++;
			//print "<br />";
		}
		
		$trainingTableHTML .= "</table>";

		print "<strong>Training Confusion Matrix: </strong><br />";
		print $trainingTableHTML;
		print "<br /><br />";
		
		
		//check if testing set exists
		if(sizeof($vDataSet) > 0){					
			$correct = 0;
			$incorrect = 0;
			$calcString = "";
					
			//validate data, one row at a time 
			for($i=0; $i<sizeof($vDataSet); $i++){
				$calcString .= "Row $i from dataset: <br />";
				foreach($vDataSet [$i] as $att =>$val ){
					$calcString .= "[$att:$val]";
				}
				$calcString .= "<br />";
				
				$tree_classifier = "Not Set";
				$unimportant_treeResult = $rootNode->findNode($vDataSet[$i],$tree_classifier);
				
				//save tree classification value, this value is obtained from the second parameter passed to the
				//function findNode. 
				$treeClassification = $tree_classifier;
				
				//get the actual class value from the dataset
				$realCValue = $vDataSet[$i][$class_label_attribute];
				
				//update testing matrix accordingly
				$testingCMatrix [$realCValue][$treeClassification] += 1;
				if($realCValue == $treeClassification){
					$correct++; //increment the correct count
				}else{
					$incorrect++; //increment the incorrect count
				}
					
				$calcString .= "Tree Classified row $i as: <strong>".$treeClassification."</strong><br />";
				$calcString .= "Row $i is actually: <strong>".$realCValue."</strong><br /><br/>";
			}
			unset($unimportant_treeResult);
			
			//compute accuracy for testing data
			$sizeOfTestingSet = sizeof($vDataSet);			
			$testingAcc = ($correct / $sizeOfTestingSet) * 100.0;
			
			print "<strong>Accuracy based on testing set: </strong><br />";
			print "----<strong>Training Set Size: </strong>$sizeOfTestingSet <br />";
			print "----<strong>Total Data Size: </strong>$totalRecords<br />";
			print "----<strong>Correctly Classified: </strong>$correct <br />";
			print "----<strong>Incorrectly Classifeied: </strong>$incorrect <br />";
			print "----<strong>Accuracy: </strong>$testingAcc%<br /><br />";
			
			
			//start the table
			$TestingTableHTML = "<table BORDER='1' CELLSPACING='0'>";
			$trCounter = 0;
			$testingCMSum = array();
				
			//create confusion matrix table string for training values
			foreach($testingCMatrix as $classValue => $rows){	
				$TestingTableHTML .= "<tr>"; //start row
				if($trCounter == 0){ //print header
					foreach($testingCMatrix as $columnHeader=>$crows){
							$TestingTableHTML .= "<td bgcolor='#488AC7'><strong> $columnHeader </strong></td>";
							$testingCMSum [$columnHeader] = 0;
					}
					
					$TestingTableHTML .= "<td bgcolor='#488AC7'><strong>C.A./A.C.</strong></td>";
					$TestingTableHTML .= "</tr><tr>"; //end row, start new
				}
				
				foreach($rows as $ClassifiedValue => $value ){
					$TestingTableHTML .= "<td>".$testingCMatrix[$classValue][$ClassifiedValue]."</td>";
					$testingCMSum [$ClassifiedValue]+= $value;
				}
				$TestingTableHTML .= "<td bgcolor='#488AC7'> <strong>$classValue</strong></td>";//print right column header
				$TestingTableHTML .= "</tr>"; //end row
				$trCounter++;
			}			
			//$TestingTableHTML .= "<tr><td></td><td></td><td></td><td></td><td><strong>Total</strong></td></tr>";
			
			//print out correct
			$TestingTableHTML .= "<tr>";
			foreach($testingCMSum as $attRow=>$trash1){ //only interested in array keys (att names)
				foreach($testingCMSum as $attColumn=>$trash2){
					if($attRow == $attColumn){ //row is equal to column, i.e, row is equal to column
						$TestingTableHTML .= "<td>".$testingCMatrix[$attRow][$attColumn]."</td>";
					}
				}
			}
			$TestingTableHTML .= "<td bgcolor='#488AC7'><strong>Correct</strong></td>";
			$TestingTableHTML .= "</tr>";
			
			
			//print out column sums
			$TestingTableHTML .= "<tr>";
			foreach($testingCMSum as $attribute=>$count){
				$TestingTableHTML .= "<td>$count </td>";
			} 
			$TestingTableHTML .= "<td bgcolor='#488AC7'><strong>C. Total</strong></td>";
			$TestingTableHTML .= "</tr>";
			$TestingTableHTML .= "</table>";
	
			print "<br />";
			print "<strong>Testing Set Confusion Matrix:</strong><br />";
			print $TestingTableHTML;
			print "<br />";
			
			foreach($testingCMSum as $attribute=>$count){
				print "$attribute: $count <br />";
			} 
			print "<br /><br />";
		}else{
			print "Testing set not chosen. Tree accuracy based soley on training set.<br />";
		}
	}
	else{
		print "OOPs! You, Didn't get to this page correctly.";
	}
?>
		<p><a href="../index.html"> <- Return Home </a></p>