<?php

// get average luminance, by sampling $num_samples times in both x,y directions
    function get_avg_luminance($filename, $num_samples=10) {
        $img = imagecreatefromjpeg($filename);

        $width = imagesx($img);
        $height = imagesy($img);

        $x_step = intval($width/$num_samples);
        $y_step = intval($height/$num_samples);

        $total_lum = 0;

        $sample_no = 1;

        for ($x=0; $x<$width; $x+=$x_step) {
            for ($y=0; $y<$height; $y+=$y_step) {

                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // choose a simple luminance formula from here
                // http://stackoverflow.com/questions/596216/formula-to-determine-brightness-of-rgb-color
                $lum = ($r+$r+$b+$g+$g+$g)/6;

                $total_lum += $lum;

                // debugging code
     //           echo "$sample_no - XY: $x,$y = $r, $g, $b = $lum<br />";
                $sample_no++;
            }
        }

        // work out the average
        $avg_lum  = $total_lum/$sample_no;

        return $avg_lum;
    }

?>