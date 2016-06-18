<?php
	class node{
	
		private $name;
		private $nodeType;
		private $split_criterion;
		private $level;
		public $attachedNodes = array();
		
		public function __construct($name = "not set",$nodeType="Parent"){
			$this->name = $name;
			$this->nodeType = $nodeType;
		}
				
		public function setName($name){
			$this->name = $name;
		}
		
		public function getName(){
			return $this->name;
		}
			
		public function setNodeType($nodeType){
			$this->nodeType = $nodeType;
		}
		
		public function getNodeType(){
			return $this->nodeType;
		}
		
		public function setNodeLevel($level){
			$this->level = $level;
		}
		
		public function getNodeLevel(){
			return $this->level;
		}
				
		public function setSplitCriterion($split_cri){
			$this->split_criterion = $split_cri;
		}
		
		public function getSplitCriterion()	{
			return $this->split_criterion;
		}
			
		public function getAttachedNodes()
		{
			//print_r($this->attachedNodes);
			//print "<br />";
			return $this->attachedNodes;
		}
		
		public function getTree(){
			return $this->attachedNodes;
		}
				
		public function printNodesDepthFirst($treeLevel=0){
			//$return_str = ":";
			$return_str = "";
			//terminating condition, print leaf
			if(sizeof($this->attachedNodes) == 0)
			{
				$temp_str = "";
				for($i=0; $i<$treeLevel*2; $i++)
					$temp_str .= "-";
				if($treeLevel != 0)
					$return_str .= $temp_str."[".$this->getSplitCriterion()."]<br />";
				$return_str .= $temp_str.$this->getName()."<br />";
				return $return_str;
			}
			else{ //not a leaf node
								
				$temp_str = "";
				for($i=0; $i<$treeLevel*2; $i++)
					$temp_str .= "-";
				
				if($treeLevel != 0)
					$return_str .= $temp_str."[".$this->getSplitCriterion()."]<br />";
				$return_str .= $temp_str.$this->getName()."<br />";
				$treeLevel++;
				
				//print same treeLevel nodes
				foreach($this->attachedNodes as $node){
					$return_str .= $node->printNodesDepthFirst($treeLevel);
				}
				return $return_str;
			}
		}
		
		public function printNodesDepthFirstFile($treeLevel=0){
			$return_str = "";
			//terminating condition, print leaf
			if(sizeof($this->attachedNodes) == 0)
			{
				$temp_str = "";
				for($i=0; $i<$treeLevel*2; $i++)
					$temp_str .= "-";
				if($treeLevel != 0)
					$return_str .= $temp_str."[".$this->getSplitCriterion()."]\n";
					
				$return_str .= $temp_str.$this->getName().":Leaf\n";
				return $return_str;
			}
			else{ //not a leaf node
								
				$temp_str = "";
				for($i=0; $i<$treeLevel*2; $i++)
					$temp_str .= "-";
				
				if($treeLevel != 0)
					$return_str .= $temp_str."[".$this->getSplitCriterion()."]\n";
				$return_str .= $temp_str.$this->getName()."\n";
				$treeLevel++;
				
				//print same treeLevel nodes
				foreach($this->attachedNodes as $node){
					$return_str .= $node->printNodesDepthFirstFile($treeLevel);
				}
				return $return_str;
			}
		}
		
		public function findNode(array $attributeValues,&$classV){
		
			//hit a leaf node, return the node name. Node name should be 
			//class value
			if($this->nodeType == "leaf"){
				$classV = $this->name;
				return $classV;
			}else{	//not a leaf node 
				
				//grab the name of the current split attribute 
				$tNName = $this->getName();
				//print "Split Attribute Name: $tNName <br />";
				
				//get passed dataset value for given split attribute determined above
				$dataSetAttributeValue = $attributeValues[$tNName];
	
				//find attached node which has the split value equal to that which is passed in
				foreach($this->attachedNodes as $childNode){
					$tempNodeSplitCriterion = $childNode->getSplitCriterion();
					
					if($tempNodeSplitCriterion == $dataSetAttributeValue){
						$childNode->findNode($attributeValues,$classV);
						return;
					}
					unset($tempNodeSplitCriterion);
				}
				$classV = "Item not in tree!";
				return;
			}
		}
	}
?>