
/* 왼쪽메뉴 열닫 */
jQuery(function($){

	// Side Menu
    var leftmenu = $('div.leftmenu');
	var sItem = leftmenu.find('>ul>li');
	var ssItem = leftmenu.find('>ul>li>ul>li');
	var lastEvent = null;
	 
	sItem.find('>ul').css('display','none');
    leftmenu.find('>ul>li>ul>li[class=active]').parents('li').attr('class','active');
	leftmenu.find('>ul>li[class=active]').find('>ul').css('display','block');
	
	function leftmenuToggle(event){
		var t = $(this);
		if (this == lastEvent) return false;
		lastEvent = this;
		setTimeout(function(){ lastEvent=null }, 200);
		
		if (t.next('ul').is(':hidden')) {
			sItem.find('>ul').slideUp(0);
			t.next('ul').slideDown(0);
			
		} else if(!t.next('ul').length) {
			sItem.find('>ul').slideUp(0);
		} else {
			t.next('ul').slideUp(0);
		}
		
		 if (t.parent('li').hasClass('active')){
			 t.parent('li').removeClass('active');
		 } else {
			 sItem.removeClass('active');
			 t.parent('li').addClass('active');
		 }
	}

	sItem.find('>a').click(leftmenuToggle).focus(leftmenuToggle);
    function subMenuActive(){
        ssItem.removeClass('active');
        $(this).parent(ssItem).addClass('active');
    };
    ssItem.find('>a').click(subMenuActive).focus(subMenuActive);


});




/* 탭메뉴 */

$(document).ready(function() {

	//When page loads...
	$(".tab_content").hide(); //Hide all content
	$("ul.tab_nav li:first").addClass("active").show(); //Activate first tab
	$(".tab_content:first").show(); //Show first tab content

	//On Click Event
	$("ul.tab_nav li").click(function() {

		$("ul.tab_nav li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		$(".tab_content").hide(); //Hide all tab content

		var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
		$(activeTab).fadeIn(); //Fade in the active ID content
		return false;
	});

});



/* 탭메뉴2 */

$(document).ready(function() {

	//When page loads...
	$(".tab_content2").hide(); //Hide all content
	$("ul.tab_nav2 li:first").addClass("active").show(); //Activate first tab
	$(".tab_content2:first").show(); //Show first tab content

	//On Click Event
	$("ul.tab_nav2 li").click(function() {

		$("ul.tab_nav2 li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		$(".tab_content2").hide(); //Hide all tab content

		var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
		$(activeTab).fadeIn(); //Fade in the active ID content
		return false;
	});

});


$(function() {
	// 검색 scale
	$('form[name=frm_search] select[name=scale]').on('change', function(e) {
		e.preventDefault();

		$(this).closest('form[name=frm_search]').submit();
	});

	$('.btn-popup-close').on('click', function(e) {
		e.preventDefault();

		if (window.opener) {
			self.close();
		}
	});

	$('.menu_all a.deny,#leftmenu a.deny').on('click', function(e) {
		e.preventDefault();
		return alert('권한이 없습니다. 관리자에게 문의하세요.');
	});

	$('#btn-logout').on('click', function(e) {
		e.preventDefault();

        location.href = _WEB_ROOT + '/login.php?act=logout';
	});

});

var popupOpen = function (url, title, width = 500, height = 300) {
    if (url.length == 0 || title.length == 0) {
        return false;
    }
    var popup = window.open(url, title, "width="+width+",height="+height);
    popup.focus();
};
