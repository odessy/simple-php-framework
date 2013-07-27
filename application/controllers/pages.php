<?php
	class Pages extends Controller
	{
		function homePage( $args = false ) {
			//$this->setview("page/home.php");
			print "This is the home page.";
		}
		
		function aboutPage( $args = false ) {
			print "This is the about page.";
		}
	}