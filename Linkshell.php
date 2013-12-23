<?php
	/* Linkshell
	 * ---------
	 */
	class Lodestone_Linkshell extends Lodestone_API
	{
		private $ID;
		private $Name;
		private $Server;
		private $TotalMembers;

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
		public function setNameServer($String)
		{
			$this->Name 	= trim(explode("(", $String[0])[0]);
			$this->Server 	= trim(str_ireplace(")", null, explode("(", $String[0])[1]));
		}
		public function getName() { return $this->Name; }
		public function getServer() { return $this->Server; }

		// MEMBER COUNT
		public function setMemberCount($String)
		{
			$this->TotalMembers = intval(trim(preg_replace("/[^0-9]/", "", $String)[0]));
		}
		public function getTotalMembers() { return $this->TotalMembers; }

		// MEMBERS
		public function setMembers($Array)
		{
			$temp = [];

			// Loop through members
			foreach($Array as $arr)
			{
				// Rank can move offset. Take it out, process it and remove it
				if (stripos($arr[9], "ic_") !== false)
				{
					$Rank = isset(explode("&quot;", $arr[9])[1]) ? trim(explode("&quot;", $arr[9])[1]) : null;
					switch($Rank)
					{
						default: $Rank = 'member'; break;
						case 'ic_master': $Rank = 'master'; break;
						case 'ic_leader': $Rank = 'leader'; break;
					}
					$arr[9] = null;
					$arr = array_values(array_filter($arr));
				}
				else
				{
					// Default rank
					$Rank = 'member';
				}

				// Char data
				$ID 				= trim(explode("/", $arr[1])[3]);
				$Avatar 			= trim(explode("?", explode("&quot;", $arr[2])[1])[0]);
				$Name 				= trim(explode("(", strip_tags(htmlspecialchars_decode($arr[8])))[0]);
				$Server				= trim(explode("(", str_ireplace(")", null, strip_tags(htmlspecialchars_decode($arr[8]))))[1]);

				// Class
				$ClassIcon			= trim(explode("&quot;", $arr[12])[1]);
				$ClassLevel 		= intval(trim(strip_tags(htmlspecialchars_decode($arr[13]))));

				// Company
				$CompanyName = null; $CompanyRank = null;
				$CompanyIcon		= isset(explode("&quot;", $arr[15])[1]) ? trim(explode("&quot;", $arr[15])[1]) : null;
				if ($CompanyIcon)
				{
					$CompanyName 	= trim(explode("/", str_ireplace("-->", null, strip_tags(htmlspecialchars_decode($arr[15]))))[0]);
					$CompanyRank 	= trim(explode("/", str_ireplace("-->", null, strip_tags(htmlspecialchars_decode($arr[15]))))[1]);
				}

				// Free Company
				if ($CompanyIcon) {
					$freeCompanyDetails      = 24;
					$freeCompanyImagesFirst  = $arr[20];
					$freeCompanyImagesSecond = $arr[21];
					$freeCompanyImagesThird  = $arr[22];
				} else {
					$freeCompanyDetails      = 23;
					$freeCompanyImagesFirst  = $arr[19];
					$freeCompanyImagesSecond = $arr[20];
					$freeCompanyImagesThird  = $arr[21];
				}
				$FC_ID = null; $FC_Name = null;
				$FC_Icon = array();
				$FC_Icon[] 			= isset(explode("&quot;", $freeCompanyImagesFirst)[1]) ? trim(explode("&quot;", $freeCompanyImagesFirst)[1]) : null;
				$FC_Icon[] 			= isset(explode("&quot;", $freeCompanyImagesSecond)[1]) ? trim(explode("&quot;", $freeCompanyImagesSecond)[1]) : null;
				$FC_Icon[] 			= isset(explode("&quot;", $freeCompanyImagesThird)[1]) ? trim(explode("&quot;", $freeCompanyImagesThird)[1]) : null;
				if ($FC_Icon[0] != null)
				{
					if ($FC_Icon[2] == null) {
						$freeCompanyDetails--;
						unset($FC_Icon[2]);
					}
					if ($FC_Icon[1] == null) {
						$freeCompanyDetails--;
						unset($FC_Icon[1]);
					}
					if ($FC_Icon[0] == null) {
						$freeCompanyDetails--;
					}

					$FC_ID			= trim(explode("/", explode("&quot;", $arr[$freeCompanyDetails])[3])[3]);
					$FC_Name 		= trim(str_ireplace("-->", null, strip_tags(htmlspecialchars_decode($arr[$freeCompanyDetails]))));
				}

				// Sort array
				$arr =
				[
					'id'		=> $ID,
					'avatar'	=> $Avatar,
					'name'		=> $Name,
					'server'	=> $Server,
					'rank'		=> $Rank,

					'class' =>
					[
						'icon'	=> $ClassIcon,
						'level'	=> $ClassLevel,
					],

					'company' =>
					[
						'icon'	=> $CompanyIcon,
						'name'	=> $CompanyName,
						'rank'	=> $CompanyRank,
					],

					'freecompany' =>
					[
						'icon'	=> $FC_Icon,
						'id'	=> $FC_ID,
						'name'	=> $FC_Name,
					],
				];

				// append to temp array
				$temp[] = $arr;
			}

			// Set Members
			$this->Members = $temp;
		}
		public function getMembers() { return $this->Members; }
	}