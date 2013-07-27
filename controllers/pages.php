<?php
	class Pages extends Controller
	{
		function homePage( $args = false ) {
			$this->setview("home.php");
		}
		
		function about( $args = false ) {
			print "This is the about page.";
		}
		
		
		function notFound( $args = false) {
			print "Not Found";
		}
	}