<?php
/*
===========code here==========
 USAGE - for javascript
polylineEncoder = new PolylineEncoder();
polyline = polylineEncoder.dpEncodeToGPolyline(points);
*/
class PolylineEncoder{

        var $numLevels;
        var $zoomFactor;
        var $verySmall;
        var $forceEndpoints;
        var $zoomLevelBreaks;

        function PolylineEncoder(){
                $this->numLevels = 18;
                $this->zoomFactor = 2;
                $this->verySmall = 0.00001;
                $this->forceEndpoints = true;

                for($i = 0; $i < $this->numLevels; $i++)
                {
                        $this->zoomLevelBreaks[$i] = $this->verySmall*pow($this->zoomFactor, $this->numLevels-$i-1);

                }
        }

        function computeLevel($dd)
        {
                if($dd > $this->verySmall)
                {
                        $lev = 0;
                        while($dd < $this->zoomLevelBreaks[$lev])
                        {
                                $lev++;
                        }
                }
                return $lev;
        }

        function dpEncode($points)
        {
                if(count($points) > 2)
                {
                        $stack[] = array(0, count($points)-1);
                        while(count($stack) > 0)
                        {
                                $current = array_pop($stack);
                                $maxDist = 0;
                                for($i = $current[0]+1; $i < $current[1]; $i++)
                                {
                                        $temp = $this->distance($points[$i], $points[$current[0]],$points[$current[1]]);
                                        if($temp > $maxDist)
                                        {
                                                $maxDist = $temp;
                                                $maxLoc = $i;
                                                if($maxDist > $absMaxDist)
                                                {
                                                        $absMaxDist = $maxDist;
                                                }
                                        }
                                }
                                if($maxDist > $this->verySmall)
                                {
                                        $dists[$maxLoc] = $maxDist;
                                        array_push($stack, array($current[0], $maxLoc));
                                        array_push($stack, array($maxLoc, $current[1]));
                                }
                        }
                }

                $encodedPoints = $this->createEncodings($points, $dists);
                $encodedLevels = $this->encodeLevels($points, $dists, $absMaxDist);
                $encodedPointsLiteral = str_replace('\\',"\\\\",$encodedPoints);

                return array($encodedPoints, $encodedLevels, $encodedPointsLiteral);
        }

        function distance($p0, $p1, $p2)
        {
                if($p1[0] == $p2[0] && $p1[1] == $p2[1])
                {
                        $out = sqrt(pow($p2[0]-$p0[0],2) + pow($p2[1]-$p0[1],2));
                }
                else
                {
                        $u = (($p0[0]-$p1[0])*($p2[0]-$p1[0]) + ($p0[1]-$p1[1]) * ($p2[1]-$p1[1])) / (pow($p2[0]-$p1[0],2) + pow($p2[1]-$p1[1],2));
                        if($u <= 0)
                        {
                                $out = sqrt(pow($p0[0] - $p1[0],2) + pow($p0[1] - $p1[1],2));
                        }
                        if($u >= 1)
                        {
                                $out = sqrt(pow($p0[0] - $p2[0],2) + pow($p0[1] - $p2[1],2));
                        }
                        if(0 < $u && $u < 1)
                        {
                                $out = sqrt(pow($p0[0]-$p1[0]-$u*($p2[0]-$p1[0]),2) + pow($p0[1]-$p1[1]-$u*($p2[1]-$p1[1]),2));
                        }
                }
                return $out;
        }

        function encodeSignedNumber($num)
        {
                $sgn_num = $num << 1;
                if ($num < 0)
                {
                        $sgn_num = ~($sgn_num);
                }
                return $this->encodeNumber($sgn_num);
        }

        function createEncodings($points, $dists)
        {
                for($i=0; $i<count($points); $i++)
                {
                        if(isset($dists[$i]) || $i == 0 || $i == count($points)-1)
                        {
                                $point = $points[$i];
                                $lat = $point[0];
                                $lng = $point[1];
                                $late5 = floor($lat * 1e5);
                                $lnge5 = floor($lng * 1e5);
                                $dlat = $late5 - $plat;
                                $dlng = $lnge5 - $plng;
                                $plat = $late5;
                                $plng = $lnge5;
                                $encoded_points .= $this->encodeSignedNumber($dlat) . $this->encodeSignedNumber($dlng);

                        }
                }
                return $encoded_points;
        }

        function encodeLevels($points, $dists, $absMaxDist)
        {

                if($this->forceEndpoints)
                {
                        $encoded_levels .= $this->encodeNumber($this->numLevels-1);
                }
                else
                {
                        $encoded_levels .= $this->encodeNumber($this->numLevels - $this->computeLevel($absMaxDist)-1);

                }
                for($i=1; $i<count($points)-1; $i++)
                {
                        if(isset($dists[$i]))
                        {
                                $encoded_levels .= $this->encodeNumber($this->numLevels - $this->computeLevel($dists[$i])-1);

                        }
                }
                if($this->forceEndpoints)
                {
                        $encoded_levels .= $this->encodeNumber($this->numLevels -1);
                }
                else
                {
                        $encoded_levels .= $this->encodeNumber($this->numLevels - $this->computeLevel($absMaxDist)-1);

                }
                return $encoded_levels;
        }

        function encodeNumber($num)
        {
                while($num >= 0x20)
                {
                        $nextValue = (0x20 | ($num & 0x1f)) + 63;
                        $encodeString .= chr($nextValue);
                        $num >>= 5;
                }
                $finalValue = $num + 63;
                $encodeString .= chr($finalValue);
                return $encodeString;
        }

}
/*

$points[0][0] = 37.4419;
$points[0][1] = -122.1419;
$points[1][0] = 37.4519;
$points[1][1] = -122.1519;
//$points[2][0] = 37.1619;
//$points[2][1] = -122.1619; 

$polylineEncoder = new PolylineEncoder();
$polyline = $polylineEncoder->dpEncode($points);
echo "
Array points = { <br />
    '37.4419, -122.1419',    <br />
    '37.4519, -122.1519',    <br />
    '37.4619, -122.1819' ) <br />
";
echo "Google string: yzocFzynhVq}@n}@o}@nzD<br />";
echo "This script encoded string: <br />".print_r($polyline); 
*/
?>
