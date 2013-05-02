<?php
/*========================================================
/* Implementation of Douglas-Peuker in PHP.
/* 
/* Anthony Cartmell
/* ajcartmell@fonant.com
/* 
/* This software is provided as-is, with no warranty.
/* Please use and modify freely for anything you like :)
/* Version 1.2 - 18 Aug 2009  Fixes problem with line of three points.
/*                            Thanks to Craig Stanton http://craig.stanton.net.nz
/* Version 1.1 - 17 Jan 2007  Fixes nasty bug!
/*========================================================*/
class GeoPoint {
    public $latitude;
    public $longitude;

    public function __construct($lat,$lng)
    {
	$this->latitude = (float)$lat;
	$this->longitude = (float)$lng;
    }
};
?>
