<?php

	/* Free Company
	 * ------------
	 */	
	class Lodestone_FreeCompany extends Lodestone_API
	{
		private $ID;
		private $Company;
		private $Name;
		private $Server;
		private $Tag;
		private $Formed;
		private $MemberCount;
		private $Slogan;

		private $Members = [];

		#-------------------------------------------#
		# FUNCTIONS									#
		#-------------------------------------------#

		// ID
		public function setID($ID, $URL = NULL)
		{
			$this->ID = $ID;
			$this->Lodestone = $URL;
		}
		public function getID() { return $this->ID; }
		public function getLodestone() { return $this->Lodestone; }

		// NAME + SERVER
		public function setNameServerCompany($String)
		{
			$this->Company 	= htmlspecialchars_decode(trim($String[0]), ENT_QUOTES);
			$this->Name 	= htmlspecialchars_decode(trim($String[1]), ENT_QUOTES);
			$this->Server 	= str_ireplace(array("(", ")"), null, htmlspecialchars_decode(trim($String[2]), ENT_QUOTES));
		}

		// TAG + FORMED + MEMBERS + SLOGAN
		public function setTagFormedMembersSlogan($String)
		{
			$Data 				= explode("&gt;", strip_tags($String[4]));
			$this->Name 		= str_ireplace("&lt;/span", "", $Data[2]);
			$this->Tag 			= str_ireplace(array("&amp;laquo;", "&amp;raquo;", "&lt;/td"), "", $Data[4]);
			$this->Formed 		= trim(explode(",", explode("(", $String[11])[2])[0]);
			$this->MemberCount 	= trim(strip_tags(htmlspecialchars_decode(trim($String[17]), ENT_QUOTES)));
			$this->Slogan 		= trim(strip_tags(htmlspecialchars_decode(trim($String[21]), ENT_QUOTES)));
		}
		public function getCompany() { return $this->Company; }
		public function getName() { return $this->Name; }
		public function getServer() { return $this->Server; }
		public function getTag() { return $this->Tag; }
		public function getFormed() { return $this->Formed; }
		public function getMemberCount() { return $this->MemberCount; }
		public function getSlogan() { return $this->Slogan; }

		// MEMBERS / PARSE + SET + GET
		public function parseMembers($Data)
		{
			// Temp array
			$temp = [];

			// Loop through data
			foreach($Data as $D)
			{
				$Name 		= trim(explode("(", trim(strip_tags(htmlspecialchars_decode($D[1]), ENT_QUOTES)))[0]);
				$Server 	= trim(str_ireplace(")", "", trim(explode("(", trim(strip_tags(htmlspecialchars_decode($D[1]), ENT_QUOTES)))[1])));
				$ID 		= trim(explode("/", $D[1])[3]);

				$RankImage	= trim(explode("?", explode("&quot;", $D[3])[1])[0]);
				$Rank		= trim(str_ireplace("&gt;", null, explode("&quot;", $D[3])[8]));

				$ClassImage = explode("?", explode("&quot;",$D[7])[3])[0];
				$ClassLevel = explode(">", strip_tags(htmlspecialchars_decode(explode("&quot;",$D[7])[10])))[1];

				$arr =
				[
					'id'		=> $ID,
					'name'		=> $Name,
					'server'	=> $Server,

					'rank' =>
					[
						'image' => $RankImage,
						'title' => $Rank
					],

					'class' =>
					[
						'image' => $ClassImage,
						'level' => $ClassLevel,
					]
				];
				
				// Append to array
				$temp[] = $arr;
			}

			// Return temp
			return $temp;
		}
		public function setMembers($Array)
		{
			if (isset($Array) && is_array($Array) && count($Array) > 0)
			{
				$this->Members = $Array;
			}
			else
			{
				$this->Members = false;
			}
		}
		public function getMembers() { return $this->Members; }
	}