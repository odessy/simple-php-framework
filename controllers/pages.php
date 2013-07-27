<?php
	class Pages extends Controller
	{
		function homePage( $args = false ) {
		
			$this->setview("home");
			
		}
		
		function about( $args = false ) {
			print "This is the about page.";
		}
		
		
		function notFound( $args = false) {
			die("Not Found");
		}
	}