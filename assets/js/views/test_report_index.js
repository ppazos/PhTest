
$(document).ready(function() {

$("li.nav-item").on("click",function(e){
  var id_li = $("a", this).attr("id");
  var class_card = 'card_' + id_li;

  if (id_li === 'dashboard') 
  {    
    $('#cardSummaryTables').show();
    $('#headCardSummary').show();
    $(this).addClass("active");
    $('#title_suite').hide();
    $('#Card_suites').hide();
    $('#collapseUtilities_' + id_li).collapse('hide');
  }
  else
  if (id_li === class_card.substring(5)) 
  {
    $('#Card_suites').show();
    $('#title_suite').show();
    $('#title_suite').html(id_li);
    $('#Card_suites').find('.' + class_card).show();
    $(this).addClass("active");

    $('.suites_test').not('.' + class_card).hide();
    $('#headCardSummary').hide();
    $('#cardSummaryTables').hide();
  }
  $('.nav-item').not(this).removeClass("active"); 
});

});