// JavaScript Document
// hilfsroutinen für Bootstrap js
// 1. Toggle Click ereignis
console.log('pbboottrap.js gerufen');
if (typeof jQuery !== 'undefined') {
    console.log('jQuery is loaded');
    console.log(jQuery.fn.jquery);  
  } else {
    console.log('jQuery is not loaded');
  }
var myAlias = jQuery.noConflict();

myAlias(function($) {    // Toggle-Klickereignis für den Navbar-Toggler
console.log('funktion gerufen');
    $('.dropdown-toggle').dropdown();
    $(".navbar-toggler").click(function() {    
      $(".navbar-collapse").toggleClass("show");
      console.log ('click gerufen');
    });                // Handler, wenn dropdown geklickt wird, damit submenus aufgehen
                       // angeblich ist das in bootstrap.bundle enthalten
/*
    document.querySelectorAll('[data-toggle="dropdown"]').forEach(function (dropdown) {
//console.log('eventlistener fuer dropdown');
        dropdown.addEventListener('click', function () {
        //dropdown.parentElement.classList.toggle('show');
        var clickedElement = this;
        console.log('Geklicktes Element:', clickedElement);
        var elements = clickedElement.parentElement.querySelectorAll('.dropdown-menu');   // eins zurueck muesste das li element sein
                                                                                          // das geclickte ist oft das <span> element
        var count = elements.length;
//console.log('Anzahl der Elemente dropdown-menu:', count);        
        elements.forEach(function(element) {
//console.log('data-toggle dropdown',element);
          $(element).toggleClass("show");
        });        
      });
    });
*/
});


