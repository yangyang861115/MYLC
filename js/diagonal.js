jQuery(document).ready(function($) {

window.onresize = function(event) {
  set_diagonals();
}
function set_diagonals(){
  var width = $('.s_1').width();
  var height = width * 0.05;
  //console.log(width, height);
  $('.diagonal').css('border-left-width', width + 100);
  $('.diagonal_top').css('border-top-width', height);
  $('.diagonal_bottom').css('border-bottom-width', height);
}
set_diagonals();
});