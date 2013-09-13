/*
/	script.js for Project 2 - 539 (Programming for the World Wide Web aka Server Side Programming)
/	
/	Vlad Ionescu 2013, RIT Student, 2nd year Information Security & Forensics major
/
/	This file contains support functions for the Project 1 news site.
/	Issues/comments can be addressed to vxi6514@rit.edu
*/
$(document).ready(function() {
	// check the external feeds boxes
	checkSelected("selected_feeds\\[\\]", 3);
	$("input[type=checkbox][name=selected_feeds\\[\\]]").click(function() {
		checkSelected("selected_feeds\\[\\]", 3);
	});

	// check the services boxes
	checkSelected("selected_services\\[\\]", 10);
	$("input[type=checkbox][name=selected_services\\[\\]]").click(function() {
		checkSelected("selected_services\\[\\]", 10);
	});
});

// checkSelected takes the name of the checkbox group and the max allowed number
// and checks that the number checked does not exceed, if it does, disable the
// unchecked ones. if it does not, it makes sure the boxes are unchecked
function checkSelected(name, maxChecked) {
		var bol = $("input[type=checkbox][name=" + name + "]:checked").length >= maxChecked;     
		$("input[type=checkbox][name=" + name + "]").not(":checked").attr("disabled",bol);
}