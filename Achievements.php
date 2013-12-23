<?php

	/*	Achievement
	 *	-----------
	 */
	class Loadestone_Achievements
	{
		private $Category;
		private $TotalPoints;
		private $Points;
		private $List;

		// CATEGORIES
		public function setCategory($ID)
		{
			$this->Category = $ID;
		}
		public function getCategory() { return $this->Category; }

		// POINTS
		public function setPoints($String)
		{
			$this->TotalPoints = trim($String[0]);
		}
		public function getPoints() { return $this->TotalPoints; }

		// ACHIEVEMENTS
		public function set($Array)
		{
			// New list of achievements
			$NewList = array();

			// Loop through achievement blocks
			foreach($Array as $A)
			{
				// Temp data array
				$Temp = array();

				// Loop through block data
				$i = 0;
				foreach($A as $Line)
				{
					// Get achievement Data
					if (stripos($Line, 'achievement_name') !== false) { $Data = trim(strip_tags(html_entity_decode($Line))); $Temp['name'] = $Data; }
					if (stripos($Line, 'achievement_point') !== false) { $Data = trim(strip_tags(html_entity_decode($Line))); $Temp['points'] = $Data; }
					if (stripos($Line, 'getElementById') !== false) { $Temp['date'] = trim(filter_var(explode("(", strip_tags(html_entity_decode($Line)))[2], FILTER_SANITIZE_NUMBER_INT)); }

					// Increment
					$i++;
				}

				// Obtained or not
				if ($Temp['date']) { $Temp['obtained'] = true; } else { $Temp['obtained'] = false; }

				// Increment Points
				if ($Temp['obtained']) { $this->Points['current'] += $Temp['points']; }
				$this->Points['max'] += $Temp['points'];

				// Append temp data
				$NewList[] = $Temp;
			}

			// Set Achievement List
			$this->List = $NewList;
		}
		public function get() { return $this->List; }
	}