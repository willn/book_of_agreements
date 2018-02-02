<?php

/**
 * #!#
 */
class Committee {
	var $cid;

	function __construct($id) {
		if (!is_null($id)) {
			$this->setId($id);
		}
	}

	function setId($cid) {
		$this->cid = $cid;
	}

	function getSelectCommittee($Cmtys, $SubCmtys) {
		$out = '';
		foreach( $Cmtys as $cmty_num=>$c ) {
			if ( $this->cid == $cmty_num ) {
				$out .= '<option value="'.$cmty_num.'" selected="selected">'.
					"$c</option>\n";
			}
			else {
				$out .= '<option value="'.$cmty_num.'">'."$c</option>\n";
			}

			if ( isset( $SubCmtys[$cmty_num] )) {
				foreach( $SubCmtys[$cmty_num] as $scmty_num=>$sc ) {
					if ( $this->cid == $scmty_num ) {
						$out .= '<option value="' . $scmty_num . 
							'" selected="selected">' . "$c:$sc</option>\n";
					}
					else {
						$out .= '<option value="' . $scmty_num . '">' . 
							"$c:$sc</option>\n";
					}
				}
			}

		}

		return <<<EOHTML
		<label>
			<span>Committee:</span>
			<select name="cid" size="1">
				{$out}
			</select>
		</label>
EOHTML;
	}

	function getName() {
		if (is_null($this->cid)) {
			return;
		}

		global $Cmtys;
		global $SubCmtys;
		$name = '';

		$id = $this->cid;
		if ( isset( $Cmtys[$id] )) {
			return $Cmtys[$id];
		}

		foreach( $SubCmtys as $major => $Sub ) {
			if ( isset( $Sub[$id] )) {
				return $Cmtys[$major] . ': ' .$Sub[$id];
			}
		}

		echo <<<EOHTML
			<div class="error">Error! Could not find requested committee</div>
EOHTML;
		exit;
	}
}

