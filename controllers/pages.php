<?php
	class Pages extends Controller
	{
		function homePage( $args = false ) {
			//$this->setview("home.php");
			print "This is the home page.".$this->name;
		}
		
		function aboutPage( $args = false ) {
			print "This is the about page.";
		}
	}