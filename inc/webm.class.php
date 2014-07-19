<?php
/* 
 *  Class for handling webm files around the MitsubaBBS Project
 *  Copyright (C) 2014  Malkovich <chlodnapiwnica@gmail.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class webm {
	/* Public variables */
	public $codec;			//codec used to create thumbnail
	public $max_time;		//maximum time for thumbnail
	public $font_name;		//font used for overlay text

	/* private properties */
	private $input_file;	//orginal webm movie
	private $exec_string;	//commandline for ffmpeg

	
	function webm($webm_clip_name) {
		$this->codec      = 'vp8';           //used only in .webm thumbnail option
		$this->input_file = $webm_clip_name;
		$this->max_time   = '00:00:05';

	}

	function thumbnail($thumbnail_location, $max_w = 125, $max_h=125,$overlay="webm") {
		$ext = strtolower(pathinfo($thumbnail_location, PATHINFO_EXTENSION));
		switch($ext) {
			case 'webm':
				$this->exec_string = '/home/www/ffmpeg -i '.$this->input_file.
									 ' -vcodec '.$this->codec.
									 ' -an'.
									 ' -t '.$this->max_time.
									 " -vf scale=\"'if(gte(iw,ih),$max_w,-1)':'if(gte(iw,ih),-1,$max_h)'\",drawtext=\"fontfile=/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf:  text='$overlay': fontcolor=white: x=2: y=(h-text_h)\" ".
									 ' -y '.$thumbnail_location.
									 ' </dev/null 2>&1';
				break;
			case 'gif':
				$this->exec_string = '/home/www/ffmpeg -i '.$this->input_file.
									 ' -t '.$this->max_time.
									 ' -r 10 '.
									 " -vf scale=\"'if(gte(iw,ih),$max_w,-1)':'if(gte(iw,ih),-1,$max_h)'\",drawtext=\"fontfile=/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf:  text='$overlay': fontcolor=white: x=2: y=(h-text_h)\" ".
									 ' -y '.$thumbnail_location.
									 ' </dev/null 2>&1';
				break;
		}

		exec($this->exec_string,$output,$return_var);
		if ($return_var==0) {return true;} else {return false;}
	}
	
	/* 
	 *    check for VP8 format 
	 */
	function valid_webm() {
		$this->exec_string = 'ffmpeg -i '.$this->input_file .' </dev/null 2>&1';
		$lines = shell_exec($this->exec_string);
		$lines = explode("\n", $lines);
		$found = false;
		foreach ($lines as $line) {
			if (preg_match('/Stream.+#\d:\d.+Video.+vp8/i', $line)) {$found = true;}
		}
		return $found;
	}
	

}

?>